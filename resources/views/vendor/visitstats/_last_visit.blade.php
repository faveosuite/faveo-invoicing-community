@if (!isset($hideLastVisitDatetime))
    {{ \Carbon\Carbon::parse($visit->created_at)
        ->tz(config('visitortracker.timezone', 'UTC'))
        ->format(config('visitortracker.datetime_format')) }}<br>
@endif

@include('visitstats::_visitor')