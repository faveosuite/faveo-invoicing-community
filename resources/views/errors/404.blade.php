@extends('themes.default1.layouts.front.master')
@section('title')
404
@stop
@section('page-heading')
404 - {{ __('message.page_not_found') }}
@stop
@section('breadcrumb')
<li><a href="{{url('my-invoices')}}">{{ __('message.home') }}</a></li>
<li class="active">404</li>
@stop
@section('main-class') "main shop" @stop
@section('content')
			<div class="container">

					<section class="http-error">
						<div class="row justify-content-center py-3">
							<div class="col-md-7 text-center">
								<div class="http-error-main">
									<h2>404!</h2>
									<p>{{ __('message.sorry') }}</p>
								</div>
							</div>
							<div class="col-md-4 mt-4 mt-md-0">
								<h4 class="text-primary">{{ __('message.useful_links') }}</h4>
								<ul class="nav nav-list flex-column">
									<li class="nav-item"><a class="nav-link" href="{{url('my-invoices')}}">{{ __('message.my_invoices') }}</a></li>
									<li class="nav-item"><a class="nav-link" href="{{url('my-orders')}}">{{ __('message.my_orders') }}</a></li>
									<li class="nav-item"><a class="nav-link" href="{{url('my-profile')}}">{{ __('message.my_profile') }}</a></li>
									<li class="nav-item"><a class="nav-link" href="{{url('contact-us')}}">{{ __('message.contact_us') }}</a></li>
								</ul>
							</div>
						</div>
					</section>

				</div>


@stop
