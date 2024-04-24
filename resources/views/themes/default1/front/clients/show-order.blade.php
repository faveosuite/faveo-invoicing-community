@extends('themes.default1.layouts.front.master')
@section('title')
    Orders
@stop
@section('nav-orders')
    active
@stop
@section('page-heading')
    Order Details
@stop
@section('breadcrumb')
    <style>
        option
        {
            font-size: 15px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {display:none;}

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
        .scrollit {
            overflow:scroll;
            height:600px;
        }

        .horizontal-images {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .horizontal-images img {
            height: auto;
            width: 12%;
            margin-right: 5px;
        }
        .custom-close {
            position: absolute;
            top: -20px;
            right: -20px;
            width: 30px;
            height: 30px;
            background-color: red;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 20px;
        }
        .alert.alert-danger .close {
            position: absolute;
            top: 0;
            right: 0;
        }
        .modal {
            z-index: 1050;
        }

        .modal-backdrop.show {
            z-index: 1040;
        }
        .order-table{
            border: none;
        }
        .plan-features strong {
            color: #000 !important;
        }
        
        [type=search] {
            padding-right: 20px;
            border: 1px solid #aaa;
            border-radius: 3px;
            padding: 5px;
            margin-left: 3px;
            background-color: transparent;
}
        }
        #showpayment-table_paginate{
        margin-right: -20px !important;

}
    .table th{
        border-top: unset !important;
    }
    

    </style>
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">Home</a></li>
    @else
        <li><a class="text-primary" href="{{url('login')}}">Home</a></li>
    @endif
    <li class="active text-dark">Order Details</li>
@stop
<?php

$cartSubtotalWithoutCondition = 0;

use Razorpay\Api\Api;
$merchant_orderid= generateMerchantRandomString();

function generateMerchantRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
$rzp_key = app\ApiKey::where('id', 1)->value('rzp_key');
$rzp_secret = app\ApiKey::where('id', 1)->value('rzp_secret');
$apilayer_key = app\ApiKey::where('id', 1)->value('apilayer_key');
$api = new Api($rzp_key, $rzp_secret);
$displayCurrency = getCurrencyForClient(\Auth::user()->country);
$symbol = getCurrencyForClient(\Auth::user()->country);
if ($symbol == 'INR'){


    $exchangeRate= '';


    $orderData = [
        'receipt'         => '3456',
        'amount'          => round(1.00*100), // 2000 rupees in paise

        'currency'        => getCurrencyForClient(\Auth::user()->country),
        'payment_capture' => 0 // auto capture

    ];


} else {

    $exchangeRate = '';
    $orderData = [
        'receipt'         => '3456',
        'amount'          =>  round(1.00*100), // 2000 rupees in paise

        'currency'        => getCurrencyForClient(\Auth::user()->country),
        'payment_capture' => 0 // auto capture

    ];
}
$razorpayOrder = $api->order->create($orderData);
$razorpayOrderId = $razorpayOrder['id'];
$_SESSION['razorpay_order_id'] = $razorpayOrderId;
$displayAmount = $amount = $orderData['amount'];



$data = [


    "key"               => $rzp_key,
    "name"              => 'Faveo Helpdesk',
    "currency"          => 'INR',
    "prefill"=> [
        "contact"=>    \Auth::user()->mobile_code .\Auth::user()->mobile,
        "email"=>      \Auth::user()->email,
    ],
    "description"       =>  'Order for Invoice No' .-$invoice->number,
    "notes"             => [
        "First Name"         => \Auth::user()->first_name,
        "Last Name"         =>  \Auth::user()->last_name,
        "Company Name"      => \Auth::user()->company,
        "Address"           =>  \Auth::user()->address,
        "Email"             =>  \Auth::user()->email,
        "Country"           =>  \Auth::user()->country,
        "State"             => \Auth::user()->state,
        "City"              => \Auth::user()->town,
        "Zip"               => \Auth::user()->zip,
        "Currency"          => \Auth::user()->currency,
        "Amount Paid"   => '1',
        "Exchange Rate"   =>  $exchangeRate,



        "merchant_order_id" =>  $merchant_orderid,
    ],
    "theme"             => [
        "color"             => "#F37254"
    ],
    "order_id"          => $razorpayOrderId,
];

if ($displayCurrency !== 'INR')
{
    $data['display_currency']  = 'USD';
    $data['display_amount']    ='1';

}
$json = json_encode($data);


$currency = \Auth::user()->currency;



$gateways = \App\Http\Controllers\Common\SettingsController::checkPaymentGateway(getCurrencyForClient(\Auth::user()->country));
// $processingFee = \DB::table(strtolower($gateways))->where('currencies',\Auth::user()->currency)->value('processing_fee');

$planid = \App\Model\Payment\Plan::where('product',$product->id)->value('id');
$price = $order->price_override;





?>



@section('content')
    @include('themes.default1.front.clients.reissue-licenseModal')
    @include('themes.default1.front.clients.domainRestriction')




    <div class="container pt-3 pb-2">
        <div id="alertMessage-2"></div>
        <div id="error-1"></div>
        <div id="response1"></div>

        <div class="row justify-content-center">

            <div class="col-lg-12 alert bg-color-light-scale-2">

                <div class="d-flex flex-column flex-md-row justify-content-between plan-features">

                    <div class="text-center">
                            <span>
                                <strong>Order Number</strong> <br>
                                #{{$order->number}}
                            </span>
                    </div>
                    <div class="text-center mt-4 mt-md-0">
                            <span>
                                <strong>Date</strong> <br>
                                {!! getDateHtml($order->created_at) !!}
                            </span>
                    </div>
                    <div class="text-center mt-4 mt-md-0">
                            <span>
                                <strong>Status</strong><br>
                                {{$order->order_status}}
                            </span>
                    </div>
                    <div class="text-center mt-4 mt-md-0">
                            <span>
                                <strong>Expiry Date</strong><br>
                                {!! getDateHtml($subscription->update_ends_at) !!}
                            </span>
                    </div>
                </div>

            </div>
        </div>

                    <!-- Modal for Localized License domain-->

            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Please Enter Your Domain That You Wish To Host</h5>
                        </div>
                        <div class="modal-body">
                            <form method="GET" action="{{url('uploadFile')}}">
                                {!! csrf_field() !!}
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Domain Name:</label>
                                    <input type="text" class="form-control" id="recipient-name" placeholder="https://faveohelpdesk.com/public" name="domain" value="" onkeydown="return event.key != 'Enter';">
                                    {{Form::hidden('orderNo', $order->number)}}
                                    {{Form::hidden('userId',$user->id)}}
                                    <br>
                                    <div class="modal-footer">
                                        <button type="button" id="close" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
                                        @if((!Storage::disk('public')->exists('faveo-license-{'.$order->number.'}.txt')) || $order->is_downloadable==0)
                                            <button type="submit" id="domainSave" class="done btn btn-primary" {{$order->where('number',$order->number)->update(['is_downloadable'=> 1])}}><i class="fas fa-save"></i>&nbsp;Done</button>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <div class="row pt-2">

            <div class="col-lg-3 mt-4 mt-lg-0">

                <aside class="sidebar mt-2 mb-5">

                    <ul class="nav nav-list flex-column">

                        <li class="nav-item">

                            <a class="nav-link active" href="#license" data-bs-toggle="tab" data-hash data-hash-offset="0"
                               data-hash-offset-lg="500" data-hash-delay="500">License Details
                            </a>
                        </li>

                        <li class="nav-item">

                            <a class="nav-link" href="#users" data-bs-toggle="tab" data-hash data-hash-offset="0"
                               data-hash-offset-lg="500" data-hash-delay="500">User Details
                            </a>
                        </li>

                        <li class="nav-item">

                            <a class="nav-link" href="#invoice" data-bs-toggle="tab" data-hash data-hash-offset="0"
                               data-hash-offset-lg="500" data-hash-delay="500">Invoice List
                            </a>
                        </li>

                        <li class="nav-item">

                            <a class="nav-link" href="#receipt" data-bs-toggle="tab" data-hash data-hash-offset="0"
                               data-hash-offset-lg="500" data-hash-delay="500">Payment Receipts
                            </a>
                        </li>
                        @if(in_array($product->id,cloudPopupProducts()) && $order->order_status!='Terminated')

                            <li class="nav-item">

                                <a class="nav-link" href="#cloud" data-bs-toggle="tab" data-hash data-hash-offset="0"
                                   data-hash-offset-lg="500" data-hash-delay="500">Cloud Settings
                                </a>
                            </li>
                        @endif

                        @if($price == '0' && !in_array($product->id,cloudPopupProducts()) && $order->order_status!='Terminated')

                            <li class="nav-item">

                                <a class="nav-link" href="#auto-renew" data-bs-toggle="tab" data-hash data-hash-offset="0"
                                   data-hash-offset-lg="500" data-hash-delay="500">Auto Renewal
                                </a>
                            </li>
                        @elseif($order->order_status!='Terminated')
                            <li class="nav-item">

                                <a class="nav-link" href="#auto-renew" data-bs-toggle="tab" data-hash data-hash-offset="0"
                                   data-hash-offset-lg="500" data-hash-delay="500">Auto Renewal
                                </a>
                            </li>
                        @endif
                    </ul>
                </aside>
            </div>

            <div class="col-lg-9 mt-2">
                @if($order->order_status != 'Terminated')
                        <?php
                        $terminatedOrderId = \DB::table('terminated_order_upgrade')->where('upgraded_order_id',$order->id)->value('terminated_order_id');
                        $terminatedOrderNumber = \App\Model\Order\Order::where('id',$terminatedOrderId)->value('number');
                        ?>
                    @if(!empty($terminatedOrderId))
                        <p class="order-links">
                            Order: <b>{{$order->number}}</b>
                            has been generated because order: <a class="order-link" href="{{$terminatedOrderId}}">{{$terminatedOrderNumber}}</a> was terminated.
                        </p>
                    @endif
                    <input type="hidden" name="domainRes" id="domainRes" value={{$allowDomainStatus}}>


                    <div class="tab-pane tab-pane-navigation active" id="license" role="tabpanel">


                        <div class="row">

                            <div class="col">

                                <div class="row align-items-center">

                                    <div class="col-sm-5">

                                        <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                            <span class="mb-2 font-weight-bold">License Code:</span>
                                        </div>
                                    </div>

                                    <div class="col-sm-7">
                                        <span id="serialKey">{{$order->serial_key}}</span>

                                        <a href="#" class="btn btn-light-scale-2 text-black btn-sm ms-4" id="copyButton" data-bs-toggle="tooltip" title="Copy">
                                            <i class="fas fa-copy"></i>
                                        </a>

                                        <span id="copiedMessage" class="hidden">Copied</span>

                                        @if ($licenseStatus == 1)
                                            @if(!in_array($product->id,cloudPopupProducts()) && $price != '0')

                                                <a class="btn btn-light-scale-2 btn-sm text-black btn-sm" data-bs-toggle="tooltip" title="Reissue License" id="reissueLic" data-id="{{$order->id}}" data-name="{{$order->domain}}" {{!Storage::disk('public')->exists('faveo-license-{'.$order->number.'}.txt') || $order->license_mode!='File' ? "enabled" : "disabled"}}>
                                                  <i class="fas fa-id-card-alt"></i>
                                                    @elseif(!in_array($product->id,cloudPopupProducts()) && $price == '0')
                                                        <a class="btn btn-light-scale-2 btn-sm text-black btn-sm" data-bs-toggle="tooltip" title="Reissue License" id="reissueLic" data-id="{{$order->id}}" data-name="{{$order->domain}}" {{!Storage::disk('public')->exists('faveo-license-{'.$order->number.'}.txt') || $order->license_mode!='File' ? "enabled" : "disabled"}}>
                                                          <i class="fas fa-id-card-alt"></i>
                                                            @elseif($product->type == '4' && $price != '0')
                                                                <a class="btn btn-light-scale-2 btn-sm text-black btn-sm" data-bs-toggle="tooltip" title="Reissue License" id="reissueLic" data-id="{{$order->id}}" data-name="{{$order->domain}}" {{!Storage::disk('public')->exists('faveo-license-{'.$order->number.'}.txt') || $order->license_mode!='File' ? "enabled" : "disabled"}}>
                                                                 <i class="fas fa-id-card-alt"></i>
                                                                    @endif

                                                                   
                                                                </a>
                                            @endif
                                    </div>
                                </div>

                                <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                                <div class="row align-items-center">

                                    <div class="col-sm-5">

                                        <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                            <span class="mb-2 font-weight-bold">License Expiry Date:</span>
                                        </div>
                                    </div>

                                    <div class="col-sm-7">

                                        {!! $licdate !!}
                                    </div>
                                </div>

                                <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                                <div class="row align-items-center">

                                    <div class="col-sm-5">

                                        <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                            <span class="mb-2 font-weight-bold">Update Expiry Date:</span>
                                        </div>
                                    </div>

                                    <div class="col-sm-7">

                                        {!! $date !!}
                                    </div>
                                </div>

                                @if($order->license_mode=='File')
                                <div class="row"><div class="col"><hr class="solid my-3"></div></div>
                                <div class="row align-items-center">

                                    <div class="col-sm-5">

                                        <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                            <span class="mb-2 font-weight-bold">Localized License:</span>
                                        </div>
                                    </div>

                                    <div class="col-sm-7">

                                     <button class="btn btn-dark mb-2 btn-sm" id="defaultModalLabel" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo" {{!Storage::disk('public')->exists('faveo-license-{'.$order->number.'}.txt') || $order->is_downloadable==0 ? "enabled" : "disabled"}}>Download License File</button>
                                     <a href="{{url('downloadPrivate/'.$order->number)}}"><button class="btn btn-dark mb-2 btn-sm" onclick="refreshPage()">Download License Key</button></a>
                                     <i class="fa fa-info ml-2" data-bs-toggle="tooltip" title="It is mandatory to download both files in order for licensing to work. Please place these files in Public\Script\Signature in faveo." >{!!tooltip('Edit')!!}</i>


                                    </div>
                                </div>
                                @endif

                                <div class="row"><div class="col"><hr class="solid my-3"></div></div>
                                <br >

                                <div class="table-responsive">
                                     <table id="installationDetail-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                                      <thead>
                                      <tr>
                                      <th >Installation Path</th>
                                      <th>Installation IP</th>
                                      <th>Version </th>
                                      <th>Last Active</th>
                                        
                                    </tr></thead>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                     @else
                  <div class="tab-pane tab-pane-navigation active" id="license" role="tabpanel">
                        <?php
                        $idOrdert  = \DB::table('terminated_order_upgrade')->where('terminated_order_id',$order->id)->get();
                        foreach ($idOrdert as $ordt) {
                            $newOrders[] = \App\Model\Order\Order::where('id', $ordt->upgraded_order_id)->get();
                        }
                        ?>

                        @foreach($newOrders as $newOrder)
                            <div class="termination-message">
                                <p class="termination-notice"><b>Important: Termination Notice</b></p>
                                <p class="termination-description">
                                    The order you had placed has been terminated. Consequently, the features and licenses associated with this order are no longer accessible.
                                </p>
                                <p class="order-links">
                                    The terminated order: <b>{{$order->number}}</b>
                                    has been upgraded to the new order: <a class="order-link" href="{{$newOrder[0]->id}}">{{$newOrder[0]->number}}</a>.
                                </p>
                            </div>

                        @endforeach
                       </div>

                                  @endif

                <div class="tab-pane tab-pane-navigation" id="users" role="tabpanel">

                    <div class="row">

                        <div class="col">

                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">Name:</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">{{ucfirst($user->first_name)}}</div>
                            </div>

                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">Email:</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">{{$user->email}}</div>
                            </div>

                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">Mobile:</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">@if($user->mobile_code)(<b>+</b>{{$user->mobile_code}})@endif&nbsp;{{$user->mobile}}</div>
                            </div>

                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">Address:</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">{{$user->address}}</div>
                            </div>

                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">Country:</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">{{getCountryByCode($user->country)}}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane tab-pane-navigation" id="invoice" role="tabpanel">
                    <div class="table-responsive">

                        <table id="showorder-table" class="table table-striped table-bordered mw-auto" cellspacing="0" width="100%" styleClass="borderless">
                            <thead>
                            <tr>
                                <th>Number</th>
                                <th>Product</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>


                </div>

                <div class="tab-pane tab-pane-navigation" id="receipt" role="tabpanel">
                    <div class="table-responsive">
                        <table id="showpayment-table" class="table table-striped table-bordered mw-auto" cellspacing="0" width="100%" styleClass="borderless">
                            <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Total</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <div class="tab-pane tab-pane-navigation" id="cloud" role="tabpanel">

                    <div class="row pb-4">

                        <div class="col-7"></div>

                        <div class="col-5">

                            <div class="text-end">

                                <span class="font-weight-normal text-4">Plan Expiry : <strong class="font-weight-bold">{!! getDateHtml($subscription->ends_at) !!}</strong> </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-lg-6 mb-5 mb-lg-0">

                            <div class="card border-radius-1 bg-color-light box-shadow-6 box-shadow-hover cur-pointer" data-bs-toggle="modal" data-bs-target="#cloudDomainModal">

                                <div class="card-body p-relative zindex-1 p-3">

                                    <div class="feature-box feature-box-style-6 text-center d-block">

                                        <div class="feature-box-icon justify-content-center">

                                            <i class="fas fa-globe text-primary"></i>
                                        </div>
                                        <?php
                                        $installation_path=\App\Model\Order\InstallationDetail::where('order_id',$id)
                                            ->where('installation_path','!=',cloudCentralDomain())->latest()->value('installation_path');
                                        ?>

                                        <div class="feature-box-info">

                                            <h4 class="text-4 mt-3 mb-2 text-color-grey">Change Cloud Domain</h4>

                                            <p class="mb-2"><strong class="text-black text-2">Current domain:</strong> {{$installation_path}}</p>

                                            <p class="mb-0 text-2">Click here to start customising your cloud domain. Please note that there will be a short 5-minute downtime while we work our magic</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-5 mb-lg-0">

                            <div class="card border-radius-1 bg-color-light box-shadow-6 box-shadow-hover cur-pointer" data-bs-toggle="modal" data-bs-target="#numberOfAgentsModal">

                                <div class="card-body p-relative zindex-1 p-3">

                                    <div class="feature-box feature-box-style-6 text-center d-block">

                                        <div class="feature-box-icon justify-content-center">

                                            <i class="fas fa-users text-primary"></i>
                                        </div>

                                        <div class="feature-box-info">
                                            <?php
                                            $latestAgents   = ltrim(substr($order->serial_key, 12),'0');
                                            ?>

                                            <h4 class="text-4 mt-3 mb-2 text-color-grey">Increase/Decrease Agents</h4>

                                            <p class="mb-2"><strong class="text-black text-2">Current number of agents: </strong>{{$latestAgents}}</p>

                                            <p class="mb-0 text-2">Update your agent count by clicking here. Upgrades incur costs, and downgrades in between billing cycles aren't refunded.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        $planIdOld = \App\Model\Product\Subscription::where('order_id',$id)->value('plan_id');
                        $planName = \App\Model\Payment\Plan::where('id',$planIdOld)->value('name');
                        $ExistingPlanPirce= \App\Model\Payment\PlanPrice::where('plan_id',$planIdOld)->where('currency',getCurrencyForClient(\Auth::user()->country))->latest()->value('add_price');
                        ?>
                        @if(strpos($planName,'free')==false)

                            <div class="col-lg-6 mb-5 mb-lg-0 mt-3">

                                <div class="card border-radius-1 bg-color-light box-shadow-6 box-shadow-hover cur-pointer" data-bs-toggle="modal" data-bs-target="#cloudPlanModal">

                                    <div class="card-body p-relative zindex-1 p-3">

                                        <div class="feature-box feature-box-style-6 text-center d-block">

                                            <div class="feature-box-icon justify-content-center">

                                                <i class="fas fa-cloud-upload-alt text-primary"></i>
                                            </div>

                                            <div class="feature-box-info">

                                                <h4 class="text-4 mt-3 mb-2 text-color-grey">Upgrade/Downgrade Cloud Plan</h4>

                                                <p class="mb-2"><strong class="text-black text-2">Current Plan:</strong> {{$planName}}</p>

                                                <p class="mb-0 text-2">Click here to change your cloud plan. Upgrades may cost extra. Downgrades auto-credited based on billing balance for future use in credits.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <h6 class="mb-1"><i>Current Plan: {{$planName}}</i></h6>
                        @endif


                    </div>
                </div>


                <div class="tab-pane tab-pane-navigation" id="auto-renew" role="tabpanel">
            
                    <div class="row">

                        <div class="col">

                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">Auto renewal:</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">  <div class="form-check form-switch">

                                    <input id="renew" value="{{$statusAutorenewal}}"  name="is_subscribed" class="form-check-input renewcheckbox" style="padding-right: 2rem;padding-top: 1rem!important;padding-bottom: 0rem!important;" type="checkbox" role="switch">
                                    <input type="hidden" name="" id="order" value="{{$id}}">

                                </div></div>
                            </div>

                        <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">Status:</span>
                                    </div>
                                </div>

                              <div class="col-sm-7">
                                @if($statusAutorenewal == 1)
                                    <span class="text-success font-weight-bold">Active</span>
                                @else
                                    <span class="text-danger font-weight-bold">Inactive</span>
                                @endif
                            </div>

                            </div>

                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>
                            <?php
                            if($statusAutorenewal == 1 && $payment_log == null && !empty($terminatedOrderId)){
                           
                             $payment_log = \App\Payment_log::where('order',  $terminatedOrderNumber)
                                ->where('payment_type', 'Payment method updated')
                                ->orderBy('id', 'desc')
                                ->first();
                                if(!$payment_log){
                                    $payment_log = \App\Payment_log::where('order',  $terminatedOrderNumber)
                                    ->orderBy('id', 'desc')
                                    ->first();
                                }
                            }
                            ?>
                            @if($statusAutorenewal == 1 && $payment_log)
                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">Payment Gateway:</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">{{$payment_log->payment_method}}</div>
                            </div>
                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>
                            @endif
                             @if($statusAutorenewal == 1 && $payment_log)
                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">Subscription Enabled Date:</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">{!! getDateHtml($payment_log->date) !!}</div>
                            </div>
                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="autorenewModal" tabindex="-1" role="dialog" aria-labelledby="autorenewModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="autorenewModalLabel">Auto Renewal</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="form-group col">

                            <label class="form-label">Select the payment gateway <span class="text-danger"> *</span></label>

                            <div class="custom-select-1">
                                <select class="form-select form-control h-auto py-2" data-msg-required="Please select a city." name="city" required>
                                    <option value="">Select</option>
                                    <option value="1">Razorpay</option>
                                    <option value="2">Stripe</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>

                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cloudDomainModal" tabindex="-1" role="dialog" aria-labelledby="cloudDomainModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="cloudDomainModalLabel">Change Cloud Domain</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <div id="success-domain"></div>
                    <div id="failure-domain"></div>

                    <p>If you wish to purchase a domain, you can <a href="https://store.ladybirdwebhost.com/" target="_blank">Click here.</a>And after the domain is set up, you will have to point your CNAME to our cloud <a href="https://docs.faveohelpdesk.com/docs/helper/cname/" target="_blank">Learn more.</a></p>

                    <p class="text-black"><strong>Current Cloud Domain:</strong> {{$installation_path}}</p>

                    <div class="row">

                        <div class="form-group col">

                            <label class="form-label">Enter your new domain name <span class="text-danger"> *</span></label>

                            <div class="input-group mb-3">

                                <input type="text" class="form-control col col-2 rounded-1" value="https://" disabled="true" style="background-color: lightslategray; color:white;">
                                <input type="text" class="form-control col-10" id="clouduserdomain" autocomplete="off" placeholder="billing.custom.com" required>

                            </div>
                        </div>
                        <script>
                            $(document).ready(function() {
                                var orderId = {{$id}};
                                $.ajax({
                                    data: {'orderId' : orderId},
                                    url: '{{url("/api/takeCloudDomain")}}',
                                    method: 'POST',
                                    dataType: 'json',
                                    success: function(data) {
                                        $('#clouduserdomainfill').html('Current cloud domain: <a href="' + data.data + '">' + data.data + '</a>');
                                    },
                                    error: function(error) {
                                        console.error('Error:', error);
                                    }
                                });
                            });
                        </script>

                        <div class="overlay" style="display: none;"></div> <!-- Add this line -->

                        <div class="loader-wrapper" style="display: none; background: white; height: 100%;" >
                            <i class="fas fa-spinner fa-spin" style="font-size: 40px;"></i>

                        </div>
                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button" id="changeDomain" class="btn btn-primary"><i class="fas fa-globe"></i> Change Domain</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="numberOfAgentsModal" tabindex="-1" role="dialog" aria-labelledby="numberOfAgentsModalLabel" aria-hidden="true">
        <?php
        $latestAgents   = ltrim(substr($order->serial_key, 12),'0');
        ?>

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="numberOfAgentsModalLabel">Change Number of Agents</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <div id="response-agent"></div>
                    <div id="failure-agent"></div>
                    <?php
                    $country_idagnt = \App\Model\Common\Country::where('country_code_char2', \Auth::user()->country)->value('country_id');
                    $ExistingPlanPirce= \App\Model\Payment\PlanPrice::where('plan_id',$planIdOld)->where('currency',getCurrencyForClient(\Auth::user()->country))->where('country_id',$country_idagnt)->latest()->value('add_price');
                    if(!$ExistingPlanPirce){
                        $ExistingPlanPirce= \App\Model\Payment\PlanPrice::where('plan_id',$planIdOld)->where('currency',getCurrencyForClient(\Auth::user()->country))->where('country_id',0)->latest()->value('add_price');
                    }
                    ?>

                    <p class="text-black"><strong>Current number of agents:</strong> {{$latestAgents}}</p>

                    <p class="text-black"><strong>Price per agent: </strong>{!! currencyFormat($ExistingPlanPirce,getCurrencyForClient(\Auth::user()->country),true) !!}</p>

                    <div class="row">


                        <label class="text-black"><strong>Choose your desired number of agents <span class="text-danger"></strong> *</span></label>

                        <div class="quantity">
                            {!! Form::number('number', null, ['class' => 'form-control', 'id' => 'numberAGt', 'min' => '1', 'placeholder' => '']) !!}
                        </div>
                        <br><br>

                        <div class="col-12">
                            <p class="text-black" id="pricetopaid" style="display: none;"><strong>Price to be paid:</strong> <span id="pricetopay" class="pricetopay"></span></p>
                        </div>
                        <div class="overlay" style="display: none;"></div> <!-- Add this line -->

                        <div class="loader-wrapper" style="display: none; background: white;" >
                            <i class="fas fa-spinner fa-spin" style="font-size: 40px;"></i>

                        </div>
                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-primary" id="agentNumber"><i class="fas fa-users"></i> Update Agents</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="cloudPlanModal" tabindex="-1" role="dialog" aria-labelledby="cloudPlanModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="cloudPlanModalLabel">Upgrade or downgrade your cloud plan</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <div id="response-upgrade"></div>
                    <div id="failure-upgrade"></div>
                    <?php
                    // Retrieve the plans data as before
                    $plans = App\Model\Payment\Plan::join('products', 'plans.product', '=', 'products.id')
                        ->leftJoin('plan_prices','plans.id','=','plan_prices.plan_id')
                        ->where('plans.product','!=',$product->id)
                        ->where('products.type',4)
                        ->where('products.can_modify_agent',1)
                        ->where('plan_prices.renew_price','!=','0')
                        ->pluck('plans.name', 'plans.id')
                        ->toArray();
                    $planIds = array_keys($plans);

                    $countryids = \App\Model\Common\Country::where('country_code_char2', \Auth::user()->country)->first();


                    $renewalPrices = \App\Model\Payment\PlanPrice::whereIn('plan_id', $planIds)
                        ->where('country_id',$countryids->country_id)
                        ->where('currency',getCurrencyForClient(\Auth::user()->country))
                        ->latest()
                        ->pluck('renew_price', 'plan_id')
                        ->toArray();

                    if(empty($renewalPrices)){
                        $renewalPrices = \App\Model\Payment\PlanPrice::whereIn('plan_id', $planIds)
                            ->where('country_id',0)
                            ->where('currency',getCurrencyForClient(\Auth::user()->country))
                            ->latest()
                            ->pluck('renew_price', 'plan_id')
                            ->toArray();
                    }

                    foreach ($plans as $planId => $planName) {
                        if (isset($renewalPrices[$planId])) {
                            if(in_array($product->id,cloudPopupProducts())) {
                                $plans[$planId] .= " (Plan price-per agent: " . currencyFormat($renewalPrices[$planId], getCurrencyForClient(\Auth::user()->country), true) . ")";
                            }
                        }
                    }
                    // Add more cloud IDs until we have a generic way to differentiate
                    if(in_array($product->id,cloudPopupProducts())){
                        $plans = array_filter($plans, function ($value) {
                            return stripos($value, 'free') === false;
                        });
                    }


                    $planIdOld = \App\Model\Product\Subscription::where('order_id',$id)->value('plan_id');
                    $planNameReal = \App\Model\Payment\Plan::where('id',$planIdOld)->value('name');
                    ?>


                    <p class="text-black"><strong>Current Plan: </strong>{{$planNameReal}}</p>

                    <div class="row">

                        <div class="form-group col">

                            <label class="text-black"><strong>Select a new plan</strong> <span class="text-danger"> *</span></label>

                            <div class="custom-select-1">

                                {!! Form::select('plan', ['' => 'Select'] + $plans, null, ['class' => 'form-control upgrade-select', 'onchange' => 'getPrice(this.value)']) !!}

                            </div>
                        </div>

                        <p class="text-black" id="upgrade1" style="display: none;" ><strong>Total Credits remaining on your current plan: </strong><span id="priceOldPlan" class="priceOldPlan"></span></p>

                        <p class="text-black" id="upgrade2" style="display: none;" ><strong>Price for the new plan: </strong><span id="priceNewPlan" class="priceNewPlan"></span></p>

                        <p class="text-black" id="upgrade2" style="display: none;" ><strong>Price to be paid: </strong><span id="priceToPay" class="priceToPay" ></span></p>
                        <div class="overlay" style="display: none;"></div> <!-- Add this line -->

                        <div class="loader-wrapper" style="display: none; background: white;" >
                            <i class="fas fa-spinner fa-spin" style="font-size: 40px;"></i>

                        </div>
                    </div>
                     </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-primary" id="upgradedowngrade"><i class="fas fa-cloud-upload-alt"></i> Change Plan</button>
                    </div>
               
            </div>
        </div>
    </div>



    <div class="modal fade" id="renewal-modal" tabindex="-1" role="dialog" aria-labelledby="autorenewModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="autorenewModalLabel">Auto Renewal</h4>

                    <button type="button" class="btn-close"  id="srclose" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                        <div class="modal-body">
                    <div class="row">
                        <div class="form-group col">
                            <label class="form-label">Select the payment gateway <span class="text-danger">*</span></label>
                            <div class="custom-select-1">
                                <select name="" id="sel-payment" class="form-control">
                                    <option value="" disabled>Choose your option</option>
                                    @foreach($gateways as $gateway)
                                    <option value="{{ strtolower($gateway) }}" {{ $recentPayment && strtolower($gateway) === strtolower($recentPayment->payment_method) ? 'selected' : '' }}>{{ $gateway }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal-footer">

                    <button type="button" class="btn btn-light"  onclick="refreshPage()" data-bs-dismiss="modal">Close</button>

                    <button type="button" id="payment"  class="btn btn-primary" data-bs-dismiss="modal">Save</button>
                </div>
            </div>
        </div>
    </div>
    </div>


    <div class="modal fade" id="stripe-Modal" data-keyboard="false" data-backdrop="static" aria-hidden="true" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button style="position: absolute; top: -10px; right: -10px; width: 30px; height: 30px; border-radius: 50%; background-color: black;" type="button" class="close custom-close" data-dismiss="modal" aria-hidden="true" onclick="refreshPage()">&times;</button>
                    <h4 class="modal-title" id="defaultModalLabel" style="white-space: nowrap;">Stripe payment</h4>
                    <div class="horizontal-images">
                        <img class="img-responsive" src="https://static.vecteezy.com/system/resources/previews/020/975/567/non_2x/visa-logo-visa-icon-transparent-free-png.png">
                        <img class="img-responsive" src="https://pngimg.com/d/mastercard_PNG23.png">
                        <img class="img-responsive" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ2lfp0fkZmeGd6aCOzuIBC1QDTvcyGcM6OGQ&usqp=CAU">
                    </div>
                </div>
                <div id="alertMessage-2"></div>
                <div id="error-1"></div>
                <div class="col-md-12 ">
                    <div class="modal-body">
                        <form id="valid-modal">
                            <div id="payment-element">
                                <!-- Information or instructions -->
                                <div class="form-group row">
                                    <div class="col-md-12 alert alert-info">
                                        Your card information is secure with us. We are performing a verification check of {{currencyFormat(1,getCurrencyForClient(\Auth::user()->country))}}, which will be automatically reversed within a week.
                                    </div>
                                </div>
                                <!-- Card No. input -->
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <input id="card_no" type="tel" class="form-control @error('card_no') is-invalid @enderror" name="card_no" value="{{ old('card_no') }}" required autocomplete="card_no" placeholder="Card No." autofocus>
                                        @error('card_no')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Exp. Month and Exp. Year inputs -->
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <input id="exp_month" type="number" class="form-control @error('exp_month') is-invalid @enderror" name="exp_month" value="{{ old('exp_month') }}" required autocomplete="exp_month" placeholder="Exp. Month(02)" autofocus>
                                        @error('exp_month')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input id="exp_year" type="number" class="form-control @error('exp_year') is-invalid @enderror" name="exp_year" value="{{ old('exp_year') }}" required autocomplete="exp_year" placeholder="Exp. Year(20)" autofocus>
                                        @error('exp_year')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>
                                <!-- CVV input -->
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <input id="cvv" type="password" class="form-control @error('cvv') is-invalid @enderror" name="cvv" required autocomplete="current-password" placeholder="CVV">
                                        @error('cvv')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Amount input -->
                                <div class="form-group row">
                                    <div class="col-md-12">
                                        <input id="amount" type="text" value={{currencyFormat(1,getCurrencyForClient(\Auth::user()->country))}} class="form-control @error('amount') is-invalid @enderror" required autocomplete="current-password" name="amount" placeholder="Amount" readonly>
                                        @error('amount')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Pay button -->
                                <div class="form-group row mb-0">
                                    <div class="col-md-12">
                                        <button type="button" id="pay" class="btn btn-primary btn-block">
                                            {{ __('PAY NOW') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <div class="modal fade" id="confirmStripe" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="autorenewModalLabel">Payment Confirmation</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input class="hidden" id="orderID" value={{$id}}>
                            <p style="color: #333;">Do not refresh or go back.Please complete the process by clicking <strong style="font-weight: bold;">Finish</strong> to complete the payment process.</p>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="confirmStripePayment" class="btn btn-primary" data-bs-dismiss="modal">Finish</button>
                    </div>
                </div>
            </div>
        </div>





    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <form name='razorpayform' action="{!!url('rzpRenewal-disable/'.$order->id)!!}" method="POST">
        {{ csrf_field() }}
        <!--<button id="rzp-button1" class="btn btn-primary pull-right mb-xl" data-loading-text="Loading...">Pay Now</button>-->
        <!--<form name='razorpayform' action="verify.php" method="POST">                                -->
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >


    </form>



    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script>
        function refreshPage() {
            location.reload();
        }

        $(document).ready(function() {
            $("#valid-modal").validate({
                rules: {
                    card_no: {
                        required: true,
                        digits: true,
                        minlength: 15,
                        maxlength: 16,
                    },
                    exp_month: {
                        required: true,
                        digits: true,
                        minlength: 2,
                        maxlength: 2,
                        range: [1, 12]
                    },
                    exp_year: {
                        required: true,
                        digits: true,
                        minlength: 2,
                        maxlength: 2,
                        notPastYear: true
                    },
                    cvv: {
                        required: true,
                        digits: true,
                        rangelength: [3, 4]
                    }
                },
                messages: {
                    card_no: {
                        required: "Card number is required",
                        digits: "Please enter digits only",
                        minlength: "Card number must be at least 15 digits",
                        maxlength: "Card number cannot exceed 16 digits"
                    },
                    exp_month: {
                        required: "Expiration month is required",
                        digits: "Please enter digits only",
                        minlength: "Expiration month must be 2 digits",
                        maxlength: "Expiration month must be 2 digits",
                        range: "Expiration month cannot exceed 12"
                    },
                    exp_year: {
                        required: "Expiration year is required",
                        digits: "Please enter digits only",
                        minlength: "Expiration year must be 2 digits",
                        maxlength: "Expiration year must be 2 digits",
                        notPastYear: "Expiration year cannot be in the past"
                    },
                    cvv: {
                        required: "CVV is required",
                        digits: "Please enter digits only",
                        rangelength: "CVV must be either 3 or 4 digits"
                    }
                },
                errorElement: "span",
                errorPlacement: function(error, element) {
                    error.addClass("invalid-feedback");
                    error.insertAfter(element);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass("is-invalid").removeClass("is-valid");
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass("is-invalid").addClass("is-valid");
                }
            });

            var $form = $("#submit_total");
            var $cardNo = $("#card_no");
            var $expMonth = $("#exp_month");
            var $expYear = $("#exp_year");
            var $cvv = $("#cvv");
            var $payButton = $("#pay");

            $form.on("submit", function(event) {
                // Check if each field is valid
                var isCardNoValid = $cardNo.valid();
                var isExpMonthValid = $expMonth.valid();
                var isExpYearValid = $expYear.valid();
                var isCvvValid = $cvv.valid();

                if (isCardNoValid && isExpMonthValid && isExpYearValid && isCvvValid) {
                    $payButton.prop("disabled", true);
                    $payButton.html("<i class='fa fa-circle-o-notch fa-spin fa-1x'></i> Processing ...");
                } else {
                    event.preventDefault();
                }
            });

            $.validator.addMethod("notPastYear", function(value, element) {
                var currentYear = new Date().getFullYear() % 100;
                var enteredYear = parseInt(value, 10);
                return enteredYear >= currentYear;
            }, "Expiration year cannot be in the past");
        });


        function validateForm() {
            var isValid = $("#valid-modal").valid();
            var isCardNoValid = $('#card_no').valid();
            var isExpMonthValid = $('#exp_month').valid();
            var isExpYearValid = $('#exp_year').valid();
            var isCvvValid = $('#cvv').valid();

            return isValid && isCardNoValid && isExpMonthValid && isExpYearValid && isCvvValid;
        }

    </script>
    <script type="text/javascript">

        $('#srclose').click(function()
        {
            location.reload();
        });
        // $('#strclose').click(function()
        //          {
        //          location.reload();
        //          });

        // Checkout details as a json
        var options = <?php echo $json; ?>


        /**
         * The entire list of Checkout fields is available at
         * https://docs.razorpay.com/docs/checkout-form#checkout-fields
         */
            options.handler = function (response){
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;

                document.razorpayform.submit();
            };

        // Boolean whether to show image inside a white frame. (default: true)
        options.theme.image_padding = false;

        options.modal = {
            ondismiss: function() {
            },
            // Boolean indicating whether pressing escape key
            // should close the checkout form. (default: true)
            escape: true,
            // Boolean indicating whether clicking translucent blank
            // space outside checkout form should close the form. (default: false)
            backdropclose: false
        };

        var rzp = new Razorpay(options);

        $(document).ready(function(){
            var status = $('.renewcheckbox').val();
            if(status ==1) {
                $('#renew').prop('checked',true)
            } else if(status ==0) {
                $('#renew').prop('checked',false)
            }
        });
        $('#renew').on('change',function () {
            if ($(this).prop("checked")) {
                cardUpdate();


            }else{
                var id = $('#order').val();
                $.ajax({
                    url : '{{url("renewal-disable")}}',
                    method : 'post',
                    data : {
                        "order_id" : id,

                    },
                    success: function(response){
                        $('#alertMessage-2').show();
                        var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+response.message+'.</div>';
                        $('#alertMessage-2').html(result+ ".");
                        $("#pay").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                       setTimeout(function() {
                        $('#alertMessage-2').slideUp(3000, function() {
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        });
                    }, 4000);
                        $('#updateButton').hide();
                    },
                })
            }
        });


        $('#cardUpdate').on('click',function(){
            cardUpdate();

        });

        function cardUpdate() {
            $('#renewal-modal').modal('show');
            var id = $('#order').val();
            var domain = window.location.href;
            $('#payment').on('click', function () {
                var pay = $('#sel-payment').val();
                if (pay == null) {
                    $("#payment").html("<i class='fa fa-check'></i> Validate");
                    $('#payerr').show();
                    $('#payerr').html("Please Select the Payment");
                    $('#payerr').focus();
                    $('#sel-payment').css("border-color", "red");
                    $('#payerr').css({ "color": "red" });
                    return false;
                }
                if (pay == 'stripe') {
                    $('#renewal-modal').modal('hide');
                    $('#stripe-Modal').modal('show');

                    $('#pay').on('click', function () {
                        var isValid = validateForm();
                        if (isValid) {
                            $('#pay').html("<i class='fa fa-spinner fa-spin'></i> Please Wait..");
                            $.ajax({
                                url: '{{url("strRenewal-enable")}}',
                                type: 'POST',
                                data: {
                                    "order_id": id,
                                    "card_no": $('#card_no').val(),
                                    "exp_month": $('#exp_month').val(),
                                    "exp_year": $('#exp_year').val(),
                                    "cvv": $('#cvv').val(),
                                    "amount": $('#amount').val(),
                                    "_token": "{!! csrf_token() !!}",
                                },
                                success: function (response) {
                                    if(response.type == 'success'){
                                    $('#stripe-Modal').modal('hide');
                                    $('#alertMessage-2').show();
                                    $('#updateButton').show();
                                    var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>' + response.message + '.</div>';
                                    $('#alertMessage-2').html(result + ".");
                                    $("#pay").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
                                    setTimeout(function() {
                                        location.reload();
                                    }, 3000);

                                    }
                                    else{
                                    window.location.href = response;
                                    }
                                   

                                },
                                error: function (data) {
                                    var errorMessage = data.responseJSON.result;
                                    $('#stripe-Modal').modal('hide');
                                    $("#pay").attr('disabled', false);
                                    $("#pay").html("Pay now");
                                    $('html, body').animate({ scrollTop: 0 }, 500);
                                    var html = '<div class="alert alert-danger alert-dismissable alert-content"><strong><i class="fas fa-exclamation-triangle"></i>Oh Snap! </strong>' + data.responseJSON.result + ' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><br><ul>';
                                    $('#error-1').show();
                                    document.getElementById('error-1').innerHTML = html;
                                }
                            });
                        }
                    });
                } else if (pay == 'razorpay') {
                    $('#renewal-modal').modal('hide');
                    rzp.open();
                    e.preventDefault();
                }
            });
        }
    </script>
    <script type="text/javascript">

        $('#showpayment-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                "url":  '{!! Url('get-my-payment-client/'.$order->id.'/'.$user->id) !!}',
                error: function(xhr) {
                    if(xhr.status == 401) {
                        alert('Your session has expired. Please login again to continue.')
                        window.location.href = '/login';
                    }
                }

            },

            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": '<img id="blur-bg" class="backgroundfadein" style="top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;" src="{!! asset("lb-faveo/media/images/gifloader3.gif") !!}">'
            },

            columns: [
                {data: 'number', name: 'invoice.number'},
                {data: 'total', name: 'total'},
                {data: 'payment_method', name: 'payment_method'},
                {data: 'payment_status', name: 'payment_status'},
                {data: 'created_at', name: 'created_at'},
            ],
            "fnDrawCallback": function( oSettings ) {
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip({
                        container : 'body'
                    });
                });
                $('.loader').css('display', 'none');
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });


        $('.done').click(function()
        {
            $(this).hide();
        });

        $('#showorder-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                "url":  '{!! Url('get-my-invoices/'.$order->id.'/'.$user->id) !!}',
                error: function(xhr) {
                    if(xhr.status == 401) {
                        alert('Your session has expired. Please login again to continue.')
                        window.location.href = '/login';
                    }
                }

            },
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": '<img id="blur-bg" class="backgroundfadein" style="top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;" src="{!! asset("lb-faveo/media/images/gifloader3.gif") !!}">'
            },

            columns: [
                {data: 'number', name: 'number'},
                {data: 'products', name: 'products'},
                {data: 'date', name: 'date'},
                {data: 'total', name: 'total'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action'}
            ],
            "fnDrawCallback": function( oSettings ) {
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip({
                        container : 'body'
                    });
                });
                $('.loader').css('display', 'none');
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });


        $("#reissueLic").click(function(){
            if ($('#domainRes').val() == 1) {
                var oldDomainId = $(this).attr('data-id');
                $("#orderId").val(oldDomainId);
                $("#domainModal").modal('show');
                $("#domainSave").on('click',function(){
                    var id = $('#orderId').val();
                    $.ajax ({
                        type: 'patch',
                        url : "{{url('reissue-license')}}",
                        data : {'id':id},
                        beforeSend: function () {
                            $('#response1').html( "<img id='blur-bg' class='backgroundfadein' style='top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;' src='{!! asset('lb-faveo/media/images/gifloader3.gif') !!}'>");

                        },

                        success: function (data) {
                            if (data.message =='success'){
                                var result =  '<div class="alert alert-success alert-dismissable"><strong><i class="fa fa-check"></i> Success! </strong> '+data.update+' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                                $('#response1').html(result);
                                $('#response1').css('color', 'green');
                                setTimeout(function(){
                                    window.location.reload();
                                },3000);
                            }

                        }

                    });
                });
            } else {
                var oldDomainName = $(this).attr('data-name');
                var oldDomainId = $(this).attr('data-id');
                $("#licesnseModal").modal();
                $("#newDomain").val(oldDomainName);
                $("#orderId").val(oldDomainId);

                $("#licenseSave").on('click',function(){
                    var pattern = new RegExp(/^((?!-))(xn--)?[a-z0-9][a-z0-9-_]{0,61}[a-z0-9]{0,1}\.(xn--)?([a-z0-9\-]{1,61}|[a-z0-9-]{1,30}\.[a-z]{2,})$/);
                    var ip_pattern = new RegExp(/^\b((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}\b/);
                    if (pattern.test($('#newDomain').val()) || ip_pattern.test($('#newDomain').val())) {
                        $('#domaincheck').hide();
                        $('#newDomain').css("border-color","");
                    }
                    else{
                        $('#domaincheck').show();
                        $('#domaincheck').html("Please enter a valid Domain in the form domain.com or sub.domain.com or enter a valid IP");
                        $('#domaincheck').focus();
                        $('#newDomain').css("border-color","red");
                        $('#domaincheck').css({"color":"red","margin-top":"5px"});
                        domErr = false;
                        return false;

                    }
                    var domain = $('#newDomain').val();
                    var id = $('#orderId').val();

                    $.ajax ({
                        type: 'patch',
                        url : "{{url('change-domain')}}",
                        data : {'domain':domain,'id':id},
                        beforeSend: function () {
                            $('#response').html( "<img id='blur-bg' class='backgroundfadein' style='top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;' src='{!! asset('lb-faveo/media/images/gifloader3.gif') !!}'>");

                        },
                        success: function (data) {
                            if (data.message =='success'){
                                var result =  '<div class="alert alert-success alert-dismissable"><strong><i class="far fa-thumbs-up"></i> Well Done! </strong> '+data.update+' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                                $('#response').html(result);
                                $('#response').css('color', 'green');
                                setTimeout(function(){
                                    window.location.reload();
                                },3000);
                            }

                        }, error: function(err) {
                            console.log(err);
                        }

                    });
                });
            }
        });


        $(document).ready(function() {
            $('#changeDomain').on('click', function() {
                $('#changeDomain').attr('disabled',true);
                $('#changeDomain').html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i> Please Wait...");
                $('.loader-wrapper').show();
                $('.overlay').show(); // Show the overlay
                $('.modal-body').css('pointer-events', 'none');
                var newDomain = $('#clouduserdomain').val();
                var currentDomain = "{!! \App\Model\Order\InstallationDetail::where('order_id', $id)->latest()->value('installation_path') !!}";
                var license_code = "{!!$order->serial_key!!}";
                var productId = "{!! $order->product !!}";

                $.ajax({
                    type: "POST",
                    data: { 'newDomain': newDomain, 'currentDomain': currentDomain,'lic_code':license_code,'product_id':productId},
                    beforeSend: function() {
                        $('#response').html("<img id='blur-bg' class='backgroundfadein' style='width: 50px; height: 50px; display: block; position: fixed;' src='{!! asset('lb-faveo/media/images/gifloader3.gif') !!}'>");
                    },
                    url: "{{ url('change/domain') }}",
                    success: function (data) {
                        if (data.success ==true){
                            var result =  '<div class="alert alert-success alert-dismissable"><strong><i class="far fa-thumbs-up"></i> Well Done! </strong> '+data.message+'</div>';
                            $('#success-domain').html(result).css('color', 'green').show();
                            $('#changeDomain').attr('disabled',false);
                            $('#changeDomain').html("<i class='fa fa-globe'>&nbsp;&nbsp;</i>Change domain");
                            $('.loader-wrapper').hide();
                            $('.overlay').hide(); // Hide the overlay
                            $('.modal-body').css('pointer-events', 'auto');
                            // Auto-disappear after 5 seconds (5000 milliseconds)
                            setTimeout(function() {
                                $('#success-domain').fadeOut('slow', function() {
                                    $(this).empty().hide(); // Clear and hide the error message after fading out
                                });
                            }, 30000);
                        }

                    }, error: function(data) {
                        if (data.responseJSON.success === false) {
                            var result = '<div class="alert alert-danger alert-dismissable"><strong><i class="far fa-thumbs-down"></i> Oops! </strong> ' + data.responseJSON.message + ' </div>';
                            $('#failure-domain').html(result).css('color', 'red').show(); // Show the error message
                            $('#changeDomain').attr('disabled', false);
                            $('#changeDomain').html("<i class='fa fa-globe'>&nbsp;&nbsp;</i>Change domain");
                            $('.loader-wrapper').hide();
                            $('.overlay').hide(); // Hide the overlay
                            $('.modal-body').css('pointer-events', 'auto');

                            // Auto-disappear after 5 seconds (5000 milliseconds)
                            setTimeout(function() {
                                $('#failure-domain').fadeOut('slow', function() {
                                    $(this).empty().hide(); // Clear and hide the error message after fading out
                                });
                            }, 10000); // Change this timeout to your desired duration
                        }
                    }

                });
            });
        });

        $(document).ready(function() {
            $('#agentNumber').on('click', function() {
                $('#agentNumber').attr('disbaled',true);
                $('#agentNumber').html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i> Please Wait...");
                $('.loader-wrapper').show();
                $('.overlay').show(); // Show the overlay
                $('.modal-body').css('pointer-events', 'none');
                var newAgents = $('#numberAGt').val();
                var orderId = {!! $id !!};
                var productId ={!! $product->id !!};
                var subId = {!! $subscription->id !!};

                $.ajax({
                    type: "POST",
                    data: { 'newAgents': newAgents, 'orderId': orderId, 'product_id':productId, 'subId': subId},
                    beforeSend: function() {
                        $('#response').html("<img id='blur-bg' class='backgroundfadein' style='width: 50px; height: 50px; display: block; position: fixed;' src='{!! asset('lb-faveo/media/images/gifloader3.gif') !!}'>");
                    },
                    url: "{{ url('changeAgents') }}",
                    success: function(response) {
                        $('#agentNumber').attr('disbaled',false);
                        $('#agentNumber').html("<i class='fa fa-users'>&nbsp;&nbsp;</i>  Update Agents");
                        $('.loader-wrapper').hide();
                        $('.overlay').hide(); // Hide the overlay
                        $('.modal-body').css('pointer-events', 'auto');
                        window.location.href = response;
                    },
                    error: function(data) {
                        if (data.responseJSON.success == false) {
                            $('#agentNumber').attr('disabled', false);
                            $('#agentNumber').html("<i class='fa fa-users'>&nbsp;&nbsp;</i>  Update Agents");
                            var result = '<div class="alert alert-danger alert-dismissable"><strong><i class="far fa-thumbs-down"></i> Oops! </strong> ' + data.responseJSON.message + ' </div>';
                            $('#failure-agent').html(result).css('color', 'red').show();
                            $('.loader-wrapper').hide();
                            $('.overlay').hide(); // Hide the overlay
                            $('.modal-body').css('pointer-events', 'auto');

                            // Auto-disappear after 5 seconds (5000 milliseconds)
                            setTimeout(function() {
                                $('#failure-agent').fadeOut('slow', function() {
                                    $(this).empty().hide();
                                });
                            }, 10000);
                        }
                    }
                });
            });
        });

    </script>
    <script>
        function getPrice(val) {
            $('.loader-wrapper').show();
            $('.overlay').show(); // Show the overlay
            $('.modal-body').css('pointer-events', 'none');
            $.ajax({
                type: "POST",
                url: "{{url('get-cloud-upgrade-cost')}}",
                data: {'plan': val, 'agents': '{{$latestAgents}}', 'orderId': '{{$id}}'},
                success: function (data) {
                    $(".priceperagent").val(data.priceperagent);
                    $(".priceOldPlan").text(data.priceoldplan);
                    $(".priceNewPlan").text(data.pricenewplan);
                    $(".discount").text(data.discount);
                    $(".priceToPay").text(data.price_to_be_paid);
                    $('.loader-wrapper').hide();
                    $('.overlay').hide(); // Hide the overlay
                    $('.modal-body').css('pointer-events', 'auto');

                }
            });
        }


    </script>
    
       <script>

        $(document).ready(function () {
            $('#numberAGt').on('input', function () {
                $(this).prop("disabled", true);
                $('#agentNumber').attr('disabled',true);
                var selectedNumber = $(this).val();
                var oldAgents = '{{$latestAgents}}';
                var orderId = '{{$id}}';
                $('.loader-wrapper').show();
                $('.overlay').show(); // Show the overlay
                $('.modal-body').css('pointer-events', 'none');

                $.ajax({
                    type: 'POST',
                    url: "{{url('get-agent-inc-dec-cost')}}",
                    data: { 'number': selectedNumber, 'oldAgents':  oldAgents, 'orderId' : orderId},
                    success: function (data) {
                        // Update the other fields based on the API response
                        $('#priceagent').text(data.pricePerAgent);
                        $('#Totalprice').val(data.totalPrice);
                        $('#pricetopay').text(data.priceToPay);
                        $('#agentNumber').attr('disabled',false);
                        $('.loader-wrapper').hide();
                        $('.overlay').hide(); // Hide the overlay
                        $('.modal-body').css('pointer-events', 'auto');
                    },
                });
                $(this).prop("disabled", false);

            });
        });
    </script>

    <script type="text/javascript">


        $(document).ready(function() {
            $('#upgradedowngrade').on('click', function() {
                $('#upgradedowngrade').attr('disabled',true);
                $('#upgradedowngrade').html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i> Please Wait...");
                $('.loader-wrapper').show();
                $('.overlay').show(); // Show the overlay
                $('.modal-body').css('pointer-events', 'none');
                var planId = $('select[name="plan"]').val();
                var user = $('input[name="user"]').val();
                var agents = {{$latestAgents}};
                var orderId = {!! $id !!};
                $.ajax({
                    type: "POST",
                    data: { 'id': planId,'agents': agents,'userId': user, 'orderId':orderId},
                    beforeSend: function() {
                        $('#response').html("<img id='blur-bg' class='backgroundfadein' style='width: 50px; height: 50px; display: block; position: fixed;' src='{!! asset('lb-faveo/media/images/gifloader3.gif') !!}'>");
                    },
                    url: "{{ url('upgradeDowngradeCloud') }}",
                    success: function (data) {
                        window.location.href = data.redirectTo;
                        if (data.success ==true){
                            window.location = data.redirectTo;
                            var result =  '<div class="alert alert-success alert-dismissable"><strong><i class="far fa-thumbs-up"></i> Well Done! </strong> '+data.message+' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                            $('#response-upgrade').html(result);
                            $('#response-upgrade').css('color', 'green');
                            $('#upgradedowngrade').attr('disabled',false);
                            $('#upgradedowngrade').html("<i class='fas fa-cloud-upload-alt'>&nbsp;&nbsp;</i>Change Plan");
                            $('.loader-wrapper').hide();
                            $('.overlay').hide(); // Hide the overlay
                            $('.modal-body').css('pointer-events', 'auto');
                        }

                    },  error: function(data) {
                        if (data.responseJSON.success == false) {
                            var result = '<div class="alert alert-danger alert-dismissable"><strong><i class="far fa-thumbs-down"></i> Oops! </strong> ' + data.responseJSON.message + '</div>';
                            $('#failure-upgrade').html(result).css('color', 'red').show();
                            $('#upgradedowngrade').attr('disabled',false);
                            $('#upgradedowngrade').html("<i class='fas fa-cloud-upload-alt'>&nbsp;&nbsp;</i>Change Plan");
                            $('.loader-wrapper').hide();
                            $('.overlay').hide(); // Hide the overlay
                            $('.modal-body').css('pointer-events', 'auto');

                            // Auto-disappear after 5 seconds (5000 milliseconds)
                            setTimeout(function() {
                                $('#failure-upgrade').fadeOut('slow', function() {
                                    $(this).empty().hide();
                                });
                            }, 10000);
                        }
                    }

                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.upgrade-select').on('change', function() {
                var selectedPlanId = $(this).val();
                if (selectedPlanId !== '') {
                    $('#upgrade1, #upgrade2, #upgrade3').show();
                } else {
                    $('#upgrade1, #upgrade2, #upgrade3').hide();
                }
            });
        });


        $(document).ready(function() {
            $('#numberAGt').on('input', function() {
                var enteredValue = $(this).val();
                if (enteredValue !== '') {
                    $('#pricetopaid').show();
                } else {
                    $('#pricetopaid').hide();
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const copyButton = document.getElementById('copyButton');
            const serialKey = document.getElementById('serialKey').innerText;
            const copiedMessage = document.getElementById('copiedMessage');

            copyButton.addEventListener('click', () => {
                const textarea = document.createElement('textarea');
                textarea.value = serialKey;

                document.body.appendChild(textarea);

                textarea.select();

                document.execCommand('copy');

                document.body.removeChild(textarea);

                const tooltip = new bootstrap.Tooltip(copyButton);
                copyButton.removeAttribute('title');


                copiedMessage.classList.remove('hidden');
                setTimeout(() => copiedMessage.classList.add('hidden'), 2000);
            });
        });
    </script>
    <script>
  function refreshPage() {
    setTimeout(function() {
      location.reload();
    }, 1000); 
  }
</script>

<script>
$(document).ready(function() {
    let hash = window.location.hash;
    if (hash !== '') {
        $('.nav-link').removeClass('active');
        $('.tab-pane').removeClass('active show');

        $(`a[href="${hash}"]`).addClass('active');
        $(hash).addClass('active show');
    }

    $('.nav-link').on('click', function(e) {
        let hash = $(this).attr('href');
        history.replaceState(null, null, hash);
    });
});
</script>

<script>

$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const paymentIntent = urlParams.get('payment_intent');
    if (paymentIntent) {
        openModalIfQueryParamExists();
        
    }
});

</script>


<script>
       function openModalIfQueryParamExists() {
        const urlParams = new URLSearchParams(window.location.search);
        const paymentIntent = urlParams.get('payment_intent');
        const orderId = $('#orderID').val();
        var currentUrl = window.location.origin + window.location.pathname;
        var newUrl = currentUrl + '#auto-renew';
        $.ajax({
            url: "{{ url('stripeUpdatePayment/confirm') }}",
            method: 'POST',
            data: { payment_intent: paymentIntent,orderId: orderId },
            success: function(response) {
                $('#confirmStripe').modal('hide');
                $('html, body').animate({ scrollTop: 0 }, 500);
                $('#alertMessage-2').show();
                
                var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>' + response.message + '.</div>';
                $('#alertMessage-2').html(result + ".");
                $('#updateButton').show();
                setTimeout(function() {
                    window.location.href = newUrl; 
                }, 5000);
            },
               error: function(xhr, status, error) {
                var errorMessage = 'Something went wrong. Try with a different payment method.';
                $('#confirmStripe').modal('hide');
                $('html, body').animate({ scrollTop: 0 }, 500);
                var html = '<div class="alert alert-danger alert-dismissable alert-content"><strong><i class="fas fa-exclamation-triangle"></i> Oh Snap! </strong>' + errorMessage + ' <br><ul>';
                $('#error-1').show();
                document.getElementById('error-1').innerHTML = html;
                setTimeout(function() {
                    window.location.href = newUrl; 
                }, 5000);
            }

        });
       
    }
    


</script>

                  <script type="text/javascript">
                          $('#installationDetail-table').DataTable({
                              processing: true,
                              serverSide: true,
                               stateSave: false,
                            //   order: [[3, "asc"]],
                                ajax: {
                              "url":  "{{Url('get-installation-details/'.$order->id)}}",
                                 error: function(xhr) {
                                 if(xhr.status == 401) {
                                  alert('Your session has expired. Please login again to continue.')
                                  window.location.href = '/login';
                                 }
                              }

                              },
                             
                              "oLanguage": {
                                  "sLengthMenu": "_MENU_ Records per page",
                                  "sSearch"    : "Search: ",
                                  "sProcessing": '<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>'
                              },

                              columns: [
                              
                                  {data: 'path', name: 'path'},
                                  {data: 'ip', name: 'ip'},
                                  {data: 'version', name: 'version'},
                                  {data: 'active', name: 'active'},
                                  
                              ],
                              "fnDrawCallback": function( oSettings ) {
                                  $(function () {
                                      $('[data-toggle="tooltip"]').tooltip({
                                          container : 'body'
                                      });
                                  });
                                  $('.loader').css('display', 'none');
                              },
                              "fnPreDrawCallback": function(oSettings, json) {
                                  $('.loader').css('display', 'block');
                              },
                          });
                        </script>
    <style>
        .hidden {
            display: none;
        }
        #copiedMessage {
            position: absolute;
            top: -30px;
            left: 45%;
            color: green;

        }
    </style>



@stop