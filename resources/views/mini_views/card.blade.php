<div class="col-md-6">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{$title}}</h3>
        </div>
        <!-- /.box-header -->

            <div class="scrollit">
                <div class="card-body table-responsive p-0">
                @if(!count($collection))
                    <tr>
                        <td><p class="text-center">{{ __('message.no_records_found')}}</p></td>
                    </tr>
                @elseif($layout == 'table')

                    {{--  table view --}}
                    <table class="table table-head-fixed text-nowrap">
                        <thead>
                        <tr>
                            @foreach($columns as $column)
                                <th>{{$column}}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                            {{$slot}}
                        </tbody>
                    </table>

                @elseif($layout == 'list')
                    {{--  list view --}}
                    <ul class="products-list product-list-in-box">
                        {{$slot}}
                    </ul>

                @elseif($layout == 'custom')

                    {{$slot}}

                @endif
            </div>
        </div>
        <!-- /.box-body -->
        <div class="card-footer clearfix">
            <a href="{!! array_values($linkLeft)[0] !!}" class="btn btn-sm btn-secondary btn-flat float-left"><i class='fa fa-eye' style='color:white;'> </i>&nbsp;{!! array_keys($linkLeft)[0] !!}</a>
            <a href="{!! array_values($linkRight)[0] !!}" class="btn btn-sm btn-default btn-flat float-right"><span class="fas fa-plus"></span>&nbsp;{!! array_keys($linkRight)[0] !!}</a>
        </div>
        <!-- /.box-footer -->
    </div>
</div>