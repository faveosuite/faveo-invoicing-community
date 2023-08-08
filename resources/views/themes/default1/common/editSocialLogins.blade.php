@extends('themes.default1.layouts.master')
@section('title')
Social Logins
@stop
@section('content-header')
<div class="col-sm-6">
    <h1>Social Logins</h1>
</div>
<div class="col-sm-6">
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
        <li class="breadcrumb-item active">Social Logins</li>
    </ol>
</div>
@stop
@section('content')
<style>
     .required:after {
  content: "*";
    color: red;
    font-weight: 100;
}
</style>

<?php
$httpOrigin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;


?>
<div class="row">
    <div class="col-md-12">
    @if($message = Session::get('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Error!</strong> {{ $message }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
        {!! Session::forget('error') !!}
        @if($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ $message }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        {!! Session::forget('success') !!}
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">Api Keys</h3>
            </div>

            <div class="card-body table-responsive">
                <form method="POST" action="{{ url('update-social-login') }}">
                    @csrf

                    <div class="mb-3">
                        @if($socialLogins->type == 'Twitter')
                            <label for="id" class="form-label required">API Key</label>
                        <input type="text" class="form-control" id="id"  value="{{old('title', $socialLogins->client_id)}}" name="client_id">
                    </div>
                    <input type="hidden" name="type" value="{{old('title', $socialLogins->type)}}">
                    <div class="mb-3">
                        <label for="pwd" class="form-label required">API Secret</label>
                        <input type="password" class="form-control" id="pwd"  value="{{old('title', $socialLogins->client_secret)}}" name="client_secret">
                        @else
                        <label for="id" class="form-label required">Client Id</label>
                        <input type="text" class="form-control" id="id"  value="{{old('title', $socialLogins->client_id)}}" name="client_id">
                    </div>
                    <input type="hidden" name="type" value="{{old('title', $socialLogins->type)}}">
                    <div class="mb-3">
                        <label for="pwd" class="form-label required">Client Secret</label>
                        <input type="password" class="form-control" id="pwd"  value="{{old('title', $socialLogins->client_secret)}}" name="client_secret">
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="pwd" class="form-label required">Redirect URL</label>
                        <input type="text" class="form-control" id="pwd"  value="{{ url('/auth/callback/' . lcfirst($socialLogins->type)) }}" name="redirect_url">
                    </div>

                    <label for="email" class="form-label">Activate Login via {{$socialLogins->type}}</label>
                    <div class="row">
                    @if($socialLogins->status == 1)
                    <div class="form-check col-1" style="padding-left: 2.25rem;">
                        <input type="radio" class="form-check-input" id="radio1" name="optradio" value="1" name="status" checked>Yes
                        <label class="form-check-label" for="radio1"></label>
                    </div>
                    <div class="form-check col-1">
                        <input type="radio" class="form-check-input" id="radio2" name="optradio" value="0" name="status">No
                        <label class="form-check-label" for="radio2"></label>
                    </div>
                    @endif
                    @if($socialLogins->status == 0)
                    <div class="form-check col-1" style="padding-left: 2.25rem;">
                        <input type="radio" class="form-check-input" id="radio1" name="optradio" value="1" name="status">Yes
                        <label class="form-check-label" for="radio1"></label>
                    </div>
                    <div class="form-check col-1">
                        <input type="radio" class="form-check-input" id="radio2" name="optradio" value="0" name="status" checked>No
                        <label class="form-check-label" for="radio2"></label>
                    </div>
                    @endif
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm mt-3">
                        <i class="fa fa-sync-alt"></i> &nbspUpdate
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
@stop