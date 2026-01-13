<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KamalTecSaleLine extends Model
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
        'qty' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'line_total' => 'decimal:4',
    ];

    /**
     * Get the sale that owns this line.
     */
    public function sale()
    {
        return $this->belongsTo(\App\KamalTecSale::class, 'kamal_tec_sale_id');
    }

    /**
     * Get the product associated with this line.
     */
    public function product()
    {
        return $this->belongsTo(\App\Product::class);
    }
}
