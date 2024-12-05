@extends('layouts.pdf')

@section('page:title')
Sales Quotation {{ $model->quotation_number }}
@endsection

@section('page:content')
<h1 class="title">Sales Quotation</h1>
<h2 class="subtitle">No: {{ $model->quotation_number }}</h2>

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
        <td>{{ $model->quotation_date_formated }}</td>
    </tr>
    <tr>
        <td colspan="3">&nbsp;</td>
        <td class="label">Reference Number</td>
        <td class="field-separator">:</td>
        <td></td>
    </tr>
</table>

<table class="table-information" border="0">
    <tr>
        <td class="label">Company</td>
        <td class="field-separator">:</td>
        <td>PT. Utomodeck Metal Work</td>
        <td class="label">Vendor</td>
        <td class="field-separator">:</td>
        <td>{{ $model->customer->name }}</td>
    </tr>
    <tr>
        <td class="label">Address</td>
        <td class="field-separator">:</td>
        <td></td>
        <td class="label">Address</td>
        <td class="field-separator">:</td>
        <td>{{ optional($model->customer->default_address)->address }}</td>
    </tr>
    <tr>
        <td class="label">Phone / FAX</td>
        <td class="field-separator">:</td>
        <td></td>
        <td class="label">Phone / FAX</td>
        <td class="field-separator">:</td>
        <td>{{ $model->customer->phone }}/{{ $model->customer->fax ? $model->customer->fax : '-' }}</td>
    </tr>    
    <tr>
        <td class="label">Contact</td>
        <td class="field-separator">:</td>
        <td>{{ $model->pic->name }}</td>
        <td class="label">Contact</td>
        <td class="field-separator">:</td>
        <td>{{ $model->customer->name }}</td>
    </tr>
</table>

<p class="text-align-right" style="margin: 20px 0px 5px;">Currency : IDR</p>

<table class="table-items">
    <thead>
        <tr>
            <th class="label">Item</th>
            <th class="label">Qty (Length * Sheet)</th>
            <th class="label">Price</th>
            <th class="label">Amount</th>
        </tr>
    </thead>

    <tbody>
        @foreach($model->sales_details as $salesDetail)
        <tr>
            <td>{{ $salesDetail->item_material->name }} {{ $salesDetail->item_material->material->name }} {{ $salesDetail->item_material->thick }} {{ $salesDetail->item_material->color->name }}</td>
            <td class="text-align-center">{{ $salesDetail->quantity }} ({{$salesDetail->length}}m x {{$salesDetail->sheet}})</td>
            <td class="text-align-right">{{ \Rupiah::format($salesDetail->price) }}</td>
            <td class="text-align-right">{{ \Rupiah::format($salesDetail->total_price) }}</td>
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
        <td>{{ $model->pic->role->display_name }}</td>
    </tr>
</table>
@endsection