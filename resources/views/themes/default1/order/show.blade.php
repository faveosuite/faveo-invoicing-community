@extends('themes.default1.layouts.master')
@section('title')
Order
@stop
@section('content-header')
<style>
    .col-2, .col-lg-2, .col-lg-4, .col-md-2, .col-md-4,.col-sm-2 {
        width: 0px;
    }
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
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
  height: 16px;
  width: 16px;
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
        height:300px;
        
    }
    .btn-xs{
        padding:.300rem!important;
    }
</style>


<div class="col-sm-6">
    <h1>{{ __('message.order_details') }}</h1>
</div>
<div class="col-sm-6">
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
        <li class="breadcrumb-item"><a href="{{url('orders')}}"><i class="fa fa-dashboard"></i> {{ __('message.all-orders') }}</a></li>
        <li class="breadcrumb-item active">{{ __('message.view_order') }}</li>
    </ol>
</div><!-- /.col -->


@stop
@section('content')
    <div class="card card-secondary card-outline">
<div class="row">
<div class="col-md-12">
         
            <div class="card-body">
                <div class="box-group" id="accordion">

                   <div class="card-header with-border">
                    <h4 class="card-title" style="color:black;">
                     
                         <i class="fa fa-users"></i>
                        {{ __('message.overview') }}
                     
                    </h4>
                  </div>

                       <div class="card-body">
                        <div class="callout callout-info">
                            <div class="row">
                                <div class="col-md-4">
                                    <b>{{ __('message.date') }}: </b>{!! getDateHtml($order->created_at) !!}
                                </div>
                                <div class="col-md-4">
                                    <b>{{ __('message.order_no') }}: </b>  #{{$order->number}}

                                </div>
                                <div class="col-md-4">
                                    <b>{{ __('message.status') }}: </b>{{$order->order_status}}
                                </div>
                            </div>
                            <br>
                            <?php
                               $terminatedOrderId = \DB::table('terminated_order_upgrade')->where('upgraded_order_id',$order->id)->value('terminated_order_id');
                               $terminatedOrderNumber = \App\Model\Order\Order::where('id',$terminatedOrderId)->value('number');
                               ?>
                            @if(!empty($terminatedOrderId))
                                <p class="order-links">
                                    {{ __('message.this_order') }} <b>{{$order->number}}</b>
                                    {{ __('message.has_generated') }}: <a class="order-link" href="{{$terminatedOrderId}}">{{$terminatedOrderNumber}}</a>.
                                </p>
                            @endif

                            @if($order->order_status=='Terminated')
                                <br>
                                <div class="row">
                                <div class="col-md-12">
                                <?php
                                $idOrdert  = \DB::table('terminated_order_upgrade')->where('terminated_order_id',$order->id)->get();
                                foreach ($idOrdert as $ordt) {
                                    $newOrders[] = \App\Model\Order\Order::where('id', $ordt->upgraded_order_id)->get();
                                }
                                ?>

                                @foreach($newOrders as $newOrder)
                                    <div class="termination-message">
                                        <p class="termination-notice"><b>{{ __('message.termination_notice') }}</b></p>
                                        <p class="termination-description">
                                            {{ __('message.order_terminated') }}
                                        </p>
                                        <p class="order-links">
                                            {{ __('message.termination_order') }}: <b>{{$order->number}}</b>
                                            {{ __('message.has_new_order') }}: <a class="order-link" href="{{$newOrder[0]->id}}">{{$newOrder[0]->number}}</a>.
                                        </p>
                                    </div>

                                @endforeach
                                </div>
                            </div>
                                @endif

                        </div>
                           <div class="row">


                             <div class="col-md-12">
                             <div class="card card-secondary card-outline">
                                 <div class="scrollit">
                           <div class="card-body table-responsive">
                               <div class="card-header">
                              <h5 class="card-title" style="position: absolute;left: 7px;bottom: 10px;">
                                  {{ __('message.user_details') }}
                             </h5>
                               </div>

                        
                              
                            <table class="table table-hover">

                                <tbody><tr><td><b>{{ __('message.name_page') }}:</b></td><td><a href="{{url('clients/'.$user->id)}}">{{ucfirst($user->first_name)}}</a></td></tr>
                                    <tr><td><b>{{ __('message.email') }}:</b></td><td>{{$user->email}}</td></tr>
                                    <tr><td><b>{{ __('message.mobile') }}:</b></td><td>@if($user->mobile_code)(<b>+</b>{{$user->mobile_code}})@endif&nbsp;{{$user->mobile}}</td></tr>
                                    <tr><td><b>{{ __('message.address') }}:</b></td><td>{{$user->address}},
                                            {{ucfirst($user->town)}}, 
                                            @if(key_exists('name',getStateByCode($user->state)))
                                            {{getStateByCode($user->state)['name']}}
                                            @endif
                                        </td></tr>
                                    <tr><td><b>{{ __('message.country') }}:</b></td><td>{{getCountryByCode($user->country)}}</td></tr>

                                </tbody>
                              </table>
                             </div>
                                 </div>

                             </div>
                       </div>


                               <div class="col-md-12">
                                   <div class="card card-secondary card-outline">
                                       <div class="scrollit">
                                       <div class="card-body table-responsive">
                                           @include('themes.default1.front.clients.reissue-licenseModal')
                                           @include('themes.default1.front.clients.domainRestriction')
                                           @include('themes.default1.order.installationLimit')
                                           @include('themes.default1.order.update_ends-modal')
                                           @include('themes.default1.order.license_end-modal')
                                           @include('themes.default1.order.support_end-modal')

                                           <!-- Modal for Localized License domain-->

                                               <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                   <div class="modal-dialog" role="document">
                                                       <div class="modal-content">
                                                           <div class="modal-header">
                                                               <h5 class="modal-title" id="exampleModalLabel">{{ __('message.enter_domain_host') }}</h5>
                                                           </div>
                                                           <div class="modal-body">
                                                               <form method="GET" action="{{url('uploadFile')}}">
                                                                   {!! csrf_field() !!}
                                                                   <div class="form-group">
                                                                       <label for="recipient-name" class="col-form-label">{{ __('message.domain_name') }}</label>
                                                                       <input type="text" class="form-control" id="recipient-name" placeholder="https://faveohelpdesk.com/public" name="domain" value="" required>
                                                                       {{Form::hidden('orderNo', $order->number)}}
                                                                       {{Form::hidden('userId',$user->id)}}
                                                                       <br>
                                                                       <div class="modal-footer">
                                                                           <button type="button" id="close" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                                                                           <button type="submit" id="domainSave" class="done btn btn-primary"><i class="fas fa-save"></i>&nbsp;{{ __('message.done') }}</button>
                                                                       </div>
                                                                   </div>
                                                               </form>
                                                           </div>
                                                       </div>
                                                   </div>
                                               </div>
                                           <div class="card-header">
                                               <h4 class="card-title" style="position: absolute;left: 7px;bottom: 10px;">
                                                   {{ __('message.license_details') }}
                                               </h4>
                                           </div>


                                           <table id="lic_details" class="table table-hover">

                                               <input type="hidden" name="domainRes" id="domainRes" value={{$allowDomainStatus}}>
                                               <tbody>
                                               <tr>
                                                   <td><b>{{ __('message.license_code') }}</b></td>
                                                   <td id="" data-type="serialkey">{{($order->serial_key)}}</td>

                                                   <td> @component('mini_views.copied_flash_text',
                                                        ['navigations'=>[['btnName'=>'lic_btn','slot'=>'license','style'=>'<span data-type="copy" style="font-size: 12px; pointer-events: initial; cursor: pointer; display: inline-block;height: 28px;width: 24px;" id="copyBtn" data-toggle="tooltip"  title="Click to copy to clipboard" class="btn btn-sm btn-secondary btn-xs" style="width:max-content;border:none;margin-left: 20px;"><i class="fas fa-copy"></i></span><span class="badge badge-success badge-xs pull-right" id="copied1" style="display:none;margin-top:-40px;margin-left:-20px;position: absolute;">Copied</span>'],
                                                        ]
                                                        ])                    
                                                        @endcomponent
                                                    <button class='class="btn btn-sm btn-secondary btn-xs' style="width:max-content;border:none;margin-left: 20px;" id="reissueLic" data-id="{{$order->id}}" data-name="{{$order->domain}}"><i class="fas fa-id-card-alt" style='color:white;' {!! tooltip('Reissue&nbsp;License') !!}</i>
                                                     </button>

                                                   </td>
                                                 
                                               </tr>
                                               @if ($licenseStatus)


                                                  
                                                   <tr>
                                                       <td><b>{{ __('message.installation_limit') }}:</b></td>
                                                       <td>
                                                           {{$noOfAllowedInstallation}}
                                                       </td>
                                                       <td>
                                                           <button class="btn btn-sm btn-secondary btn-xs"  id="installlimit" limit-id="{{$order->id}}" install-limit="{{$noOfAllowedInstallation}}"><i class="fa fa-edit" style='color:white;' {!! tooltip('Edit') !!}</i></button>

                                                       </td>

                                                   </tr>
                                              
                                              <!--  <tr>
                                                   <td><b>Current Version:</b></td>
                                                   <td>{!! $versionLabel !!} </td>
                                                   <td></td>
                                               </tr> -->

                                             <!--   <tr>
                                                   <td><b><label data-toggle="tooltip" data-placement="top" title="" data-original-title="Last connection with License Manager">Last Active:</label></b></td>
                                                   <td>
                                                       {!! $lastActivity !!}
                                                   </td>
                                                   <td></td>
                                               </tr> -->
                                               @endif
                                               <tr>
                                                   <td><b>{{ __('message.updates_expiry') }}:</b></td>
                                                   <td class="brtags"> {!! $date !!} </td>
                                                   <td>
                                                       @if($date != '--')
                                                           <button class="btn btn-sm btn-secondary btn-xs" id="updates_end" updates-id="{{$order->id}}" data-date="{{getTimeInLoggedInUserTimeZone($subscription->update_ends_at,'m/d/Y')}}"><i class="fa fa-edit" style='color:white;' {!! tooltip('Edit') !!}</i></button>
                                                       @endif
                                                   </td>
                                               </tr>

                                               <tr>
                                                   <td><b>{{ __('message.license_expiry') }}:</b></td>
                                                   <td class="brtags">{!! $licdate !!} </td>
                                                   <td>
                                                       @if($licdate != '--')
                                                           <button class="btn btn-sm btn-secondary btn-xs" id="license_end" license-id="{{$order->id}}" license-date="{{getTimeInLoggedInUserTimeZone($subscription->ends_at,'m/d/Y')}}"><i class="fa fa-edit" style='color:white;' {!! tooltip('Edit') !!}</i>
                                                           </button>
                                                       @endif
                                                   </td>
                                               </tr>

                                               
                                               <tr>
                                                   <td><b>{{ __('message.support_expiry') }}:</b></td>
                                                   <td class="brtags">{!! $supdate !!}</td>
                                                   <td>
                                                       @if($supdate != '--')
                                                           <button class="btn btn-sm btn-secondary btn-xs" id="support_end" support-id="{{$order->id}}" support-date="{{getTimeInLoggedInUserTimeZone($subscription->support_ends_at,'m/d/Y')}}" ><i class="fa fa-edit" style='color:white;' {!! tooltip('Edit') !!}</i></button>
                                                             </button>
                                                       @endif
                                                   </td>
                                               </tr>  
                                               <tr>
                                                   <td><b>{{ __('message.switch_localized_license') }}</b></td>
                                                   <td class="brtags"> 
                                                 <label class="switch toggle_event_editing">
                                                     <input data-id="{{$order->number}}" class="localized-slider checkbox" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" {{$order->license_mode=='File' ? 'checked' : '' }}>
                                                   <span class="slider round"></span>
                                               </label>
                                                </td>
                                                <td>
                                                @if($order->license_mode=='File')  
                                                <button class="btn btn-secondary mb-2 btn-sm" id="defaultModalLabel" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo"> <span title="{{ __('message.enter_client_domain_license') }}" {!!tooltip('Edit')!!} {{ __('message.enter_domain_license') }}></span></button>
                                                @endif
                                               </td>
                                               </tr>
                                               </tbody>
                                           </table>
                                       </div>
                                    </div>
                                   </div>
                               </div>

           @if($licenseStatus)
            <div class="col-md-12">
            <div class="card card-secondary card-outline">
       
            <div class="row">
            <div class="card-body table-responsive">
                <div class="box-group" id="accordion5">

                     <a data-toggle="collapse" data-parent="#accordion3" href="#collapseFour">
                   <div class="card-header with-border">
                    <h4 class="card-title" style="color:black;">
                      <i class="fa fa-credit-card"></i>
                        {{ __('message.installation_details') }}
                    </h4>
                  </div>
                </a>
                       <div class="card-body">
                       <div class="col-md-12">
                         <table id="installationDetail-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                        <thead><tr>
                        <th>{{ __('message.installation_path') }}</th>
                         <th>{{ __('message.installation_ip') }}</th>
                         <th>{{ __('message.version') }}</th>
                         <th>{{ __('message.last_active') }}</th>
                         </tr>
                    </thead>
                         </table>
                         </div>
                         </div>
                    </div>
            </div>
          </div>
           
            
          </div>
        </div>
              @endif
              </div>
 
           


    <div class="card card-secondary card-outline">
        <div class="col-md-12">
            <div class="row">
            <div class="card-body table-responsive">
                <div class="box-group" id="accordion3">

                     <a data-toggle="collapse" data-parent="#accordion3" href="#collapseFour">
                   <div class="card-header with-border">
                    <h4 class="card-title" style="color:black;margin-left: -8px;">
                      <i class="fa fa-credit-card"></i>
                        {{ __('message.invoice_list') }}
                    </h4>
                  </div>
                </a>
                       <div class="card-body">
                       <div class="col-md-12">
                         <table id="editorder-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                             

                    <thead><tr>
                        
                         <th >{{ __('message.invoice_no') }}</th>
                          <th>{{ __('message.products') }}</th>
                           
                            <th>{{ __('message.date') }}</th>
                            <th>{{ __('message.total') }}</th>
                            
                             <th>{{ __('message.status') }}</th>
                             
                            <th>{{ __('message.action') }}</th>
                        </tr></thead>
                        </table>

                          </div>
      

                          </div>

                </div>
            </div>
          </div>
        </div>
        

 <script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'all_order';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'all_order';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">

<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
        
        

       <script type="text/javascript">
              $('#installationDetail-table').DataTable({
                  processing: true,
                  serverSide: true,
                   stateSave: true,
                    ajax: {
                  "url":  "{{Url('get-installation-details/'.$order->id)}}",
                     error: function(xhr) {
                     if(xhr.status == 401) {
                      alert('{{ __('message.session_expired') }}')
                      window.location.href = '/login';
                     }
                  }

                  },
                 
                  "oLanguage": {
                      "sLengthMenu": "_MENU_ Records per page",
                      "sSearch"    : "Search: ",
                      "sProcessing": '<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'
                  },
                      columnDefs: [
                      { 
                          targets: 'no-sort', 
                          orderable: false,
                          order: []
                      }
                  ],

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
<script type="text/javascript">
        $('#editorder-table').DataTable({
            processing: true,
            serverSide: true,
              ajax: {
            "url":  "{{Url('get-my-invoices/'.$order->id.'/'.$user->id.'/admin')}}",
               error: function(xhr) {
               if(xhr.status == 401) {
                alert('{{ __('message.session_expired') }}')
                window.location.href = '/login';
               }
            }

            },
           
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": '<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'
            },
                columnDefs: [
                { 
                    targets: 'no-sort', 
                    orderable: false,
                    order: []
                }
            ],

            columns: [
            
                {data: 'number', name: 'number'},
                {data: 'products', name: 'invoice_item'},
                {data: 'date', name: 'created_at'},
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
      </script>
    </div>
    <div class="card card-secondary card-outline">
         <div class="row">
            <div class="col-md-12">
            <div class="card-body table-responsive">
                <div class="box-group" id="accordion4">
                     <a data-toggle="collapse" data-parent="#accordion4" href="#collapseFive">
                   <div class="card-header">
                    <h4 class="card-title" style="color:black;">
                       <i class="fa fa-bars"></i>
                        {{ __('message.payment_receipts') }}
                    </h4>
                  </div>
                </a>
                       <div class="card-body">
                       <div class="col-md-12">
                           <table id="order1-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">
                 <button  value="" class="btn btn-secondary btn-sm btn-alldell" id="bulk_delete"><i class= "fa fa-trash"></i>&nbsp;&nbsp;{{ __('message.delmultiple') }}</button><br /><br />
                    <thead><tr>
                        <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                         <th>{{ __('message.invoice_no') }}</th>
                          <th>{{ __('message.total') }}</th>
                           
                            <th>{{ __('message.method') }}</th>
                            <th>{{ __('message.status') }}</th>
                            
                             <th>{{ __('message.payment_date') }}</th>
                             
                        </tr></thead>
                     </table>

                          </div>
      

                          </div>
                    </div>

            </div>
          </div>
        </div>
        </div>
        <div id="alertMessage"></div>


         <div class="card card-secondary card-outline">
         <div class="row">
            <div class="col-md-12">
            <div class="card-body table-responsive">
                <div class="box-group" id="accordion4">
                     <a data-toggle="collapse" data-parent="#accordion4" href="#collapseFive">
                   <div class="card-header">
                    <h4 class="card-title" style="color:black;">
                       <i class="fa fa-bars"></i>
                        {{ __('message.auto_renewal') }}
                    </h4>
                  </div>
                </a>
                      
                       <div class="col-md-12">
                        
                         <div class="row">
                            <table class="table table-hover">

                                <tbody>
                                    <tr><td><b>{{ __('message.auto_renewal_subscription') }}</b></td><td>
                                  <label class="switch toggle_event_editing">
                                             <input type="checkbox" value="{{$statusAutorenewal}}"  name="is_subscribed"
                                              class="renewcheckbox" id="renew">
                                              <span class="slider round"></span>
                                              <input type="hidden" name="" id="autoorder" value="{{$order->id}}">


                                        </label></td></tr>
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
                                    @if($subscription && $subscription->is_subscribed && $payment_log)
                                     <tr><td><b>{{ __('message.status') }}:</b></td><td>
                                     <span class="text-success font-weight-bold">{{ __('message.active') }}</span>
                                     </td></tr>
                                    <tr><td><b>{{ __('message.payment-method') }}:</b></td><td>{{ucfirst($payment_log->payment_method)}}</td></tr>
  
                                    <tr><td><b>{{ __('message.subscription_start_date') }}:</b></td><td>{!! getDateHtml($payment_log->date) !!}</td></tr>
                                    @else
                                    <tr><td><b>{{ __('message.status') }}:</b></td><td>
                                     <span class="text-danger font-weight-bold">{{ __('message.inactive') }}</span>
                                     </td></tr>
                                    @endif

                                </tbody>
                              </table>
                       
          </div>
        </div>

        
          </div>
        </div>
        </div>
<script>

    $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip({
                container : 'body'
            });

    })
</script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">

<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
 <script src="{{asset('common/js/licCode.js')}}"></script>
<script type="text/javascript">

        $(document).ready(function(){
         var status = $('.renewcheckbox').val();
         if(status ==1) {
         $('#renew').prop('checked',true)
         } else if(status ==0) {
          $('#renew').prop('checked',false)
          $('#renew').prop('disabled',true)
             }
        });

     $('#renew').on('change',function () {
         if (!$(this).prop("checked")) {
            var id = $('#autoorder').val();
              $.ajax({
                    url : '{{url("renewal-disable")}}',
                    method : 'post',
                    data : {
                        "order_id" : id,

                    },
                    success: function(response){
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#pay").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                    location.reload();
                    },
                })
         }
     });


        $('#order1-table').DataTable({
            processing: true,
            serverSide: true,
         
     
             ajax: {
            "url":  "{{Url('get-my-payment/'.$order->id.'/'.$user->id)}}",
               error: function(xhr) {
               if(xhr.status == 401) {
                alert('{{ __('message.session_expired') }}')
                window.location.href = '/login';
               }
            }

            },

            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": ' <div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'
            },
                 columnDefs: [
                { 
                    targets: 'no-sort', 
                    orderable: false,
                    order: []
                }
            ],
            columns: [
            {data: 'checkbox', name: 'checkbox'},
                {data: 'number', name: 'number'},
                {data: 'amount', name: 'amount'},
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
     

    </script>
@stop


@section('icheck')
<script>

    function checking(e){
          
          $('#order1-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
     }
     

     $(document).on('click','#bulk_delete',function(){
      var id=[];
      if (confirm("Are you sure you want to delete this?"))
        {
            $('.payment_checkbox:checked').each(function(){
              id.push($(this).val())
            });
            if(id.length >0)
            {
               $.ajax({
                      url:"{!! route('payment-delete') !!}",
                      method:"delete",
                      data: $('#check:checked').serialize(),
                      beforeSend: function () {
                $('#gif').show();
                },
                success: function (data) {
                $('#gif').hide();
                $('#response').html(data);
                location.reload();
                }
               })
            }
            else
            {
                alert("{{ __('message.select_checkbox') }}");
            }
        }  

     });
</script>
@stop
@section('datepicker')
    <script>
    $('#licenseEnds').datetimepicker({
    format: 'L'
    });
    $('#updateEnds').datetimepicker({
    format: 'L'
    });
    $('#supportEnds').datetimepicker({
        format: 'L'
    });
    </script>
<!-----------------------------------For Reissuing License Domain------------------------------------------------------------->
<script>
        $("#reissueLic").click(function(){
          if ($('#domainRes').val() == 1) {
            var oldDomainId = $(this).attr('data-id');
            $("#orderId").val(oldDomainId);
            $("#domainModal").modal();
            $("#domainSave").on('click',function(){
            var id = $('#orderId').val();
            $.ajax ({
                type: 'patch',
                url : "{{url('reissue-license')}}",
                data : {'id':id},
                  beforeSend: function () {
                 $('#response1').html( '<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>');

                },
          
                success: function (data) {
               if (data.message =='success'){
                 var result =  '<div class="alert alert-success alert-dismissable"><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong> '+data.update+' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                  $('#response1').html(result);
                     $('#response1').css('color', 'green');
                setTimeout(function(){
                    window.location.reload();
                },2000);
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
      
            var domain = $('#newDomain').val();
            var id = $('#orderId').val();
             
            $.ajax ({
                type: 'patch',
                url : "{{url('change-domain')}}",
                data : {'domain':domain,'id':id},
                  beforeSend: function () {
                 $('#response').html( '<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>');

                },
          
                success: function (data) {
               if (data.message =='success'){
                 var result =  '<div class="alert alert-success alert-dismissable"><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong> '+data.update+' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                  $('#response').html(result);
                     $('#response').css('color', 'green');
                setTimeout(function(){
                    window.location.reload();
                },3000);
                  }
               
                }
                
             });
            });
          }
           
        });



 
<!------------------------------------------------------------------------------------------------------------------------------->
/*
* Update Updates Expiry date 
 */

 $("#updates_end").click(function(){
        var olddate = $(this).attr('data-date');
        var orderId = $(this).attr('updates-id');
        $("#updateEndsModal").modal();
        $("#order").val(orderId);
        $("#newDate").val(olddate);
        });

 //When Submit Button is Clicked in Modal Popup, passvalue through Ajax
    $("#updatesSave").on('click',function(){
      $('#updatesSave').attr('disabled',true);
      $('#updatesSave').html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
        var newdate = $("#newDate").val();
        var orderId = $("#order").val();
        $.ajax({
            type: "post",
            data: {'orderid': orderId , 'date': newdate},
            url: "{{url('edit-update-expiry')}}",
            success: function (response) {
              $("#updatesSave").attr('disabled',false);
              $("#updatesSave").html("Save");
                if (response.message =='success') {

                var result =  '<div class="alert alert-success alert-dismissable"><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong> '+response.update+' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                     $('#response2').html(result);
                     $('#response2').css('color', 'green');
                 setTimeout(function(){
                    window.location.reload();
                },2000);
                }
            },
            error: function(response) {
              $("#updatesSave").attr('disabled',false);
              $("#updatesSave").html("Save");
                var myJSON = JSON.parse(response.responseText).errors;
                var html = '<div class="alert alert-danger"><strong>{{ __('message.whoops') }} </strong>{{ __('message.something_wrong') }}<br><br><ul>';
                  for (var key in myJSON)
                  {
                      html += '<li>' + myJSON[key][0] + '</li>'
                  }
                 html += '</ul></div>';
                 $('#error2').show(); 
                 $('#response2').html(''); 
                  document.getElementById('error2').innerHTML = html;
                }
        });
    });

 /*
* Update License Expiry date 
 */

 $("#license_end").click(function(){
        var olddate = $(this).attr('license-date');
        var orderId = $(this).attr('license-id');
        $("#licenseEndsModal").modal();
        $("#order2").val(orderId);
        $("#newDate2").val(olddate);
        });

 //When Submit Button is Clicked in Modal Popup, passvalue through Ajax
    $("#licenseExpSave").on('click',function(){
       $('#licenseExpSave').attr('disabled',true);
      $('#licenseExpSave').html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.success') }}");
        var newdate = $("#newDate2").val();
        var orderId = $("#order2").val();
        $.ajax({
            type: "post",
            data: {'orderid': orderId , 'date': newdate},
            url: "{{url('edit-license-expiry')}}",
            success: function (response) {
              $("#licenseExpSave").attr('disabled',false);
              $("#licenseExpSave").html("Save");
                if (response.message =='success') {
                var result =  '<div class="alert alert-success alert-dismissable"><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong> '+response.update+' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                     $('#response3').html(result);
                     $('#response3').css('color', 'green');
                 setTimeout(function(){
                    window.location.reload();
                },2000);
                }
            },
            error: function(response) {
              $("#licenseExpSave").attr('disabled',false);
              $("#licenseExpSave").html("Save");
                  var myJSON = JSON.parse(response.responseText).errors;
                       var html = '<div class="alert alert-danger"><strong>{{ __('message.whoops') }} </strong>{{ __('message.something_wrong') }}<br><br><ul>';
                          for (var key in myJSON)
                          {
                              html += '<li>' + myJSON[key][0] + '</li>'
                          }
                         html += '</ul></div>';
                         $('#error1').show(); 
                         $('#response3').html(''); 
                          document.getElementById('error1').innerHTML = html;
                }
            })
        });


/*
* Update Support Expiry date 
 */

 $("#support_end").click(function(){
        var olddate = $(this).attr('support-date');
        var orderId = $(this).attr('support-id');
        $("#supportEndsModal").modal();
        $("#order3").val(orderId);
        $("#newDate3").val(olddate);
        });

 //When Submit Button is Clicked in Modal Popup, passvalue through Ajax
    $("#supportExpSave").on('click',function(){
       $('#supportExpSave').attr('disabled',true);
       $('#supportExpSave').html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
        var newdate = $("#newDate3").val();
        var orderId = $("#order3").val();
        $.ajax({
            type: "post",
            data: {'orderid': orderId , 'date': newdate},
            url: "{{url('edit-support-expiry')}}",
            success: function (response) {
               $("#supportExpSave").attr('disabled',false);
              $("#supportExpSave").html("Save");
                if (response.message =='success') {
                var result =  '<div class="alert alert-success alert-dismissable"><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong> '+response.update+' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                     $('#response4').html(result);
                     $('#response4').css('color', 'green');
                 setTimeout(function(){
                    window.location.reload();
                },2000);
                }
            },
            error: function(response) {
               $("#supportExpSave").attr('disabled',false);
              $("#supportExpSave").html("Save");
                  var myJSON = JSON.parse(response.responseText).errors;
                       var html = '<div class="alert alert-danger"><strong>{{ __('message.whoops') }}! </strong>{{ __('message.something_wrong') }}<br><br><ul>';
                          for (var key in myJSON)
                          {
                              html += '<li>' + myJSON[key][0] + '</li>'
                          }
                         html += '</ul></div>';
                         $('#error3').show(); 
                         $('#response4').html(''); 
                          document.getElementById('error3').innerHTML = html;
                }
            })
        });


/*
* Update Support Expiry date 
 */

 $("#installlimit").click(function(){
        var oldlimit = $(this).attr('install-limit');
        var orderId = $(this).attr('limit-id');
        $("#limitModel").modal();
        $("#order5").val(orderId);
        $("#limitnumber").val(oldlimit);
        });

 //When Submit Button is Clicked in Modal Popup, passvalue through Ajax
    $("#installLimitSave").on('click',function(){
        var newlimit = $("#limitnumber").val();
        var orderId = $("#order5").val();
        $.ajax({
            type: "post",
            data: {'orderid': orderId , 'limit': newlimit},
            url: "{{url('edit-installation-limit')}}",
             beforeSend: function () {
                 $('#response5').html( '<div class="overlay"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>');

            },
            success: function (response) {
                if (response.message =='success') {
                var result =  '<div class="alert alert-success alert-dismissable"><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong> '+response.update+' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>';
                     $('#response5').html(result);
                     $('#response5').css('color', 'green');
                 setTimeout(function(){
                    window.location.reload();
                },3000);
                }
            },
            error: function(response) {
                  var myJSON = JSON.parse(response.responseText).errors;
                       var html = '<div class="alert alert-danger"><strong>{{ __('message.whoops') }}! </strong>{{ __('message.something_wrong') }}<br><br><ul>';
                          for (var key in myJSON)
                          {
                              html += '<li>' + myJSON[key][0] + '</li>'
                          }
                         html += '</ul></div>';
                         $('#error5').show(); 
                         $('#response5').html(''); 
                          document.getElementById('error5').innerHTML = html;
                }
            })
        });


   $("#installLimitClose").on('click',function(){
       window.location.reload();
   });
</script>

<script>

  $(function() {
    $('.localized-slider').change(function() {
        var choose = $(this).prop('checked') == true ? 1 : 0; 
        var orderNo = $(this).data('id'); 
         
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "{{url('choose')}}",
            data: {'choose': choose, 'orderNo': orderNo},
            success: function(data){  
            $('#response').html(data);
            location.reload();             
            }
        });
    })
  })


   function saveStatus($id)
          {
              // $("#submitSub").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
              var status = ($('#is_subscribed').prop("checked"));
              var id = $id;

            $.ajax({

                url : '{{url("post-status")}}',
                type : 'post',
                data: {
                    data: { "is_subscribed" : status, "order_id" : id },
                   },
                success: function (response) {
                    $('#alertMessage').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setInterval(function(){
                        $('#alertMessage').slideUp(3000);
                    }, 1000);
                },


            });

          };
</script>

@stop
