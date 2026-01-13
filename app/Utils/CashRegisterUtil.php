<?php

namespace App\Utils;

use App\CashRegister;
use App\CashRegisterTransaction;
use App\Transaction;
use App\TransactionPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashRegisterUtil extends Util
{
    /**
     * Returns number of opened Cash Registers for the
     * current location (shared register for all users at location)
     *
     * @param int $location_id
     * @return int
     */
    public function countOpenedRegister($location_id = null)
    {
        $business_id = auth()->user()->business_id;
        
        $query = CashRegister::where('business_id', $business_id)
                                ->where('status', 'open');
        
        if (!empty($location_id)) {
            // Check ONLY for register with this specific location_id
            // Each location should have its own independent register
            $query->where('location_id', $location_id);
        } else {
            // If no location_id provided, check for any open register (including NULL location_id)
            // This handles backward compatibility
        }
        
        $count = $query->count();

        return $count;
    }

    /**
     * Adds sell payments to currently opened cash register (location-based shared register)
     *
     * @param object/int $transaction
     * @param  array  $payments
     * @return bool
     */
    public function addSellPayments($transaction, $payments)
    {
        // Get location from transaction
        $location_id = $transaction->location_id ?? null;
        $business_id = $transaction->business_id ?? auth()->user()->business_id;
        
        // Find open register for this specific location (shared register)
        $register = CashRegister::where('business_id', $business_id)
                                ->where('status', 'open');
        
        if (!empty($location_id)) {
            // Check ONLY for register with this specific location_id
            // Each location should have its own independent register
            $register->where('location_id', $location_id);
        }
        
        $register = $register->first();
        
        if (empty($register)) {
            Log::warning('No open cash register found for location: ' . $location_id);
            return false;
        }
        
        $payments_formatted = [];
        foreach ($payments as $payment) {
            // Use actual payment amount for all payment methods
            // This ensures we correctly track each payment method separately (cash vs bank transfer, etc.)
            // Change returns are handled via is_return flag and will be negative
            $payment_amount = (isset($payment['is_return']) && $payment['is_return'] == 1) ? (-1 * $this->num_uf($payment['amount'])) : $this->num_uf($payment['amount']);
            
            if ($payment_amount != 0) {
                $type = 'credit';
                if ($transaction->type == 'expense') {
                    $type = 'debit';
                }

                $payments_formatted[] = new CashRegisterTransaction([
                    'amount' => abs($payment_amount), // Store absolute value, type handles credit/debit
                    'pay_method' => $payment['method'],
                    'type' => $type,
                    'transaction_type' => $transaction->type,
                    'transaction_id' => $transaction->id,
                ]);
            }
        }

        if (! empty($payments_formatted)) {
            $register->cash_register_transactions()->saveMany($payments_formatted);
        }

        // After updates, resync register for sells
        if ($transaction->type == 'sell' && $transaction->status == 'final') {
            $this->resyncSellPayments($transaction->fresh('payment_lines'));
        }

        return true;
    }

    /**
     * Adds sell payments to currently opened cash register
     *
     * @param object/int $transaction
     * @param  array  $payments
     * @return bool
     */
    public function updateSellPayments($status_before, $transaction, $payments)
    {
        // Get location from transaction for shared register
        $location_id = $transaction->location_id ?? null;
        $business_id = $transaction->business_id ?? auth()->user()->business_id;
        
        // Find open register for this location (shared register)
        $register = CashRegister::where('business_id', $business_id)
                                ->where('status', 'open');
        
        if (!empty($location_id)) {
            $register->where('location_id', $location_id);
        }
        
        $register = $register->first();
        
        if (empty($register)) {
            Log::warning('No open cash register found for updating payment. Location: ' . $location_id);
            return false;
        }
        //If draft -> final then add all
        //If final -> draft then refund all
        //If final -> final then rebuild from stored payment lines to avoid duplication/format issues
        if ($status_before == 'draft' && $transaction->status == 'final') {
            $this->addSellPayments($transaction, $payments);
        } elseif ($status_before == 'final' && $transaction->status == 'draft') {
            $this->refundSell($transaction);
        } elseif ($status_before == 'final' && $transaction->status == 'final') {
            // remove existing entries for this transaction and rebuild from DB
            CashRegisterTransaction::where('transaction_id', $transaction->id)->delete();
            $this->resyncSellPayments($transaction->fresh('payment_lines'));
        }

        return true;
    }

    /**
     * Resync cash register transactions for a sell based on current payment lines.
     * Useful after editing payments so register reports stay accurate.
     * If editing a past sale, uses the register from the original sale date.
     * If editing a current sale, uses the current open register.
     *
     * @param  \App\Transaction $transaction
     * @return void
     */
    public function resyncSellPayments($transaction)
    {
        if (empty($transaction) || $transaction->type != 'sell') {
            return;
        }

        // Get location and business
        $location_id = $transaction->location_id ?? null;
        $business_id = $transaction->business_id ?? auth()->user()->business_id;

        // Check if this is a past sale (transaction date is before today)
        $transaction_date = \Carbon\Carbon::parse($transaction->transaction_date);
        $is_past_sale = $transaction_date->isPast() && !$transaction_date->isToday();

        $register = null;
        
        if ($is_past_sale) {
            // For past sales, find the register that was open on the transaction date
            $register = CashRegister::where('business_id', $business_id)
                ->where('location_id', $location_id)
                ->whereDate('created_at', '<=', $transaction_date->format('Y-m-d'))
                ->where(function($query) use ($transaction_date) {
                    $query->whereNull('closed_at')
                        ->orWhereDate('closed_at', '>=', $transaction_date->format('Y-m-d'));
                })
                ->orderBy('created_at', 'desc')
                ->first();
            
            // If no register found for that date, try to find a closed register from that date
            if (empty($register)) {
                $register = CashRegister::where('business_id', $business_id)
                    ->where('location_id', $location_id)
                    ->where('status', 'closed')
                    ->whereDate('created_at', '<=', $transaction_date->format('Y-m-d'))
                    ->whereDate('closed_at', '>=', $transaction_date->format('Y-m-d'))
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        } else {
            // For current sales, use the current open register
            $register = CashRegister::where('business_id', $business_id)
                                ->where('status', 'open');

            if (!empty($location_id)) {
                $register->where('location_id', $location_id);
            }

            $register = $register->first();
        }

        if (empty($register)) {
            Log::warning('No cash register found for resync. Location: ' . $location_id . ', Date: ' . $transaction_date->format('Y-m-d'));
            return;
        }

        // Ensure payment lines are loaded
        $transaction->loadMissing('payment_lines');

        // For past sales: only resync payments that were made on the original sale date
        // Payments added later should remain in today's register (they were added via addCreditSalePayment)
        // We identify payments from sale date by checking if paid_on matches transaction_date
        if ($is_past_sale) {
            // Delete only cash register transactions that have the transaction date as created_at
            // This identifies payments that were synced to the past register
            // Payments added later will have today's date and won't be deleted
            CashRegisterTransaction::where('transaction_id', $transaction->id)
                ->whereDate('created_at', $transaction_date->format('Y-m-d'))
                ->delete();
        } else {
            // For current sales, remove all existing entries and recreate
            CashRegisterTransaction::where('transaction_id', $transaction->id)->delete();
        }

        $payments_formatted = [];
        foreach ($transaction->payment_lines as $payment) {
            // Amount is already stored in DB; avoid re-parsing formatted strings.
            $amount = $payment->amount ?? 0;

            // If payment is marked as return, treat as negative so it shows as debit.
            if (isset($payment->is_return) && $payment->is_return) {
                $amount = -1 * $amount;
            }

            if ($amount == 0) {
                continue;
            }

            // For past sales, only sync payments made on the original sale date
            // Payments added later (paid_on is today or later) should stay in today's register
            if ($is_past_sale) {
                $payment_date = \Carbon\Carbon::parse($payment->paid_on ?? $payment->created_at);
                // Skip payments that were added later (they should stay in today's register)
                if ($payment_date->format('Y-m-d') != $transaction_date->format('Y-m-d')) {
                    continue;
                }
            }

            $type = $amount > 0 ? 'credit' : 'debit';
            
            $cr_transaction = new CashRegisterTransaction([
                'amount' => abs($amount),
                'pay_method' => $payment->method,
                'type' => $type,
                'transaction_type' => $transaction->type,
                'transaction_id' => $transaction->id,
            ]);
            
            // If this is a past sale, set the created_at to the transaction date
            if ($is_past_sale) {
                $cr_transaction->created_at = $transaction_date;
                $cr_transaction->updated_at = $transaction_date;
            }
            
            $payments_formatted[] = $cr_transaction;
        }

        if (!empty($payments_formatted)) {
            $register->cash_register_transactions()->saveMany($payments_formatted);
        }
    }

    /**
     * Adds a new payment for a credit sale to the current day's register.
     * This is used when a payment is added later (not during sale creation).
     * The payment will appear in today's register report, not the original sale date.
     *
     * @param  \App\Transaction $transaction
     * @param  \App\TransactionPayment $payment
     * @return bool
     */
    public function addCreditSalePayment($transaction, $payment)
    {
        if (empty($transaction) || $transaction->type != 'sell') {
            return false;
        }

        // Get location and business
        $location_id = $transaction->location_id ?? null;
        $business_id = $transaction->business_id ?? auth()->user()->business_id;

        // Always use the current open register (today's register)
        $register = CashRegister::where('business_id', $business_id)
                                ->where('status', 'open');

        if (!empty($location_id)) {
            $register->where('location_id', $location_id);
        }

        $register = $register->first();

        if (empty($register)) {
            Log::warning('No open cash register found for adding credit sale payment. Location: ' . $location_id);
            return false;
        }

        // Calculate amount
        $amount = $payment->amount ?? 0;
        if ($amount == 0) {
            return false;
        }

        // Create cash register transaction for today
        $cr_transaction = new CashRegisterTransaction([
            'cash_register_id' => $register->id,
            'amount' => abs($amount),
            'pay_method' => $payment->method,
            'type' => 'credit',
            'transaction_type' => $transaction->type,
            'transaction_id' => $transaction->id,
        ]);

        $cr_transaction->save();

        return true;
    }


    /**
     * Adds sell return payment to cash register
     * All sell return payments are deducted from today's register
     * This ensures when a client returns a product and payment is made, it shows in today's register
     *
     * @param \App\Transaction $sell_return
     * @param \App\TransactionPayment $payment
     * @return bool
     */
    public function addSellReturnPayment($sell_return, $payment)
    {
        if (empty($sell_return) || $sell_return->type != 'sell_return') {
            return false;
        }

        $business_id = $sell_return->business_id ?? auth()->user()->business_id;
        $location_id = $sell_return->location_id ?? null;
        
        // Always use today's open register for all sell return payments
        $register = CashRegister::where('business_id', $business_id)
                                ->where('status', 'open');
        
        if (!empty($location_id)) {
            $register->where('location_id', $location_id);
        }
        
        $register = $register->first();
        
        if (empty($register)) {
            Log::warning('No open cash register found for sell return payment. Location: ' . $location_id);
            return false;
        }
        
        // Calculate payment amount (negative for returns)
        $payment_amount = -1 * abs($payment->amount);
        
        // Create cash register transaction for today
        $cr_transaction = new CashRegisterTransaction([
            'cash_register_id' => $register->id,
            'amount' => abs($payment_amount),
            'pay_method' => $payment->method,
            'type' => 'debit', // Returns are debits
            'transaction_type' => 'refund', // Sell returns are refunds
            'transaction_id' => $sell_return->id,
        ]);
        
        $cr_transaction->save();
        
        return true;
    }

    /**
     * Refunds all payments of a sell
     *
     * @param object/int $transaction
     * @return bool
     */
    public function refundSell($transaction)
    {
        // Get location from transaction for shared register
        $location_id = $transaction->location_id ?? null;
        $business_id = $transaction->business_id ?? auth()->user()->business_id;
        
        // Find open register for this location (shared register)
        $register = CashRegister::where('business_id', $business_id)
                                ->where('status', 'open');
        
        if (!empty($location_id)) {
            $register->where('location_id', $location_id);
        }
        
        $register = $register->first();
        
        if (empty($register)) {
            Log::warning('No open cash register found for refund. Location: ' . $location_id);
            return false;
        }

        $total_payment = CashRegisterTransaction::where('transaction_id', $transaction->id)
                            ->select(
                                DB::raw("SUM(IF(pay_method='cash', IF(type='credit', amount, -1 * amount), 0)) as total_cash"),
                                DB::raw("SUM(IF(pay_method='card', IF(type='credit', amount, -1 * amount), 0)) as total_card"),
                                DB::raw("SUM(IF(pay_method='cheque', IF(type='credit', amount, -1 * amount), 0)) as total_cheque"),
                                DB::raw("SUM(IF(pay_method='bank_transfer', IF(type='credit', amount, -1 * amount), 0)) as total_bank_transfer"),
                                DB::raw("SUM(IF(pay_method='other', IF(type='credit', amount, -1 * amount), 0)) as total_other"),
                                DB::raw("SUM(IF(pay_method='custom_pay_1', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_1"),
                                DB::raw("SUM(IF(pay_method='custom_pay_2', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_2"),
                                DB::raw("SUM(IF(pay_method='custom_pay_3', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_3"),
                                DB::raw("SUM(IF(pay_method='custom_pay_4', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_4"),
                                DB::raw("SUM(IF(pay_method='custom_pay_5', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_5"),
                                DB::raw("SUM(IF(pay_method='custom_pay_6', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_6"),
                                DB::raw("SUM(IF(pay_method='custom_pay_7', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_7")
                            )->first();
        $refunds = [
            'cash' => $total_payment->total_cash,
            'card' => $total_payment->total_card,
            'cheque' => $total_payment->total_cheque,
            'bank_transfer' => $total_payment->total_bank_transfer,
            'other' => $total_payment->total_other,
            'custom_pay_1' => $total_payment->total_custom_pay_1,
            'custom_pay_2' => $total_payment->total_custom_pay_2,
            'custom_pay_3' => $total_payment->total_custom_pay_3,
            'custom_pay_4' => $total_payment->total_custom_pay_4,
            'custom_pay_5' => $total_payment->total_custom_pay_5,
            'custom_pay_6' => $total_payment->total_custom_pay_6,
            'custom_pay_7' => $total_payment->total_custom_pay_7,
        ];
        $refund_formatted = [];
        foreach ($refunds as $key => $val) {
            if ($val > 0) {
                $refund_formatted[] = new CashRegisterTransaction([
                    'amount' => $val,
                    'pay_method' => $key,
                    'type' => 'debit',
                    'transaction_type' => 'refund',
                    'transaction_id' => $transaction->id,
                ]);
            }
        }

        if (! empty($refund_formatted)) {
            $register->cash_register_transactions()->saveMany($refund_formatted);
        }

        return true;
    }

    /**
     * Retrieves details of given rigister id else currently opened register
     *
     * @param $register_id default null
     * @return object
     */
    public function getRegisterDetails($register_id = null, $location_id = null)
{
    $query = CashRegister::leftjoin(
        'cash_register_transactions as ct',
        'ct.cash_register_id',
        '=',
        'cash_registers.id'
    )
    ->join(
        'users as u',
        'u.id',
        '=',
        'cash_registers.user_id'
    )
    ->leftJoin(
        'business_locations as bl',
        'bl.id',
        '=',
        'cash_registers.location_id'
    );
    if (empty($register_id)) {
        // Get shared register for location (not user-specific)
        $business_id = auth()->user()->business_id;
        $query->where('cash_registers.business_id', $business_id)
            ->where('cash_registers.status', 'open');
        
        // Use provided location_id or get from session
        if (empty($location_id)) {
            $location_id = request()->session()->get('user.location_id');
        }
        
        if (!empty($location_id)) {
            // Check for register with this location_id OR NULL location_id (backward compatibility)
            $query->where(function($q) use ($location_id) {
                $q->where('cash_registers.location_id', $location_id)
                  ->orWhereNull('cash_registers.location_id');
            });
        }
    } else {
        $query->where('cash_registers.id', $register_id);
    }

    $register_details = $query->select(
        'cash_registers.id',
        'cash_registers.created_at as open_time',
        'cash_registers.closed_at as closed_at',
        'cash_registers.user_id',
        'cash_registers.closing_note',
        'cash_registers.location_id',
        'cash_registers.denominations',
        DB::raw("SUM(IF(transaction_type='initial', amount, 0)) as cash_in_hand"),
        // FIX: Use transaction final_total instead of payment amount for sales
        // This ensures we count actual sale amount (9.99) not payment amount (20.00)
        DB::raw("COALESCE((
            SELECT SUM(DISTINCT t.final_total)
            FROM cash_register_transactions crt
            INNER JOIN transactions t ON t.id = crt.transaction_id
            WHERE crt.cash_register_id = cash_registers.id
            AND crt.transaction_type = 'sell'
        ), 0) + COALESCE(SUM(IF(transaction_type='refund', -1 * amount, 0)), 0) as total_sale"),
        DB::raw("SUM(IF(transaction_type='expense', IF(transaction_type='refund', -1 * amount, amount), 0)) as total_expense"),
        // Calculate cash: sum actual cash payment amounts from cash_register_transactions
        // Account for type (credit/debit) - credit adds to cash, debit subtracts from cash
        // This matches the register report calculation logic
        DB::raw("SUM(IF(pay_method='cash', IF(transaction_type='sell', IF(ct.type='credit', amount, -1*amount), 0), 0)) - SUM(IF(pay_method='cash', IF(transaction_type='refund', amount, 0), 0)) as total_cash"),
        DB::raw("SUM(IF(pay_method='cash', IF(transaction_type='expense', amount, 0), 0)) as total_cash_expense"),
        // Calculate card: sales - refunds (expenses are separate)
        DB::raw("SUM(IF(pay_method='card', IF(transaction_type='sell', amount, 0), 0)) - SUM(IF(pay_method='card', IF(transaction_type='refund', amount, 0), 0)) as total_card"),
        DB::raw("SUM(IF(pay_method='card', IF(transaction_type='expense', amount, 0), 0)) as total_card_expense"),
        // Calculate bank transfer: sales - refunds (expenses are separate)
        DB::raw("SUM(IF(pay_method='bank_transfer', IF(transaction_type='sell', amount, 0), 0)) - SUM(IF(pay_method='bank_transfer', IF(transaction_type='refund', amount, 0), 0)) as total_bank_transfer"),
        DB::raw("SUM(IF(pay_method='bank_transfer', IF(transaction_type='expense', amount, 0), 0)) as total_bank_transfer_expense"),
        // Calculate advance: sales - refunds (expenses are separate)
        DB::raw("SUM(IF(pay_method='advance', IF(transaction_type='sell', amount, 0), 0)) - SUM(IF(pay_method='advance', IF(transaction_type='refund', amount, 0), 0)) as total_advance"),
        DB::raw("SUM(IF(pay_method='advance', IF(transaction_type='expense', amount, 0), 0)) as total_advance_expense"),
        // Calculate custom payment methods: sales - refunds (expenses are separate)
        DB::raw("SUM(IF(pay_method='custom_pay_1', IF(transaction_type='sell', amount, 0), 0)) - SUM(IF(pay_method='custom_pay_1', IF(transaction_type='refund', amount, 0), 0)) as total_custom_pay_1"),
        DB::raw("SUM(IF(pay_method='custom_pay_2', IF(transaction_type='sell', amount, 0), 0)) - SUM(IF(pay_method='custom_pay_2', IF(transaction_type='refund', amount, 0), 0)) as total_custom_pay_2"),
        DB::raw("SUM(IF(pay_method='custom_pay_3', IF(transaction_type='sell', amount, 0), 0)) - SUM(IF(pay_method='custom_pay_3', IF(transaction_type='refund', amount, 0), 0)) as total_custom_pay_3"),
        DB::raw("SUM(IF(pay_method='custom_pay_4', IF(transaction_type='sell', amount, 0), 0)) - SUM(IF(pay_method='custom_pay_4', IF(transaction_type='refund', amount, 0), 0)) as total_custom_pay_4"),
        DB::raw("SUM(IF(pay_method='custom_pay_5', IF(transaction_type='sell', amount, 0), 0)) - SUM(IF(pay_method='custom_pay_5', IF(transaction_type='refund', amount, 0), 0)) as total_custom_pay_5"),
        DB::raw("SUM(IF(pay_method='custom_pay_6', IF(transaction_type='sell', amount, 0), 0)) - SUM(IF(pay_method='custom_pay_6', IF(transaction_type='refund', amount, 0), 0)) as total_custom_pay_6"),
        DB::raw("SUM(IF(pay_method='custom_pay_7', IF(transaction_type='sell', amount, 0), 0)) - SUM(IF(pay_method='custom_pay_7', IF(transaction_type='refund', amount, 0), 0)) as total_custom_pay_7"),
        DB::raw("SUM(IF(transaction_type='refund', amount, 0)) as total_refund"),
        DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='cash', amount, 0), 0)) as total_cash_refund"),
        DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='card', amount, 0), 0)) as total_card_refund"),
        DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='bank_transfer', amount, 0), 0)) as total_bank_transfer_refund"),
        DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='advance', amount, 0), 0)) as total_advance_refund"),
        DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_1', amount, 0), 0)) as total_custom_pay_1_refund"),
        DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_2', amount, 0), 0)) as total_custom_pay_2_refund"),
        DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_3', amount, 0), 0)) as total_custom_pay_3_refund"),
        DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_4', amount, 0), 0)) as total_custom_pay_4_refund"),
        DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_5', amount, 0), 0)) as total_custom_pay_5_refund"),
        DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_6', amount, 0), 0)) as total_custom_pay_6_refund"),
        DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_7', amount, 0), 0)) as total_custom_pay_7_refund"),
        DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as user_name"),
        'u.email',
        'bl.name as location_name'
    )->first();

    return $register_details;
}

    /**
     * Get the transaction details for a particular register
     *
     * @param $user_id int (who opened the register, for backward compatibility)
     * @param $open_time datetime
     * @param $close_time datetime
     * @param $is_types_of_service_enabled bool
     * @param $location_id int (optional, to get all transactions for location)
     * @return array
     */
    public function getRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled = false, $location_id = null)
    {
        // Get all transactions for the location (shared register), not just one user
        $product_details_by_brand = Transaction::whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0);
        
        if (!empty($location_id)) {
            $product_details_by_brand->where('transactions.location_id', $location_id);
        }
        
        $product_details_by_brand = $product_details_by_brand
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('products AS P', 'TSL.product_id', '=', 'P.id')
                ->where('TSL.children_type', '!=', 'combo')
                ->leftjoin('brands AS B', 'P.brand_id', '=', 'B.id')
                ->groupBy('B.id')
                ->select(
                    'B.name as brand_name',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax*TSL.quantity) as total_amount')
                )
                ->orderByRaw('CASE WHEN brand_name IS NULL THEN 2 ELSE 1 END, brand_name')
                ->get();

        // Get all product details for the location
        $product_details = Transaction::whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0);
        
        if (!empty($location_id)) {
            $product_details->where('transactions.location_id', $location_id);
        }
        
        $product_details = $product_details
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('variations AS v', 'TSL.variation_id', '=', 'v.id')
                ->join('product_variations AS pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products AS p', 'v.product_id', '=', 'p.id')
                ->where('TSL.children_type', '!=', 'combo')
                ->groupBy('v.id')
                ->select(
                    'p.name as product_name',
                    'p.type as product_type',
                    'v.name as variation_name',
                    'pv.name as product_variation_name',
                    'v.sub_sku as sku',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax*TSL.quantity) as total_amount')
                )
                ->get();

        //If types of service
        $types_of_service_details = null;
        if ($is_types_of_service_enabled) {
            $types_of_service_query = Transaction::whereBetween('transaction_date', [$open_time, $close_time])
                ->where('transactions.is_direct_sale', 0)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final');
            
            if (!empty($location_id)) {
                $types_of_service_query->where('transactions.location_id', $location_id);
            }
            
            $types_of_service_details = $types_of_service_query
                ->leftjoin('types_of_services AS tos', 'tos.id', '=', 'transactions.types_of_service_id')
                ->groupBy('tos.id')
                ->select(
                    'tos.name as types_of_service_name',
                    DB::raw('SUM(final_total) as total_sales')
                )
                ->orderBy('total_sales', 'desc')
                ->get();
        } else {
            $types_of_service_details = null;
        }

        // Get all transaction details for the location (including sell returns)
        // Include both sell and sell_return transactions
        $transaction_details = Transaction::whereBetween('transactions.created_at', [$open_time, $close_time])
                ->whereIn('transactions.type', ['sell', 'sell_return'])
                ->where('transactions.status', 'final')
                ->where(function($query) {
                    $query->where('transactions.type', 'sell_return')
                          ->orWhere(function($q) {
                              $q->where('transactions.type', 'sell')
                                ->where('transactions.is_direct_sale', 0);
                          });
                });
        
        if (!empty($location_id)) {
            $transaction_details->where('transactions.location_id', $location_id);
        }
        
        $transaction_details = $transaction_details
                ->select(
                    DB::raw('SUM(IF(type = "sell", tax_amount, -tax_amount)) as total_tax'),
                    DB::raw('SUM(IF(type = "sell", IF(discount_type = "percentage", total_before_tax*discount_amount/100, discount_amount), -IF(discount_type = "percentage", total_before_tax*discount_amount/100, discount_amount))) as total_discount'),
                    DB::raw('SUM(IF(type = "sell", final_total, -final_total)) as total_sales'),
                    DB::raw('SUM(IF(type = "sell", shipping_charges, -shipping_charges)) as total_shipping_charges'),
                    // FIX: Calculate actual credit sales (unpaid sales only, not non-cash payment methods)
                    // Only count sell transactions for credit sales, not sell returns
                    DB::raw('SUM(IF(type = "sell" AND payment_status IN ("due", "partial"), final_total, 0)) as credit_sales')
                )
                ->first();

        return ['product_details_by_brand' => $product_details_by_brand,
            'transaction_details' => $transaction_details,
            'types_of_service_details' => $types_of_service_details,
            'product_details' => $product_details,
        ];
    }

    /**
     * Retrieves the currently opened cash register for the location (shared register)
     *
     * @param $int user_id (optional, for backward compatibility)
     * @param $int location_id (optional)
     * @return obj
     */
    public function getCurrentCashRegister($user_id = null, $location_id = null)
    {
        $business_id = auth()->user()->business_id;
        
        $query = CashRegister::where('business_id', $business_id)
                                ->where('status', 'open');
        
        // Use provided location_id or get from session
        if (empty($location_id)) {
            $location_id = request()->session()->get('user.location_id');
        }
        
        if (!empty($location_id)) {
            // Check ONLY for register with this specific location_id
            // Each location should have its own independent register
            $query->where('location_id', $location_id);
        }
        
        $register = $query->first();

        return $register;
    }
}
