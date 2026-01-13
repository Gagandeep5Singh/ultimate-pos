<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KamalTecPayment extends Model
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
        'paid_on' => 'date',
        'amount' => 'decimal:4',
    ];

    /**
     * Get the sale that owns this payment.
     */
    public function sale()
    {
        return $this->belongsTo(\App\KamalTecSale::class, 'kamal_tec_sale_id');
    }
}
