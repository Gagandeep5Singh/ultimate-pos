<div class="tab-pane" id="psr_by_brand_tab">
    <div class="table-responsive">
        <table class="table table-bordered table-striped" 
        id="product_sell_report_by_brand" style="width: 100%;">
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
                    @can('view_purchase_price')
                        <th>@lang('lang_v1.unit_perchase_price')</th>
                    @endcan
                    @can('access_default_selling_price')
                        <th>@lang('lang_v1.selling_price')</th>
                        <th>Discount</th>
                        <th>Sale Price</th>
                    @endcan
                    <th>@lang('product.category')</th>
                    <th>@lang('product.sub_category')</th>
                    <th>@lang('product.brand')</th>
                    <th>@lang('report.total_unit_sold')</th>
                    <th>@lang('sale.total')</th>
                </tr>
            </thead>
            <tfoot>
                <tr class="bg-gray font-17 footer-total text-center">
                    <td><strong>@lang('sale.total'):</strong></td>
                    <td></td>
                    <td id="footer_psr_by_brand_total_stock"></td>
                    @if(isset($location_list))
                        @foreach($location_list as $loc)
                            <td></td>
                        @endforeach
                    @endif
                    @can('view_purchase_price')
                        <td></td>
                    @endcan
                    @can('access_default_selling_price')
                        <td></td>
                        <td></td>
                        <td></td>
                    @endcan
                    <td></td>
                    <td></td>
                    <td></td>
                    <td id="footer_psr_by_brand_total_sold"></td>
                    <td><span class="display_currency" id="footer_psr_by_brand_total_sell" data-currency_symbol ="true"></span></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>