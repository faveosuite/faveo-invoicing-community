<html>
    <head>
        <meta charset="UTF-8">
        <title>{{Lang::get('message.faveo-billing-application')}}</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <!-- Bootstrap 3.3.4 -->
        <!--<link href="{{asset('bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />-->
        <!-- Font Awesome Icons -->
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <!--<link href="{{asset('dist/css/AdminLTE.min.css')}}" rel="stylesheet" type="text/css" />-->

        <!-- Custom style -->
        <!--<link rel="stylesheet" href="{{asset('dist/css/custom.css')}}">-->

        <!-- AdminLTE Skins. Choose a skin from the css/skins 
             folder instead of downloading all of them to reduce the load. -->
        <!--<link href="{{asset('dist/css/skins/_all-skins.min.css')}}" rel="stylesheet" type="text/css" />-->

        <!--<link href="{!!asset('plugins/datatables/dataTables.bootstrap.css')!!}" rel="stylesheet" type="text/css" />-->

        <!--<link href="{!!asset('dist/css/bill.css')!!}" rel="stylesheet" type="text/css" />-->

    </head>
    <!-- ADD THE CLASS fixed TO GET A FIXED HEADER AND SIDEBAR LAYOUT -->
    <!-- the fixed layout is not compatible with sidebar-mini -->
    <body class="skin-white fixed">
        <!-- Site wrapper -->
        <div class="wrapper">
            <!--<div class="content-wrapper">-->


            <!-- Main content -->
            <section class="content">
                <div class="box box-primary">

                    <div class="box-header">

                        <h4>{{Lang::get("message.invoice")}}
                            <!--<a href="{{url('orders/create')}}" class="btn btn-primary pull-right   ">{{Lang::get('message.create')}}</a></h4>-->
                    </div>
                    <div id="response"></div>

                    <div class="box-body">
                        <div class="row">

                            <div class="col-md-12">

                                <?php $set = App\Model\Common\Setting::where("id", "1")->first(); ?>
                                <!-- Main content -->
                                <section class="invoice">
                                    <!-- title row -->
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <h2 class="page-header">
                                                {{ucfirst($set->company)}}
                                                <small class="pull-right">{{ __('message.date') }}: {{$invoice->created_at}}</small>
                                            </h2>
                                        </div><!-- /.col -->
                                    </div>
                                    <!-- info row -->
                                    <div class="row invoice-info">
                                        <div class="col-sm-4 invoice-col">
                                            {{ __('message.from') }}
                                            <address>

                                                <strong>{{$set->company}}</strong><br>
                                                {{$set->address}}<br>
                                                {{ __('message.phone') }}: {{$set->phone}}<br/>
                                                {{ __('message.email') }}: {{$set->email}}
                                            </address>
                                        </div><!-- /.col -->
                                        <div class="col-sm-4 invoice-col">
                                            {{ __('message.to') }}
                                            <address>
                                                <strong>{{$user->first_name}} {{$user->last_name}}</strong><br>
                                                {{$user->address}}<br/>
                                                {{$user->town}}<br/>
                                                {{$user->state}} {{$user->zip}}<br/>
                                                {{ __('message.country') }} : {{$user->country}}<br/>
                                                {{ __('message.mobile') }}: @if($user->mobile_code)<b>+</b>{{$user->mobile_code}}@endif{{$user->mobile}}<br/>
                                                {{ __('message.email') }} : {{$user->email}}
                                            </address>
                                        </div><!-- /.col -->
                                        <div class="col-sm-4 invoice-col">
                                            <b>{{ __('message.invoice') }}   #{{$invoice->number}}</b><br/>
                                            <br/>

                                        </div><!-- /.col -->
                                    </div><!-- /.row -->

                                    <!-- Table row -->
                                    <div class="row">
                                        <div class="col-xs-12 table-responsive">
                                            <table class="table table-responsive">

                                                <tr>
                                                
                                                    <td>{{ __('message.product') }}</td>
                                                    <td>{{ __('message.quantity') }}</td>
                                                    <td>{{ __('message.price') }}</td>
                                                    <td>{{ __('message.taxes') }}</td>
                                                    <td>{{ __('message.tax_rates') }}</td>
                                                    <td>{{ __('message.sub_total') }}</td>
                                                </tr>

                                                @forelse($invoiceItems as $item)
                                                <tr>
                                                    <td>{{$item->product_name}}</td>
                                                    <td>{{$item->quantity}}</td>
                                                    <td>{{$item->regular_price}}</td>
                                                    <td>
                                                        <?php $taxes = explode(",", $item->tax_name); ?>
                                                        <ul class="list-unstyled">
                                                            @forelse($taxes as $tax)
                                                            <li>{{$tax}}</li>
                                                            @empty 
                                                            <li>No Tax</li>
                                                            @endif
                                                        </ul>
                                                    </td>
                                                    <td>
                                                        <?php $taxes = explode(",", $item->tax_percentage); ?>
                                                        <ul class="list-unstyled">
                                                            @forelse($taxes as $tax)
                                                            <li>{{$tax}}</li>
                                                            @empty 
                                                            <li>{{ __('message.no_tax_rates') }}</li>
                                                            @endif
                                                        </ul>
                                                    </td>
                                                    <td>{{$item->subtotal}}</td>
                                                </tr>
                                                @empty 
                                                <tr><td>Null</td></tr>
                                                @endforelse

                                            </table>
                                        </div><!-- /.col -->
                                    </div><!-- /.row -->

                                    <div class="row">

                                        <div class="col-xs-12">
                                            <p class="lead">{{ __('message.amount') }}</p>
                                            <div class="table-responsive">
                                                <table class="table">
                                                     @if($invoice->discount)
                                                    <tr>
                                                          
                                                    <th>{{ __('message.discount') }}</th>
                                                    <td>{{currencyFormat($invoice->discount,$code=$symbol)}}</td>
                                                    </tr>
                                                     @endif
                                                    <?php
                                                    $tax_name = [];
                                                    $tax_percentage = [];
                                                    if ($invoiceItems->count() > 0) {
                                                        foreach ($invoiceItems as $key => $item) {
                                                            if (str_finish(",", $item->tax_name)) {
                                                                $name = substr_replace($item->tax_name, "", -1);
                                                            }
                                                            if (str_finish(",", $item->tax_percentage)) {
                                                                $rate = substr_replace($item->tax_percentage, "", -1);
                                                            }
                                                            $tax_name = explode(",", $name);
                                                            $tax_percentage = explode(",", $rate);
                                                        }
                                                    }
                                                    ?>
                                                    @if(count($tax_name)>0)
                                                    @for($i=0;$i < count($tax_name);$i++)
                                                    @if(key_exists($i,$tax_name))
                                                    @if($tax_name[$i]!='null')
                                                    <tr>
                                                        <th style="width:50%">
                                                            <strong>{{ucfirst($tax_name[$i])}}<span>@</span>{{$tax_percentage[$i]}}%</strong>
                                                        </th>
                                                        <td>
                                                            <small>{!! $invoice->currency !!}</small>&nbsp;{{App\Http\Controllers\Front\CartController::taxValue($tax_percentage[$i],$invoice->grand_total)}}
                                                        </td>

                                                    </tr>
                                                    @endif
                                                    @endif
                                                    @endfor
                                                    @endif
                                                    <tr>
                                                        <th style="width:50%">{{ __('message.total') }}:</th>
                                                        <td><small>{!! $invoice->currency !!}</small>&nbsp;{{$invoice->grand_total}}</td>
                                                    </tr>

                                                </table>
                                            </div>
                                        </div><!-- /.col -->
                                    </div><!-- /.row -->


                                </section><!-- /.content -->


                            </div>
                        </div>
                    </div>
                </div>
            </section><!-- /.content -->
        </div><!-- /.content-wrapper -->
        <!--</div>-->
    </body>
</html>