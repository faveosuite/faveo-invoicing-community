@extends('themes.default1.layouts.master')
@section('title')
    Create Configurable Groups
@stop
@section('content-header')
    <div class="col-sm-6 md-6">
        <h1>Create Configurable Groups</h1>
    </div>
    <div class="col-sm-6 md-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('configurable-groups')}}"><i class="fa fa-dashboard"></i> Configurable Groups</a></li>
            <li class="breadcrumb-item active">Create Configurable Groups</li>
        </ol>
    </div>
@stop

@section('content')
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <div id="alertMessage12"></div>
            <div id="error"></div>
            <h5>Create Configurable Groups</h5>
        </div>

        <div class="card-body flex">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <div class="form-inline">
                        <label for="display_option" class="mr-2">Group Name:</label>
                        <select class="form-control w-100 {{$errors->has('disk') ? ' is-invalid' : ''}}" name="product_name" id="product_name">
                            <option value="">{{ __('message.choose') }}</option>
                            @foreach($product as $pro)
                                <option value="{{ $pro->name }}">{{ $pro->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-6">
                    <div class="form-inline">
                        <label for="description" class="mr-2">Description:</label>
                        <input type="text" name="description" id="description" class="form-control w-100"/>
                    </div>
                </div>
            </div>
            <div class="row justify-content-around mt-4">
                <a class="btn btn-primary btn px-5" onclick="myFunction()"> Save Changes</a>
            </div>
        </div>
    </div>
    <script>
        function myFunction(){
            var product_name=$('#product_name').val();
            var description=$('#description').val();

            $.ajax({
                url:"{!! url('config-group-create') !!}",
                method:'post',
                data:{'product_name':product_name,'description':description},
                success:function(data){
                    if (data.success === true) {
                        var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-check"></i>{{ __('message.success') }}! </strong>' + data.message + '!</div>';
                        $('#alertMessage12').show();
                        $('#error').hide();
                        $('#alertMessage12').html(result);
                        setInterval(function () {
                            $('#alertMessage12').slideUp(5000);
                            location.reload();
                        }, 3000);
                    } else if (data.success === false) {
                        $('#alertMessage12').hide();
                        $('#error').show();
                        var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.message + '!</div>';
                        $('#error').html(result);
                        setInterval(function () {
                            $('#error').slideUp(5000);
                            location.reload();
                        }, 10000);
                    }
                },
            })
            }
    </script>

    @stop