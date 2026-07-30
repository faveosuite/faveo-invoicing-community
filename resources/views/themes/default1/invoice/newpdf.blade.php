<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('message.pdf_invoice') }}</title>
    <style>
        html { -webkit-print-color-adjust: exact; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 13px;
            color: #212529;
            background: #fff;
        }

        .page { padding: 36px 40px; }

        /* ── Layout helpers ── */
        .row { display: flex; width: 100%; }
        .col-6 { width: 50%; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #6c757d; }
        .fw-bold { font-weight: 700; }
        .small { font-size: 11px; }
        .text-uppercase { text-transform: uppercase; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 24px; }
        .ms-auto { margin-left: auto; }

        /* ── Header ── */
        .logo-wrap img { max-height: 60px; max-width: 160px; width: auto; height: auto; display: block; margin-bottom: 8px; }
        .invoice-title { font-size: 15px; font-weight: 700; margin-top: 8px; }
        .invoice-number { color: #6c757d; }

        /* ── Status badge ── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-success       { background: #198754; color: #fff; }
        .badge-warning       { background: #ffc107; color: #000; }
        .badge-danger        { background: #dc3545; color: #fff; }
        .badge-info          { background: #0dcaf0; color: #000; }
        .badge-secondary     { background: #6c757d; color: #fff; }

        /* ── Divider ── */
        hr { border: none; border-top: 1px solid #dee2e6; margin: 20px 0; }

        /* ── Address ── */
        address { font-style: normal; line-height: 1.8; font-size: 12px; }
        .address-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #6c757d;
            margin-bottom: 6px;
        }

        /* ── Items table ── */
        .table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 24px; }
        .table th {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px 10px;
            font-weight: 600;
            color: #495057;
        }
        .table td {
            border: 1px solid #dee2e6;
            padding: 8px 10px;
        }
        .table tbody tr:nth-child(even) td { background: #f8f9fa; }

        /* ── Totals ── */
        .totals-table { width: 45%; margin-left: auto; border-collapse: collapse; font-size: 12px; }
        .totals-table td { padding: 10px 16px; }
        .totals-table .label { color: #6c757d; }
        .totals-table .value { text-align: right; width: 140px; }
        .totals-table .total-row td {
            border-top: 2px solid #dee2e6;
            font-weight: 700;
            font-size: 15px;
            padding-top: 10px;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ── HEADER ── --}}
    <div class="row mb-4">
        <div class="col-6">
            @if(!empty($set->logo))
                <div class="logo-wrap">@inlinedImage($set->logo)</div>
            @else
                <strong style="font-size:18px;">{{ ucfirst($set->company) }}</strong>
            @endif
{{--            <div class="invoice-title">--}}
{{--                {{ __('message.invoice') }}--}}
{{--                <span class="invoice-number">#{{ $invoice->number }}</span>--}}
{{--            </div>--}}
        </div>
        <div class="col-6 text-end">
            <p class="mb-1 text-muted small">
                {{ __('message.date') }}: <strong style="color:#212529;">{!! $date !!}</strong>
            </p>
            <p class="mb-1 text-muted small">
                {{ __('message.invoice') }}: <strong style="color:#212529;">#{{ $invoice->number }}</strong>
            </p>
            @php
                $badgeClass = match(strtolower($invoice->status ?? '')) {
                    'paid', 'success' => 'badge-success',
                    'pending'         => 'badge-warning',
                    'cancelled'       => 'badge-danger',
                    'overdue'         => 'badge-danger',
                    'partially paid'  => 'badge-info',
                    default           => 'badge-secondary',
                };
            @endphp
            <p><span class="badge {{ $badgeClass }}">{{ ucfirst($invoice->status ?? '') }}</span></p>
        </div>
    </div>

    <hr>

    {{-- ── FROM / TO ── --}}
    <div class="row mb-4">
        <div class="col-6">
            <div class="address-label">{{ __('message.from') }}</div>
            <address>
                <strong>{{ $set->company }}</strong><br>
                @if($set->address){{ $set->address }}<br>@endif
                @if($set->city){{ $set->city }}<br>@endif
                @if($set->state || $set->zip){{ getStateByCode($set->country, $set->state)['name'] ?? '' }} {{ $set->zip }}<br>@endif
                @if($set->country){{ getCountryByCode($set->country) }}<br>@endif
                @if($set->phone)+{{ $set->phone_code }} {{ $set->phone }}<br>@endif
                @if($set->company_email){{ $set->company_email }}<br>@endif
                @if($set->gstin)<span class="text-muted small">GSTIN:</span> {{ $set->gstin }}<br>@endif
                @if($set->cin_no)<span class="text-muted small">CIN:</span> {{ $set->cin_no }}<br>@endif
            </address>
        </div>
        <div class="col-6">
            <div class="address-label">{{ __('message.to') }}</div>
            <address>
                <strong>{{ $user->first_name }} {{ $user->last_name }}</strong><br>
                @if($user->address){{ $user->address }}<br>@endif
                @if($user->town){{ $user->town }}<br>@endif
                @if($user->state || $user->zip){{ getStateByCode($user->country, $user->state)['name'] ?? '' }} {{ $user->zip }}<br>@endif
                @if($user->country){{ getCountryByCode($user->country) }}<br>@endif
                @if($user->mobile)@if($user->mobile_code)+{{ $user->mobile_code }} @endif{{ $user->mobile }}<br>@endif
                @if($user->email){{ $user->email }}<br>@endif
                @if($user->gstin)<span class="text-muted small">GSTIN:</span> {{ $user->gstin }}<br>@endif
            </address>
        </div>
    </div>

    {{-- ── LINE ITEMS ── --}}
    <table class="table">
        <thead>
            <tr>
                <th class="text-center">{{ __('message.order_no') }}</th>
                <th class="text-center">{{ __('message.product') }}</th>
                <th class="text-center">{{ __('message.price') }}</th>
                <th class="text-center">{{ __('message.agents') }}</th>
                <th class="text-center">{{ __('message.quantity') }}</th>
                <th class="text-center">{{ __('message.sub_total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoiceItems as $item)
                <tr>
                    <td class="text-center">{!! $item->order_link ?? '—' !!}</td>
                    <td class="text-center">{{ $item->product_name }}</td>
                    <td class="text-center">{{ currencyFormat($item->regular_price, $symbol) }}</td>
                    <td class="text-center">{{ $item->agents ?: __('message.unlimited') }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-center">{{ currencyFormat($item->subtotal, $symbol) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── TOTALS ── --}}
    <table class="totals-table">
        <thead class="visually-hidden"><tr><th>Description</th><th>Amount</th></tr></thead>
        <tbody>
            <tr>
                <td class="label">{{ __('message.sub_total') }}</td>
                <td class="value">{{ $totals['subtotal'] }}</td>
            </tr>
            @if($invoice->credits)
                <tr>
                    <td class="label">{{ __('message.discount') }} (Credits)</td>
                    <td class="value">{{ $totals['credits'] }}</td>
                </tr>
            @endif
            @if($invoice->discount)
                <tr>
                    <td class="label">{{ __('message.discount') }} ({{ $invoice->coupon_code }})</td>
                    <td class="value">{{ $totals['discount'] }}</td>
                </tr>
            @endif
            @foreach($totals['tax'] as $taxLabel => $taxValue)
                <tr>
                    <td class="label">{{ $taxLabel }}</td>
                    <td class="value">{{ $taxValue }}</td>
                </tr>
            @endforeach
            @if($invoice->processing_fee)
                <tr>
                    <td class="label">{{ __('message.processing_fee') }}</td>
                    <td class="value">{{ $totals['processing_fee'] }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td class="label fw-bold">{{ __('message.total') }}</td>
                <td class="value fw-bold">{{ $totals['total'] }}</td>
            </tr>
        </tbody>
    </table>

</div>
</body>
</html>
