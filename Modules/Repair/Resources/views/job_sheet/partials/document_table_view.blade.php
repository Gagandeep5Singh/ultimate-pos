
<script>
function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}
</script>
<table class="table table-striped">
	<thead>
		<tr>
			<th>@lang('lang_v1.image')</th>
			<th>@lang('messages.action')</th>
		</tr>
	</thead>
	<tbody>
		@foreach($medias as $media)
			<tr class="media_row">
				<td>
					@if(isFileImage($media->display_url))
						{!! $media->thumbnail() !!}
						<br>
					@endif
					<a href="{{$media->display_url}}" class="cursor-pointer"target="_blank">
						{{$media->display_name}}	
					</a>
				</td>
				<td>
					<a href="{{$media->display_url}}" download="{{$media->display_name}}" class="btn btn-success btn-sm">
						<i class="fas fa-download"></i>
					</a>
					<a data-href="{{action([\Modules\Repair\Http\Controllers\JobSheetController::class, 'deleteJobSheetImage'], ['id' => $media->id])}}" class="btn btn-danger btn-sm delete_media">
						<i class="fas fa-trash-alt"></i>
					</a>
				</td>
			</tr>
		@endforeach
	</tbody>
</table>