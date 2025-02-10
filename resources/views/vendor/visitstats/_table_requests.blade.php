 <table id="example2" class="table table-bordered table-hover">
    <thead>
        <th>{{ __('message.request') }}</th>
        <th>{{ __('message.referrer') }}</th>
        <th>{{ __('message.visitor') }}</th>
    </thead>
      
    <tbody>
        @foreach ($visits as $visit)
            <tr>
                <td class="visitortracker-cell-break-words">
                    {{ \Carbon\Carbon::parse($visit->created_at)
                        ->tz(config('visitortracker.timezone', 'UTC'))
                        ->format(config('visitortracker.datetime_format')) }}
                    
                    <br>

                    {{ $visit->is_ajax ? 'AJAX' : '' }}
                    
                    @if ($visit->is_login_attempt)
                        <img class="visitortracker-icon"
                            src="{{ asset('/vendor/visitortracker/icons/login_attempt.png') }}"
                            title="Login attempt">
                    @endif
                    {{ $visit->method }} 
                     
                  <!--   <a href="{{ $visit->url }}"
                        title="{{ $visit->url }}"
                        target="_blank">{{ $visit->url }}</a> -->
                </td>

                <td class="visitortracker-cell-break-words">
                    {!!
                        $visit->referer
                        ? '<a href="' . $visit->referer . '" title="' . $visit->referer . '" target="_blank">' . $visit->referer . '</a>'
                        : '-'
                    !!}
                </td>

                <td class="visitortracker-cell-nowrap">
                    @include('visitstats::_visitor')
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" />
<script src="{{asset('bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script>
  $(function () {
    $('#example2').DataTable({
      'paging'      : true,
      'lengthChange': true,
      'searching'   : true,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : true
    })
  })
</script>