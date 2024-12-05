@extends('layouts.pdf')

@section('page:title')
Good Issued {{ $model->number }}
@endsection

@section('page:header-content')
<h1 class="title">Good Issued</h1>
@endsection

@section('page:content')
@if($isCopy)
<div class="watermark">
    <img src="{{ asset('img/watermark-copy-file.png') }}" style="opacity: 0.2" width="100%">
</div>
@endif

<table>
    <tr>
        <td class="label">Good Issued Number</td>
        <td class="field-separator">:</td>
        <td>{{ $model->number }}</td>
        <td class="label">Date</td>
        <td class="field-separator">:</td>
        <td>{{ $model->date_formated }}</td>
    </tr>
    <tr>
        <td class="label">JO Number</td>
        <td class="field-separator">:</td>
        <td>{{ $model->job_order->number }}</td>
        <td class="label">Factory</td>
        <td class="field-separator">:</td>
        <td>{{ $model->factory->name }}</td>
    </tr>
</table>

<p class="text-align-right" style="margin: 20px 0px 5px;">Currency : IDR</p>

<table class="table-items">
    <thead>
        <tr>
            <th class="label">Material Code</th>
            <th class="label">Material</th>
            <th class="label">Material Use Code</th>
            <th class="label">Qty Use (kg)</th>
            <th class="label">Location</th>
        </tr>
    </thead>

    <tbody>
        @foreach($model->good_issued_details as $goodIssuedDetail)
        <?php 
            $rawMaterial = $goodIssuedDetail->raw_material;
        ?>
        <tr>
            <td>{{ $rawMaterial->number }}</td>
            <td>{{ $rawMaterial->name }} {{ $rawMaterial->material->name }} {{ $rawMaterial->thick }} {{ $rawMaterial->color->name }}</td>
            <td>{{ $goodIssuedDetail->inventory_warehouse->inventory_warehouse_number }}</td>
            <td class="text-align-center">{{ $goodIssuedDetail->quantity }}</td>
            <td>{{ $goodIssuedDetail->inventory_warehouse->warehouse->name }}</td>
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
        <td>{{ $model->job_order->pic->name }}</td>
    </tr>
    <tr>
        <td></td>
        <td>{{ $model->job_order->pic->role->display_name }}</td>
    </tr>
</table>
@endsection