@extends('visitstats::layout')

@section('visitortracker_content')
<div class="row">
	<div class="col-md-12">
		<h5>{{ __('message.summary') }}</h5>

		<table class="visitortracker-table table table-sm table-striped fs-1">
			<thead>
				<th>{{ __('message.period') }}</th>
				<th>{{ __('message.unique_visitors') }}</th>
				<th>{{ __('message.visits') }}</th>
			</thead>

			<tbody>
                <tr>
                    <td>24 hours</td>

                    <td>{{ $unique24h }}</td>

                    <td>{{ $visits24h }}</td>
                </tr>

                <tr>
                    <td>1 week</td>

                    <td>{{ $unique1w }}</td>

                    <td>{{ $visits1w }}</td>
                </tr>

                <tr>
                    <td>1 month</td>

                    <td>{{ $unique1m }}</td>

                    <td>{{ $visits1m }}</td>
                </tr>

                <tr>
                    <td>1 year</td>

                    <td>{{ $unique1y }}</td>

                    <td>{{ $visits1y }}</td>
                </tr>

                <tr>
                    <td>All time</td>

                    <td>{{ $uniqueTotal }}</td>

                    <td>{{ $visitsTotal }}</td>
                </tr>
			</tbody>
		</table>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<h5>{{ __('message.last_10_requests') }}</h5>

		@include('visitstats::_table_requests', ['visits' => $lastVisits])
	</div>
</div>
@endsection