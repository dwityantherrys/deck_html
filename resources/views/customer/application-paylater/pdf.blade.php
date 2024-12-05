@extends('layouts.pdf')

@section('page:title')
Purchase Order {{ $model->order_number }}
@endsection

@section('page:content')
<h1 class="title">Purchase Order</h1>
<h2 class="subtitle">No: {{ $model->order_number }}</h2>

@if($isCopy)
<div class="watermark">
    <img src="{{ asset('img/watermark-copy-file.png') }}" style="opacity: 0.2" width="100%">
</div>
@endif

<table>
    <tr>
        <td colspan="3">&nbsp;</td>
        <td class="label">Date</td>
        <td class="field-separator">:</td>
        <td>{{ $model->order_date_formated }}</td>
    </tr>
    <tr>
        <td colspan="3">&nbsp;</td>
        <td class="label">Reference Number</td>
        <td class="field-separator">:</td>
        <td>{{ $model->request_number }}</td>
    </tr>
</table>

<table class="table-information" border="0">
    <tr>
        <td class="label">Company</td>
        <td class="field-separator">:</td>
        <td>PT. Utomodeck Metal Work</td>
        <td class="label">Vendor</td>
        <td class="field-separator">:</td>
        <td>{{ $model->vendor->name }}</td>
    </tr>
    <tr>
        <td class="label">Address</td>
        <td class="field-separator">:</td>
        <td></td>
        <td class="label">Address</td>
        <td class="field-separator">:</td>
        <td>{{ optional($model->vendor->profile->default_address)->address }}</td>
    </tr>
    <tr>
        <td class="label">Phone / FAX</td>
        <td class="field-separator">:</td>
        <td></td>
        <td class="label">Phone / FAX</td>
        <td class="field-separator">:</td>
        <td>{{ $model->vendor->profile->phone }}/{{ $model->vendor->profile->fax ? $model->vendor->profile->fax : '-' }}</td>
    </tr>    
    <tr>
        <td class="label">Contact</td>
        <td class="field-separator">:</td>
        <td>{{ $model->pic->name }}</td>
        <td class="label">Contact</td>
        <td class="field-separator">:</td>
        <td>{{ $model->vendor->name }}</td>
    </tr>
</table>

<p class="text-align-right" style="margin: 20px 0px 5px;">Currency : IDR</p>

<table class="table-items">
    <thead>
        <tr>
            <th class="label">Raw Material</th>
            <th class="label">Qty</th>
            <th class="label">Est. Price</th>
            <th class="label">Amount</th>
        </tr>
    </thead>

    <tbody>
        @foreach($model->purchase_details as $purchase_detail)
        <tr>
            <td>{{ $purchase_detail->raw_material->name }} {{ $purchase_detail->raw_material->material->name }} {{ $purchase_detail->raw_material->thick }} {{ $purchase_detail->raw_material->color->name }}</td>
            <td class="text-align-center">{{ $purchase_detail->quantity }}</td>
            <td class="text-align-right">{{ \Rupiah::format($purchase_detail->estimation_price) }}</td>
            <td class="text-align-right">{{ \Rupiah::format($purchase_detail->amount) }}</td>
        </tr>
        @endforeach
    </tbody>

    <tfoot>
        <tr>
            <td rowspan="3" colspan="2"></td>
            <td>Sub Total</td>
            <td class="text-align-right">{{ \Rupiah::format($model->total_price) }}</td>
        </tr>
        <tr>
            <td>VAT 10%</td>
            <?php $vat = $model->total_price*0.1; ?>
            <td class="text-align-right">{{ \Rupiah::format($vat) }}</td>
        </tr>
        <tr>
            <td class="label">Total</td>
            <td class="label text-align-right">{{ \Rupiah::format($model->total_price+$vat) }}</td>
        </tr>
    </tfoot>
</table>

<table class="table-signment">
    <tr>
        <td width="75%">
            <span class="label">Catatan:</span>
            <div style="border: 1px solid black; width: 75%; height: 100px"></div>
        </td>
        <td valign="top">tertanda,</td>
    </tr>
    <tr>
        <td></td>
        <td>{{ $model->pic->name }}</td>
    </tr>
    <tr>
        <td></td>
        <td>PIC Role</td>
    </tr>
</table>
@endsection