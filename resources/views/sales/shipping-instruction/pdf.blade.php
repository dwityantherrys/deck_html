<?php
use App\Models\Master\Item\Item;
// use App\Models\Master\Material\RawMaterial;
?>
@extends('layouts.pdf')

@section('page:title')
Shipping Instruction {{ $model->instruction_number }}
@endsection

@section('page:header-content')
<h1 class="title">Shipping Instruction</h1>
@endsection

@section('page:content')

<table>
    <tr>
        <td valign="top">
            <table>
                <tr>
                    <td class="label">SI Number</td>
                    <td class="field-separator">:</td>
                    <td>{{ $model->number }}</td>
                </tr>
                <tr>
                    <td class="label">Purchase Number</td>
                    <td class="field-separator">:</td>
                    <td>{{ $model->purchase->request_number ?? "-" }}</td>
                </tr>
            </table>
        </td>
        <td>
            <table style="border: 1px solid black">
                <tr>
                    <td class="text-align-right" colspan="3">
                        <span class="label">Tanggal</span> : {{ $model->date_formated }}
                    </td>
                </tr>
                <tr>
                    <td class="label" colspan="3" style="padding-top: 30px">Kirim ke :</td>
                </tr>
                <tr>
                    <td colspan="3">{{ $model->branch->name }}</td>
                </tr>
            
            </table>
        </td>
    </tr>
</table>

<table class="table-items">
    <thead>
        <tr>
            <th class="label">Nama Barang</th>
            <th class="label">Qty</th>
            <th class="label">Remark</th>
        </tr>
    </thead>

    <tbody>
        <?php $totalQty = 0; ?>
        @foreach($model->shipping_instruction_details as $shippingDetail)
        <?php $purchaseDetail = $shippingDetail->purchase_detail; ?>
        
        <?php
        $itemMaterialName = $purchaseDetail->item_name;
        // $rawMaterialName = $rawMaterial->name . ' ' . $rawMaterial->material->name . ' ' . $rawMaterial->thick . 'mm ' . $rawMaterial->color->name . ' '
        ?>
        <tr>
            <td>{{ $itemMaterialName }}</td>
            <td class="text-align-center">{{ $shippingDetail->quantity }}</td>
            <td></td>
        </tr>
        <?php $totalQty = $totalQty + $shippingDetail->quantity; ?>
        @endforeach
    </tbody>

    <tfoot>
        <tr>
            <td class="label text-align-center">Total</td>
            <td class="text-align-center">{{ $totalQty }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

<table class="table-additional" style="margin-top: 35px">
    <tr>
        <td class="label text-align-center" width="20%">Hormat Kami</td>
        <td class="label text-align-center" width="20%">Ekspedisi</td>
        <td class="label text-align-center" width="20%">Sopir</td>
        <td class="label text-align-center" width="20%">Security</td>
        <td class="label text-align-center" width="20%">Penerima</td>
    </tr>
    <tr>
        <td style="padding-bottom: 80px"></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>
            <div class="label">Tgl : </div>
            <div class="label">Jam : </div>
        </td>
        <td>
            <div class="label">Tgl : </div>
            <div class="label">Jam : </div>
        </td>
        <td>
            <div class="label">Tgl : </div>
            <div class="label">Jam : </div>
        </td>
        <td>
            <div class="label">Tgl : </div>
            <div class="label">Jam : </div>
        </td>
        <td>
            <div class="label">Tgl : </div>
            <div class="label">Jam : </div>
        </td>
    </tr>
</table>
@endsection
