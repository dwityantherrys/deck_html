@extends('layouts.pdf')

@section('page:title')
Berita Acara Perbaikan {{ $model->number }}
@endsection

@section('page:content')
<h1 class="title"  style="margin-top : -5px; margin-bottom : -20px; ">Berita Acara Perbaikan</h1>

<table class="table-information" border="0">
    <tr>
        <td class="label">Nomor</td>
        <td class="field-separator">:</td>
        <td>{{ $model->number }}</td>
        <td class="label">Kepada</td>
        <td class="field-separator">:</td>
        <td>{{ $model->job_order->vendor->name }}</td>
        
    </tr>

    <tr>
        <td class="label">Tanggal</td>
        <td class="field-separator">:</td>
        <td>{{ $model->date_formated }}</td>
        <td class="label">Lokasi</td>
        <td class="field-separator">:</td>
        <td>{{ $model->job_order->location }}</td>
    </tr>

    
</table>


<table class="table-items">
    <thead>
        <tr>
            <th class="label">Item</th>
            <th class="label">Qty</th>
        </tr>
    </thead>

    <tbody>
        @foreach($model->job_order->job_order_details as $purchase_detail)
        <tr>
            <td>{{ $purchase_detail->item_material->name }}</td>
            <td class="text-align-center">{{ $purchase_detail->quantity }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="table-signment">
    <tr>
    <td width="75%">
            <span class="label">Catatan:</span>
            <div style="border: 1px solid black; width: 75%; height: 100px; padding-left : 5px;">
            {{ $model->remark }}
            </div>
        </td>
        <td valign="top">tertanda,</td>
    </tr>
    <tr>
        <td></td>
        <td>{{ $model->job_order->pic->name }}</td>
    </tr>
    <tr>
        <td></td>
        <td>PIC Role</td>
    </tr>
</table>
@endsection