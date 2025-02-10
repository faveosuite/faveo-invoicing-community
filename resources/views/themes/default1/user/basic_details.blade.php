@extends('themes.default1.layouts.front.master')
@section('title')
Email/Mobile Verification | Faveo Helpdesk
@stop
@section('page-heading')
Email/Mobile Verification
@stop
@section('page-header')
Reset Password
@stop
@section('breadcrumb')
<li><a href="{{url('home')}}">{{ __('message.home') }}</a></li>
<li class="active">{{ __('message.verify') }}</li>
@stop
@section('main-class')
main
@stop
@section('content')
<?php $setting = \App\Model\Common\Setting::where('id', 1)->first(); ?>
<!DOCTYPE html>
<html ng-app="smsApp">
    <body ng-controller="smsCtrl">
        <?php
        $domain = [];
        $set = new \App\Model\Common\Setting();
        $set = $set->findOrFail(1);
        ?>

        <div class="content-header">
            <!-- Rest of the template content -->
        </div>

        <section class="content">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <!-- Rest of the template content -->
                    </div>
                    <div class="card-body">
                        
                        <form action="{{url('save-basic-details')}}" method="POST" enctype="multipart/form-data">
                        @csrf 
    <!--<small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>-->
    <!--aria-describedby="emailHelp" -->
  <div class="form-group">
    <label for="first_name">{{ __('message.first_name') }}</label>
    <input type="text" class="form-control" id="first_name" placeholder="{{ __('message.enter_your_firstname') }}" name="first_name">
  </div>
  
    <input type="hidden" class="form-control" value= "{{$user->id}}" name="id">
  
  <div class="form-group">
    <label for="last_name">{{ __('message.last_name') }}</label>
    <input type="text" class="form-control" id="last_name" placeholder="{{ __('message.enter_your_lastname') }}" name="last_name">
  </div>
    <div class="form-group">
    <label for="company_name">{{ __('message.company-name') }}</label>
    <input type="text" class="form-control" id="company_name" placeholder="{{ __('message.enter_your_companyname') }}" name="company_name">
  </div>
    <div class="form-group">
    <label for="country">{{ __('message.countryname') }}</label>
    <input type="text" class="form-control" id="country" placeholder="{{ __('message.enter_your_countryname') }}" name="country">
  </div>
    <div class="form-group">
    <label for="address">{{ __('message.address') }}</label>
    <input type="text" class="form-control" id="address" placeholder="{{ __('message.enter_your_addressname') }}" name="address">
  </div>
  <button type="submit" class="btn btn-primary">{{ __('message.submit') }}</button>

                            <div class="row">
                                <!-- Rest of the form fields -->
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Custom JS -->
        <script src="{{ asset('js/app.js') }}"></script>
        <script>
            var app = angular.module('smsApp', []);
            app.controller('smsCtrl', function($scope, $http) {
                $scope.sendOTP = function() {
                    var stdata = {
                        "mobile": $("#mobile").val(),
                    };
                    $http({
                        url: '{{url("otp2/send")}}',
                        method: "POST",
                        data: stdata
                    }).then(function(response) {
                        console.log(response.data);
                        alert('hi');
                        alert(response.data);
                        if (response.data == 1) {
                            alert('in');
                            window.location.reload();
                        } else {
                            alert('{{ __('message.oops') }}');
                        }
                    }).catch(function(error) {
                        console.error(error);
                    });
                };
            });
        </script>
    </body>
</html>
@endsection
