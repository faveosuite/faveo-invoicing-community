@extends('themes.default1.layouts.master')

@section('title')
    Social Logins
@stop

@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.social_logins') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> {{ __('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.social_logins') }}</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')
<style>
.btn {
    display: inline-block;
    font-weight: 300;
    color: #212529;
    text-align: center;
    vertical-align: middle;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.175rem 0.55rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.25rem;
    transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
    .btn-secondary {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
    box-shadow: none;
}
    .btn-secondary:hover {
    color: #fff;
    background-color: #5a6268;
    border-color: #545b62;
}
</style>

<div id="response">
    <div class="card card-secondary card-outline">
        <div class="card-body table-responsive">
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <div id="Localized-license-table_wrapper" class="dataTables_wrapper no-footer">
                        <div id="Localized-license-table_processing" class="dataTables_processing" style="display: none;">
                            <div class="overlay">
                                <i class="fas fa-3x fa-sync-alt fa-spin"></i>
                                <div class="text-bold pt-2">{{ __('message.loading') }}</div>
                            </div>
                        </div>
                        <table id="social-table" class="table display dataTable no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="third-party-app-table_info">
                            <thead>
                                <tr>
                                    <th>{{ __('message.provider') }}</th>
                                    <th>{{ __('message.status') }}</th>
                                    <th>{{ __('message.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($socialLoginss as $key => $item)
                                <tr>
                                    <td>{{$item->type}}</td>
                                    <td>
                                        @if($item->status == 1)
                                        Active
                                        @else
                                        Deactive
                                        @endif
                                    </td>
                                    <td>
                                        <a href="edit/SocialLogins/{{$item->id}}" class="btn btn-secondary a-btn-slide-text">
                                            <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                                            <span><strong><i class="fas fa-edit"></i></strong></span>
                                           
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('#social-table').DataTable({
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
        });
    });
</script>

<script>
    $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>

<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@stop
