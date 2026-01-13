<?php

namespace App\Http\Controllers;

use App\KamalTecPayment;
use App\KamalTecSale;
use App\Utils\Util;
use DB;
use Illuminate\Http\Request;

class KamalTecPaymentController extends Controller
{
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }
    /**
     * Show form to add payment
     */
    public function addPayment($sale_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $sale = KamalTecSale::where('business_id', $business_id)
            ->with(['contact', 'payments'])
            ->findOrFail($sale_id);

        return view('kamal_tec_sale.partials.add_payment', compact('sale'));
    }

    /**
     * Store payment
     */
    public function storePayment(Request $request, $sale_id)
    {
        try {
            // Validate
            $request->validate([
                'paid_on' => 'required',
                'amount' => 'required|numeric|min:0.01',
                'method' => 'required',
            ]);

            // Convert date format
            $paid_on = null;
            if (!empty($request->paid_on)) {
                try {
                    $date_str = trim($request->paid_on);
                    $date_format = session('business.date_format', 'd/m/Y');
                    try {
                        $paid_on = \Carbon\Carbon::createFromFormat($date_format, $date_str)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $paid_on = \Carbon\Carbon::parse($date_str)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    throw new \Exception('Invalid date format');
                }
            }

            DB::beginTransaction();

            $business_id = $request->session()->get('user.business_id');
            $sale = KamalTecSale::where('business_id', $business_id)->findOrFail($sale_id);

            $amount = $this->commonUtil->num_uf($request->amount);
            
            // Check if amount exceeds commission amount (what Kamal Tec can receive)
            if ($amount > $sale->commission_amount) {
                throw new \Exception('Payment amount cannot exceed commission amount: ' . number_format($sale->commission_amount, 2));
            }

            // Create payment (this tracks individual commission payments received)
            KamalTecPayment::create([
                'kamal_tec_sale_id' => $sale_id,
                'paid_on' => $paid_on,
                'amount' => $amount,
                'method' => $request->method,
                'note' => $request->note,
            ]);

            // Update sale payment status (paid_amount = commission_amount, due_amount = total - commission)
            $sale->updatePaymentStatus();

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('purchase.payment_added_success'),
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage() ?: __('messages.something_went_wrong'),
            ];
        }

        return redirect()->action([\App\Http\Controllers\KamalTecSaleController::class, 'show'], [$sale_id])->with('status', $output);
    }

    /**
     * Show form to edit payment
     */
    public function editPayment($payment_id)
    {
        $business_id = request()->session()->get('user.business_id');
        $payment = KamalTecPayment::whereHas('sale', function ($query) use ($business_id) {
            $query->where('business_id', $business_id);
        })->with(['sale.contact'])->findOrFail($payment_id);

        return view('kamal_tec_sale.partials.edit_payment', compact('payment'));
    }

    /**
     * Update payment
     */
    public function updatePayment(Request $request, $payment_id)
    {
        try {
            // Validate
            $request->validate([
                'paid_on' => 'required',
                'amount' => 'required|numeric|min:0.01',
                'method' => 'required',
            ]);

            // Convert date format
            $paid_on = null;
            if (!empty($request->paid_on)) {
                try {
                    $date_str = trim($request->paid_on);
                    $date_format = session('business.date_format', 'd/m/Y');
                    try {
                        $paid_on = \Carbon\Carbon::createFromFormat($date_format, $date_str)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $paid_on = \Carbon\Carbon::parse($date_str)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    throw new \Exception('Invalid date format');
                }
            }

            DB::beginTransaction();

            $business_id = $request->session()->get('user.business_id');
            $payment = KamalTecPayment::whereHas('sale', function ($query) use ($business_id) {
                $query->where('business_id', $business_id);
            })->findOrFail($payment_id);

            $sale = KamalTecSale::find($payment->kamal_tec_sale_id);
            $amount = $this->commonUtil->num_uf($request->amount);
            
            // Check if new amount exceeds commission amount (what Kamal Tec can receive)
            if ($amount > $sale->commission_amount) {
                throw new \Exception('Payment amount cannot exceed commission amount: ' . number_format($sale->commission_amount, 2));
            }

            // Update payment
            $payment->update([
                'paid_on' => $paid_on ?: $payment->paid_on,
                'amount' => $amount,
                'method' => $request->method,
                'note' => $request->note,
            ]);

            // Update sale payment status
            $sale->updatePaymentStatus();

            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage() ?: __('messages.something_went_wrong'),
            ];
        }

        return redirect()->action([\App\Http\Controllers\KamalTecSaleController::class, 'show'], [$payment->kamal_tec_sale_id])->with('status', $output);
    }

    /**
     * Delete payment
     */
    public function destroy($payment_id)
    {
        try {
            $business_id = request()->session()->get('user.business_id');
            $payment = KamalTecPayment::whereHas('sale', function ($query) use ($business_id) {
                $query->where('business_id', $business_id);
            })->findOrFail($payment_id);

            $sale_id = $payment->kamal_tec_sale_id;
            $payment->delete();

            // Update sale payment status
            $sale = KamalTecSale::find($sale_id);
            $sale->updatePaymentStatus();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->action([\App\Http\Controllers\KamalTecSaleController::class, 'show'], [$sale_id])->with('status', $output);
    }
}
