<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('message.pdf_invoice') }}</title>
    <link rel="stylesheet" href="{{ asset('admin/css/bootstrap.min.css')}}">


    <style>
        body { font-family: DejaVu Sans; color:#000; margin:0; padding:0; }
        .container { width: 100%; padding: 20px; }

        /* Header table */
        .header-table { width:100%; border-collapse: collapse; margin-bottom:20px; }
        .header-table td { vertical-align:top; }

        /* Info columns */
        .info-table { width:100%; border-collapse: collapse; margin-bottom:20px; }
        .info-col { width:50%; vertical-align:top; font-size:12px; }

        /* Items */
        .items-table { width:100%; border-collapse: collapse; font-size:13px; margin-bottom:20px; }
        .items-table th {
            border-bottom:2px solid #000;
            padding:8px;
            text-align:left;
        }
        .items-table td {
            border-bottom:1px solid #000;
            padding:8px;
        }

        .right { text-align:right; }
        .center { text-align:center; }

        /* Summary */
        .summary-table { width:50%; float:right; border-collapse: collapse; font-size:13px; }
        .summary-table td, .summary-table th {
            padding:6px;
        }
        .total-row th, .total-row td {
            border-top: 1px solid #ccc !important; /* light gray */
            font-weight: bold;
        }

        footer {
            clear:both;
            border-top:1px solid #000;
            text-align:center;
            margin-top:30px;
            padding-top:10px;
            font-size:12px;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td>
                @if(!empty($set->logo))
                    <img alt="Logo" src="{{ $set->logo }}" width="100" height="50">
                @else
                    <strong style="font-size:18px">{{ ucfirst($set->company) }}</strong>
                @endif
            </td>

            <td class="right" style="font-size:12px;">
                <div><strong>{{ __('message.date') }}:</strong> {!! $date !!}</div>
{{--                <div><strong>{{ __('message.invoice') }}:</strong> #{{ $invoice->number }}</div>--}}
            </td>
        </tr>
    </table>

    <!-- FROM / TO -->
    <table class="info-table">
        <tr>
            <td class="info-col">
                <strong>{{ __('message.from') }}</strong><br>
                {{ $set->company }}<br>
                @if($set->address) {{ $set->address }}<br>@endif
                @if($set->city) {{ $set->city }}<br>@endif
                {{ getStateByCode($set->country,$set->state)['name'] ?? '' }} {{ $set->zip }}<br>
                <strong>{{ __('message.country') }} :</strong> {{ getCountryByCode($set->country) }}<br>
                <strong>{{ __('message.mobile') }} :</strong> +{{ $set->phone_code }} {{ $set->phone }}<br>
                <strong>{{ __('message.email') }} :</strong> {{ $set->company_email }}<br>

                @if($set->gstin) <strong>GSTIN :</strong> {{ $set->gstin }}<br>@endif
                @if($set->cin_no) <strong>CIN :</strong> {{ $set->cin_no }}<br>@endif
            </td>

            <td class="info-col">
                <strong>{{ __('message.to') }}</strong><br>
                {{ $user->first_name }} {{ $user->last_name }}<br>
                @if($user->address) {{ $user->address }}<br>@endif
                @if($user->town) {{ $user->town }}<br>@endif
                {{ getStateByCode($user->country,$user->state)['name'] ?? '' }} {{ $user->zip }}<br>
                <strong>{{ __('message.country') }} :</strong> {{ getCountryByCode($user->country) }}<br>
                <strong>{{ __('message.mobile') }} :</strong>
                @if($user->mobile_code)+{{ $user->mobile_code }} @endif{{ $user->mobile }}<br>
                <strong>{{ __('message.email') }} :</strong> {{ $user->email }}<br>

                @if($user->gstin) <strong>GSTIN :</strong> {{ $user->gstin }}<br>@endif
            </td>
        </tr>
    </table>

    <!-- ITEMS -->
    <table class="table table-striped table-hover items-table">
        <thead class="thead-dark">
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
                <td>{!! $order ?? '--' !!}</td>
                <td>{{ $item->product_name }}</td>
                <td>{{ currencyFormat($item->regular_price,$symbol) }}</td>
                <td>{{ $item->agents ?: 'Unlimited' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ currencyFormat($item->subtotal,$symbol) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>


    <!-- SUMMARY -->
{{--    <table class="summary-table">--}}
{{--        <tr>--}}
{{--            <th>{{ __('message.sub_total') }}</th>--}}
{{--            <td class="right">{{ currencyFormat($itemsSubtotal, $symbol) }}</td>--}}
{{--        </tr>--}}

{{--        @if($invoice->credits)--}}
{{--            <tr>--}}
{{--                <th>{{ __('message.discount') }}</th>--}}
{{--                <td class="right">{{ currencyFormat($invoice->credits,$symbol) }}</td>--}}
{{--            </tr>--}}
{{--        @endif--}}

{{--        @if($invoice->discount)--}}
{{--            <tr>--}}
{{--                <th>{{ __('message.discount') }}</th>--}}
{{--                <td class="right">{{ currencyFormat($invoice->discount,$symbol) }} ({{ $invoice->coupon_code }})</td>--}}
{{--            </tr>--}}
{{--        @endif--}}

{{--        @foreach($gstSplit as $tax)--}}
{{--            @foreach($tax['labels'] as $i => $label)--}}
{{--                <tr>--}}
{{--                    <th>{{ $label }}</th>--}}
{{--                    <td class="right">{{ $tax['values'][$i] }}</td>--}}
{{--                </tr>--}}
{{--            @endforeach--}}
{{--        @endforeach--}}

{{--        @if($processingFeeAmount > 0)--}}
{{--            <tr>--}}
{{--                <th>{{ __('message.processing_fee') }}</th>--}}
{{--                <td class="right">{{ currencyFormat($processingFeeAmount,$symbol) }}</td>--}}
{{--            </tr>--}}
{{--        @endif--}}

{{--        <tr class="total-row">--}}
{{--            <th>{{ __('message.total') }}</th>--}}
{{--            <td class="right">{{ currencyFormat($invoice->grand_total,$symbol) }}</td>--}}
{{--        </tr>--}}
{{--    </table>--}}

    <!-- FOOTER -->
{{--    <footer>--}}
{{--        {{ $set->company }} | {{ $set->company_email }} | +{{ $set->phone_code }} {{ $set->phone }}--}}
{{--    </footer>--}}

</div>

</body>
</html>
