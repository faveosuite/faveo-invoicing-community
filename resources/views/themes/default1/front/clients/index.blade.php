@extends('themes.default1.layouts.front.master')
@section('title')
    {{ __('message.dashboard') }}
@stop
@section('page-header')
    {{ __('message.dashboard') }}
@stop
@section('page-heading')
    {{ __('message.dashboard') }}
@stop

@section('main-class')
    main
@stop
@section('content')

<div class="row pt-2">

            @include('themes.default1.front.clients.navbar')

<?php
$status = 'pending';
$updated_ends_at = 'expired';
?>
                <div class="col-lg-9">

                    <div class="tab-pane tab-pane-navigation active" id="dashboard" role="tabpanel">

                        <div class="row counters with-borders">

                            <div class="col-sm-4 mb-4 mb-lg-0">

                                <a  href="{{url('my-invoices?status='.$status)}}" class="text-decoration-none">

                                    <div class="card border-1 bg-color-grey">

                                        <div class="card-body p-2">

                                            <div class="row">

                                                <div class="col-sm-4 pt-4 ps-4">

                                                    <i class="icon-user-following icons text-color-grey text-10"></i>
                                                </div>

                                                <div class="col-sm-8">

                                                    <strong class="text-8 text-color-grey text-end me-2" data-to="{{$pendingInvoicesCount}}">{{$pendingInvoicesCount}}</strong>
                                                </div>
                                            </div>

                                            <h4 class="card-title mb-2 ps-2 mt-2 text-4 font-weight-bold">{{ __('message.pending_invoices')}}</h4>
                                        </div>
                                    </div>
                                </a>

                            </div>

                            <div class="col-sm-4 mb-4 mb-lg-0">

                                <a href="{{url('my-orders')}}" class="text-decoration-none">

                                    <div class="card border-1 bg-color-grey">

                                        <div class="card-body p-2">

                                            <div class="row">

                                                <div class="col-sm-4 pt-4 ps-4">

                                                    <i class="icon-check icons text-color-grey text-10"></i>
                                                </div>

                                                <div class="col-sm-8">

                                                    <strong class="text-8 text-color-grey text-end me-2" data-to="{{$ordersCount}}">{{$ordersCount}}</strong>
                                                </div>
                                            </div>

                                            <h4 class="card-title mb-2 ps-2 mt-2 text-4 font-weight-bold">{{ __('message.caps_orders') }}</h4>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-4 mb-4 mb-lg-0">

                                <a href="{{url('my-orders?updated_ends_at='.$updated_ends_at)}}" class="text-decoration-none">

                                    <div class="card border-1 bg-color-grey">

                                        <div class="card-body p-2">

                                            <div class="row">

                                                <div class="col-sm-4 pt-4 ps-4">

                                                    <i class="icon-reload icons text-color-grey text-10"></i>
                                                </div>

                                                <div class="col-sm-8">

                                                    <strong class="text-8 text-color-grey text-end me-2" data-to="{{$renewalCount}}">{{$renewalCount}}</strong>
                                                </div>
                                            </div>

                                            <h4 class="card-title mb-2 ps-2 mt-2 text-4 font-weight-bold">{{ __('message.order_renewals')}}</h4>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                

            </div>




 <!-- Include jQuery -->


 @stop
