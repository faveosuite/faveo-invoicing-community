@extends('visitstats::layout')

@section('visitortracker_content')
<div class="row">
	<div class="col-md-12">
		<h5>{{ $visitortrackerSubtitle }}</h5>

		<table class="visitortracker-table table table-sm table-striped fs-1">
			<thead>
				<th>OS</th>
				<th>{{ __('message.unique_visitors') }}</th>
				<th>{{ __('message.visits') }}</th>
				<th>{{ __('message.last_visit') }}</th>
			</thead>

			<tbody>
				@foreach ($visits as $visit)
					<tr>
						<td>
							@if ($visit->os_family)
								@if (file_exists(public_path('vendor/visitortracker/icons/os/'.$visit->os_family.'.png')))
									<img class="visitortracker-icon"
										src="{{ asset('/vendor/visitortracker/icons/os/'.$visit->os_family.'.png') }}"
										title="{{ ucwords(str_replace('-', ' ', $visit->os_family)) }}"
										alt="{{ ucwords(str_replace('-', ' ', $visit->os_family)) }}">
								@endif

								{{ ucwords(str_replace('-', ' ', $visit->os_family)) }}
                            @else
                                <span>{{ __('message.unknown') }}</span>
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