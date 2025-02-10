@extends('visitstats::layout')

@section('visitortracker_content')
<div class="row">
	<div class="col-md-12">
		<h5>{{ $visitortrackerSubtitle }}</h5>

		<table class="visitortracker-table table table-sm table-striped fs-1">
			<thead>
				<th>{{ __('message.language') }}</th>
				<th>{{ __('message.unique_visitors') }}</th>
				<th>{{ __('message.visits') }}</th>
				<th>{{ __('message.last_visit') }}</th>
			</thead>

			<tbody>
				@foreach ($visits as $visit)
					<tr>
						<td>
							@if ($visit->browser_language_family)
                                {{ strtoupper($visit->browser_language_family) }}
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