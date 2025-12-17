@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.invoice') }}
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
        address li {
            list-style: none;
            padding: 0;
            margin: 0;
        }

    </style>
    <div class="invoice p-3 mb-3">
        <!-- title row -->
        <div class="row">
            <div class="col-12">
                @if($set->logo)
                    <img alt="Logo" src="{{ $set->logo }}" width="150" height="100">
                @endif

                <div class="float-right">
                    <div>
                        <strong>{{ __('message.date') }}:</strong> {!! $date !!}
                    </div>
                    <div>
                        <strong>{{ __('message.invoice') }}:</strong> #{!! $invoice->number !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- info row -->
        <div class="row invoice-info mt-4 mb-4">
            <div class="col-sm-6 invoice-col">
                From
                <address>
                    <li><strong>{{ $set->company }}</strong></li>
                    <li>{{ $set->address }}</li>
                    <li>{{ $set->city }}</li>
                    @php
                        $stateData = getStateByCode($set->country, $set->state);
                    @endphp
                    @if(isset($stateData['name']) && $stateData['name'])
                        <li>{{ $stateData['name'] }} {{ $set->zip }}</li>
                    @else
                        <li>{{ $set->zip }}</li>
                    @endif
                    <li>{{ getCountryByCode($set->country) }}</li>
                    <li><strong>{{ __('message.mobile') }}:</strong> +{{ $set->phone_code }} {{ $set->phone }}</li>
                    <li><strong>{{ __('message.email') }}:</strong> {{ $set->company_email }}</li>

                    @if($set->gstin)
                        <li class="mt-2"><b>GSTIN:</b> {{ $set->gstin }}</li>
                    @endif
                    @if($set->cin_no)
                        <li><b>CIN:</b> {{ $set->cin_no }}</li>
                    @endif
                </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-6 invoice-col">
                To
                <address>
                    <li><strong>{{ $user->first_name }} {{ $user->last_name }}</strong></li>

                    <li>{{ $user->address }}</li>
                    <li>{{ $user->town }}</li>
                    @php
                        $state = getStateByCode($user->country, $user->state);
                    @endphp

                    @if(!empty($state['name']))
                        <li>{{ $state['name'] }} {{ $user->zip }}</li>
                    @else
                        <li>{{ $user->zip }}</li>
                    @endif
                    <li>{{ getCountryByCode($user->country) }}</li>

                    <li><strong>{{ __('message.mobile') }}:</strong>
                        +{{ $user->mobile_code }} {{ $user->mobile }}
                    </li>

                    <li><strong>{{ __('message.email') }}:</strong> {{ $user->email }}</li>

                    @if($user->gstin)
                        <li class="mt-2"><b>GSTIN:</b> {{ $user->gstin }}</li>
                    @endif
                </address>
            </div>
        </div>
        <!-- /.row -->

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
                    @foreach($items as $item)
                        <tr>
                            <td>{!! $item->order?->getOrderLink($item->order->id) ?? '--' !!}</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ currencyFormat($item->regular_price, $symbol) }}</td>
                            <td>{{ $item->agents ?? 'Unlimited' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ currencyFormat($item->subtotal, $symbol) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->

        <div class="row">
            <div class="col-6"></div>
            <!-- /.col -->
            <div class="col-6">

                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                        <tr>
                            <th>{{ __('message.sub_total') }}</th>
                            <td>{{ currencyFormat($itemsSubtotal, $symbol) }}</td>
                        </tr>

                        @if($invoice->credits)
                            <tr>
                                <th>{{ __('message.discount') }}</th>
                                <td>
                                    {{ currencyFormat($invoice->credits, $symbol) }} (Credits)
                                </td>
                            </tr>
                        @endif

                        @if($invoice->coupon_code && $invoice->discount)
                            <tr>
                                <th>{{ __('message.discount') }}</th>
                                <td>
                                    {{ currencyFormat($invoice->discount, $symbol) }} ({{ $invoice->coupon_code }})
                                </td>
                            </tr>
                        @endif


                        <!-- GST / IGST SPLIT -->
                        @foreach($gstSplit as $taxRow)
                            @foreach($taxRow['labels'] as $i => $label)
                                <tr>
                                    <th>{{ $label }}</th>
                                    <td>{{ $taxRow['values'][$i] }}</td>
                                </tr>
                            @endforeach
                        @endforeach


                        <!-- PROCESSING FEE -->
                        @if($processingFeeAmount > 0)
                            <tr>
                                <th>{{ __('message.processing_fee') }}
                                    ({{ $invoice->processing_fee }})
                                </th>
                                <td>
                                    {{ currencyFormat($processingFeeAmount, $symbol) }}
                                </td>
                            </tr>
                        @endif


                        <!-- TOTAL -->
                        <tr class="h6">
                            <th>{{ __('message.total') }}</th>
                            <td>
                                {{ currencyFormat($invoice->grand_total, $symbol) }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->

        <!-- this row will not appear when printing -->
        <div class="row no-print">
            <div class="col-12">
                <a href="{{url('pdf?invoiceid='.$invoice->id)}}">
                <button type="button" class="btn btn-primary float-right" style="margin-right: 5px;">
                    <i class="fas fa-download"></i> {{ __('message.generate_pdf') }}
                </button>
                </a>
            </div>
        </div>
    </div>
@stop