@extends('themes.default1.layouts.front.master')

@section('title', __('message.invoice'))

@section('page-heading')
    {{ __('message.view_invoice') }}
@stop

@section('breadcrumb')
    @if(Auth::check())
        <li><a class="text-primary" href="{{ url('my-invoices') }}">{{ __('message.home') }}</a></li>
    @else
        <li><a class="text-primary" href="{{ url('login') }}">{{ __('message.home') }}</a></li>
    @endif
    <li class="active text-dark">{{ __('message.view_invoice') }}</li>
@stop

@section('content')

    <style type="text/css">
        .text-fail{
            color: red;
        }
        .text-warning{
            color: yellow;
        }
        .invoice-table{
            border: none;
        }
        .table th{
            border-top: unset !important;
        }
        .moveleft{
            position: relative;
            left: 35px;
        }
        .table tr{
            line-height: 25px;
        }
    </style>


    <div  id="examples"  class="container py-4" style="max-width:900px">

        <!-- HEADER -->
        <div class="row">

            <div class="col-lg-6 col-sm-12">
                @if($set->logo)
                    <img alt="Logo" src="{{ $set->logo }}" width="150" height="100">
                @endif
                <h2 class="font-weight-normal text-7 mb-0">
                    {{ __('message.invoice') }} <span class="text-0 text-color-grey">#{{ $invoice->number }}</span>
                </h2>
            </div>

            <div class="col-lg-6 col-sm-12 text-end">

                <h4 class="mb-1">{{ __('message.date') }} {!! $date !!}</h4>

                <h2 class="{{ $statusClass }}">
                    <strong  class="font-weight-extra-bold">{{ $statusText }}</strong>
                </h2>
            </div>

        </div>


        <!-- FROM / TO -->
        <div class="row pt-3">

            <!-- FROM -->
            <div class="col-lg-6">
                <h2 class="text-dark font-weight-bold text-4 mb-1">{{ __('message.from') }}</h2>

                <ul class="list-unstyled text-2 mb-0">
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
                </ul>
            </div>

            <!-- TO -->
            <div class="col-lg-6">
                <h2 class="text-dark font-weight-bold text-4 mb-1">{{ __('message.to') }}</h2>

                <ul class="list-unstyled text-2 mb-0">
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
                </ul>
            </div>

        </div>


        <!-- ITEMS TABLE -->
        <div class="card p-3 mt-3">

            <div class="table-responsive">

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
                            <td>{!! $item->order?->getOrderLink($item->order->id,'my-order') ?? '--' !!}</td>
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


            <!-- SUMMARY -->
            <div class="row">
                <div class="col-sm-12 col-lg-6"></div>
                <div class="col-sm-12 col-lg-6 text-lg-end">
                    <div class="table-responsive">
                        <table class="table h6 text-dark">
                            <tbody>
                            <tr>
                                <th class="text-start">{{ __('message.sub_total') }}</th>
                                <td class="text-end">{{ currencyFormat($itemsSubtotal, $symbol) }}</td>
                            </tr>

                            @if($invoice->credits)
                                <tr>
                                    <th class="text-start">{{ __('message.discount') }}</th>
                                    <td class="text-end">
                                        {{ currencyFormat($invoice->credits, $symbol) }} (Credits)
                                    </td>
                                </tr>
                            @endif

                            @if($invoice->coupon_code && $invoice->discount)
                                <tr>
                                    <th class="text-start">{{ __('message.discount') }}</th>
                                    <td class="text-end">
                                        {{ currencyFormat($invoice->discount, $symbol) }} ({{ $invoice->coupon_code }})
                                    </td>
                                </tr>
                            @endif


                            <!-- GST / IGST SPLIT -->
                            @foreach($gstSplit as $taxRow)
                                @foreach($taxRow['labels'] as $i => $label)
                                    <tr>
                                        <th class="text-start font-weight-bold text-color-grey">{{ $label }}</th>
                                        <td class="text-end text-color-grey">{{ $taxRow['values'][$i] }}</td>
                                    </tr>
                                @endforeach
                            @endforeach


                            <!-- PROCESSING FEE -->
                            @if($processingFeeAmount > 0)
                                <tr>
                                    <th class="text-start font-weight-bold text-color-grey">{{ __('message.processing_fee') }}
                                        ({{ $invoice->processing_fee }})
                                    </th>
                                    <td class="text-color-grey text-end">
                                        {{ currencyFormat($processingFeeAmount, $symbol) }}
                                    </td>
                                </tr>
                            @endif


                            <!-- TOTAL -->
                            <tr class="h6">
                                <th class="border-0 text-start">{{ __('message.total') }}</th>
                                <td class="border-0 text-end">
                                    {{ currencyFormat($invoice->grand_total, $symbol) }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAYMENTS -->
        @if(!$payments->isEmpty())
            <div class="card p-3 mt-3">
                <div class="table-responsive">

                <table class="table">

                    <thead>
                    <tr>
                        <th>{{ __('message.transaction_date') }}</th>
                        <th>{{ __('message.method') }}</th>
                        <th>{{ __('message.total') }}</th>
                        <th>{{ __('message.status') }}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($payments as $payment)

                        <tr>
                            <td>{!! getDateHtml($payment->created_at) !!}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>{{ currencyFormat($payment->amount, $symbol) }}</td>
                            <td>
                                @if($payment->payment_status == 'success')
                                    <span class="badge badge-success badge-xs">{{ $payment->payment_status }}</span>
                                @else
                                    <span class="badge badge-success badge-xs">{{ $payment->payment_status }}</span>
                                @endif
                            </td>
                        </tr>

                    @endforeach
                    </tbody>

                </table>
                </div>

            </div>
        @endif

        <!-- BUTTONS -->
        <div class="mt-4">
            <button id="invoice-pdf" onclick="downloadPdf({{ $invoice->id }})"
                    data-loading-text="{{ __('message.generating_pdf')}}"
                    data-original-text="{{ __('message.generate_pdf')}}"
                    class="btn btn-dark float-end ms-2">
                <i class="fa fa-download"></i> {{ __('message.generate_pdf')}}
            </button>

            @if($invoice->status != 'Success')
                <a href="{{ url('paynow/'.$invoice->id) }}"
                   target="_blank" class="btn btn-primary float-end ms-2">
                    <i class="fa fa-credit-card"></i> {{ __('message.pay_now') }}
                </a>
            @endif
        </div>

    </div>

    <script>
        function downloadPdf(invoiceId) {
            $btn = $("#invoice-pdf");
            $.ajax({
                url: "{{ url('pdf') }}",
                type: "GET",
                data: {invoiceid: invoiceId},
                xhrFields: {
                    responseType: 'blob'
                },
                beforeSend: function () {
                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>  ' + $btn.data('loading-text'));
                },
                success: function (response) {
                    var blob = new Blob([response], {type: "application/pdf"});
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement("a");
                    a.href = url;
                    a.download = `invoice_${invoiceId}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                },
                error: function (xhr, status, error) {
                    console.error("Download failed:", error);
                    alert(@json( __('message.failed_generate_pdf')));
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-download"></i>  ' + $btn.data('original-text'));
                }
            });
        }
    </script>
@endsection