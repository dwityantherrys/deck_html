@extends('layouts.pdf')

@section('page:title')
Good Receipt {{ $model->number }}
@endsection

@section('page:header-content')
<h1 class="title">Good Receipt</h1>
@endsection

@section('page:content')
@if($isCopy)
<div class="watermark">
    <img src="{{ asset('img/watermark-copy-file.png') }}" style="opacity: 0.2" width="100%">
</div>
@endif

<table>
    <tr>
        <td class="label">Good Receipt Number</td>
        <td class="field-separator">:</td>
        <td>{{ $model->number }}</td>
        <td class="label">Date</td>
        <td class="field-separator">:</td>
        <td>{{ $model->date_formated }}</td>
    </tr>
    <tr>
        <td class="label">GI Number</td>
        <td class="field-separator">:</td>
        <td>{{ $model->good_issued->number }}</td>
        <td class="label">Warehouse</td>
        <td class="field-separator">:</td>
        <td>{{ $model->warehouse->name }}</td>
    </tr>
</table>

<p class="text-align-right" style="margin: 20px 0px 5px;">Currency : IDR</p>

<table class="table-items">
    <thead>
        <tr>
            <th class="label">Item Code</th>
            <th class="label">Nama Barang</th>
            <th class="label">Ukuran (m)</th>
            <th class="label">Lembar</th>
            <th class="label">Qty</th>
        </tr>
    </thead>

    <tbody>
        @foreach($model->good_receipt_details as $goodReceiptDetail)
        <?php 
            $itemMaterial = $goodReceiptDetail->item_material;
            $itemMaterialLength = $goodReceiptDetail->job_order_detail->length;
        ?>
        <tr>
            <td>{{ $itemMaterial->id }}</td>
            <td>{{ $itemMaterial->item->name }} {{ $itemMaterial->name }} {{ $itemMaterial->material->name }} {{ $itemMaterial->thick }} {{ $itemMaterial->color->name }}</td>
            <td class="text-align-center">{{ $itemMaterialLength }}</td>
            <td class="text-align-center">{{ $goodReceiptDetail->quantity / $itemMaterialLength }}</td>
            <td class="text-align-center">{{ $goodReceiptDetail->quantity }}</td>
        </tr>
        @endforeach
    </tbody>
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