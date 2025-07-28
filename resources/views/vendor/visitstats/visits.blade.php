@extends('visitstats::layout')

@section('visitortracker_content')
<div class="row">
	<div class="col-md-12">
		<h5>{{ $visitortrackerSubtitle }}</h5>

		@include('visitstats::_table_requests')

		{{ $visits->links() }}
	</div>
</div>
@endsection