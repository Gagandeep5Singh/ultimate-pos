<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KamalTecSale extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'decimal:4',
        'commission_value' => 'decimal:4',
        'commission_amount' => 'decimal:4',
        'paid_amount' => 'decimal:4',
        'due_amount' => 'decimal:4',
    ];

    /**
     * Get the business that owns the sale.
     */
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    /**
     * Get the location associated with the sale.
     */
    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    /**
     * Get the contact (customer) associated with the sale.
     */
    public function contact()
    {
        return $this->belongsTo(\App\Contact::class);
    }

    /**
     * Get the Kamal Tec customer associated with the sale.
     */
    public function customer()
    {
        return $this->belongsTo(\App\KamalTecCustomer::class);
    }

    /**
     * Get the user who created the sale.
     */
    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    /**
     * Get the sale lines for this sale.
     */
    public function saleLines()
    {
        return $this->hasMany(\App\KamalTecSaleLine::class);
    }

    /**
     * Get the payments for this sale.
     */
    public function payments()
    {
        return $this->hasMany(\App\KamalTecPayment::class);
    }

    /**
     * Get the total amount paid (sum of all payments)
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    /**
     * Get the due commission (commission amount - payments received)
     */
    public function getDueCommissionAttribute()
    {
        $totalPaid = $this->payments()->sum('amount');
        $dueCommission = $this->commission_amount - $totalPaid;
        return max(0, $dueCommission); // Never negative
    }

    /**
     * Update paid and due amounts based on commission
     * The paid amount represents the commission received (not the full sale amount)
     * The due amount represents what the customer still owes to the 3rd party
     */
    public function updatePaymentStatus()
    {
        // Calculate total paid from payments
        $totalPaid = $this->payments()->sum('amount');
        
        // Paid amount = sum of payments (what Kamal Tec has actually received)
        $this->paid_amount = $totalPaid;
        
        // Due amount = total sale amount - commission amount (what customer owes to 3rd party)
        $this->due_amount = $this->total_amount - $this->commission_amount;
        
        // Calculate due commission
        $dueCommission = $this->commission_amount - $totalPaid;
        
        // Auto-update status: close when commission is fully paid (due commission = 0)
        // Only auto-update if status is not 'pending' or 'cancelled'
        // Also ensure sales without Floa Ref stay in 'pending'
        if ($this->status != 'pending' && $this->status != 'cancelled') {
            // If no Floa Ref, should be pending (installment not done yet)
            if (empty($this->floa_ref)) {
                $this->status = 'pending';
            } elseif ($dueCommission <= 0) {
                $this->status = 'closed';
            } elseif ($this->paid_amount > 0) {
                $this->status = 'open'; // Keep as open if partially paid and has Floa Ref
            }
        } elseif ($this->status == 'open' && empty($this->floa_ref)) {
            // If somehow in 'open' status but no Floa Ref, revert to pending
            $this->status = 'pending';
        }
        
        $this->save();
    }
}
