@extends('themes.default1.layouts.front.master')
@section('title')
    {{ __('message.orders') }}
@stop
@section('nav-orders')
    active
@stop
@section('page-heading')
    {{ __('message.order_details')}}
@stop
@section('breadcrumb')
    <style>
        option
        {
            font-size: 12px;
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
        #showpayment-table_paginate{
        margin-right: -20px !important;

}
    .table th{
        border-top: unset !important;
    }
        #card-number, #card-expiry, #card-cvc {
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-color: rgba(0, 0, 0, 0.09);
            height: calc(1.5em + 0.75rem + 2px);
            min-height: calc(1.5em + 1rem + 2px);
            display: block;
            width: 100%;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            background-clip: padding-box;
            border-top-color: rgb(206, 212, 218);
            border-right-color: rgb(206, 212, 218);
            border-bottom-color: rgb(206, 212, 218);
            border-left-color: rgb(206, 212, 218);
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            border-radius: .375rem;
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            align-content: center;
        }
        .StripeElement--invalid {
            border: 1px solid #df1b41 !important;
        }

        .auto-renew-status{
            display: inline-block !important;
        }

    </style>
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home')}}</a></li>
    @else
        <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home')}}</a></li>
    @endif
    <li class="active text-dark">{{ __('message.order_details')}}</li>
@stop


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
                                <strong>{{ __('message.order_number')}}</strong> <br>
                                #{{$order->number}}
                            </span>
                    </div>
                    <div class="text-center mt-4 mt-md-0">
                            <span>
                                <strong>{{ __('message.date')}}</strong> <br>
                                {!! getDateHtml($order->created_at) !!}
                            </span>
                    </div>
                    <div class="text-center mt-4 mt-md-0">
                            <span>
                                <strong>{{ __('message.status')}}</strong><br>
                                {{$order->order_status}}
                            </span>
                    </div>
                    <div class="text-center mt-4 mt-md-0">
                            <span>
                                <strong>{{ __('message.expiry_date')}}</strong><br>
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
                            <h5 class="modal-title" id="exampleModalLabel">{{ __('message.enter_domain_host')}}</h5>
                        </div>
                        <div class="modal-body">
                            <form method="GET" action="{{url('uploadFile')}}">
                                {!! csrf_field() !!}
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">{{ __('message.domain_name')}}</label>
                                    <input type="text" class="form-control" id="recipient-name" placeholder="https://faveohelpdesk.com/public" name="domain" value="" onkeydown="return event.key != 'Enter';">
                                    {!! html()->hidden('orderNo', $order->number) !!}
                                    {!! html()->hidden('userId', $user->id) !!}
                                    <br>
                                    <div class="modal-footer">
                                        <button type="button" id="close" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close')}}</button>
                                        @if((!Storage::disk('public')->exists('faveo-license-{'.$order->number.'}.txt')) || $order->is_downloadable==0)
                                            <button type="submit" id="domainSave" class="done btn btn-primary" {{$order->where('number',$order->number)->update(['is_downloadable'=> 1])}}><i class="fas fa-save"></i>&nbsp;{{ __('message.done')}}</button>
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
                               data-hash-offset-lg="500" data-hash-delay="500">{{ __('message.license_details')}}
                            </a>
                        </li>

                        <li class="nav-item">

                            <a class="nav-link" href="#users" data-bs-toggle="tab" data-hash data-hash-offset="0"
                               data-hash-offset-lg="500" data-hash-delay="500">{{ __('message.user_details')}}
                            </a>
                        </li>

                        <li class="nav-item">

                            <a class="nav-link" href="#invoice" data-bs-toggle="tab" data-hash data-hash-offset="0"
                               data-hash-offset-lg="500" data-hash-delay="500">{{ __('message.invoice_list')}}
                            </a>
                        </li>

                        <li class="nav-item">

                            <a class="nav-link" href="#receipt" data-bs-toggle="tab" data-hash data-hash-offset="0"
                               data-hash-offset-lg="500" data-hash-delay="500">{{ __('message.payment_receipts')}}
                            </a>
                        </li>
                        @if(in_array($product->id,cloudPopupProducts()) && $order->order_status!='Terminated' && $subscription->is_deleted == 0)

                            <li class="nav-item">

                                <a class="nav-link" href="#cloud" data-bs-toggle="tab" data-hash data-hash-offset="0"
                                   data-hash-offset-lg="500" data-hash-delay="500">{{ __('message.cloud_settings')}}
                                </a>
                            </li>
                        @endif

                        @if($price == '0' && !in_array($product->id,cloudPopupProducts()) && $order->order_status!='Terminated')

                            <li class="nav-item">

                                <a class="nav-link" href="#auto-renew" data-bs-toggle="tab" data-hash data-hash-offset="0"
                                   data-hash-offset-lg="500" data-hash-delay="500">{{ __('message.auto_renewal')}}
                                </a>
                            </li>
                        @elseif($order->order_status!='Terminated')
                            <li class="nav-item">

                                <a class="nav-link" href="#auto-renew" data-bs-toggle="tab" data-hash data-hash-offset="0"
                                   data-hash-offset-lg="500" data-hash-delay="500">{{ __('message.auto_renewal')}}
                                </a>
                            </li>
                        @endif
                        @if($whatsappStatus)
                            <li class="nav-item">

                                <a class="nav-link" href="#whats-app-integration" data-bs-toggle="tab" data-hash data-hash-offset="0"
                                   data-hash-offset-lg="500" data-hash-delay="500">WhatsApp SignUp
                                </a>
                            </li>
                            @endif
                        @php $hasDeployableUploads = \App\Model\Product\ProductUpload::where('product_id', $product->id)->where('is_private', 0)->whereNotNull('file')->where('file', '!=', '')->exists(); @endphp
                        @if($hasDeployableUploads && $order->order_status != 'Terminated')
                            <li class="nav-item">
                                <a class="nav-link" href="#deploy" data-bs-toggle="tab" data-hash data-hash-offset="0"
                                   data-hash-offset-lg="500" data-hash-delay="500">
                                    Deploy
                                </a>
                            </li>
                        @endif
                    </ul>
                </aside>
            </div>

            <div class="col-lg-9 mt-2">
                @if($order->order_status != 'Terminated')
                    @if(!empty($terminatedOrderId))
                        <p class="order-links">
                            {{ __('message.order')}}: <b>{{$order->number}}</b>
                            {{ __('message.has_been_generated')}} <a class="order-link" href="{{$terminatedOrderId}}">{{$terminatedOrderNumber}}</a> {{ __('message.was_terminated')}}
                        </p>
                    @endif
                    <input type="hidden" name="domainRes" id="domainRes" value={{$allowDomainStatus}}>


                    <div class="tab-pane tab-pane-navigation active" id="license" role="tabpanel">


                        <div class="row">

                            <div class="col">

                                <div class="row align-items-center">

                                    <div class="col-sm-5">

                                        <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                            <span class="mb-2 font-weight-bold">{{ __('message.license_code')}}</span>
                                        </div>
                                    </div>

                                    <div class="col-sm-7">
                                        <span id="serialKey">{{$order->serial_key}}</span>

                                        <a href="#" class="btn btn-light-scale-2 text-black btn-sm ms-4" id="copyButton" data-bs-toggle="tooltip" title="{{ __('message.copy') }}">
                                            <i class="fas fa-copy"></i>
                                        </a>

                                        <span id="copiedMessage" class="hidden">{{ __('message.copied')}}</span>

                                        @if ($licenseStatus == 1)
                                            @if(!in_array($product->id,cloudPopupProducts()) && $price != '0')

                                                <a class="btn btn-light-scale-2 btn-sm text-black btn-sm" data-bs-toggle="tooltip" title="{{ __('message.reissue_license') }}" id="reissueLic" data-id="{{$order->id}}" data-name="{{$order->domain}}" {{!Storage::disk('public')->exists('faveo-license-{'.$order->number.'}.txt') || $order->license_mode!='File' ? "enabled" : "disabled"}}>
                                                  <i class="fas fa-id-card-alt"></i>
                                                    @elseif(!in_array($product->id,cloudPopupProducts()) && $price == '0')
                                                        <a class="btn btn-light-scale-2 btn-sm text-black btn-sm" data-bs-toggle="tooltip" title="{{ __('message.reissue_license') }}" id="reissueLic" data-id="{{$order->id}}" data-name="{{$order->domain}}" {{!Storage::disk('public')->exists('faveo-license-{'.$order->number.'}.txt') || $order->license_mode!='File' ? "enabled" : "disabled"}}>
                                                          <i class="fas fa-id-card-alt"></i>
                                                            @elseif($product->type == '4' && $price != '0')
                                                                <a class="btn btn-light-scale-2 btn-sm text-black btn-sm" data-bs-toggle="tooltip" title="{{ __('message.reissue_license') }}" id="reissueLic" data-id="{{$order->id}}" data-name="{{$order->domain}}" {{!Storage::disk('public')->exists('faveo-license-{'.$order->number.'}.txt') || $order->license_mode!='File' ? "enabled" : "disabled"}}>
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

                                            <span class="mb-2 font-weight-bold">{{ __('message.license_expiry_date')}}</span>
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

                                            <span class="mb-2 font-weight-bold">{{ __('message.update_expiry_date')}}</span>
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

                                            <span class="mb-2 font-weight-bold">{{ __('message.localized_license')}}</span>
                                        </div>
                                    </div>

                                    <div class="col-sm-7">

                                     <button class="btn btn-dark mb-2 btn-sm" id="defaultModalLabel" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo" {{!Storage::disk('public')->exists('faveo-license-{'.$order->number.'}.txt') || $order->is_downloadable==0 ? "enabled" : "disabled"}}>{{ __('message.download_license_file')}}</button>
                                     <a href="{{url('downloadPrivate/'.$order->number)}}"><button class="btn btn-dark mb-2 btn-sm" onclick="refreshPage()">{{ __('message.download_license_key')}}</button></a>
                                     <i class="fa fa-info ml-2" data-bs-toggle="tooltip" title="{{ __('message.license_mandatory')}}" >{!!tooltip('Edit')!!}</i>


                                    </div>
                                </div>
                                @endif

                                <div class="row"><div class="col"><hr class="solid my-3"></div></div>
                                <br >

                                <div class="table-responsive">
                                     <table id="installationDetail-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                                      <thead>
                                      <tr>
                                      <th >{{ __('message.installation_path')}}</th>
                                      <th>{{ __('message.installation_ip')}}</th>
                                      <th>{{ __('message.version')}} </th>
                                      <th>{{ __('message.last_active')}}</th>
                                        
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
                                <p class="termination-notice"><b>{{ __('message.imp_termination_notice')}}</b></p>
                                <p class="termination-description">
                                    {{ __('message.order_msg')}}
                                </p>
                                <p class="order-links">
                                    {{ __('message.termination_order')}} <b>{{$order->number}}</b>
                                    {{ __('message.upgrade_new_order')}} <a class="order-link" href="{{$newOrder[0]->id}}">{{$newOrder[0]->number}}</a>.
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

                                        <span class="mb-2 font-weight-bold">{{ __('message.client_name')}}</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">{{ucfirst($user->first_name)}}</div>
                            </div>

                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">{{ __('message.email')}}:</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">{{$user->email}}</div>
                            </div>

                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">{{ __('message.mobile')}}:</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">@if($user->mobile_code)(<b>+</b>{{$user->mobile_code}})@endif&nbsp;{{$user->mobile}}</div>
                            </div>

                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">{{ __('message.address')}}:</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">{{$user->address}}</div>
                            </div>

                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">{{ __('message.country')}}:</span>
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
                                <th>{{ __('message.number')}}</th>
                                <th>{{ __('message.product')}}</th>
                                <th>{{ __('message.date')}}</th>
                                <th>{{ __('message.total')}}</th>
                                <th>{{ __('message.status')}}</th>
                                <th>{{ __('message.action')}}</th>
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
                                <th>{{ __('message.invoice_no')}}</th>
                                <th>{{ __('message.total')}}</th>
                                <th>{{ __('message.method')}}</th>
                                <th>{{ __('message.status')}}</th>
                                <th>{{ __('message.created_at')}}</th>
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

                                <span class="font-weight-normal text-4">{{ __('message.plan_expiry')}} <strong class="font-weight-bold">{!! getDateHtml($subscription->ends_at) !!}</strong> </span>
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

                                        <div class="feature-box-info">

                                            <h4 class="text-4 mt-3 mb-2 text-color-grey">{{ __('message.change_cloud_domain')}}</h4>

                                            <p class="mb-2"><strong class="text-black text-2">{{ __('message.current_domain_name')}}</strong> {{$installation_path}}</p>

                                            <p class="mb-0 text-2">{{ __('message.click_customising_domain')}}</p>
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

                                            <h4 class="text-4 mt-3 mb-2 text-color-grey">{{ __('message.increase_decrease_agents')}}</h4>

                                            <p class="mb-2"><strong class="text-black text-2">{{ __('message.current_no_agents')}} </strong>{{$latestAgents}}</p>

                                            <p class="mb-0 text-2">{{ __('message.update_agent_count')}}</p>
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

                                                <h4 class="text-4 mt-3 mb-2 text-color-grey">{{ __('message.upgrade_downgrade_cloud')}}</h4>

                                                <p class="mb-2"><strong class="text-black text-2">{{ __('message.current_plan')}}</strong> {{$planName}}</p>

                                                <p class="mb-0 text-2">{{ __('message.change_cloud_plan')}}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <h6 class="mb-1"><i>{{ __('message.current_plan')}} {{$planName}}</i></h6>
                        @endif


                    </div>
                </div>


                <div class="tab-pane tab-pane-navigation" id="auto-renew" role="tabpanel">

                    <div class="row">

                        <div class="col">

                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">{{ __('message.auto_renewal')}}:</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">
                                    <div class="form-check form-switch auto-renew-status" tabindex="0"
                                         data-bs-toggle="tooltip"
                                         data-bs-title="{{ $ExistingPlanPirce ? 'Auto renewal status' : 'No active plan available to active auto renewal' }}">

                                        <input id="renew" value="{{ $statusAutorenewal }}" name="is_subscribed"
                                               class="form-check-input renewcheckbox" type="checkbox" role="switch"{{ !$ExistingPlanPirce ? 'disabled' : '' }}>

                                    </div>
                                </div>
                            </div>

                        <div class="row"><div class="col"><hr class="solid my-3"></div></div>

                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">{{ __('message.status')}}:</span>
                                    </div>
                                </div>

                              <div class="col-sm-7">
                                @if($statusAutorenewal == 1)
                                    <span class="text-success font-weight-bold">{{ __('message.active')}}</span>
                                @else
                                    <span class="text-danger font-weight-bold">{{ __('message.inactive')}}</span>
                                @endif
                            </div>

                            </div>

                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>
                            @if($statusAutorenewal == 1 && $payment_log)
                            <div class="row align-items-center">

                                <div class="col-sm-5">

                                    <div class="pe-3 pe-sm-5 pb-3 pb-sm-0 border-right-light">

                                        <span class="mb-2 font-weight-bold">{{ __('message.payment_gateway')}}</span>
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

                                        <span class="mb-2 font-weight-bold">{{ __('message.subscription_enabled_date')}}</span>
                                    </div>
                                </div>

                                <div class="col-sm-7">{!! getDateHtml($payment_log->date) !!}</div>
                            </div>
                            <div class="row"><div class="col"><hr class="solid my-3"></div></div>
                            @endif

                        </div>
                    </div>
                </div>

    <div class="tab-pane tab-pane-navigation" id="whats-app-integration" role="tabpanel">
        <div id="alertMessage-22"></div>
        @if($actualWhatsappStatus)
        <div class="row mb-4">
            <div class="col">
                <button id="get-url" style="background-color: #1877f2; border: 0; border-radius: 4px; color: #fff; cursor: pointer; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: bold; height: 40px; padding: 0 24px;">
                    {{ __('message.add_new_number')}}
                </button>
            </div>
        </div>
@endif
        <div class="row">
            <div class="table-responsive">
                <table id="shownumber-table" class="table table-striped table-bordered mw-auto" cellspacing="0" width="100%" styleClass="borderless">
                    <thead>
                    <tr>
{{--                        <th>UserName</th>--}}
                        <th>{{__('message.phone_number')}}</th>
                        <th>{{__('message.waba_id')}}</th>
                        <th>{{__('message.phone_number_id')}}</th>
                        <th>{{__('message.business_id')}}</th>
                        <th>{{__('message.whatsapp_access_token')}}</th>
                        <th>{{__('message.create_at')}}</th>
                        <th>{{__('message.action')}}</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    @if($hasDeployableUploads && $order->order_status != 'Terminated')
    <div class="tab-pane tab-pane-navigation" id="deploy" role="tabpanel">

    <style>
    /* ── Faveo Deploy · Design System ──────────────────────────────────── */
    #fv-deploy {
      --fv-blue-50:#e8f4fb; --fv-blue-100:#d0e9f7; --fv-blue-500:#0088cc;
      --fv-blue-600:#0077b3; --fv-blue-700:#006699; --fv-blue-800:#004d80;
      --fv-slate-50:#f8f9fa; --fv-slate-100:#f0f2f5; --fv-slate-200:#e9ecef;
      --fv-slate-300:#dee2e6; --fv-slate-400:#adb5bd; --fv-slate-500:#6c757d; --fv-slate-600:#495057;
      --fv-green-50:#e9f7ee; --fv-green-500:#28a745; --fv-green-600:#218838; --fv-green-700:#1a6e2e;
      --fv-amber-50:#fff8e1; --fv-amber-600:#d48a00;
      --fv-red-50:#fdf0f0; --fv-red-500:#dc3545; --fv-red-600:#c82333;
      --fv-surface:#ffffff; --fv-border:#dee2e6; --fv-text:#212529; --fv-text-2:#6c757d;
      --fv-accent:#0088cc; --fv-accent-wk:#e8f4fb;
      --fv-radius:8px; --fv-radius-lg:12px;
      --fv-shadow-sm:0 1px 2px rgba(14,23,41,.05);
      --fv-shadow:0 1px 3px rgba(14,23,41,.06),0 4px 14px rgba(14,23,41,.05);
      --fv-shadow-lg:0 10px 30px rgba(14,23,41,.08);
      --fv-mono:SFMono-Regular,Menlo,Monaco,Consolas,'Courier New',monospace;
      font-family:'Poppins',-apple-system,sans-serif;
    }
    /* Legacy tokens kept for JS compat */
    #dw {
      --p:#0088cc; --pc:#006699; --sf:#f8f9fa; --sfl:#f0f2f5;
      --sc:#dee2e6; --sch:#d0e9f7; --scl:#ffffff; --scv:#aeddf5;
      --os:#212529; --osv:#6c757d; --ov:rgba(0,0,0,.12);
      --inv:#0b1020; --invs:#f8f9fa;
      background:transparent; min-width:0;
    }
    #dw * { box-sizing:border-box; }

    /* ── Two-column layout ──────────────────────────────────────────────── */
    .fv-layout { display:grid; grid-template-columns:minmax(0,1fr) 280px; gap:20px; align-items:start; }
    @media (max-width:860px) { .fv-layout { grid-template-columns:1fr; } }

    /* ── Sidebar cards ──────────────────────────────────────────────────── */
    .fv-card { background:#fff; border:1px solid var(--fv-border); border-radius:var(--fv-radius-lg); box-shadow:var(--fv-shadow-sm); overflow:hidden; margin-bottom:14px; }
    .fv-card-head { padding:12px 16px; display:flex; align-items:center; justify-content:space-between; gap:10px; border-bottom:1px solid var(--fv-border); }
    .fv-card-head h3 { margin:0; font-size:13px; font-weight:600; color:var(--fv-text); display:flex; align-items:center; gap:7px; }
    .fv-card-body { padding:14px 16px; }
    .fv-kv { display:grid; grid-template-columns:auto 1fr; gap:5px 10px; font-size:12.5px; margin:0; }
    .fv-kv dt { color:var(--fv-text-2); white-space:nowrap; }
    .fv-kv dd { margin:0; font-weight:600; color:var(--fv-text); text-align:right; word-break:break-all; }
    .fv-divider { height:1px; background:var(--fv-border); margin:11px 0; }
    .fv-act-item { display:flex; gap:10px; align-items:flex-start; padding:7px 0; }
    .fv-act-item+.fv-act-item { border-top:1px solid var(--fv-border); }
    .fv-act-ic { width:24px; height:24px; border-radius:50%; flex-shrink:0; display:grid; place-items:center; font-size:11px; }
    .fv-act-ic.ok  { background:var(--fv-green-50); color:var(--fv-green-600); }
    .fv-act-ic.dim { background:var(--fv-slate-100); color:var(--fv-text-2); }
    .fv-act-ic.run { background:var(--fv-blue-50); color:var(--fv-accent); }
    .fv-act-ic.err { background:var(--fv-red-50); color:var(--fv-red-600); }
    .fv-link-btn { display:flex; align-items:center; gap:8px; padding:7px 0; font-size:12.5px; font-weight:500; color:var(--fv-accent); background:none; border:none; width:100%; text-align:left; text-decoration:none; cursor:pointer; }
    .fv-link-btn:hover { color:var(--fv-blue-700); text-decoration:none; }
    .fv-link-btn+.fv-link-btn { border-top:1px solid var(--fv-border); }

    /* ── Stepper ────────────────────────────────────────────────────────── */
    .fv-stepper { display:flex; gap:2px; background:var(--fv-slate-50); border:1px solid var(--fv-border); border-radius:var(--fv-radius-lg); padding:4px; margin-bottom:20px; }
    .fv-stp { flex:1; display:flex; align-items:center; gap:8px; padding:8px 10px; border-radius:8px; font-size:12px; font-weight:500; color:var(--fv-text-2); }
    .fv-stp-num { width:22px; height:22px; border-radius:50%; flex-shrink:0; background:#fff; border:1.5px solid var(--fv-border); display:grid; place-items:center; font-size:11px; font-weight:700; color:var(--fv-text-2); }
    .fv-stp.active { background:#fff; color:var(--fv-text); box-shadow:var(--fv-shadow-sm); }
    .fv-stp.active .fv-stp-num { background:var(--fv-accent); color:#fff; border-color:var(--fv-accent); }
    .fv-stp.done { color:var(--fv-green-700); }
    .fv-stp.done .fv-stp-num { background:var(--fv-green-500); color:#fff; border-color:var(--fv-green-500); font-size:0; }
    .fv-stp.done .fv-stp-num::after { content:"✓"; font-size:12px; }

    /* ── Badges ────────────────────────────────────────────────────────── */
    .fv-badge { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; padding:3px 9px; border-radius:999px; }
    .fv-badge .fv-dot { width:6px; height:6px; border-radius:50%; background:currentColor; }
    .fv-badge-green { color:var(--fv-green-700); background:var(--fv-green-50); border:1px solid rgba(40,167,69,.2); }
    .fv-badge-red   { color:var(--fv-red-600); background:var(--fv-red-50); border:1px solid rgba(220,53,69,.2); }
    .fv-badge-blue  { color:var(--fv-blue-700); background:var(--fv-blue-50); border:1px solid rgba(0,136,204,.2); }

    /* ── Wizard animations ──────────────────────────────────────────────── */
    .dw-fade { animation:dw-in .25s ease; }
    @keyframes dw-in { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }
    .dw-step-badge { display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:999px;background:var(--sch);color:var(--p);font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:16px; }

    /* ── Type cards ─────────────────────────────────────────────────────── */
    .dw-type-card { background:#fff;border-radius:var(--fv-radius-lg);padding:20px;box-shadow:var(--fv-shadow-sm);cursor:pointer;border:2px solid var(--fv-border);transition:box-shadow .2s,transform .15s,border-color .2s;text-align:left;width:100%;margin-bottom:12px;position:relative;overflow:hidden;display:block; }
    .dw-type-card:hover { border-color:var(--sch);box-shadow:var(--fv-shadow); }
    .dw-type-card.sel { border-color:var(--p);box-shadow:0 0 0 3px rgba(0,136,204,.1); }
    .dw-card-icon { width:40px;height:40px;border-radius:8px;background:var(--fv-slate-100);color:var(--p);display:flex;align-items:center;justify-content:center;font-size:17px;margin-bottom:12px;transition:background .2s,color .2s; }
    .dw-type-card.sel .dw-card-icon,.dw-type-card:hover .dw-card-icon { background:var(--p);color:#fff; }
    .dw-type-card h3 { font-size:13.5px;font-weight:700;color:var(--os);margin:0 0 5px; }
    .dw-type-card p  { font-size:12px;color:var(--osv);line-height:1.5;margin:0 0 10px; }
    .dw-tags { display:flex;gap:6px; }
    .dw-tag { padding:2px 7px;border-radius:4px;font-size:10px;font-weight:700;letter-spacing:.06em;font-family:var(--fv-mono);background:var(--fv-slate-100);color:var(--osv); }
    .dw-tag.p { background:var(--fv-blue-50);color:var(--p); }
    .dw-card-decor { position:absolute;top:0;right:0;padding:10px;opacity:.04;pointer-events:none;font-size:64px;line-height:1; }

    /* ── Sys req ────────────────────────────────────────────────────────── */
    .dw-sysreq { background:var(--fv-slate-50);border:1px solid var(--fv-border);border-radius:var(--fv-radius-lg);padding:16px;margin-top:18px; }
    .dw-pulse { width:8px;height:8px;border-radius:50%;background:var(--p);animation:dw-pulse-a 2s infinite;flex-shrink:0; }
    @keyframes dw-pulse-a{0%{box-shadow:0 0 0 0 rgba(0,136,204,.7)}70%{box-shadow:0 0 0 8px rgba(0,136,204,0)}100%{box-shadow:0 0 0 0 rgba(0,136,204,0)}}

    /* ── Form elements ──────────────────────────────────────────────────── */
    .dw-sec { display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--osv);margin-bottom:12px; }
    .dw-sec i { color:var(--p);font-size:14px; }
    .dw-auth-toggle { display:flex;background:var(--fv-slate-100);border:1px solid var(--fv-border);border-radius:var(--fv-radius);padding:3px;margin-bottom:16px; }
    .dw-auth-btn { flex:1;padding:8px 0;border:none;background:transparent;border-radius:6px;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--osv);cursor:pointer;transition:all .2s; }
    .dw-auth-btn.on { background:#fff;color:var(--p);box-shadow:var(--fv-shadow-sm); }
    .dw-inp { width:100%;background:#fff;border:1px solid var(--fv-border);border-radius:var(--fv-radius);padding:9px 13px;font-size:13px;color:var(--os);transition:border-color .2s,box-shadow .2s;outline:none;font-family:inherit; }
    .dw-inp:focus { border-color:var(--p);box-shadow:0 0 0 3px rgba(0,136,204,.1); }
    .dw-inp.mono { font-family:var(--fv-mono);font-size:12px; }
    .dw-inp.ta { resize:vertical; }
    select.dw-inp { appearance:auto; }
    .dw-label { display:block;font-size:11.5px;font-weight:600;color:var(--osv);margin-bottom:5px;padding-left:1px; }
    .dw-hint { font-size:11px;color:var(--osv);margin-top:5px;padding-left:1px;line-height:1.5; }
    .dw-hint .pro { font-weight:700;color:var(--p); }
    .dw-field { margin-bottom:16px; }
    .dw-grid2 { display:grid;grid-template-columns:2fr 1fr;gap:12px; }
    .dw-grid2h { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
    .dw-path-sec { background:var(--fv-slate-50);border:1px solid var(--fv-border);border-radius:var(--fv-radius-lg);padding:16px;margin-bottom:16px; }
    .dw-stack-row { display:flex;align-items:center;justify-content:space-between;padding:9px 13px;background:var(--fv-slate-50);border:1px solid var(--fv-border);border-radius:var(--fv-radius);margin-bottom:6px; }
    .dw-stack-badge { font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;background:var(--fv-blue-50);color:var(--p);border:1px solid rgba(0,136,204,.2);letter-spacing:.04em; }
    .dw-ssl-row { display:flex;align-items:center;gap:12px;background:#fff;border-radius:var(--fv-radius);padding:11px 14px;border:1.5px solid var(--fv-border);cursor:pointer;transition:border-color .2s;margin-bottom:8px; }
    .dw-ssl-row:hover { border-color:var(--sch); }
    .dw-ssl-row.sel { border-color:var(--p);background:var(--fv-blue-50); }
    .dw-ssl-radio { width:18px;height:18px;border-radius:50%;border:2px solid var(--fv-border);flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:border-color .2s; }
    .dw-ssl-row.sel .dw-ssl-radio { border-color:var(--p); }
    .dw-ssl-dot { width:8px;height:8px;border-radius:50%;background:var(--p);display:none; }
    .dw-ssl-row.sel .dw-ssl-dot { display:block; }
    .dw-meta-grid { display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:20px;margin-bottom:20px; }
    .dw-meta-card { background:#fff;border-radius:var(--fv-radius-lg);padding:14px;border:1px solid var(--fv-border);box-shadow:var(--fv-shadow-sm);display:flex;flex-direction:column;gap:14px;min-height:84px; }
    .dw-meta-label { font-size:10px;font-weight:700;text-transform:uppercase;color:var(--osv);letter-spacing:.06em;margin-bottom:2px; }
    .dw-meta-val { font-size:11px;font-weight:600;color:var(--os);line-height:1.3; }
    .dw-ready { display:flex;align-items:center;gap:6px; }
    .dw-ready span { font-size:10px;font-weight:900;color:var(--p);letter-spacing:.06em; }

    /* ── Timeline ───────────────────────────────────────────────────────── */
    #dw-timeline { position:relative;padding-top:4px; }
    .dw-tl-line { position:absolute;left:19px;top:0;bottom:0;width:4px;background:var(--sch);border-radius:2px;z-index:0; }
    .dw-tl-item { position:relative;z-index:1;display:flex;gap:20px;padding-bottom:24px; }
    .dw-tl-item:last-child { padding-bottom:0; }
    .dw-tl-dot { flex-shrink:0;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;transition:all .3s; }
    .dw-tl-dot.pending { background:var(--fv-slate-100);color:var(--osv); }
    .dw-tl-dot.running { background:#fff;color:var(--p);box-shadow:0 4px 16px rgba(0,136,204,.25);outline:4px solid rgba(0,136,204,.12);animation:dw-pulse-a 2s infinite; }
    .dw-tl-dot.done  { background:var(--p);color:#fff;box-shadow:0 4px 16px rgba(0,136,204,.25); }
    .dw-tl-dot.error { background:var(--fv-red-500);color:#fff; }
    .dw-tl-body { padding-top:8px; }
    .dw-tl-body h4 { font-size:13px;font-weight:600;color:var(--os);margin:0 0 3px; }
    .dw-tl-body p  { font-size:11px;color:var(--osv);margin:0; }
    .dw-tl-item.pend { opacity:.45; }
    .dw-tl-bar { margin-top:10px;width:140px;height:4px;background:var(--sch);border-radius:2px;overflow:hidden; }
    .dw-tl-bar-inner { height:100%;background:var(--p);border-radius:2px;animation:dw-ind 1.6s ease-in-out infinite; }
    @keyframes dw-ind{0%{transform:translateX(-100%);width:60%}100%{transform:translateX(200%);width:60%}}

    /* ── Live badge ─────────────────────────────────────────────────────── */
    .dw-live-badge { display:inline-flex;align-items:center;gap:8px;background:var(--fv-blue-50);color:var(--p);padding:5px 14px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:16px;border:1px solid rgba(0,136,204,.2); }
    .dw-live-dot { position:relative;width:8px;height:8px;flex-shrink:0; }
    .dw-live-dot::before,.dw-live-dot::after { content:'';position:absolute;border-radius:50%;inset:0; }
    .dw-live-dot::before { background:var(--p); }
    .dw-live-dot::after { background:var(--p);opacity:.7;animation:dw-ping 1.2s cubic-bezier(0,0,.2,1) infinite; }
    @keyframes dw-ping{0%{transform:scale(1);opacity:.7}100%{transform:scale(2.4);opacity:0}}

    /* ── Terminal ───────────────────────────────────────────────────────── */
    .dw-term { background:#0b1020;border-radius:var(--fv-radius);overflow:hidden;box-shadow:var(--fv-shadow-lg);margin-top:20px;border:1px solid #1f2a47; }
    .dw-term-hdr { display:flex;align-items:center;justify-content:space-between;padding:10px 16px;background:rgba(255,255,255,.05); }
    .dw-term-dots { display:flex;gap:6px; }
    .dw-term-dots span { width:10px;height:10px;border-radius:50%; }
    .dw-term-body { padding:14px 16px;font-family:var(--fv-mono);font-size:11.5px;line-height:1.7;color:#c9d3ec;max-height:200px;overflow-y:auto; }
    .dw-term-body .ts { color:#6de89a; }
    .dw-term-body::-webkit-scrollbar{width:4px} .dw-term-body::-webkit-scrollbar-thumb{background:#374060;border-radius:2px}

    /* ── Success ────────────────────────────────────────────────────────── */
    .dw-success-icon { width:76px;height:76px;border-radius:50%;background:linear-gradient(135deg,var(--p) 0%,var(--pc) 100%);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;position:relative;box-shadow:0 8px 24px rgba(0,136,204,.3); }
    .dw-success-icon::before { content:'';position:absolute;inset:-12px;border-radius:50%;background:rgba(0,136,204,.08);filter:blur(12px); }
    .dw-success-icon i { color:#fff;font-size:34px;position:relative;z-index:1; }
    .dw-endpoint { background:#fff;border-radius:var(--fv-radius-lg);padding:16px;border:1px solid var(--fv-border);box-shadow:var(--fv-shadow-sm);margin-bottom:12px; }
    .dw-ep-label { font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--p);margin-bottom:2px; }
    .dw-ep-sub { font-size:12px;color:var(--osv);margin-bottom:12px; }
    .dw-url-row { display:flex;align-items:center;justify-content:space-between;gap:10px;background:var(--fv-slate-50);border-radius:var(--fv-radius);padding:9px 13px;border:1px solid var(--fv-border); }
    .dw-url-row code { font-family:var(--fv-mono);font-size:12px;color:var(--p);overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .dw-copy-btn { flex-shrink:0;width:30px;height:30px;border-radius:6px;background:#fff;border:1px solid var(--fv-border);cursor:pointer;color:var(--p);box-shadow:var(--fv-shadow-sm);display:flex;align-items:center;justify-content:center;transition:transform .15s; }
    .dw-copy-btn:active { transform:scale(.92); }
    .dw-creds-card { background:var(--fv-slate-50);border-radius:var(--fv-radius-lg);padding:16px;margin-bottom:12px;border:1px solid var(--fv-border); }

    /* ── Buttons ────────────────────────────────────────────────────────── */
    .dw-btn-p { width:100%;padding:12px 0;background:linear-gradient(135deg,var(--p) 0%,var(--pc) 100%);color:#fff;font-weight:700;font-size:14px;border:none;border-radius:var(--fv-radius);cursor:pointer;box-shadow:0 2px 8px rgba(0,136,204,.25);transition:transform .15s,box-shadow .15s;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none; }
    .dw-btn-p:hover { box-shadow:0 4px 16px rgba(0,136,204,.35);color:#fff;text-decoration:none; }
    .dw-btn-p:active { transform:scale(.98); }
    .dw-btn-p:disabled { opacity:.6;cursor:not-allowed; }
    .dw-btn-s { width:100%;padding:12px 0;background:var(--fv-blue-50);color:var(--p);font-weight:700;font-size:14px;border:1px solid rgba(0,136,204,.2);border-radius:var(--fv-radius);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .2s; }
    .dw-btn-s:hover { background:var(--fv-blue-100); }
    .dw-btn-back { padding:12px 20px;background:var(--fv-slate-100);color:var(--osv);font-weight:700;font-size:13px;border:1px solid var(--fv-border);border-radius:var(--fv-radius);cursor:pointer;transition:background .2s; }
    .dw-btn-back:hover { background:var(--fv-slate-200); }
    .dw-footer { position:sticky;bottom:0;background:rgba(248,249,250,.95);backdrop-filter:blur(8px);padding:14px 0 6px;margin-top:20px;border-top:1px solid var(--fv-border);display:flex;gap:10px;z-index:10; }
    .dw-alert-e { background:var(--fv-red-50);color:var(--fv-red-600);border:1px solid rgba(220,53,69,.2);border-radius:var(--fv-radius);padding:10px 14px;font-size:12px;margin-bottom:14px; }

    /* ── License key box (design: LicenseKeyBox) ────────────────────────── */
    .dw-license-box { background:var(--fv-blue-50);border:1.5px dashed rgba(0,136,204,.4);border-radius:var(--fv-radius-lg);padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:20px; }
    .dw-lic-label { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--p);margin-bottom:4px; }
    .dw-lic-key { font-family:var(--fv-mono);font-size:13.5px;font-weight:700;color:var(--os);letter-spacing:.06em;word-break:break-all; }

    /* ── Deploy method buttons (design: "3. Deploy options") ────────────── */
    .dw-callout-i { display:flex;gap:10px;padding:11px 14px;border-radius:var(--fv-radius);background:var(--fv-blue-50);border:1px solid rgba(0,136,204,.2);font-size:13px;color:var(--fv-blue-800);margin-bottom:16px;line-height:1.5; }
    .dw-meth-row { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:22px; }
    .dw-btn-meth { display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:var(--fv-radius);font-size:13px;font-weight:600;cursor:pointer;transition:all .2s;border:1.5px solid var(--fv-border);background:#fff;color:var(--os);text-decoration:none;font-family:inherit; }
    .dw-btn-meth:hover { border-color:var(--sch);color:var(--os);text-decoration:none; }
    .dw-btn-meth.primary { background:linear-gradient(135deg,var(--p),var(--pc));color:#fff;border-color:var(--p);box-shadow:0 2px 8px rgba(0,136,204,.25); }
    .dw-btn-meth.primary:hover { color:#fff;box-shadow:0 4px 14px rgba(0,136,204,.35); }

    /* ── Version badge in step 1 ────────────────────────────────────────── */
    .dw-ver-tile { display:flex;align-items:center;gap:12px;padding:12px 14px;background:#fff;border:1px solid var(--fv-border);border-radius:var(--fv-radius);margin-bottom:12px;box-shadow:var(--fv-shadow-sm); }
    .dw-ver-ic { width:36px;height:36px;border-radius:var(--fv-radius);background:var(--fv-slate-100);display:flex;align-items:center;justify-content:center;color:var(--osv);flex-shrink:0; }
    .dw-ver-meta { flex:1;min-width:0; }
    .dw-ver-name { font-size:13px;font-weight:600;color:var(--os); }
    .dw-ver-sub  { font-size:11.5px;color:var(--osv);font-family:var(--fv-mono); }
    </style>

    <div id="fv-deploy">
    <div class="fv-layout">
    <div id="dw">

    {{-- ══ STEP 1: DEPLOY OPTIONS ═══════════════════════════════════════════ --}}
    <div id="dw-s1" class="dw-fade">

        {{-- 2. Latest version tile (populated by JS) ----------------------- --}}
        <div id="dw-ver-tile-wrap"></div>

        {{-- 3. Deploy options ---------------------------------------------- --}}
        <div class="dw-sec"><i class="fas fa-rocket"></i> Deploy Options</div>

        <div class="dw-callout-i">
            <i class="fas fa-info-circle" style="margin-top:2px;flex-shrink:0;"></i>
            <div><strong>Pick your path.</strong> The <em>guided wizard</em> connects via SSH and automatically deploys Faveo to your server. Choose <em>manual install</em> if you prefer to set up the environment yourself.</div>
        </div>

        <div class="dw-meth-row">
            <button class="dw-btn-meth primary" onclick="$('#dw-guided-opts').slideDown(200);$(this).closest('.dw-meth-row').find('.dw-btn-meth').removeClass('primary');$(this).addClass('primary');DW.method='guided';">
                <i class="fas fa-plug"></i> Start Guided Deploy
            </button>
            @if($manualInstallGuideUrl)
            <a class="dw-btn-meth" href="{{ $manualInstallGuideUrl }}" target="_blank">
                <i class="fas fa-book"></i> Manual Install Guide
            </a>
            @endif
            <button class="dw-btn-meth" disabled style="opacity:.55;cursor:not-allowed;position:relative;">
                <i class="fab fa-docker"></i> Deploy with Docker
                <span style="font-size:9px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;background:var(--fv-amber-600);color:#fff;padding:2px 6px;border-radius:999px;margin-left:6px;">Coming Soon</span>
            </button>
        </div>

        {{-- Guided deploy: server type selection --------------------------- --}}
        <div id="dw-guided-opts" style="display:none;">
            <div class="dw-sec" style="margin-bottom:14px;"><i class="fas fa-server"></i> Server Type</div>

            <button class="dw-type-card" id="dw-card-extract" onclick="dwSelectMode('extract_only')">
                <div class="dw-card-icon"><i class="fas fa-server"></i></div>
                <h3>Deploy on Existing Server</h3>
                <p>Copies Faveo files to a configured server via SSH/SFTP. Installation is completed using the Faveo web installer.</p>
                <div class="dw-tags">
                    <span class="dw-tag">FASTEST</span>
                    <span class="dw-tag">SSH / SFTP</span>
                </div>
                <div class="dw-card-decor"><i class="fas fa-terminal"></i></div>
            </button>

            <button class="dw-type-card" id="dw-card-fresh" onclick="dwSelectMode('fresh_install')">
                <div class="dw-card-icon"><i class="fas fa-rocket"></i></div>
                <h3>Deploy on Fresh Server</h3>
                <p>Automated full-stack provisioning — installs PHP, Apache / Nginx, MariaDB, Redis, and Supervisor on a bare OS.</p>
                <div class="dw-tags">
                    <span class="dw-tag p">FULL STACK</span>
                    <span class="dw-tag">MANAGED</span>
                </div>
            </button>

            {{-- System requirements --}}
            <div class="dw-sysreq">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                    <div class="dw-pulse"></div>
                    <span style="font-family:var(--fv-mono);font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--osv);">System Requirements</span>
                </div>
                <div style="display:flex;flex-direction:column;gap:7px;">
                    <div style="display:flex;justify-content:space-between;"><span style="font-family:var(--fv-mono);font-size:11px;color:var(--osv);">CPU</span><span style="font-family:var(--fv-mono);font-size:11px;font-weight:700;color:var(--os);">4 vCPU</span></div>
                    <div style="display:flex;justify-content:space-between;"><span style="font-family:var(--fv-mono);font-size:11px;color:var(--osv);">RAM</span><span style="font-family:var(--fv-mono);font-size:11px;font-weight:700;color:var(--os);">8 GiB</span></div>
                    <div style="display:flex;justify-content:space-between;"><span style="font-family:var(--fv-mono);font-size:11px;color:var(--osv);">Storage</span><span style="font-family:var(--fv-mono);font-size:11px;font-weight:700;color:var(--os);">40 GiB</span></div>
                    <div style="display:flex;justify-content:space-between;"><span style="font-family:var(--fv-mono);font-size:11px;color:var(--osv);">OS</span><span style="font-family:var(--fv-mono);font-size:11px;font-weight:700;color:var(--os);">Ubuntu / Debian / Rocky / RHEL</span></div>
                </div>
            </div>
        </div>

        <div class="dw-footer">
            <button class="dw-btn-p" id="dw-confirm-btn" onclick="dwGoTo(2)" disabled>
                <i class="fas fa-arrow-right"></i> Continue to Configure
            </button>
        </div>
    </div>

    {{-- ══ STEP 2: CONFIGURATION ════════════════════════════════════════════ --}}
    <div id="dw-s2" style="display:none;">
        <div class="fv-stepper">
            <div class="fv-stp done"><span class="fv-stp-num">1</span> Select</div>
            <div class="fv-stp active"><span class="fv-stp-num">2</span> Configure</div>
            <div class="fv-stp"><span class="fv-stp-num">3</span> Deploy</div>
            <div class="fv-stp"><span class="fv-stp-num">4</span> Live</div>
        </div>

        <div id="dw-s2-alert"></div>

        {{-- SSH --}}
        <div class="dw-sec"><i class="fas fa-network-wired"></i> SSH Connectivity</div>
        <div class="dw-grid2 dw-field">
            <div>
                <label class="dw-label">Host Address</label>
                <input id="dw-host" class="dw-inp mono" placeholder="192.168.1.1 or server.com" type="text">
            </div>
            <div>
                <label class="dw-label">Port</label>
                <input id="dw-port" class="dw-inp mono" value="22" type="number" min="1" max="65535">
            </div>
        </div>
        <div class="dw-field">
            <label class="dw-label">Username</label>
            <input id="dw-username" class="dw-inp mono" placeholder="root or ubuntu" type="text">
        </div>

        {{-- Auth toggle --}}
        <div class="dw-auth-toggle">
            <button class="dw-auth-btn on" id="dw-abtn-pass" onclick="dwToggleAuth('password')">Password</button>
            <button class="dw-auth-btn"    id="dw-abtn-key"  onclick="dwToggleAuth('private_key')">Private Key</button>
        </div>
        <div id="dw-auth-pass" class="dw-field">
            <label class="dw-label">SSH Password</label>
            <div style="position:relative;">
                <input id="dw-password" class="dw-inp mono" type="password" placeholder="••••••••••••" autocomplete="new-password" style="padding-right:42px;">
                <button type="button" onclick="dwTogglePwd('dw-password',this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--osv);padding:0;">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
        <div id="dw-auth-key" class="dw-field" style="display:none;">
            <label class="dw-label">SSH Private Key</label>
            <textarea id="dw-privatekey" class="dw-inp mono ta" rows="6" placeholder="-----BEGIN OPENSSH PRIVATE KEY-----&#10;...&#10;-----END OPENSSH PRIVATE KEY-----" style="font-size:11px;"></textarea>
            <p class="dw-hint">Paste contents of your key file (e.g. <code style="background:var(--sch);padding:1px 5px;border-radius:4px;">~/.ssh/id_ed25519</code>)</p>
        </div>

        {{-- Sudo --}}
        <div class="dw-field">
            <label class="dw-label">Sudo Password <span style="font-weight:400;color:var(--osv);">(optional)</span></label>
            <input id="dw-sudo" class="dw-inp mono" type="password" placeholder="Leave blank if SSH user has direct write access" autocomplete="new-password">
            <p class="dw-hint">Required only if the SSH user needs sudo to write to the deploy path.</p>
        </div>

        {{-- Deploy path + domain (extract_only only) --}}
        <div id="dw-path-wrap" class="dw-path-sec">
            <div class="dw-sec" style="margin-bottom:10px;"><i class="fas fa-folder-open"></i> Deployment Path</div>
            <div class="dw-field" style="margin-bottom:14px;">
                <label class="dw-label">Deploy Path</label>
                <input id="dw-path" class="dw-inp mono" value="/var/www/faveo" placeholder="/var/www/faveo" type="text">
                <p class="dw-hint"><span class="pro">Pro Tip:</span> Ensure the SSH user has <code style="background:var(--sch);padding:1px 5px;border-radius:4px;">rwx</code> permissions for this directory.</p>
            </div>
            <div class="dw-field" style="margin-bottom:0;">
                <label class="dw-label">Domain <span style="font-weight:400;color:var(--osv);">(optional — for web installer link)</span></label>
                <input id="dw-domain-extract" class="dw-inp mono" placeholder="helpdesk.example.com" type="text">
                <p class="dw-hint">Point your domain to this server's IP before deploying. After files are extracted the installer URL will use this domain.</p>
            </div>
        </div>

        {{-- Fresh install section --}}
        <div id="dw-fresh-wrap" style="display:none;">
            {{-- Stack info --}}
            <div class="dw-sec" style="margin-top:4px;"><i class="fas fa-layer-group"></i> Server Stack</div>
            <div style="margin-bottom:20px;">
                <div class="dw-stack-row"><span style="font-size:12px;font-weight:600;color:var(--os);"><i class="fas fa-code" style="color:var(--p);margin-right:8px;width:16px;"></i>PHP 8.1</span><span class="dw-stack-badge">WILL INSTALL</span></div>
                <div class="dw-stack-row"><span style="font-size:12px;font-weight:600;color:var(--os);"><i class="fas fa-server" style="color:var(--p);margin-right:8px;width:16px;"></i>Apache 2.4 or Nginx</span><span class="dw-stack-badge">WILL INSTALL</span></div>
                <div class="dw-stack-row"><span style="font-size:12px;font-weight:600;color:var(--os);"><i class="fas fa-database" style="color:var(--p);margin-right:8px;width:16px;"></i>MariaDB 10.6</span><span class="dw-stack-badge">WILL INSTALL</span></div>
                <div class="dw-stack-row"><span style="font-size:12px;font-weight:600;color:var(--os);"><i class="fas fa-bolt" style="color:var(--p);margin-right:8px;width:16px;"></i>Redis + Supervisor</span><span class="dw-stack-badge">WILL INSTALL</span></div>
            </div>

            {{-- Installation details --}}
            <div class="dw-sec"><i class="fas fa-id-card"></i> Installation Details</div>
            <div class="dw-grid2h dw-field">
                <div>
                    <label class="dw-label">Domain <span style="color:#ba1a1a;">*</span></label>
                    <input id="dw-domain" class="dw-inp mono" placeholder="helpdesk.example.com" type="text">
                </div>
                <div>
                    <label class="dw-label">Admin Email <span style="color:#ba1a1a;">*</span></label>
                    <input id="dw-email" class="dw-inp" value="{{ $order->client->email ?? '' }}" type="email">
                </div>
            </div>
            <div class="dw-grid2h dw-field">
                <div>
                    <label class="dw-label">License Code <span style="color:#ba1a1a;">*</span></label>
                    <input id="dw-license" class="dw-inp mono" value="{{ $order->serial_key ?? '' }}" maxlength="16" placeholder="16-char code" type="text">
                </div>
                <div>
                    <label class="dw-label">Order Number <span style="color:#ba1a1a;">*</span></label>
                    <input id="dw-order" class="dw-inp mono" value="{{ $order->number ?? '' }}" maxlength="8" placeholder="8-char #" type="text">
                </div>
            </div>

            {{-- SSL --}}
            <div class="dw-sec"><i class="fas fa-lock"></i> SSL Setup</div>
            <div id="dw-ssl-rows" style="margin-bottom:14px;">
                <div class="dw-ssl-row sel" data-ssl="A" onclick="dwSelectSsl('A')">
                    <div class="dw-ssl-radio"><div class="dw-ssl-dot"></div></div>
                    <div><div style="font-size:12px;font-weight:600;color:var(--os);">Free (Let's Encrypt)</div><div style="font-size:11px;color:var(--osv);">Domain must be publicly reachable</div></div>
                </div>
                <div class="dw-ssl-row" data-ssl="B" onclick="dwSelectSsl('B')">
                    <div class="dw-ssl-radio"><div class="dw-ssl-dot"></div></div>
                    <div><div style="font-size:12px;font-weight:600;color:var(--os);">Self-Signed</div><div style="font-size:11px;color:var(--osv);">For testing or internal use</div></div>
                </div>
                <div class="dw-ssl-row" data-ssl="C" onclick="dwSelectSsl('C')">
                    <div class="dw-ssl-radio"><div class="dw-ssl-dot"></div></div>
                    <div><div style="font-size:12px;font-weight:600;color:var(--os);">Paid Certificate</div><div style="font-size:11px;color:var(--osv);">Certificate files already on server</div></div>
                </div>
            </div>
            <div id="dw-ssl-cert-wrap" class="dw-grid2h dw-field" style="display:none;">
                <div>
                    <label class="dw-label">Certificate Path</label>
                    <input id="dw-ssl-cert" class="dw-inp mono" placeholder="/etc/ssl/certs/server.crt" type="text">
                </div>
                <div>
                    <label class="dw-label">Key Path</label>
                    <input id="dw-ssl-key" class="dw-inp mono" placeholder="/etc/ssl/private/server.key" type="text">
                </div>
            </div>

            {{-- Web Server --}}
            <div class="dw-sec"><i class="fas fa-globe"></i> Web Server</div>
            <div style="display:flex;gap:10px;margin-bottom:20px;">
                <div class="dw-ssl-row sel" id="dw-ws-apache" onclick="dwSelectWs(1)" style="flex:1;margin-bottom:0;">
                    <div class="dw-ssl-radio"><div class="dw-ssl-dot"></div></div>
                    <div style="font-size:12px;font-weight:600;color:var(--os);">Apache</div>
                </div>
                <div class="dw-ssl-row" id="dw-ws-nginx" onclick="dwSelectWs(2)" style="flex:1;margin-bottom:0;">
                    <div class="dw-ssl-radio"><div class="dw-ssl-dot"></div></div>
                    <div style="font-size:12px;font-weight:600;color:var(--os);">Nginx</div>
                </div>
            </div>
        </div>

        {{-- Version --}}
        <div class="dw-field" style="margin-top:8px;">
            <div class="dw-sec"><i class="fas fa-code-branch"></i> Version to Deploy</div>
            <select id="dw-version" class="dw-inp" disabled>
                <option value="">Loading versions...</option>
            </select>
        </div>

        {{-- Advanced --}}
        <div style="margin-bottom:20px;">
            <button type="button" onclick="$('#dw-adv').toggle()" style="background:none;border:none;cursor:pointer;font-size:12px;color:var(--p);padding:0;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-cog"></i> Advanced Settings
            </button>
            <div id="dw-adv" style="display:none;margin-top:12px;">
                <label class="dw-label">Web User <span style="font-weight:400;color:var(--osv);">(auto-detected if blank)</span></label>
                <input id="dw-webuser" class="dw-inp mono" placeholder="www-data" type="text">
                <p class="dw-hint">User that should own the deployed files (e.g. www-data, apache).</p>
            </div>
        </div>

        {{-- Security meta --}}
        <div class="dw-meta-grid">
            <div class="dw-meta-card">
                <i class="fas fa-shield-alt" style="color:var(--pc);font-size:20px;"></i>
                <div><div class="dw-meta-label">Security</div><div class="dw-meta-val">Credentials never stored</div></div>
            </div>
            <div class="dw-meta-card">
                <div class="dw-ready"><div class="dw-pulse"></div><span>READY</span></div>
                <div><div class="dw-meta-label">Connection</div><div class="dw-meta-val">Validated on first step</div></div>
            </div>
        </div>

        <div class="dw-footer">
            <button class="dw-btn-back" onclick="dwGoTo(1)"><i class="fas fa-arrow-left"></i> Back</button>
            <button class="dw-btn-p" id="dw-deploy-btn" onclick="dwSubmit()" style="flex:1;">
                <i class="fas fa-rocket"></i>&nbsp;<span id="dw-deploy-label">Deploy</span>
            </button>
        </div>
    </div>

    {{-- ══ STEP 3: PROGRESS ════════════════════════════════════════════════ --}}
    <div id="dw-s3" style="display:none;">
        <div class="fv-stepper">
            <div class="fv-stp done"><span class="fv-stp-num">1</span> Select</div>
            <div class="fv-stp done"><span class="fv-stp-num">2</span> Configure</div>
            <div class="fv-stp active"><span class="fv-stp-num">3</span> Deploy</div>
            <div class="fv-stp"><span class="fv-stp-num">4</span> Live</div>
        </div>
        <div style="text-align:center;margin-bottom:28px;">
            <div class="dw-live-badge"><div class="dw-live-dot"></div>Live Deployment</div>
            <h2 id="dw-v-hero" style="font-size:28px;font-weight:900;letter-spacing:-.04em;color:var(--os);margin-bottom:4px;"></h2>
            <p id="dw-srv-hero" style="font-size:12px;color:var(--osv);font-weight:500;"></p>
        </div>

        <div id="dw-timeline">
            <div class="dw-tl-line"></div>
        </div>

        <div class="dw-term" id="dw-term" style="display:none;">
            <div class="dw-term-hdr">
                <div style="display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-terminal" style="color:#b4c5ff;font-size:12px;"></i>
                    <span style="font-family:SFMono-Regular,Menlo,Monaco,Consolas,'Courier New',monospace;font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#b4c5ff;">Console Output</span>
                </div>
                <div class="dw-term-dots">
                    <span style="background:rgba(239,68,68,.5);"></span>
                    <span style="background:rgba(251,191,36,.5);"></span>
                    <span style="background:rgba(74,222,128,.5);"></span>
                </div>
            </div>
            <div class="dw-term-body" id="dw-console"></div>
        </div>
    </div>

    {{-- ══ STEP 4: SUCCESS ══════════════════════════════════════════════════ --}}
    <div id="dw-s4" style="display:none;">
        <div class="fv-stepper">
            <div class="fv-stp done"><span class="fv-stp-num">1</span> Select</div>
            <div class="fv-stp done"><span class="fv-stp-num">2</span> Configure</div>
            <div class="fv-stp done"><span class="fv-stp-num">3</span> Deploy</div>
            <div class="fv-stp active"><span class="fv-stp-num">4</span> Live</div>
        </div>
        <div style="text-align:center;margin-bottom:28px;">
            <div class="dw-success-icon"><i class="fas fa-check"></i></div>
            <h2 style="font-size:26px;font-weight:900;letter-spacing:-.03em;color:var(--os);margin-bottom:8px;">Setup Complete!</h2>
            <p style="font-size:13px;color:var(--osv);line-height:1.6;max-width:400px;margin:0 auto;">
                Your Faveo instance has been successfully deployed and is ready for the next step.
            </p>
        </div>

        <div id="dw-s4-endpoint">
            <div class="dw-endpoint">
                <div class="dw-ep-label">Web Installer</div>
                <div class="dw-ep-sub" id="dw-ep-sub-text">Open this URL to complete the Faveo setup wizard</div>
                <div class="dw-url-row">
                    <code id="dw-url-display"></code>
                    <button class="dw-copy-btn" onclick="dwCopyUrl()" title="Copy URL">
                        <i class="fas fa-copy" id="dw-copy-icon"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="dw-s4-creds" style="display:none;">
            <div class="dw-creds-card">
                <div class="dw-sec"><i class="fas fa-user-shield"></i> Administrator Credentials</div>
                <p style="font-size:11px;color:var(--osv);margin-bottom:10px;">Saved from server installation — keep these secure.</p>
                <pre id="dw-creds-text" style="background:#fff;border-radius:8px;padding:12px;font-family:SFMono-Regular,Menlo,Monaco,Consolas,'Courier New',monospace;font-size:11px;line-height:1.7;overflow-x:auto;white-space:pre-wrap;word-break:break-all;color:var(--os);margin:0;"></pre>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;margin-top:16px;">
            <a id="dw-installer-btn" href="#" target="_blank" class="dw-btn-p" style="text-decoration:none;">
                <i class="fas fa-external-link-alt"></i> Visit Web Installer
            </a>
            <button class="dw-btn-s" id="dw-logs-toggle-btn" onclick="dwToggleLogs()">
                <i class="fas fa-terminal"></i> View Server Logs
            </button>
        </div>

        <div id="dw-s4-logs" style="display:none;margin-top:14px;">
            <div class="dw-term">
                <div class="dw-term-hdr">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-terminal" style="color:#b4c5ff;font-size:12px;"></i>
                        <span style="font-family:var(--fv-mono);font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#b4c5ff;">Console Output</span>
                    </div>
                </div>
                <div class="dw-term-body" id="dw-s4-console-mirror"></div>
            </div>
        </div>

        <p style="font-size:11px;color:var(--osv);margin-top:18px;line-height:1.6;">
            <i class="fas fa-info-circle" style="color:#9d3000;margin-right:4px;"></i>
            Please save any credentials above securely.
        </p>
    </div>

    </div>{{-- #dw --}}

    {{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
    <aside>

    {{-- Order summary --}}
    <div class="fv-card">
        <div class="fv-card-head">
            <h3><i class="fas fa-file-invoice" style="color:var(--fv-text-2);font-size:12px;"></i> Order Summary</h3>
            <span class="fv-badge fv-badge-blue">{{ $order->order_status }}</span>
        </div>
        <div class="fv-card-body">
            <dl class="fv-kv">
                <dt>Product</dt><dd>{{ $product->name ?? 'Faveo Helpdesk' }}</dd>
                <dt>Order #</dt><dd style="font-family:var(--fv-mono);">{{ $order->number }}</dd>
                @if($order->serial_key)
                <dt>License</dt><dd style="font-family:var(--fv-mono);font-size:10.5px;letter-spacing:.04em;">{{ $order->serial_key }}</dd>
                @endif
                <dt>Expires</dt><dd>{!! getDateHtml($subscription->update_ends_at) !!}</dd>
            </dl>
        </div>
    </div>

    {{-- Activity log --}}
    <div class="fv-card">
        <div class="fv-card-head">
            <h3><i class="fas fa-stream" style="color:var(--fv-text-2);font-size:12px;"></i> Activity</h3>
        </div>
        <div class="fv-card-body" style="padding:10px 14px;" id="fv-activity-list">
            <div class="fv-act-item">
                <div class="fv-act-ic ok"><i class="fas fa-check" style="font-size:10px;"></i></div>
                <div>
                    <div style="font-size:12.5px;font-weight:600;color:var(--fv-text);">Order active</div>
                    <div style="font-size:11.5px;color:var(--fv-text-2);">#{{ $order->number }}</div>
                </div>
            </div>
            <div class="fv-act-item" id="fv-act-deploy-pending">
                <div class="fv-act-ic dim"><i class="fas fa-clock" style="font-size:10px;"></i></div>
                <div>
                    <div style="font-size:12.5px;font-weight:600;color:var(--fv-text);">Deploy pending</div>
                    <div style="font-size:11.5px;color:var(--fv-text-2);">Awaiting configuration</div>
                </div>
            </div>
        </div>
    </div>


    </aside>{{-- sidebar --}}

    </div>{{-- .fv-layout --}}
    </div>{{-- #fv-deploy --}}

    <script>
    var DW = { mode: null, method: 'guided', authMethod: 'password', sslType: 'A', wsType: 1, siteUrl: null };

    function fvActivity(text, sub, iconCls, cls) {
        var html = '<div class="fv-act-item">'
            + '<div class="fv-act-ic ' + cls + '"><i class="' + iconCls + '" style="font-size:10px;"></i></div>'
            + '<div><div style="font-size:12.5px;font-weight:600;color:var(--fv-text);">' + text + '</div>'
            + '<div style="font-size:11.5px;color:var(--fv-text-2);">' + sub + '</div></div></div>';
        $('#fv-act-deploy-pending').remove();
        $('#fv-activity-list').append(html);
    }

    $(document).ready(function () {
        $.get('{{ route("get-deploy-versions", $order->id) }}', function (res) {
            var $s = $('#dw-version');
            var data = (res && res.data) ? res.data : [];
            $s.empty();
            if (!Array.isArray(data) || !data.length) {
                $s.append('<option value="">No deployable versions found</option>');
                return;
            }
            $.each(data, function (i, v) {
                $s.append($('<option>', { value: v.id, text: v.version + ' \u2014 ' + v.title }));
            });
            $s.prop('disabled', false);
        }).fail(function () {
            $('#dw-version').empty().append('<option value="">Failed to load versions</option>');
        });
    });

    function dwGoTo(n) {
        $('#dw-s1,#dw-s2,#dw-s3,#dw-s4').hide();
        $('#dw-s' + n).show().addClass('dw-fade');
        setTimeout(function () { $('#dw-s' + n).removeClass('dw-fade'); }, 300);
        window.scrollTo(0, 0);
    }

    function dwSelectMode(mode) {
        DW.mode = mode;
        $('#dw-card-extract,#dw-card-fresh').removeClass('sel');
        $('#dw-card-' + (mode === 'extract_only' ? 'extract' : 'fresh')).addClass('sel');
        $('#dw-confirm-btn').prop('disabled', false);
        var isFresh = mode === 'fresh_install';
        $('#dw-path-wrap').toggle(!isFresh);
        $('#dw-fresh-wrap').toggle(isFresh);
        $('#dw-deploy-label').text(isFresh ? 'Install & Deploy' : 'Deploy');
    }

    function dwToggleAuth(method) {
        DW.authMethod = method;
        var isPass = method === 'password';
        $('#dw-abtn-pass').toggleClass('on', isPass);
        $('#dw-abtn-key').toggleClass('on', !isPass);
        $('#dw-auth-pass').toggle(isPass);
        $('#dw-auth-key').toggle(!isPass);
    }

    function dwTogglePwd(id, btn) {
        var $i = $('#' + id);
        $i.attr('type', $i.attr('type') === 'password' ? 'text' : 'password');
        $(btn).find('i').toggleClass('fa-eye fa-eye-slash');
    }

    function dwSelectSsl(t) {
        DW.sslType = t;
        $('#dw-ssl-rows .dw-ssl-row').removeClass('sel');
        $('#dw-ssl-rows .dw-ssl-row[data-ssl="' + t + '"]').addClass('sel');
        $('#dw-ssl-cert-wrap').toggle(t === 'C');
    }

    function dwSelectWs(n) {
        DW.wsType = n;
        $('#dw-ws-apache,#dw-ws-nginx').removeClass('sel');
        $('#dw-ws-' + (n === 1 ? 'apache' : 'nginx')).addClass('sel');
    }

    function dwRenderStep(id, label, state, detail) {
        var dot = {
            pending: '<div class="dw-tl-dot pending"><i class="fas fa-circle" style="font-size:7px;opacity:.4;"></i></div>',
            running: '<div class="dw-tl-dot running"><i class="fas fa-sync fa-spin"></i></div>',
            done:    '<div class="dw-tl-dot done"><i class="fas fa-check"></i></div>',
            error:   '<div class="dw-tl-dot error"><i class="fas fa-times"></i></div>',
        }[state] || '';
        var bar    = state === 'running' ? '<div class="dw-tl-bar"><div class="dw-tl-bar-inner"></div></div>' : '';
        var det    = detail ? '<p>' + $('<span>').text(detail).html() + '</p>' : '';
        var pendCls = state === 'pending' ? ' pend' : '';
        var h4color = state === 'running' ? ' style="color:var(--p);font-weight:700;"' : '';
        var html = '<div id="dw-tl-' + id + '" class="dw-tl-item' + pendCls + '">'
            + dot + '<div class="dw-tl-body"><h4' + h4color + '>' + label + '</h4>' + det + bar + '</div></div>';
        var $ex = $('#dw-tl-' + id);
        if ($ex.length) $ex.replaceWith(html); else $('#dw-timeline').append(html);
    }

    function dwAppendConsole(text) {
        if (!text) return;
        var $c = $('#dw-console');
        text.split('\n').forEach(function (line) {
            if (!line.trim()) return;
            var ts = new Date().toTimeString().slice(0, 8);
            $c.append('<div><span class="ts">[' + ts + ']</span> ' + $('<span>').text(line).html() + '</div>');
        });
        $c[0].scrollTop = $c[0].scrollHeight;
        $('#dw-term').show();
    }

    function dwCallStep(payload, stepKey, ms) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: '{{ route("deploy-product-step") }}',
                type: 'POST', timeout: ms || 120000,
                data: Object.assign({}, payload, { step: stepKey }),
                success: function (d) { d.success === true ? resolve(d) : reject(d.message || 'Step failed.'); },
                error:   function (x) { reject((x.responseJSON && x.responseJSON.message) ? x.responseJSON.message : 'Connection error.'); }
            });
        });
    }

    async function dwSubmit() {
        var $alert   = $('#dw-s2-alert');
        $alert.html('');
        var host     = $.trim($('#dw-host').val());
        var port     = $.trim($('#dw-port').val());
        var username = $.trim($('#dw-username').val());
        var password = $('#dw-password').val();
        var privKey  = $.trim($('#dw-privatekey').val());
        var sudo     = $('#dw-sudo').val();
        var webUser  = $.trim($('#dw-webuser').val());
        var path     = $.trim($('#dw-path').val()) || '/var/www/faveo';
        var domainExtract = $.trim($('#dw-domain-extract').val());
        var versionId = $('#dw-version').val();
        var isFresh   = DW.mode === 'fresh_install';
        var authVal   = DW.authMethod === 'password' ? password : privKey;

        if (!host || !port || !username || !authVal) {
            $alert.html('<div class="dw-alert-e"><i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>Please fill in all required SSH fields.</div>');
            return;
        }
        if (!versionId) {
            $alert.html('<div class="dw-alert-e"><i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>Please select a version to deploy.</div>');
            return;
        }
        var domain = '', email = '', license = '', orderNo = '';
        if (isFresh) {
            domain  = $.trim($('#dw-domain').val());
            email   = $.trim($('#dw-email').val());
            license = $.trim($('#dw-license').val());
            orderNo = $.trim($('#dw-order').val());
            if (!domain || !email || !license || !orderNo) {
                $alert.html('<div class="dw-alert-e">Please fill in all Installation Details fields.</div>'); return;
            }
            if (license.length !== 16) { $alert.html('<div class="dw-alert-e">License Code must be exactly 16 characters.</div>'); return; }
            if (orderNo.length  !==  8) { $alert.html('<div class="dw-alert-e">Order Number must be exactly 8 characters.</div>'); return; }
            if (DW.sslType === 'C' && (!$.trim($('#dw-ssl-cert').val()) || !$.trim($('#dw-ssl-key').val()))) {
                $alert.html('<div class="dw-alert-e">Please provide Certificate and Key paths for Paid SSL.</div>'); return;
            }
        }

        // Build step list and go to progress
        var vText = $('#dw-version option:selected').text().split(' \u2014 ')[0];
        $('#dw-v-hero').text(vText);
        $('#dw-srv-hero').text('Server: ' + host + (port !== '22' ? ':' + port : ''));
        $('#dw-timeline .dw-tl-item').remove();

        var steps = [{ id: 'verify', label: 'Connecting to Server' }];
        if (isFresh) steps.push({ id: 'install', label: 'Installing Stack (15–30 min)' });
        steps.push({ id: 'upload',  label: 'Copying Files' });
        steps.push({ id: 'extract', label: 'Extracting & Setting Permissions' });
        $.each(steps, function (i, s) { dwRenderStep(s.id, s.label, 'pending', ''); });

        fvActivity('Deploy started', host, 'fas fa-rocket', 'run');
        dwGoTo(3);

        var base = {
            _token: '{{ csrf_token() }}',
            order_id: {{ $order->id }},
            version_id: versionId,
            host: host, port: port, username: username,
            auth_method: DW.authMethod, deploy_mode: DW.mode,
            deploy_path: path, web_user: webUser, sudo_password: sudo,
        };
        if (DW.authMethod === 'password') base.password    = password;
        else                              base.private_key  = privKey;
        if (isFresh) {
            base.install_domain = domain; base.install_email = email;
            base.install_license = license; base.install_order = orderNo;
            base.web_server = DW.wsType; base.ssl_type = DW.sslType;
            if (DW.sslType === 'C') {
                base.ssl_cert_path = $.trim($('#dw-ssl-cert').val());
                base.ssl_key_path  = $.trim($('#dw-ssl-key').val());
            }
        }

        async function run(id, label, ms) {
            dwRenderStep(id, label, 'running', '');
            try {
                var d = await dwCallStep(base, id, ms);
                dwRenderStep(id, label, 'done', d.message);
                return d;
            } catch (e) {
                dwRenderStep(id, label, 'error', e);
                throw e;
            }
        }

        try {
            await run('verify', 'Connecting to Server', 30000);
            fvActivity('SSH connected', host, 'fas fa-check', 'ok');

            var installData = null;
            if (isFresh) {
                installData = await run('install', 'Installing Stack (15–30 min)', 2100000);
                if (installData.data && installData.data.output) dwAppendConsole(installData.data.output);
                fvActivity('Stack installed', 'PHP · Web server · DB', 'fas fa-server', 'ok');
            }

            var uploadData  = await run('upload', 'Copying Files', 120000);
            base.remote_path = uploadData.data && uploadData.data.remote_path;
            fvActivity('Files uploaded', (uploadData.data && uploadData.data.remote_path) || '/tmp', 'fas fa-upload', 'ok');

            var extractData = await run('extract', 'Extracting & Setting Permissions', 300000);
            if (extractData.data && extractData.data.output) dwAppendConsole(extractData.data.output);
            fvActivity('Files extracted', extractData.message || 'Done', 'fas fa-check-circle', 'ok');

            // Success screen — priority: user domain → auto-detected → SSH host
            if (isFresh) {
                DW.siteUrl = (installData && installData.data && installData.data.setup_url) || ('http://' + host);
            } else {
                DW.siteUrl = domainExtract
                    ? 'http://' + domainExtract
                    : ((extractData.data && extractData.data.site_url) || ('http://' + host));
            }

            var installerUrl = DW.siteUrl;
            $('#dw-installer-btn').attr('href', installerUrl);
            $('#dw-url-display').text(installerUrl);
            $('#dw-s4-endpoint').show();
            if (isFresh && installData && installData.data && installData.data.credentials) {
                $('#dw-creds-text').text(installData.data.credentials);
                $('#dw-s4-creds').show();
            }

            var actSub = isFresh ? DW.siteUrl : (domainExtract ? domainExtract : DW.siteUrl);
            fvActivity('Deployment complete', actSub || 'Ready for web installer', 'fas fa-rocket', 'ok');
            dwGoTo(4);
        } catch (e) {
            fvActivity('Deploy failed', typeof e === 'string' ? e.substring(0,40) : 'See timeline', 'fas fa-times', 'err');
        }
    }

    function dwCopyUrl() {
        var url = $('#dw-url-display').text();
        if (navigator.clipboard && url) {
            navigator.clipboard.writeText(url).then(function () {
                $('#dw-copy-icon').removeClass('fa-copy').addClass('fa-check');
                setTimeout(function () { $('#dw-copy-icon').removeClass('fa-check').addClass('fa-copy'); }, 2000);
            });
        }
    }

    function dwCopyLic() {
        var key = $('#dw-lic-display').text();
        if (navigator.clipboard && key) {
            navigator.clipboard.writeText(key).then(function () {
                $('#dw-lic-copy-icon').removeClass('fa-copy').addClass('fa-check');
                setTimeout(function () { $('#dw-lic-copy-icon').removeClass('fa-check').addClass('fa-copy'); }, 2000);
            });
        }
    }

    function dwToggleLogs() {
        var $panel = $('#dw-s4-logs');
        var $btn = $('#dw-logs-toggle-btn');
        if ($panel.is(':hidden')) {
            $('#dw-s4-console-mirror').html($('#dw-console').html() || '<span style="color:#6c757d;">No log output available.</span>');
            $panel.slideDown(200);
            $btn.html('<i class="fas fa-times"></i> Hide Logs');
        } else {
            $panel.slideUp(200);
            $btn.html('<i class="fas fa-terminal"></i> View Server Logs');
        }
    }
    </script>
        {{-- old form removed --}}
        <div class="row mb-3" style="display:none;">
            <div class="col-lg-8">

                <!-- ── Deployment Mode ── -->
                <div class="card card-outline card-primary mb-3">
                    <div class="card-header">
                        <h3 class="card-title" style="font-size:0.85rem;font-weight:600;"><i class="fas fa-server mr-2"></i>Deployment Type</h3>
                    </div>
                    <div class="card-body pb-2" style="font-size:0.82rem;">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="deploy-mode" id="mode-extract-only" value="extract_only" checked onchange="toggleDeployMode()">
                            <label class="form-check-label" for="mode-extract-only">
                                <strong>Extract Files Only</strong>
                                <small class="text-muted d-block">Upload and extract the version ZIP to an existing server. Use this for updates on an already-configured server.</small>
                            </label>
                        </div>
                        <hr class="my-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="deploy-mode" id="mode-fresh-install" value="fresh_install" onchange="toggleDeployMode()">
                            <label class="form-check-label" for="mode-fresh-install">
                                <strong>Fresh Server Installation</strong>
                                <small class="text-muted d-block">Run the Faveo installation script to set up all prerequisites (PHP, MySQL, Nginx/Apache), then deploy your files. Use this for a brand-new server.</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ── Fresh Install Prerequisites (shown only for fresh_install) ── -->
                <div id="fresh-install-section" style="display:none;">
                    <div class="card card-outline card-warning mb-3">
                        <div class="card-header">
                            <h3 class="card-title" style="font-size:0.85rem;font-weight:600;"><i class="fas fa-list-check mr-2"></i>Installation Prerequisites</h3>
                        </div>
                        <div class="card-body" style="font-size:0.82rem;">
                            <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:0.82rem;">
                                <i class="fas fa-clock mr-1"></i>
                                <strong>This may take 15–30 minutes.</strong> The installer will set up PHP, MySQL, a web server, Supervisor, and Redis on a fresh OS before deploying your files.
                            </div>

                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label class="font-weight-bold">Domain <span class="text-danger">*</span></label>
                                    <input type="text" id="install-domain" class="form-control" placeholder="e.g. helpdesk.example.com">
                                    <small class="text-muted">Must point to this server's public IP before running.</small>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label class="font-weight-bold">Admin Email <span class="text-danger">*</span></label>
                                    <input type="email" id="install-email" class="form-control" value="{{ $order->client->email ?? '' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label class="font-weight-bold">License Code <span class="text-danger">*</span></label>
                                    <input type="text" id="install-license" class="form-control" value="{{ $order->serial_key ?? '' }}" maxlength="16" style="font-family:monospace;">
                                    <small class="text-muted">16-character code — auto-filled from your order.</small>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label class="font-weight-bold">Order Number <span class="text-danger">*</span></label>
                                    <input type="text" id="install-order" class="form-control" value="{{ $order->number ?? '' }}" maxlength="8" style="font-family:monospace;">
                                    <small class="text-muted">8-character number — auto-filled from your order.</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6 form-group">
                                    <label class="font-weight-bold">Web Server <span class="text-danger">*</span></label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="install-web-server" id="ws-apache" value="1" checked>
                                            <label class="form-check-label" for="ws-apache">Apache</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="install-web-server" id="ws-nginx" value="2">
                                            <label class="form-check-label" for="ws-nginx">Nginx</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 form-group">
                                    <label class="font-weight-bold">SSL Certificate <span class="text-danger">*</span></label>
                                    <select id="install-ssl-type" class="form-control" onchange="toggleSslPaths()">
                                        <option value="A">Let's Encrypt (free, domain must be public)</option>
                                        <option value="B" selected>Self-Signed (for testing/internal)</option>
                                        <option value="C">Paid SSL (cert files already on server)</option>
                                    </select>
                                </div>
                            </div>

                            <div id="ssl-paid-section" style="display:none;">
                                <div class="row">
                                    <div class="col-sm-6 form-group">
                                        <label class="font-weight-bold">Certificate File Path <span class="text-danger">*</span></label>
                                        <input type="text" id="install-ssl-cert" class="form-control" placeholder="/etc/ssl/certs/your-cert.crt" style="font-family:monospace;">
                                        <small class="text-muted">Absolute path to the .crt file already on the server.</small>
                                    </div>
                                    <div class="col-sm-6 form-group">
                                        <label class="font-weight-bold">Certificate Key File Path <span class="text-danger">*</span></label>
                                        <input type="text" id="install-ssl-key" class="form-control" placeholder="/etc/ssl/private/your-cert.key" style="font-family:monospace;">
                                        <small class="text-muted">Absolute path to the .key file already on the server.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Version to Deploy <span class="text-danger">*</span></label>
                    <select id="deploy-version" class="form-control" disabled>
                        <option value="">Loading versions...</option>
                    </select>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-9">
                        <div class="form-group">
                            <label class="font-weight-bold">Server Host / IP <span class="text-danger">*</span></label>
                            <input type="text" id="deploy-host" class="form-control" placeholder="e.g. 192.168.1.10 or example.com">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Port <span class="text-danger">*</span></label>
                            <input type="number" id="deploy-port" class="form-control" value="22" min="1" max="65535">
                        </div>
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 mt-2 mb-1" style="font-size:0.85rem;">
                    <i class="fas fa-shield-alt me-1"></i>
                    <strong>Your credentials are never stored.</strong>
                    SSH credentials are used only for this request and discarded immediately after deployment.
                </div>

                <div class="form-group mt-2">
                    <label class="font-weight-bold">SSH Username <span class="text-danger">*</span></label>
                    <input type="text" id="deploy-username" class="form-control" placeholder="e.g. ubuntu">
                </div>

                <div class="form-group mt-2">
                    <label class="font-weight-bold">Authentication Method <span class="text-danger">*</span></label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="deploy-auth-method" id="auth-private-key" value="private_key" checked onchange="toggleAuthMethod()">
                            <label class="form-check-label" for="auth-private-key">Private Key</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="deploy-auth-method" id="auth-password" value="password" onchange="toggleAuthMethod()">
                            <label class="form-check-label" for="auth-password">Password</label>
                        </div>
                    </div>
                </div>

                <div id="auth-private-key-section" class="form-group mt-2">
                    <label class="font-weight-bold">SSH Private Key <span class="text-danger">*</span></label>
                    <textarea id="deploy-private-key" class="form-control" rows="6" placeholder="-----BEGIN OPENSSH PRIVATE KEY-----&#10;...&#10;-----END OPENSSH PRIVATE KEY-----" style="font-family: monospace; font-size: 12px;"></textarea>
                    <small class="text-muted">Paste the contents of your private key file (e.g. <code>~/.ssh/id_ed25519</code>).</small>
                </div>

                <div id="auth-password-section" class="form-group mt-2" style="display:none;">
                    <label class="font-weight-bold">SSH Password <span class="text-danger">*</span></label>
                    <input type="password" id="deploy-password" class="form-control" placeholder="SSH password for the user" autocomplete="new-password">
                    <small class="text-muted">Password authentication must be enabled on the server (<code>PasswordAuthentication yes</code>).</small>
                </div>

                <div class="form-group mt-2">
                    <label class="font-weight-bold">Sudo Password</label>
                    <input type="password" id="deploy-sudo-password" class="form-control" placeholder="Leave blank if SSH user has direct write access">
                    <small class="text-muted">Required only if the SSH user needs sudo to write to the deploy path or run commands.</small>
                </div>

                <div id="deploy-path-section" class="form-group mt-2">
                    <label class="font-weight-bold">Deploy Path <span class="text-danger">*</span></label>
                    <input type="text" id="deploy-path" class="form-control" value="/var/www/faveo" placeholder="e.g. /var/www/faveo">
                    <small class="text-muted">The zip file will be extracted into this directory on the remote server.</small>
                </div>

                <div id="advanced-settings-section" class="mt-3">
                    <a href="#deploy-advanced" data-toggle="collapse" style="font-size:0.85rem;">
                        <i class="fas fa-cog"></i> Advanced Settings
                    </a>

                    <div id="deploy-advanced" class="collapse mt-2">
                        <div class="form-group">
                            <label class="font-weight-bold">Web User</label>
                            <input type="text" id="deploy-web-user" class="form-control" placeholder="www-data">
                            <small class="text-muted">User that should own the deployed files (e.g. www-data, apache). Auto-detected if left blank.</small>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button id="deploy-btn" class="btn btn-primary" onclick="submitDeploy()">
                        <i class="fas fa-rocket"></i>&nbsp; Deploy
                    </button>
                </div>
            </div>
        </div>

        <!-- Step progress panel (shown during deployment) -->
        <div id="deploy-progress" style="display:none;" class="mt-3">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-tasks mr-1"></i> Deployment Progress</h3></div>
                <div class="card-body p-0">
                    <ul id="deploy-steps" class="list-group list-group-flush" style="font-size:0.9rem;"></ul>
                </div>
            </div>
            <div id="deploy-install-details" style="display:none;" class="mt-3"></div>
            <div id="deploy-output-section" style="display:none;" class="mt-3">
                <a href="#" onclick="$('#deploy-output-wrap').toggle();return false;" style="font-size:0.8rem;">
                    <i class="fas fa-terminal"></i> Toggle server output
                </a>
                <div id="deploy-output-wrap" style="display:none;">
                    <pre id="deploy-output" class="bg-dark text-white p-3 rounded mt-1" style="max-height:300px;overflow-y:auto;font-size:11px;"></pre>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function () {
                $.get('{{ route("get-deploy-versions", $order->id) }}', function (res) {
                    var $sel = $('#deploy-version');
                    var data = (res && res.data) ? res.data : [];
                    $sel.empty();
                    if (!Array.isArray(data) || !data.length) {
                        $sel.append('<option value="">No deployable versions found</option>');
                        return;
                    }
                    $.each(data, function (i, v) {
                        $sel.append($('<option>', { value: v.id, text: v.version + ' — ' + v.title }));
                    });
                    $sel.prop('disabled', false);
                }).fail(function () {
                    $('#deploy-version').empty().append('<option value="">Failed to load versions</option>');
                });
            });

            function toggleSslPaths() {
                $('#ssl-paid-section').toggle($('#install-ssl-type').val() === 'C');
            }

            function toggleDeployMode() {
                var mode = $('input[name="deploy-mode"]:checked').val();
                var isFresh = mode === 'fresh_install';
                $('#fresh-install-section').toggle(isFresh);
                $('#deploy-path-section').toggle(!isFresh);
                $('#deploy-btn').html(isFresh
                    ? '<i class="fas fa-rocket"></i>&nbsp; Install &amp; Deploy'
                    : '<i class="fas fa-rocket"></i>&nbsp; Deploy');
            }

            function toggleAuthMethod() {
                var isPassword = $('input[name="deploy-auth-method"]:checked').val() === 'password';
                $('#auth-private-key-section').toggle(!isPassword);
                $('#auth-password-section').toggle(isPassword);
            }

            // ── Step progress helpers ─────────────────────────────────────────

            var STEP_ICONS = {
                pending: '<i class="fas fa-circle text-secondary" style="width:16px;"></i>',
                running: '<i class="fas fa-circle-notch fa-spin text-primary" style="width:16px;"></i>',
                done:    '<i class="fas fa-check-circle text-success" style="width:16px;"></i>',
                error:   '<i class="fas fa-times-circle text-danger" style="width:16px;"></i>',
            };

            function renderStep(id, label, state, detail) {
                var icon   = STEP_ICONS[state] || STEP_ICONS.pending;
                var detail = detail ? '<br><small class="text-muted ml-4 pl-1">' + detail + '</small>' : '';
                var existing = $('#step-' + id);
                var html = '<li id="step-' + id + '" class="list-group-item py-2">'
                    + icon + ' <span class="ml-2">' + label + '</span>' + detail + '</li>';
                if (existing.length) { existing.replaceWith(html); }
                else { $('#deploy-steps').append(html); }
            }

            function callStep(postData, stepName, timeout) {
                return new Promise(function (resolve, reject) {
                    $.ajax({
                        url:     '{{ route("deploy-product-step") }}',
                        type:    'POST',
                        timeout: timeout || 120000,
                        data:    Object.assign({}, postData, { step: stepName }),
                        success: function (data) {
                            if (data.success === true) { resolve(data); }
                            else { reject(data.message || 'Step failed.'); }
                        },
                        error: function (xhr) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message)
                                ? xhr.responseJSON.message
                                : 'Connection error.';
                            reject(msg);
                        }
                    });
                });
            }

            // ── Main deploy function ──────────────────────────────────────────

            async function submitDeploy() {
                var deployMode   = $('input[name="deploy-mode"]:checked').val();
                var isFresh      = deployMode === 'fresh_install';
                var host         = $.trim($('#deploy-host').val());
                var port         = $.trim($('#deploy-port').val());
                var username     = $.trim($('#deploy-username').val());
                var authMethod   = $('input[name="deploy-auth-method"]:checked').val();
                var privateKey   = $.trim($('#deploy-private-key').val());
                var password     = $('#deploy-password').val();
                var deployPath   = $.trim($('#deploy-path').val());
                var webUser      = $.trim($('#deploy-web-user').val());
                var sudoPassword = $('#deploy-sudo-password').val();

                // ── Validate ──────────────────────────────────────────────────
                var authValue = authMethod === 'password' ? password : privateKey;
                if (!host || !port || !username || !authValue || (!isFresh && !deployPath)) {
                    $('#deploy-alert').html('<div class="alert alert-danger">Please fill in all required fields.</div>');
                    return;
                }

                if (isFresh) {
                    var domain  = $.trim($('#install-domain').val());
                    var email   = $.trim($('#install-email').val());
                    var license = $.trim($('#install-license').val());
                    var orderNo = $.trim($('#install-order').val());
                    var sslType = $('#install-ssl-type').val();

                    if (!domain || !email || !license || !orderNo) {
                        $('#deploy-alert').html('<div class="alert alert-danger">Please fill in all Installation Prerequisites fields.</div>');
                        return;
                    }
                    if (license.length !== 16) {
                        $('#deploy-alert').html('<div class="alert alert-danger">License Code must be exactly 16 characters.</div>');
                        return;
                    }
                    if (orderNo.length !== 8) {
                        $('#deploy-alert').html('<div class="alert alert-danger">Order Number must be exactly 8 characters.</div>');
                        return;
                    }
                    if (sslType === 'C' && (!$.trim($('#install-ssl-cert').val()) || !$.trim($('#install-ssl-key').val()))) {
                        $('#deploy-alert').html('<div class="alert alert-danger">Please provide Certificate and Key file paths for Paid SSL.</div>');
                        return;
                    }
                }

                // ── Setup ─────────────────────────────────────────────────────
                $('#deploy-alert').html('');
                $('#deploy-btn').prop('disabled', true).html(
                    '<i class="fas fa-circle-notch fa-spin"></i>&nbsp;' + (isFresh ? 'Installing...' : 'Deploying...')
                );
                $('#deploy-steps').empty();
                $('#deploy-install-details').hide().empty();
                $('#deploy-output-section').hide();
                $('#deploy-output').text('');
                $('#deploy-progress').show();

                // ── Base payload (sent with every step) ───────────────────────
                var versionId = $('#deploy-version').val();
                if (!versionId) {
                    $('#deploy-alert').html('<div class="alert alert-danger">Please select a version to deploy.</div>');
                    return;
                }

                var base = {
                    _token:       '{{ csrf_token() }}',
                    order_id:     {{ $order->id }},
                    version_id:   versionId,
                    host:         host,
                    port:         port,
                    username:     username,
                    auth_method:  authMethod,
                    deploy_mode:  deployMode,
                    deploy_path:  deployPath,
                    web_user:     webUser,
                    sudo_password: sudoPassword,
                };
                if (authMethod === 'password') { base.password    = password; }
                else                           { base.private_key = privateKey; }

                if (isFresh) {
                    var sslType = $('#install-ssl-type').val();
                    base.install_domain  = domain;
                    base.install_email   = email;
                    base.install_license = license;
                    base.install_order   = orderNo;
                    base.web_server      = $('input[name="install-web-server"]:checked').val();
                    base.ssl_type        = sslType;
                    if (sslType === 'C') {
                        base.ssl_cert_path = $.trim($('#install-ssl-cert').val());
                        base.ssl_key_path  = $.trim($('#install-ssl-key').val());
                    }
                }

                // ── Step runner ───────────────────────────────────────────────
                var failed = false;

                async function run(stepKey, label, timeoutMs) {
                    renderStep(stepKey, label, 'running');
                    try {
                        var data = await callStep(base, stepKey, timeoutMs);
                        renderStep(stepKey, label, 'done', data.message);
                        return data;
                    } catch (err) {
                        renderStep(stepKey, label, 'error', err);
                        failed = true;
                        throw err;
                    }
                }

                try {
                    // Step 1: Verify SSH
                    await run('verify', 'Verifying SSH connection & preparing path', 30000);

                    // Step 2: Install (fresh only)
                    if (isFresh) {
                        renderStep('install', 'Running installation script (15–30 min)', 'pending');
                        var installData = await run('install', 'Running installation script (15–30 min)', 2100000);

                        // Show credentials & setup info
                        if ((installData.data && installData.data.credentials) || (installData.data && installData.data.setup_url)) {
                            var info = '';
                            if (installData.data && installData.data.credentials) {
                                info += '<p class="font-weight-bold mb-1">Server Credentials <small class="text-muted">(save these now)</small></p>'
                                    + '<pre class="bg-light p-2 rounded" style="font-size:11px;">'
                                    + $('<div>').text(installData.data.credentials).html() + '</pre>';
                            }
                            if (installData.data && installData.data.setup_url) {
                                info += '<div class="alert alert-warning py-2 px-3 mb-2" style="font-size:0.85rem;">'
                                    + '<i class="fas fa-info-circle mr-1"></i><strong>Next:</strong> '
                                    + 'Open <a href="' + installData.data.setup_url + '" target="_blank">' + installData.data.setup_url + '</a> '
                                    + 'to complete the setup wizard and create your admin account.</div>';
                            }
                            $('#deploy-install-details').html(info).show();
                        }
                        if (installData.data && installData.data.output) {
                            $('#deploy-output').text(installData.data.output);
                            $('#deploy-output-section').show();
                        }
                    }

                    // Step 3: Upload file
                    var uploadData = await run('upload', 'Uploading Faveo files to server', 120000);
                    base.remote_path = uploadData.data && uploadData.data.remote_path;

                    // Step 4: Extract
                    var extractData = await run('extract', 'Extracting files & setting permissions', 300000);
                    if (extractData.data && extractData.data.output) {
                        $('#deploy-output').text(($('#deploy-output').text() + '\n' + extractData.data.output).trim());
                        $('#deploy-output-section').show();
                    }

                    // All done
                    var successMsg = '<div class="alert alert-success mt-2">'
                        + '<i class="fas fa-check-circle mr-1"></i><strong>Deployment complete!</strong>';
                    if (isFresh) {
                        successMsg += ' Remember to restart Supervisor after completing the GUI setup.';
                    } else if (extractData.data && extractData.data.site_url) {
                        successMsg += ' <a href="' + extractData.data.site_url + '" target="_blank" class="alert-link">'
                            + extractData.data.site_url + ' <i class="fas fa-external-link-alt" style="font-size:0.75em;"></i></a>'
                            + ' — open this URL to proceed with setup.';
                    }
                    successMsg += '</div>';
                    $('#deploy-alert').html(successMsg);

                } catch (e) {
                    // error already rendered in the step row
                }

                var btnReset = isFresh
                    ? '<i class="fas fa-rocket"></i>&nbsp; Install &amp; Deploy'
                    : '<i class="fas fa-rocket"></i>&nbsp; Deploy';
                $('#deploy-btn').prop('disabled', false).html(btnReset);
            }
        </script>
    </div>{{-- hidden old form --}}
    </div>{{-- #deploy tab pane --}}
    @endif

    <div class="modal fade" id="autorenewModal" tabindex="-1" role="dialog" aria-labelledby="autorenewModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="autorenewModalLabel">{{ __('message.auto_renewal')}}</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="form-group col">

                            <label class="form-label">{{ __('message.select_payment')}} <span class="text-danger"> *</span></label>

                            <div class="custom-select-1">
                                <select class="form-select form-control h-auto py-2" data-msg-required="{{ __('message.please_select_city')}}" name="city" required>
                                    <option value="">{{ __('message.select')}}</option>
                                    <option value="1">{{ __('message.razorpay')}}</option>
                                    <option value="2">{{ __('message.stripe')}}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('message.close')}}</button>

                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ __('message.save')}}</button>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="Whatsapp-url" tabindex="-1" role="dialog" aria-labelledby="autorenewModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="autorenewModalLabel">{{ __('message.whatsapp_product_heading')}}</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="form-group col">

                            <label class="form-label">{{ __('message.callback_url')}} <span class="text-danger"> *</span>
                                <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{__('message.webhook_explanation')}}"></i>
                            </label>
                            <div class="custom-select-1">
                                {!! html()->text('webhook_url')->class('form-control')->id('webhook_url')->placeholder('https://example.com') !!}
                                <div class="space"></div>
                            </div>

                        </div>

                    </div>
                </div>


                <div class="modal-footer">

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('message.close')}}</button>

                    <button type="button" class="btn btn-primary" id="whatsapp_close">{{ __('message.save')}}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="Whatsapp-url-edit" tabindex="-1" role="dialog" aria-labelledby="autorenewModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="autorenewModalLabel">{{ __('message.whatsapp_product_heading')}}</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <div id="alertMessage-webhook"></div>

                    <div class="row">

                        <div class="form-group col">

                            <label class="form-label">{{ __('message.callback_url')}} <span class="text-danger"> *</span>
                                <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{__('message.webhook_explanation')}}"></i>
                            </label>
                            <div class="custom-select-1">
                                {!! html()->text('webhook_url_edit')->class('form-control')->id('webhook_url_edit')->placeholder('https://example.com') !!}
                                <div class="space"></div>
                            </div>
                        <input type="hidden" id="webhook_id">
                        </div>

                    </div>
                </div>


                <div class="modal-footer">

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('message.close')}}</button>

                    <button type="button" class="btn btn-primary" id="whatsapp_close_edit">{{ __('message.save')}}</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="cloudDomainModal" tabindex="-1" role="dialog" aria-labelledby="cloudDomainModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="cloudDomainModalLabel">{{ __('message.change_cloud_domain')}}</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <div id="success-domain"></div>
                    <div id="failure-domain"></div>

                    <p>{{ __('message.wish_domain_purchase')}} <a href="https://store.ladybirdwebhost.com/" target="_blank">{{ __('message.click_here')}}.</a>{{ __('message.cname_cloud')}} <a href="https://docs.faveohelpdesk.com/docs/helper/cname/" target="_blank">{{ __('message.learn_more')}}.</a></p>

                    <p class="text-black"><strong>{{ __('message.current_cloud_domain')}}</strong> {{$installation_path}}</p>

                    <div class="row">

                        <div class="form-group col">

                            <label class="form-label">{{ __('message.enter_domain_new_name')}} <span class="text-danger"> *</span></label>

                            <div class="input-group mb-3">

                                <input type="text" class="form-control col col-2 rounded-1" value="https://" disabled="true" style="background-color: lightslategray; color:white;">
                                <input type="text" class="form-control col-10" id="clouduserdomain" autocomplete="off" placeholder="billing.custom.com" required>

                            </div>
                        </div>
                        <script>
                            $(document).ready(function() {
                                var orderId = {{$id}};
                                $.ajax({
                                    data: {'orderId' : orderId, "_token": "{!! csrf_token() !!}"},
                                    url: '{{url("/api/takeCloudDomain")}}',
                                    method: 'POST',
                                    dataType: 'json',
                                    success: function(data) {
                                        $('#clouduserdomainfill').html('{!! __('message.current_cloud_domain') !!} <a href="' + data.data + '">' + data.data + '</a>');
                                    },
                                    error: function(error) {
                                        console.error('Error:', error);
                                    }
                                });
                            });
                        </script>

                        <div class="overlay" style="display: none;"></div> <!-- Add this line -->

                        <div class="loader-wrapper" style="display: none; background: white; height: 100%" >
                            <i class="fas fa-spinner fa-spin" style="font-size: 40px;"></i>

                        </div>
                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button" id="changeDomain" class="btn btn-primary"><i class="fas fa-globe"></i> {{ __('message.chg_domain')}}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="numberOfAgentsModal" tabindex="-1" role="dialog" aria-labelledby="numberOfAgentsModalLabel" aria-hidden="true">


        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="numberOfAgentsModalLabel">{{ __('message.change_no_of_agents')}}</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <div id="response-agent"></div>
                    <div id="failure-agent"></div>
                    <?php
                    $ExistingPlanPirce= \App\Model\Payment\PlanPrice::where('plan_id',$planIdOld)->where('currency',getCurrencyForClient(\Auth::user()->country))->latest()->value('add_price');
                    ?>

                    <p class="text-black"><strong>{{ __('message.current_no_agents')}}</strong> {{$latestAgents}}</p>

                    <p class="text-black"><strong>{{ __('message.price_per_agent')}} </strong>{!! currencyFormat($ExistingPlanPirce,getCurrencyForClient(\Auth::user()->country),true) !!}</p>

                    <div class="row">


                        <div class="form-group mb-3">
                            <label class="text-black"><strong>{{ __('message.action') }}</strong> <span class="text-danger">*</span></label>
                            <select class="form-control" id="agentAction">
                                <option value="increase">{{ __('message.increase') }}</option>
                                <option value="decrease">{{ __('message.decrease') }}</option>
                            </select>
                        </div>

                        <label class="text-black"><strong>{{ __('message.choose_no_desired_agents') }}</strong> <span class="text-danger">*</span></label>

                        <div class="quantity">
                            {!! html()->number('number')->class('form-control')->id('numberAGt')->attribute('min', 1)->placeholder('') !!}
                        </div>
                        <br><br>

                        <div class="col-12">
                            <p class="text-black" id="pricetopaid" style="display: none;"><strong>{{ __('message.price_to_be_paid') }}</strong> <span id="pricetopay" class="pricetopay"></span></p>
                        </div>
                        <div class="overlay" style="display: none;"></div> <!-- Add this line -->


                        <div class="row loader-wrapper" style="display: none;">
                            <div class="col-12 d-flex justify-content-center align-items-center" style="min-height: 100px;">
                                    <i class="fas fa-spinner fa-spin" style="font-size: 40px;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-primary" id="agentNumber"><i class="fas fa-users"></i> {{ __('message.update_agents')}}</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="cloudPlanModal" tabindex="-1" role="dialog" aria-labelledby="cloudPlanModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="cloudPlanModalLabel">{{ __('message.upgrade_downgrade_cloud_plan')}}</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                <div class="modal-body">
                    <div id="response-upgrade"></div>
                    <div id="failure-upgrade"></div>


                    <p class="text-black"><strong>{{ __('message.current_plan')}} </strong>{{$planNameReal}}</p>

                    <div class="row">

                        <div class="form-group col">

                            <label class="text-black"><strong>{{ __('message.select_new_plan')}}</strong> <span class="text-danger"> *</span></label>

                            <div class="custom-select-1">

                                {!! html()->select('plan', ['' => 'Select'] + $plans)
    ->class('form-control upgrade-select')
    ->attribute('onchange', 'getPrice(this.value)') !!}

                            </div>
                        </div>

                        <p class="text-black" id="upgrade1" style="display: none;" ><strong>{{ __('message.total_credits_remaining')}} </strong><span id="priceOldPlan" class="priceOldPlan"></span></p>

                        <p class="text-black" id="upgrade2" style="display: none;" ><strong>{{ __('message.price_for_new_plan')}} </strong><span id="priceNewPlan" class="priceNewPlan"></span></p>

                        <p class="text-black" id="upgrade2" style="display: none;" ><strong>{{ __('message.price_to_be_paid')}} </strong><span id="priceToPay" class="priceToPay" ></span></p>
                        <div class="overlay" style="display: none;"></div> <!-- Add this line -->


                        <div class="row loader-wrapper" style="display: none;">
                            <div class="col-12 d-flex justify-content-center align-items-center" style="min-height: 100px;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 40px;"></i>
                            </div>
                        </div>
                    </div>
                     </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-primary" id="upgradedowngrade"><i class="fas fa-cloud-upload-alt"></i> {{ __('message.change_plan')}}</button>
                    </div>
               
            </div>
        </div>
    </div>



    <div class="modal fade" id="renewal-modal" tabindex="-1" role="dialog" aria-labelledby="autorenewModalLabel" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="autorenewModalLabel">{{ __('message.auto_renewal')}}</h4>

                    <button type="button" class="btn-close"  id="srclose" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>

                        <div class="modal-body">
                    <div class="row">
                        <div class="form-group col">
                            <label class="form-label">{{ __('message.select_payment')}} <span class="text-danger">*</span></label>
                            <div class="custom-select-1">
                                <select name="" id="sel-payment" class="form-control">
                                    <option value="" disabled>{{ __('message.choose_your_option') }}</option>
                                    @foreach($gateways as $gateway)
                                    <option value="{{ strtolower($gateway) }}" {{ $recentPayment && strtolower($gateway) === strtolower($recentPayment->payment_method) ? 'selected' : '' }}>{{ $gateway }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal-footer">

                    <button type="button" class="btn btn-light"  onclick="refreshPage()" data-bs-dismiss="modal">{{ __('message.close')}}</button>

                    <button type="button" id="payment"  class="btn btn-primary" data-bs-dismiss="modal">{{ __('message.save')}}</button>
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
                    <h4 class="modal-title" id="defaultModalLabel" style="white-space: nowrap;">{{ __('message.enter_card_details') }}</h4>
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
                        <form id="payment-form" class="mx-auto" style="max-width: 500px;">
                            <div class="form-group row">
                                <div class="col-md-12 alert alert-info">
                                    {{ __('message.card_secure')}} {{currencyFormat(1,getCurrencyForClient(\Auth::user()->country))}}, {{ __('message.automatically_reverse')}}
                                </div>
                            </div>
                            <!-- Card Number Field (with built-in Stripe icon) -->
                            <div class="mb-3">
                                <label for="card-number" class="form-label">{{ __('message.card_number') }}</label>
                                <div id="card-number" class="StripeElement"></div>
                                <div id="card-number-errors" class="text-danger mt-1" role="alert"></div>
                            </div>

                            <!-- Row for Expiry Date and CVC -->
                            <div class="row mb-3">
                                <!-- Expiry Date Field -->
                                <div class="col-md-6 mb-3">
                                    <label for="card-expiry" class="form-label">{{ __('message.expiry_date') }}</label>
                                    <div id="card-expiry" class="StripeElement"></div>
                                    <div id="card-expiry-errors" class="text-danger mt-1" role="alert"></div>
                                </div>

                                <!-- CVC Field -->
                                <div class="col-md-6 mb-3">
                                    <label for="card-cvc" class="form-label">CVC</label>
                                    <div id="card-cvc" class="StripeElement"></div>
                                    <div id="card-cvc-errors" class="text-danger mt-1" role="alert"></div>
                                </div>
                            </div>

                            <!-- Total Summary -->
                            <div class="d-grid mb-4">
                                <div class="btn btn-lg btn-outline-dark disabled" style="pointer-events: none;">
                                    <div class="d-flex justify-content-between w-100">
                                        <span>{{ __('message.total') }}</span>
                                        <span id="order-total">{{ currencyFormat(1,getCurrencyForClient(\Auth::user()->country)) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <button type="submit" id="pay" class="btn btn-primary btn-block">
                                        {{ __('message.caps_pay_now') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                        <form id="token-form">
                            <input type="hidden" id="stripe-token" name="stripeToken">
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
                        <h4 class="modal-title" id="autorenewModalLabel">{{ __('message.payment_confirmation')}}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input class="hidden" id="orderID" value={{$id}}>
                            <p style="color: #333;">{{ __('message.refresh_process')}} <strong style="font-weight: bold;">{{ __('message.finish')}}</strong> {{ __('message.to_complete_payment')}}</p>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="confirmStripePayment" class="btn btn-primary" data-bs-dismiss="modal">{{ __('message.finish')}}</button>
                    </div>
                </div>
            </div>
        </div>





    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <form name='razorpayform' action="{!!url('rzpRenewal-disable/'.$order->id)!!}" method="POST">
        {{ csrf_field() }}
        <!--<button id="rzp-button1" class="btn btn-primary pull-right mb-xl" data-loading-text="Loading...">Pay Now</button>-->
        <!--<form name='razorpayform' action="verify.php" method="POST">                                -->
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >


    </form>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">

    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>


    <script>
            $('#shownumber-table').DataTable({
                processing: true,
                serverSide: true,
                stateSave: false,

                ajax: "{{ url('whatsapp-client-table/'.$order->id) }}", // Calls the separate function
                oLanguage: {
                    sLengthMenu: "_MENU_ Records per page",
                    sSearch: "<span style='right: 180px;'>Search:</span> ",
                    {{--sProcessing: ' <div class="overlay dataTables_processing"><i class="fas fa-3x fa-sync-alt fa-spin" style=" margin-top: -25px;"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'--}}
                    sProcessing: ' <div class="overlay dataTables_processing"><i class="fas fa-3x fa-sync-alt fa-spin" style=" margin-top: -25px;"></i><div class="text-bold pt-2">{!! __('message.loading') !!}</div></div>'

                },
                language: {
                    paginate: {
                        first:      "{{ __('message.paginate_first') }}",
                        last:       "{{ __('message.paginate_last') }}",
                        next:       "{{ __('message.paginate_next') }}",
                        previous:   "{{ __('message.paginate_previous') }}"
                    },
                    emptyTable:     "{{ __('message.empty_table') }}",
                    info:           "{{ __('message.datatable_info') }}",
                    zeroRecords:    "{{ __('message.no_matching_records_found') }} ",
                    infoEmpty:      "{{ __('message.info_empty') }}",
                    infoFiltered:   "{{ __('message.info_filtered') }}",
                    lengthMenu:     "{{ __('message.length_menu') }}",
                    loadingRecords: "{{ __('message.loading_records') }}",
                    search:         "{{ __('message.table_search') }}",
                },

                // Apply 'no-sort' class only to specific targets (3rd and 4th columns)
                columnDefs: [
                    {
                        // targets: [2, 3], // Status and Action columns
                        // orderable: false
                    }
                ],

                columns: [
                    // { data: 'UserName', name: 'UserName', orderable: true, searchable: true },
                    { data: 'PhoneNumber', name: 'PhoneNumber', orderable: true , searchable: true },
                    { data: 'WabaId', name: 'WabaId', orderable: true, searchable: true },
                    { data: 'PhoneNumberId', name: 'PhoneNumberId', orderable: true, searchable: true },
                    { data: 'BusinessId', name: 'BusinessId', orderable: true, searchable: true },
                    { data: 'access_token', name: 'Access Token', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at', orderable: true, searchable: true },
                    { data: 'action', name: 'action', orderable: false, searchable: false }

                ]
            });

            $(document).on('click', '.copy-btn', function() {
                const button = $(this);
                const token = button.data('token');
                const message = button.siblings('.copy-msg');

                navigator.clipboard.writeText(token).then(() => {
                    message.fadeIn(200).delay(1000).fadeOut(400);
                });
            });

            $('#whatsapp_close').on('click',function(e){

                const userRequiredFields = {
                    name:'Please Enter Webhook URL. ',


                };
                var webhook_url=$('#webhook_url');

                const userFields = {
                    name:webhook_url,

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

                if (isValid && !isValidURL(userFields.name.val())) {
                    showError(userFields.name,'Please enter a Valid URL',);
                    isValid = false;
                }

                // If validation fails, prevent form submission
                if (!isValid) {
                    e.preventDefault();
                }else{
                    var url=webhook_url.val();
                    // var token=$('#verify_token').val();
                    $.ajax({
                        data: {'url' : url,},
                        url: '{{url("url-save")}}',
                        method: 'POST',
                        dataType: 'json',
                        success: function(data) {
                                $('#Whatsapp-url').modal('hide');
                            launchWhatsAppSignup();
                        },
                        error: function(error) {
                            console.error('Error:', error);
                        }
                    });
                }
        })


            $('#whatsapp_close_edit').on('click',function(e){

                const userRequiredFields = {
                    name:@json(trans('message.callback_url_error')),


                };
                var webhook_url=$('#webhook_url_edit');

                const userFields = {
                    name:webhook_url,

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

                if (isValid && !isValidURL(userFields.name.val())) {
                    showError(userFields.name,@json(trans('message.callback_url_error')));
                    isValid = false;
                }

                // If validation fails, prevent form submission
                if (!isValid) {
                    e.preventDefault();
                }else{
                    var url=webhook_url.val();
                    var id=$('#webhook_id').val();
                    // var token=$('#verify_token').val();
                    $.ajax({
                        data: {'url' : url,'id': id,},
                        url: '{{url("webhook-url-edit")}}',
                        method: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            $('#alertMessage-webhook').show();
                            var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> ' + @json(__('message.success')) +'! </strong>' + response.message + '.</div>';
                            $('#alertMessage-webhook').html(result + ".");
                            $("#whatsapp_close_edit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                            setTimeout(function () {
                                $('#alertMessage-webhook').slideUp(3000, function () {
                                    setTimeout(function () {
                                        location.reload();
                                    }, 1000);
                                });
                            })
                        },
                        error: function (response) {
                            $('#alertMessage-webhook').show();
                            var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> ' + @json(__('message.success')) +'! </strong>' + response.message + '.</div>';
                            $('#alertMessage-webhook').html(result + ".");
                            $("#whatsapp_close_edit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                            setTimeout(function () {
                                $('#alertMessage-webhook').slideUp(3000, function () {
                                    setTimeout(function () {
                                        location.reload();
                                    }, 1000);
                                });
                            })
                        },
                    });
                }
            })


            function isValidURL(str) {
                try {
                    new URL(str);
                    return true;
                } catch (_) {
                    return false;
                }
            }
            const removeErrorMessage = (field) => {
                field.classList.remove('is-invalid');
                const error = field.nextElementSibling;
                if (error && error.classList.contains('error')) {
                    error.remove();
                }
            };

            // Add input event listeners for all fields
            ['webhook_url'].forEach(id => {

                document.getElementById(id).addEventListener('input', function () {
                    removeErrorMessage(this);

                });
            });
        $('#get-url').on('click',function(){
            $('#Whatsapp-url').modal('show');
        });

        // SDK initialization
        window.fbAsyncInit = function() {
            FB.init({
                appId: {{$app_id}},
                autoLogAppEvents: true,
                xfbml: true,
                version: 'v24.0'
            });
        };
            var fbData=null;
            var fbToken=null;
        // Session logging message event listener
            window.addEventListener('message', (event) => {
                if (!event.origin.endsWith('facebook.com')) return;
                try {
                    const data = JSON.parse(event.data);
                    if (data.type === 'WA_EMBEDDED_SIGNUP') {
                            fbData = data;
                        getAllData();
                    }
                } catch {
                    console.log('message event: ', event.data);
                    // your code goes here
                }
            });

            // Response callback
            const fbLoginCallback = (response) => {
                if (response.authResponse) {
                    fbToken=response.authResponse.code;
                    getAllData();
                } else {
                    console.log('response2: ', response);
                }
            }

            function getAllData() {
                if (fbData && fbToken) {
                    var data = fbData;
                    $.ajax({
                        url: '{{url("save-waba-id")}}',
                        type: 'post',
                        data: {
                            "waba_id": data.data.waba_id,
                            "phone_number_id": data.data.phone_number_id,
                            "business_id": data.data.business_id,
                            'code': fbToken,
                            "order_id": {!! $order->id !!}
                        },
                        success: function (response) {
                            $('#alertMessage-22').show();
                            var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> ' + @json(__('message.success')) +'! </strong>' + response.message + '.</div>';
                            $('#alertMessage-22').html(result + ".");
                            $("#pay").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                            setTimeout(function () {
                                $('#alertMessage-22').slideUp(3000, function () {
                                    setTimeout(function () {
                                        location.reload();
                                    }, 1000);
                                });
                            })
                        },
                        error: function (response) {
                            $('#alertMessage-22').show();
                            var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> ' + @json(__('message.success')) +'! </strong>' + response.message + '.</div>';
                            $('#alertMessage-22').html(result + ".");
                            $("#pay").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                            setTimeout(function () {
                                $('#alertMessage-22').slideUp(3000, function () {
                                    setTimeout(function () {
                                        location.reload();
                                    }, 1000);
                                });
                            })
                        },
                    })
                }
            }
        // Launch method and callback registration
        const launchWhatsAppSignup = () => {

            FB.login(fbLoginCallback, {
                config_id: {{$config_id}},
                response_type: 'code',
                override_default_response_type: true,
                extras: {
                    setup: {},
                }
            });
        }
    </script>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        $(function () {
            $('[data-bs-toggle="tooltip"]').tooltip()
        })

        // Initialize Stripe
        const stripe = Stripe("{{ $stripe_key }}",{
            locale: 'en' // Set locale if needed
        });

        // Define appearance options as per Stripe's Appearance API docs
        const appearance = {
            theme: 'stripe',
            variables: {
                fontFamily: 'Arial, sans-serif',
                fontSizeBase: '16px',
                colorPrimary: '#0570de',
                colorBackground: '#ffffff',
                colorText: '#30313d',
                colorDanger: '#df1b41',
                borderRadius: '4px'
            },
            rules: {
                '.Input': { padding: '10px' },
                '.StripeElement--invalid': {
                    borderColor: '#df1b41',
                    borderWidth: '1px',
                    borderStyle: 'solid'
                }
            }
        };

        // Create an instance of Elements with the appearance configuration
        const elements = stripe.elements({ appearance });

        // Create card elements
        const cardNumber = elements.create('cardNumber', {
            showIcon: true,
            iconStyle: 'solid'
        });
        cardNumber.mount('#card-number');

        const cardExpiry = elements.create('cardExpiry');
        cardExpiry.mount('#card-expiry');

        const cardCvc = elements.create('cardCvc');
        cardCvc.mount('#card-cvc');

        // Helper function to handle error events for each element
        function setupErrorHandling(element, errorElementId, containerId) {
            element.addEventListener('change', (event) => {
                const errorDiv = document.getElementById(errorElementId);
                const container = document.getElementById(containerId);
                if (event.error) {
                    errorDiv.textContent = event.error.message;
                    container.classList.add('StripeElement--invalid');
                } else {
                    errorDiv.textContent = '';
                    container.classList.remove('StripeElement--invalid');
                }
            });
        }

        // Set up error handling for each field
        setupErrorHandling(cardNumber, 'card-number-errors', 'card-number');
        setupErrorHandling(cardExpiry, 'card-expiry-errors', 'card-expiry');
        setupErrorHandling(cardCvc, 'card-cvc-errors', 'card-cvc');
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                location.reload();
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
                if({!! $autorenewal_status !!}) {
                    cardUpdate();
                }else{
                    swal.fire({
                        title: "<h2 class='swal2-title custom-title'>{{Lang::get('message.info')}}</h2>",
                        html: "<div  class='swal2-html-container custom-content'>" +
                            "<div class='section-sa'>" +
                            "<p>{{Lang::get('message.auto_renewal_disable')}}</p>" + "</div>" +
                            "</div>",
                        position: 'top',
                        confirmButtonText: "{{ __('message.ok') }}",
                        confirmButtonColor: "#007bff",
                        width: "600px",
                    })
                    $('#renew').prop('checked',false)
                }


            }else{
                var id = $('#orderID').val();
                $.ajax({
                    url : '{{url("renewal-disable")}}',
                    method : 'post',
                    data : {
                        "order_id" : id,

                    },
                    success: function(response){
                        $('#alertMessage-2').show();
                        var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> ' + @json(__('message.success')) + '! </strong>' + response.message + '.</div>';
                        $('#alertMessage-2').html(result+ ".");
                        $("#pay").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
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
            var id = $('#orderID').val();
            var domain = window.location.href;
            $('#payment').on('click', function () {
                var pay = $('#sel-payment').val();
                if (pay == null) {
                    $("#payment").html("<i class='fa fa-check'></i> {{ __('message.validate') }}");
                    $('#payerr').show();
                    $('#payerr').html(@json(__('message.select_pay')));
                    $('#payerr').focus();
                    $('#sel-payment').css("border-color", "red");
                    $('#payerr').css({ "color": "red" });
                    return false;
                }
                if (pay == 'stripe') {
                    $('#renewal-modal').modal('hide');
                    $('#stripe-Modal').modal('show');

                    $('#pay').on('click', async function () {
                        $('#pay').prop("disabled", true);
                        $('#pay').html("<i class='fa fa-circle-o-notch fa-spin fa-1x'></i> " + @json( __('message.processing')));
                        const {token, error} = await stripe.createToken(cardNumber);

                        await $.ajax({
                            url: '{{url("strRenewal-enable")}}',
                            type: 'POST',
                            data: {
                                "order_id": id,
                                "stripeToken": token.id,
                                "_token": "{!! csrf_token() !!}",
                            },
                            success: function (response) {
                                if (response.type == 'success') {
                                    $('#stripe-Modal').modal('hide');
                                    $('#alertMessage-2').show();
                                    $('#updateButton').show();
                                    var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success')}}! </strong>' + response.message + '.</div>';
                                    $('#alertMessage-2').html(result + ".");
                                    $("#pay").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                                    setTimeout(function () {
                                        location.reload();
                                    }, 3000);

                                } else {
                                    window.location.href = response;
                                }


                            },
                            error: function (data) {
                                var errorMessage = data.responseJSON.error;
                                $('#stripe-Modal').modal('hide');
                                $("#pay").attr('disabled', false);
                                $("#pay").html("Pay now");
                                $('html, body').animate({scrollTop: 0}, 500);
                                var html = '<div class="alert alert-danger alert-dismissable alert-content"><strong><i class="fas fa-exclamation-triangle"></i>{{ __('message.oh_snap') }} </strong>' + data.responseJSON.error + ' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><br><ul>';
                                $('#error-1').show();
                                document.getElementById('error-1').innerHTML = html;
                            }
                        });
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
                        alert(@json(__('message.session_expired')));
                        window.location.href = '/login';
                    }
                }

            },

            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": '<img id="blur-bg" class="backgroundfadein" style="top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;" src="{!! asset("lb-faveo/media/images/gifloader3.gif") !!}">'
            },
            language: {
                paginate: {
                    first:      "{{ __('message.paginate_first') }}",
                    last:       "{{ __('message.paginate_last') }}",
                    next:       "{{ __('message.paginate_next') }}",
                    previous:   "{{ __('message.paginate_previous') }}"
                },
                emptyTable:     "{{ __('message.empty_table') }}",
                info:           "{{ __('message.datatable_info') }}",
                zeroRecords:    "{{ __('message.no_matching_records_found') }} ",
                infoEmpty:      "{{ __('message.info_empty') }}",
                infoFiltered:   "{{ __('message.info_filtered') }}",
                lengthMenu:     "{{ __('message.length_menu') }}",
                loadingRecords: "{{ __('message.loading_records') }}",
                search:         "{{ __('message.table_search') }}",

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
                        alert('{{ __('message.session_expired')}}')
                        window.location.href = '/login';
                    }
                }

            },
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": '<img id="blur-bg" class="backgroundfadein" style="top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;" src="{!! asset("lb-faveo/media/images/gifloader3.gif") !!}">'
            },
            language: {
                paginate: {
                    first:      "{{ __('message.paginate_first') }}",
                    last:       "{{ __('message.paginate_last') }}",
                    next:       "{{ __('message.paginate_next') }}",
                    previous:   "{{ __('message.paginate_previous') }}"
                },
                emptyTable:     "{{ __('message.empty_table') }}",
                info:           "{{ __('message.datatable_info') }}",
                zeroRecords:    "{{ __('message.no_matching_records_found') }} ",
                infoEmpty:      "{{ __('message.info_empty') }}",
                infoFiltered:   "{{ __('message.info_filtered') }}",
                lengthMenu:     "{{ __('message.length_menu') }}",
                loadingRecords: "{{ __('message.loading_records') }}",
                search:         "{{ __('message.table_search') }}",

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
                                var result = '<div class="alert alert-success alert-dismissable"><strong><i class="fa fa-check"></i> ' + @json(__('message.success')) + '! </strong> ' + data.update + ' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                                $('#response1').html(result);
                                $('#response1').css('color', 'green');
                                setTimeout(function(){
                                    window.location.reload();
                                },3000);
                            }

                        }

                    });
                });
        });


        $(document).ready(function() {
            $('#changeDomain').on('click', function() {
                $('#changeDomain').attr('disabled',true);
                $('#changeDomain').html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i> " + @json(__('message.please_wait')));
                $('.loader-wrapper').show();
                $('.overlay').show(); // Show the overlay
                $('.modal-body').css('pointer-events', 'none');
                var newDomain = $('#clouduserdomain').val();
                var currentDomain = "{!! \App\Model\Order\InstallationDetail::where('order_id', $id)->latest()->value('installation_path') !!}";
                var license_code = "{!!$order->serial_key!!}";
                var productId = "{!! $order->product !!}";
                var orderId = {!! $id !!};

                $.ajax({
                    type: "POST",
                    data: { 'newDomain': newDomain, 'currentDomain': currentDomain,'lic_code':license_code,'product_id':productId,'order_id':orderId},
                    beforeSend: function() {
                        $('#response').html("<img id='blur-bg' class='backgroundfadein' style='width: 50px; height: 50px; display: block; position: fixed;' src='{!! asset('lb-faveo/media/images/gifloader3.gif') !!}'>");
                    },
                    url: "{{ url('change/domain') }}",
                    success: function (data) {
                        if (data.success ==true){
                            var result = '<div class="alert alert-success alert-dismissable"><strong><i class="far fa-thumbs-up"></i> ' + @json(__('message.well_done')) + ' </strong> ' + data.message + '</div>';
                            $('#success-domain').html(result).css('color', 'green').show();
                            $('#changeDomain').attr('disabled',false);
                            $('#changeDomain').html("<i class='fa fa-globe'>&nbsp;&nbsp;</i>" + @json(__('message.chg_domain')));
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
                            var result = '<div class="alert alert-danger alert-dismissable"><strong><i class="far fa-thumbs-down"></i>' + @json( __('message.error_oops')) + '</strong> ' + data.responseJSON.message + ' </div>';
                            $('#failure-domain').html(result).css('color', 'red').show(); // Show the error message
                            $('#changeDomain').attr('disabled', false);
                            $('#changeDomain').html("<i class='fa fa-globe'>&nbsp;&nbsp;</i>" + @json(__('message.chg_domain')));
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
                $('#agentNumber').html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i> " + @json(__('message.please_wait')));
                $('.loader-wrapper').show();
                $('.overlay').show(); // Show the overlay
                $('.modal-body').css('pointer-events', 'none');
                var agentAction=$('#agentAction').val()
                var newAgents = $('#numberAGt').val();
                var orderId = {!! $id !!};
                var productId ={!! $product->id !!};
                var subId = {!! $subscription->id !!};

                $.ajax({
                    type: "POST",
                    data: { 'newAgents': newAgents, 'orderId': orderId, 'product_id':productId, 'subId': subId, 'agentAction':agentAction},
                    beforeSend: function() {
                        $('#response').html("<img id='blur-bg' class='backgroundfadein' style='width: 50px; height: 50px; display: block; position: fixed;' src='{!! asset('lb-faveo/media/images/gifloader3.gif') !!}'>");
                    },
                    url: "{{ url('changeAgents') }}",
                    success: function(response) {
                        $('#agentNumber').attr('disbaled',false);
                        $('#agentNumber').html("<i class='fa fa-users'>&nbsp;&nbsp;</i> " + @json(__('message.update_agents')));
                        $('.loader-wrapper').hide();
                        $('.overlay').hide(); // Hide the overlay
                        $('.modal-body').css('pointer-events', 'auto');
                        window.location.href = response;
                    },
                    error: function(data) {
                        if (data.responseJSON.success == false) {
                            $('#agentNumber').attr('disabled', false);
                            $('#agentNumber').html("<i class='fa fa-users'>&nbsp;&nbsp;</i> " + @json(__('message.update_agents')));
                            var result = '<div class="alert alert-danger alert-dismissable"><strong><i class="far fa-thumbs-down"></i>' + @json( __('message.error_oops')) + ' </strong> ' + data.responseJSON.message + ' </div>';
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
                var agentAction=$('#agentAction').val();
                $('.loader-wrapper').show();
                $('.overlay').show(); // Show the overlay
                $('.modal-body').css('pointer-events', 'none');

                $.ajax({
                    type: 'POST',
                    url: "{{url('get-agent-inc-dec-cost')}}",
                    data: { 'number': selectedNumber, 'oldAgents':  oldAgents, 'orderId' : orderId, 'agentAction': agentAction},
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
                    error: function(data) {
                        if (data.responseJSON.success == false) {
                            $('#agentNumber').attr('disabled', false);
                            $('#agentNumber').html("<i class='fa fa-users'>&nbsp;&nbsp;</i> " + @json(__('message.update_agents')));
                            var result = '<div class="alert alert-danger alert-dismissable"><strong><i class="far fa-thumbs-down"></i>' + @json( __('message.error_oops')) + ' </strong> ' + data.responseJSON.message + ' </div>';
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
                $(this).prop("disabled", false);

            });
        });
    </script>

    <script type="text/javascript">


        $(document).ready(function() {
            $('#upgradedowngrade').on('click', function() {
                $('#upgradedowngrade').attr('disabled',true);
                $('#upgradedowngrade').html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i> " + @json(__('message.please_wait')));
                $('.loader-wrapper').show();
                $('.overlay').show(); // Show the overlay
                $('.modal-body').css('pointer-events', 'none');
                var planId = $('select[name="plan"]').val();
                var user = $('input[name="user"]').val();
                var agents = "{{ $latestAgents }}";
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
                            var result = '<div class="alert alert-success alert-dismissable"><strong><i class="far fa-thumbs-up"></i> ' + @json(__('message.well_done')) + ' </strong> ' + data.message + ' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                            $('#response-upgrade').html(result);
                            $('#response-upgrade').css('color', 'green');
                            $('#upgradedowngrade').attr('disabled',false);
                            $('#upgradedowngrade').html("<i class='fas fa-cloud-upload-alt'>&nbsp;&nbsp;</i>{{ __('message.change_plan')}}");
                            $('.loader-wrapper').hide();
                            $('.overlay').hide(); // Hide the overlay
                            $('.modal-body').css('pointer-events', 'auto');
                        }

                    },  error: function(data) {
                        if (data.responseJSON.success == false) {
                            var result = '<div class="alert alert-danger alert-dismissable"><strong><i class="far fa-thumbs-down"></i>' + @json(__('message.error_oops')) + ' </strong> ' + data.responseJSON.message + '</div>';
                            $('#failure-upgrade').html(result).css('color', 'red').show();
                            $('#upgradedowngrade').attr('disabled',false);
                            $('#upgradedowngrade').html("<i class='fas fa-cloud-upload-alt'>&nbsp;&nbsp;</i>{{ __('message.change_plan')}}");
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
            data: { payment_intent: paymentIntent,orderId: orderId, _token: '{!! csrf_token() !!}'},
            success: function(response) {
                $('#confirmStripe').modal('hide');
                $('html, body').animate({ scrollTop: 0 }, 500);
                $('#alertMessage-2').show();

                var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success')}}! </strong>' + response.message + '.</div>';
                $('#alertMessage-2').html(result + ".");
                $('#updateButton').show();
                setTimeout(function() {
                    window.location.href = newUrl; 
                }, 5000);
            },
               error: function(xhr, status, error) {
                   var errorMessage = {!! json_encode(__('message.something_different_payment')) !!};
                $('#confirmStripe').modal('hide');
                $('html, body').animate({ scrollTop: 0 }, 500);
                var html = '<div class="alert alert-danger alert-dismissable alert-content"><strong><i class="fas fa-exclamation-triangle"></i> {{ __('message.oh_snap') }} </strong>' + errorMessage + ' <br><ul>';
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
                              order: [[3, "asc"]],
                                ajax: {
                              "url":  "{{Url('get-installation-details/'.$order->id)}}",
                                 error: function(xhr) {
                                 if(xhr.status == 401) {
                                  alert('{{ __('message.session_expired')}}')
                                  window.location.href = '/login';
                                 }
                              }

                              },
                             
                              "oLanguage": {
                                  "sLengthMenu": "_MENU_ Records per page",
                                  "sSearch"    : "Search: ",
                                  "sProcessing": '<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading')}}</div></div>'
                              },
                              language: {
                                  paginate: {
                                      first:      "{{ __('message.paginate_first') }}",
                                      last:       "{{ __('message.paginate_last') }}",
                                      next:       "{{ __('message.paginate_next') }}",
                                      previous:   "{{ __('message.paginate_previous') }}"
                                  },
                                  emptyTable:     "{{ __('message.empty_table') }}",
                                  info:           "{{ __('message.datatable_info') }}",
                                  zeroRecords:    "{{ __('message.no_matching_records_found') }} ",
                                  infoEmpty:      "{{ __('message.info_empty') }}",
                                  infoFiltered:   "{{ __('message.info_filtered') }}",
                                  lengthMenu:     "{{ __('message.length_menu') }}",
                                  loadingRecords: "{{ __('message.loading_records') }}",
                                  search:         "{{ __('message.table_search') }}",

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

                          function editWhatsappUser(id){
                              $.ajax({
                                  url: "{!! url('get-webhook-url') !!}",
                                  method: "get",
                                  data: { 'id': id},
                                  success: function (data) {
                                      url=data.data.url;
                                      id=data.data.id;
                                      $('#webhook_url_edit').val(url);
                                      $('#webhook_id').val(id);

                                      $('#Whatsapp-url-edit').modal('show');
                                  },
                                  error: function (data) {
                                      $('#successmsg').hide();
                                      $('#error').show();
                                      var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.responseJSON.message + '!</div>';
                                      $('#error').html(result);
                                      setInterval(function () {
                                          $('#error').slideUp(5000);
                                          location.reload();
                                      }, 10000);
                                  },

                              });
                          }

                          function deleteWhatsappUser(id) {
                              var id = id;
                              var orderId = orderId;
                              var swl=swal.fire({
                                  title:"<h2 class='swal2-title custom-title'>{{Lang::get('message.Delete')}}",
                                  html: "<div class='swal2-html-container custom-content'>" +
                                      "<div class='section-sa'>" +
                                      "<p>Are you sure you want to delete this number?" +"</p></div>"+
                                      "</div>",
                                  showCancelButton: true,
                                  cancelButtonText: "{{ __('message.cancel') }}",
                                  showCloseButton: true,
                                  position:"top",
                                  width:"600px",

                                  confirmButtonText: @json(trans('message.Delete')),
                                  confirmButtonColor: "#007bff",

                              }).then((result)=> {
                                  if(id.length > 0){
                                      if (result.isConfirmed) {

                                          $.ajax({
                                              url: "{!! url('whatsapp-deregister') !!}",
                                              method: "post",
                                              data: { 'id': id},
                                              success: function (data) {
                                                  if (data.success === true) {
                                                      var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-check"></i>{{ __('message.success') }}! </strong>' + data.message + '!</div>';
                                                      $('#alertMessage-2').show();
                                                      $('#error-1').hide();
                                                      $('#alertMessage-2').html(result);
                                                      setInterval(function () {
                                                          $('#alertMessage-2').slideUp(5000);
                                                          location.reload();
                                                      }, 3000);
                                                  } else if (data.success === false) {
                                                      $('#alertMessage-2').hide();
                                                      $('#error-1').show();
                                                      var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.message + '!</div>';
                                                      $('#error-1').html(result);
                                                      setInterval(function () {
                                                          $('#error-1').slideUp(5000);
                                                          location.reload();
                                                      }, 10000);
                                                  }
                                              },
                                              error: function (data) {
                                                  $('#successmsg').hide();
                                                  $('#error').show();
                                                  var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.responseJSON.message + '!</div>';
                                                  $('#error').html(result);
                                                  setInterval(function () {
                                                      $('#error').slideUp(5000);
                                                      location.reload();
                                                  }, 10000);
                                              },

                                          });
                                      } else {
                                          window.close();
                                      }
                                  }else if (result.dismiss === Swal.DismissReason.cancel) {
                                      // Action if "No" is clicked
                                      window.close();             }
                              })
                              return false;
                          }
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