<?php

namespace App\Exports;

use App\Product;
use Maatwebsite\Excel\Concerns\FromArray;

class ProductDetailsExport implements FromArray
{
    public function array(): array
    {
        $business_id = request()->session()->get('user.business_id');

        $products = Product::where('business_id', $business_id)
            ->with(['brand', 'category', 'sub_category'])
            ->select('products.id', 'products.name', 'products.sku', 'products.brand_id', 'products.category_id', 'products.sub_category_id')
            ->get();

        // Headers
        $products_array = [[
            'SKU',
            'PRODUCT NAME',
            'BRAND',
            'CATEGORY',
            'SUB-CATEGORY',
        ]];

        foreach ($products as $product) {
            $products_array[] = [
                $product->sku,
                $product->name,
                $product->brand->name ?? '',
                $product->category->name ?? '',
                $product->sub_category->name ?? '',
            ];
        }

        return $products_array;
    }
}
