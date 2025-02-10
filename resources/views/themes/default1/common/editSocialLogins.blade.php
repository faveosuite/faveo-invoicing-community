@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.social_logins') }}
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
</div>
@stop
@section('content')
<style>
     .required:after {
  content: "*";
    color: red;
    font-weight: 700 !important;
}
</style>

<?php
$httpOrigin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;


?>
<div class="row">
    <div class="col-md-12">
    @if($message = Session::get('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>{{ __('message.error') }}</strong> {{ $message }}
    <button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
        {!! Session::forget('error') !!}
        @if($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>{{ __('message.success') }}!</strong> {{ $message }}
            <button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        {!! Session::forget('success') !!}
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">{{ __('message.key') }}</h3>
            </div>

            <div class="card-body table-responsive">
                <form method="POST" action="{{ url('update-social-login') }}" id="socialLoginForm">
                    @csrf

                    <div class="mb-3">
                        @if($socialLogins->type == 'Twitter')
                            <label for="id" class="form-label required" id="id">{{ __('message.key') }}</label>
                        <input type="text" class="form-control {{$errors->has('api_key') ? ' is-invalid' : ''}}" id="api_id"  value="{{old('title', $socialLogins->client_id)}}" name="api_key">
                            @error('api_key')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <div class="input-group-append">
                            </div>
                    </div>
                    <input type="hidden" name="type" value="{{old('title', $socialLogins->type)}}">
                    <div class="mb-3">
                        <label for="pwd" class="form-label required">{{ __('message.lic_api_secret') }}</label>
                        <input type="password" class="form-control {{$errors->has('api_secret') ? ' is-invalid' : ''}}" id="api_pwd"  value="{{old('title', $socialLogins->client_secret)}}" name="api_secret">
                        @error('api_secret')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                        @else
                        <label for="id" class="form-label required" id="id">{{ __('message.lic_client_id') }}</label>
                        <input type="text" class="form-control {{$errors->has('client_id') ? ' is-invalid' : ''}}" id="client_id"  value="{{old('title', $socialLogins->client_id)}}" name="client_id">
                            @error('client_id')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <div class="input-group-append">
                            </div>
                    </div>
                    <input type="hidden" name="type" value="{{old('title', $socialLogins->type)}}">
                    <div class="mb-3">
                        <label for="pwd" class="form-label required">{{ __('message.lic_client_secret') }}</label>
                        <input type="password" class="form-control {{$errors->has('client_secret') ? ' is-invalid' : ''}}" id="pwd"  value="{{old('title', $socialLogins->client_secret)}}" name="client_secret">
                        @error('client_secret')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="redirect" class="form-label required">{{ __('message.redirect_url') }}</label>

                        <input type="text"
                               class="form-control {{$errors->has('redirect_url') ? ' is-invalid' : ''}}"
                               id="redirect"
                               value="{{ url('/auth/callback/' . lcfirst($socialLogins->type)) }}"
                               name="redirect_url"
                               placeholder='https://example.com'
                        >

                        @error('redirect_url')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
                        <div class="input-group-append">
                        </div>
                    </div>

                    <label for="email" class="form-label">{{ __('message.activate_login') }} {{$socialLogins->type}}</label>
                    <div class="row">
                    @if($socialLogins->status == 1)
                    <div class="form-check col-1" style="padding-left: 2.25rem;">
                        <input type="radio" class="form-check-input" id="radio1" name="optradio" value="1" name="status" checked>{{ __('message.yes') }}
                        <label class="form-check-label" for="radio1"></label>
                    </div>
                    <div class="form-check col-1">
                        <input type="radio" class="form-check-input" id="radio2" name="optradio" value="0" name="status">{{ __('message.no') }}
                        <label class="form-check-label" for="radio2"></label>
                    </div>
                    @endif
                    @if($socialLogins->status == 0)
                    <div class="form-check col-1" style="padding-left: 2.25rem;">
                        <input type="radio" class="form-check-input" id="radio1" name="optradio" value="1" name="status">{{ __('message.yes') }}
                        <label class="form-check-label" for="radio1"></label>
                    </div>
                    <div class="form-check col-1">
                        <input type="radio" class="form-check-input" id="radio2" name="optradio" value="0" name="status" checked>{{ __('message.no') }}
                        <label class="form-check-label" for="radio2"></label>
                    </div>
                    @endif
                        @error('optradio')
                        <span class="error-message"> {{$message}}</span>
                        @enderror
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


<script>

    $(document).ready(function() {
        var id=document.getElementById('id');
         let text = (id.innerText || id.textContent);

        const userRequiredFields = {
            id:@json(trans('message.socialLogin_details.client_id')),
            pwd:@json(trans('message.socialLogin_details.client_secret')),
            redirect_url:@json(trans('message.socialLogin_details.redirect_url')),
            api_id:@json(trans('message.socialLogin_details.api_id')),
            api_pwd:@json(trans('message.socialLogin_details.api_secret')),
        };
        if(text==='API Key') {
            $('#socialLoginForm').on('submit', function (e) {
                const userFields = {
                    redirect_url: $('#redirect'),
                    api_id: $('#api_id'),
                    api_pwd: $('#api_pwd'),
                };


                // Clear previous errors
                Object.values(userFields).forEach(field => {
                    field.removeClass('is-invalid');
                    field.next().next('.error').remove();

                });

                let isValid = true;

                const showError = (field, message) => {
                    field.addClass('is-invalid');
                    field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
                };

                // Validate required fields
                Object.keys(userFields).forEach(field => {
                    if (!userFields[field].val()) {
                        showError(userFields[field], userRequiredFields[field]);
                        isValid = false;
                    }
                });

                if(isValid  && !isValidURL(userFields.redirect_url.val())){
                    showError(userFields.redirect_url,@json(trans('message.page_details.valid_url')),);
                    isValid=false;
                }

                // If validation fails, prevent form submission
                if (!isValid) {
                    e.preventDefault();
                }
            });
            // Function to remove error when input'id' => 'changePasswordForm'ng data
            const removeErrorMessage = (field) => {
                field.classList.remove('is-invalid');
                const error = field.nextElementSibling;
                if (error && error.classList.contains('error')) {
                    error.remove();
                }
            };

            // Add input event listeners for all fields
            ['id', 'pwd', 'redirect', 'api_id', 'api_pwd'].forEach(id => {

                document.getElementById(id).addEventListener('input', function () {
                    removeErrorMessage(this);

                });
            });
        }else{
            $('#socialLoginForm').on('submit', function (e) {
                const userFields = {
                    id: $('#client_id'),
                    pwd: $('#pwd'),
                    redirect_url: $('#redirect'),

                };


                // Clear previous errors
                Object.values(userFields).forEach(field => {
                    field.removeClass('is-invalid');
                    field.next().next('.error').remove();

                });

                let isValid = true;

                const showError = (field, message) => {
                    field.addClass('is-invalid');
                    field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
                };

                // Validate required fields
                Object.keys(userFields).forEach(field => {
                    if (!userFields[field].val()) {
                        showError(userFields[field], userRequiredFields[field]);
                        isValid = false;
                    }
                });

                if(isValid  && !isValidURL(userFields.redirect_url.val())){
                    showError(userFields.redirect_url,@json(trans('message.page_details.valid_url')),);
                    isValid=false;
                }

                // If validation fails, prevent form submission
                if (!isValid) {
                    e.preventDefault();
                }
            });
            // Function to remove error when input'id' => 'changePasswordForm'ng data
            const removeErrorMessage = (field) => {
                field.classList.remove('is-invalid');
                const error = field.nextElementSibling;
                if (error && error.classList.contains('error')) {
                    error.remove();
                }
            };

            // Add input event listeners for all fields
            ['client_id', 'pwd', 'redirect', 'api_id', 'api_pwd'].forEach(id => {

                document.getElementById(id).addEventListener('input', function () {
                    removeErrorMessage(this);

                });
            });
        }

        function isValidURL(string) {
            try {
                new URL(string);
                return true;
            } catch (err) {
                return false;
            }
        }
    });


</script>

@stop