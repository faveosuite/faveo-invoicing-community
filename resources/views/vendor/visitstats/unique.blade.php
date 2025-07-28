@extends('visitstats::layout')

@section('visitortracker_content')
<div class="row">
	<div class="col-md-12">
		<h5>{{ $visitortrackerSubtitle }}</h5>

		<table class="visitortracker-table table table-sm table-striped fs-1">
			<thead>
				<th>IP</th>
				<th>{{ __('message.visits') }}</th>
				<th>{{ __('message.last_visit') }}</th>
			</thead>

			<tbody>
				@foreach ($visits as $visit)
					<tr>
						<td>{{ $visit->ip }}</td>
							
						<td>{{ $visit->visits_count }}</td>

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