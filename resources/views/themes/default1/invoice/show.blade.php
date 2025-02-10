@extends('themes.default1.layouts.master')
@section('title')
Invoice
@stop

@section('content-header')
    <div class="col-sm-6">
        <h1>{{ __('message.view_invoice') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('clients')}}"><i class="fa fa-dashboard"></i> {{ __('message.all-users') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('invoices')}}"><i class="fa fa-dashboard"></i> {{ __('message.all-invoices') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.view_invoice') }}</li>
        </ol>
    </div><!-- /.col -->


@stop

@section('content')

<style>
    .moveright{
        position: relative;
        right: 50px;
    }
    .table td, .table th{
        border-top: 0.5px solid #dee2e6 !important;
    }

</style>
    <div class="invoice" style="width: 100%;overflow: hidden;">
        <div class="container-fluid">



        <div class="row">

            <div class="col-12">

                <?php $set = App\Model\Common\Setting::where('id', '1')->first(); 
                $date = getDateHtml($invoice->date);
                 $symbol = $invoice->currency;
                $itemsSubtotal = 0;
                ?>

                    <!-- title row -->

                    <div class="row">
                        <div class="col-12">
                            <h4>
                                @if($set->logo)
                                    <img alt="Logo" width="100" height="50" src="{{ $set->logo }}" style="margin-top: -2px">
                                    @else
                                    {{ucfirst($set->company)}}
                                @endif

                                <small class="float-right">{{ __('message.date') }}: {!! $date !!}</small><br>
                                <small class="float-right">{{ __('message.invoice') }}: #{{$invoice->number}}</small>
                                  <!--<b>Invoice   #{{$invoice->number}}</b>-->
                            </h4>
                        </div><!-- /.col -->
                    </div>

                    <!-- info row -->
                    <div class="row invoice-info">
                        <div class="col-sm-4 invoice-col">
                            {{ __('message.from') }}
                            <address>

                                <strong>{{$set->company}}</strong><br>
                                {{$set->address}}<br>
                                {{$set->city}}<br/>
                                @if(key_exists('name',getStateByCode($set->state)))
                                {{getStateByCode($set->state)['name']}}
                                @endif
                                {{$set->zip}}<br/>
                                {{ __('message.country') }}: {{getCountryByCode($set->country)}}<br/>
                                {{ __('message.mobile') }}: <b>+</b>{{$set->phone_code}} {{$set->phone}}<br/>
                                {{ __('message.email') }}: {{$set->company_email}}
                            </address>
                             @if($set->gstin)
                            <b>GSTIN:</b>  &nbsp; #{{$set->gstin}}
                            <br>
                            @endif

                            @if($set->cin_no)
                            <b>CIN:</b>  &nbsp; #{{$set->cin_no}}
                            <br>
                            @endif<br>

                        </div><!-- /.col -->
                        <div class="col-sm-4 invoice-col">
                            {{ __('message.to') }}
                            <address>
                                <strong>{{$user->first_name}} {{$user->last_name}}</strong><br>
                                {{$user->address}}<br/>
                                {{$user->town}}<br/>
                                @if(key_exists('name',getStateByCode($user->state)))
                                {{getStateByCode($user->state)['name']}}
                                @endif
                                {{$user->zip}}<br/>
                                {{ __('message.country') }}: {{getCountryByCode($user->country)}}<br/>

                                {{ __('message.mobile') }}: @if($user->mobile_code)<b>+</b>{{$user->mobile_code}} @endif{{$user->mobile}}<br/>
                                {{ __('message.email') }}: {{$user->email}}
                            </address>
                             @if($user->gstin)
                            <b>GSTIN:</b>  &nbsp; #{{$user->gstin}}
                            <br>
                            @endif
                        </div><!-- /.col -->
                    </div><!-- /.row -->

                    <!-- Table row -->
                    <div class="row">
                        <div class="col-12 table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('message.order_no') }}</th>
                                        <th>{{ __('message.product') }}</th>
                                        <th>{{ __('message.price') }}</th>
                                        <th>{{ __('message.agents') }}</th>
                                        <th>{{ __('message.quantity') }}</th>
                                        <th>{{ __('message.sub_total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach($invoiceItems as $item)
                                    <tr>
                                        @php
                                        $orderForThisItem = $item->order()->first();
                                        $itemsSubtotal += $item->subtotal;
                                        @endphp
                                        @if($orderForThisItem)

                                        <td > {!! $orderForThisItem->getOrderLink($orderForThisItem->id) !!}</td>
                                        
                                            @elseif($order != '--')

                                            <td>{!! $order !!}</td>
                                            <span>{{ __('message.renewed') }}</span>
                                            @else
                                            <td>--</td>
                                           
                                        @endif
                                        @php
                                            $period_id =\DB::table('plans_periods_relation')->where('plan_id',$item->plan_id)->latest()->value('period_id');
                                            $plan = \DB::table('periods')->where('id',$period_id)->latest()->value('name');

                                        @endphp
                                        <td>{{$item->product_name}}
                                            {{$plan}}</td>
                                         <td>{{currencyFormat($item->regular_price,$code=$symbol)}}</td>
                                         <td>{{($item->agents)?$item->agents:'Unlimited'}}</td>
                                        <td>{{$item->quantity}}</td>
                                       
                                       <td> {{currencyFormat($item->subtotal,$code=$symbol)}}</td>
                                    </tr>
                                @endforeach
                              <tr style="border-bottom: 0.5px solid #ccc;"></tr>
                
                                </tbody>
                            </table>
                        </div><!-- /.col -->
                    </div><!-- /.row -->

                    <div class="row">
                        <!-- accepted payments column -->
                        <div class="col-6">

                        </div>
                        <div class="col-6" style="left: 2.5%;">
                            <div class="table-responsive">
                              
                                       
                                 <table class="table">
                                     <tr>
                                         <th style="border-top: unset !important;">{{ __('message.sub_total') }}</th>
                                         <td class="moveright" style="border-top: unset !important;">{{currencyFormat($itemsSubtotal,$code=$symbol)}}</td>
                                     </tr>
                                     @if($invoice->credits)
                                         <tr>
                                             <th>{{ __('message.discount') }}</th>
                                             <td class="moveright">{{currencyFormat($invoice->credits,$code=$symbol)}} (Credits)</td>
                                         </tr>
                                     @endif
                                      @if($invoice->coupon_code && $invoice->discount)
                                  <th>{{ __('message.discount') }}</th>
                                    <td class="moveright">{{currencyFormat($invoice->discount,$code=$symbol)}} ({{$invoice->coupon_code}})</td>
                                @endif

                                 <?php
                                    $order = \App\Model\Order\Order::where('invoice_item_id',$item->id)->first();
                                    if($order != null) {
                                        $productId = $order->product;
                                    } else {
                                        $productId =  App\Model\Product\Product::where('name',$item->product_name)->pluck('id')->first();
                                    }
                                    
                                    ?>
                                     @if ($item->tax_name != 'null')
                                            
                                       
                                            <tr>
                                                 <?php
                                                $bifurcateTax = bifurcateTax($item->tax_name,$item->tax_percentage,$user->currency, $user->state, $item->subtotal);
                                                ?>
                                                 @foreach(explode('<br>', $bifurcateTax['html']) as $index => $part)
                                    <tr>
                                        <th>
                                            <strong>
                                                <?php
                                                $parts = explode('@', $part);
                                                $cgst = $parts[0];
                                                $percentage = $parts[1];
                                                ?>
                                                <span class="font-weight-bold text-color-grey" style="color: grey;">{{ $cgst }}</span>
                                                <span style="font-weight: normal;color: grey;">({{ $percentage }})</span><br>
                                            </strong>
                                        </th>
                                        <td class="text-color-grey moveright">
                                            <?php
                                            $taxParts = explode('<br>', $bifurcateTax['tax']);
                                            echo $taxParts[$index]; // Output tax amount corresponding to current index
                                            ?>
                                        </td>
                                    </tr>
                                    @endforeach
                                            </tr>
                                                   <?php
                                    $feeAmount = intval(ceil($invoice->grand_total * 0.99 / 100));
                                    ?>


                                @if($invoice->processing_fee != null && $invoice->processing_fee != '0%')
                                <tr>
                                    <th style="font-weight: bold;color: grey;">{{ __('message.processing_fee') }} <label style="font-weight: normal;">({{$invoice->processing_fee}})</label></th>
                                    <td class="text-color-grey moveright">{{currencyFormat($feeAmount,$code = $symbol)}}</td>
                                </tr>
                                @endif
                                     
                                       
                                    @endif
                                    <th>{{ __('message.total') }}</th>
                                    <td class="moveright" style="font-weight: bold;">{{currencyFormat($invoice->grand_total,$code=$symbol)}}</td>
                               
                            </table>
                            </div>
                        </div><!-- /.col -->
                    </div><!-- /.row -->

                    <!-- this row will not appear when printing -->
                    <div class="row no-print">
                        <div class="col-6"></div>
                        <div class="col-6" style="left: 3%;">
                            <a href="{{url('pdf?invoiceid='.$invoice->id)}}"><button class="btn btn-primary pull-right" style="margin-right: 5px;"><i class="fa fa-download"></i> {{ __('message.generate_pdf') }}</button></a>
                        </div>
                    </div>


            </div>
        </div>

</div>
    </div>
    <script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'all_invoice';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'all_invoice';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
<script>
    $(document).ready(function(){
         $(function () {
          $('[data-toggle="tooltip"]').tooltip({
            container : 'body'
          });
        });
    })
</script>

@stop