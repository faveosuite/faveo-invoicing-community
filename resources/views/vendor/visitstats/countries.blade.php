@extends('visitstats::layout')

@section('visitortracker_content')
<div class="row">
	<div class="col-md-12">
		<h5>{{ $visitortrackerSubtitle }}</h5>

		<table class="visitortracker-table table table-sm table-striped fs-1">
			<thead>
				<th>{{ __('message.country') }}</th>
				<th>{{ __('message.unique_visitors') }}</th>
				<th>{{ __('message.visits') }}</th>
				<th>{{ __('message.last_visit') }}</th>
			</thead>

			<tbody>
				@foreach ($visits as $visit)
					<tr>
						<td>
							@if ($visit->country_code)
								@if (file_exists(public_path('vendor/visitortracker/icons/flags/'.$visit->country_code.'.png')))
									<img class="visitortracker-icon"
										src="{{ asset('/vendor/visitortracker/icons/flags/'.$visit->country_code.'.png') }}"
										title="{{ $visit->country }}"
										alt="{{ $visit->country_code }}">
								@else
									<img class="visitortracker-icon"
										src="{{ asset('/vendor/visitortracker/icons/flags/unknown.png') }}"
										title="Unknown">
								@endif

								{{ $visit->country }}
							@else
								<img class="visitortracker-icon"
									src="{{ asset('/vendor/visitortracker/icons/flags/unknown.png') }}"
									title="Unknown">

								{{ __('message.unknown') }}
							@endif
						</td>
							
						<td>
							{{ $visit->visitors_count }}
						</td>

						<td>
							{{ $visit->visits_count }}
						</td>

						<td>
							@include('visitstats::_last_visit')
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>

		{{ $visits->links() }}
	</div>
</div>
@endsection