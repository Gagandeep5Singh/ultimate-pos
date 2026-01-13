<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variation extends Model
{
    use SoftDeletes;

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
        'combo_variations' => 'array',
        'sale_price'       => 'float',   // <— added cast for sale_price
    ];

    /**
     * Extra attributes automatically appended to JSON / array.
     */
    protected $appends = ['display_price', 'discount_value', 'discount_percent'];

    public function product_variation()
    {
        return $this->belongsTo(\App\ProductVariation::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Product::class, 'product_id');
    }

    /**
     * Get the sell lines associated with the variation.
     */
    public function sell_lines()
    {
        return $this->hasMany(\App\TransactionSellLine::class);
    }

    /**
     * Get the location wise details of the the variation.
     */
    public function variation_location_details()
    {
        return $this->hasMany(\App\VariationLocationDetails::class);
    }

    /**
     * Get Selling price group prices.
     */
    public function group_prices()
    {
        return $this->hasMany(\App\VariationGroupPrice::class, 'variation_id');
    }

    public function media()
    {
        return $this->morphMany(\App\Media::class, 'model');
    }

    public function getFullNameAttribute()
    {
        $name = $this->product->name;
        if ($this->product->type == 'variable') {
            $name .= ' - ' . $this->product_variation->name . ' - ' . $this->name;
        }
        $name .= ' (' . $this->sub_sku . ')';

        return $name;
    }

    /**
     * Accessor: price that should be used (sale if set and cheaper, otherwise normal).
     */
    public function getDisplayPriceAttribute()
    {
        if (!is_null($this->sale_price) && $this->sale_price < $this->sell_price_inc_tax) {
            return $this->sale_price;
        }

        return $this->sell_price_inc_tax;
    }

    /**
     * Accessor: discount value (in currency).
     */
    public function getDiscountValueAttribute()
    {
        if (!is_null($this->sale_price) && $this->sale_price < $this->sell_price_inc_tax) {
            return $this->sell_price_inc_tax - $this->sale_price;
        }

        return 0;
    }

    /**
     * Accessor: discount in percent.
     */
    public function getDiscountPercentAttribute()
    {
        if ($this->discount_value > 0 && $this->sell_price_inc_tax > 0) {
            return round($this->discount_value / $this->sell_price_inc_tax * 100);
        }

        return 0;
    }
}
