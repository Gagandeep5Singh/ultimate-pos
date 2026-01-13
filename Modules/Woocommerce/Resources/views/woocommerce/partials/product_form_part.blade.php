<div class="col-md-3">
	<div class="form-group">
		@php
			// Default to disabled (checked) for new products
			$is_disabled = true;
			
			// If editing existing product, use its current setting
			if(!empty($product)){
				$is_disabled = !empty($product->woocommerce_disable_sync) ? true : false;
			}
			// If duplicating product, use duplicate product's setting, default to true if not set
			elseif(!empty($duplicate_product)){
				$is_disabled = !empty($duplicate_product->woocommerce_disable_sync) ? true : true;
			}
			// For new products (no product, no duplicate), default is already true (disabled/checked)
		@endphp
      <br>
        <label>
        	<input type="hidden" name="woocommerce_disable_sync" value="0">
          	{!! Form::checkbox('woocommerce_disable_sync', 1, $is_disabled, ['class' => 'input-icheck']); !!} <strong>@lang('woocommerce::lang.woocommerce_disable_sync')</strong>
        </label>
        @show_tooltip(__('woocommerce::lang.woocommerce_disable_sync_help'))
  	</div>
</div>