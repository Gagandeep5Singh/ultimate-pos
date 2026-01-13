<div class="tab-pane" id="psr_all_products_tab">
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-success btn-sm" id="export_all_products_btn">
                <i class="fa fa-download"></i> Export All Products
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped" 
        id="product_sell_report_all_products" style="width: 100%;">
            <thead>
                <tr>
                    <th>@lang('sale.product')</th>
                    <th>@lang('product.sku')</th>
                    <th>@lang('report.current_stock')</th>
                    @if(isset($location_list))
                        @foreach($location_list as $loc)
                            <th>{{ $loc->name }}</th>
                        @endforeach
                    @endif
                    <th>
                        @lang('purchase.business_location')
                        @show_tooltip(__('lang_v1.product_business_location_tooltip'))
                    </th>
                    @can('view_purchase_price')
                        <th>@lang('lang_v1.unit_perchase_price')</th>
                    @endcan
                    @can('access_default_selling_price')
                        <th>@lang('lang_v1.selling_price')</th>
                        <th>Discount</th>
                        <th>Sale Price</th>
                        <th>3x</th>
                        <th>Normal profit</th>
                        <th>after Klarna fees</th>
                    @endcan
                    <th>@lang('product.category')</th>
                    <th>@lang('product.sub_category')</th>
                    <th>@lang('product.brand')</th>
                    <th>@lang('product.tax')</th>
                </tr>
            </thead>
            <tfoot>
                <tr class="bg-gray font-17 footer-total text-center">
                    <td colspan="2"><strong>@lang('sale.total'):</strong></td>
                    <td id="footer_psr_all_products_total_stock"></td>
                    @if(isset($location_list))
                        @foreach($location_list as $loc)
                            <td></td>
                        @endforeach
                    @endif
                    <td></td>
                    @can('view_purchase_price')
                        <td></td>
                    @endcan
                    @can('access_default_selling_price')
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    @endcan
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

