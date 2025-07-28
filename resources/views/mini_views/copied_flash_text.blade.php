@foreach($navigations as $navigation)
@if($navigation['btnName'] == 'rec_code')
 @php
   $slotVariableName = $navigation['slot'];
   $style = $navigation['style']
@endphp
@elseif($navigation['btnName'] == 'lic_btn')
 @php
   $slotVariableName = $navigation['slot'];
	$style = $navigation['style']
@endphp
@endif
{!! $style !!}
@endforeach