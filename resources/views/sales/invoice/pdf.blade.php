@extends('layouts.pdf')

<?php
    $sales = $model->delivery_note->shipping_instruction->sales;

    $vat = $model->total_bill*0.1;
    $totalPriceExVat = $model->total_bill-$vat;
    $discountPrice = $model->total_bill*($model->discount/100);
    $unpaid = $sales->account_receivables()->where('balance', '>', 0)->sum('balance');
    $deliveryNoteDetails = $model->delivery_note->delivery_note_details;
?>

@section('page:title')
Sales Invoice {{ $model->number }}
@endsection

@section('page:header-content')
<h1 class="title">Sales Invoice</h1>
@endsection

@section('page:content')
@if($isCopy)
<div class="watermark">
    <img src="{{ asset('img/watermark-copy-file.png') }}" style="opacity: 0.2" width="100%">
</div>
@endif

@if($unpaid == 0)
<div class="watermark">
    <img src="{{ asset('img/watermark-lunas.jpg') }}" style="opacity: 0.2" width="100%">
</div>
@endif
<table class="table-information" border="0">
    <tr>
        <td class="label">Sales Invoice No</td>
        <td class="field-separator">:</td>
        <td>{{ $model->number }}</td>
        <td class="label">Tanggal</td>
        <td class="field-separator">:</td>
        <td>{{ $model->created_at->format('m/d/Y') }}</td>
    </tr>
</table>

<table>
    <tr>
        <td valign="top">
            <table>
                <tr>
                    <td class="label">TOP</td>
                    <td class="field-separator">:</td>
                    <td>{{ $model->payment_method->name }}</td>
                </tr>
                <tr>
                    <td class="label">SJ No</td>
                    <td class="field-separator">:</td>
                    <td>{{ $model->delivery_note->number }}</td>
                </tr>
                <tr>
                    <td class="label">SO No</td>
                    <td class="field-separator">:</td>
                    <td>{{ $sales->order_number }}</td>
                </tr>
            </table>
        </td>
        <td>
            <table style="border: 1px solid black">
                <tr>
                    <td class="label" colspan="3" style="padding-top: 30px">Kepada Yth :</td>
                </tr>
                <tr>
                    <td colspan="3">{{ $sales->customer->name }}</td>
                </tr>
                <tr>
                    <td colspan="3">
                    <?php
                    switch ($model->delivery_note->shipping_method_id) {
                        case $shippingMethod::METHOD_IS_PICKUP:
                            echo $sales->warehouse_out->name;
                            break;

                        case $shippingMethod::METHOD_IS_PICKUP_POINT:
                            echo $model->delivery_note->warehouse->name;
                            break;

                        case $shippingMethod::METHOD_IS_DELIVERY:
                            echo $model->delivery_note->address->address;
                            break;

                        default:
                            echo optional($sales->customer->default_address)->address;
                            break;
                    }
                    ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="padding-top: 30px">{{ $sales->customer->phone }}</td>
                </tr>
                <tr>
                    <td class="label">KTP ID</td>
                    <td class="field-separator">:</td>
                    <td>{{ $sales->customer->identity_number }}</td>
                </tr>
                <tr>
                    <td class="label">NPWP</td>
                    <td class="field-separator">:</td>
                    <td>{{ $sales->customer->identity_number }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p class="text-align-right" style="margin: 20px 0px 5px;">Currency : IDR</p>

<table class="table-items">
    <thead>
        <tr>
            <th class="label">Nama Barang</th>
            <th class="label">Ukuran (m)</th>
            <th class="label">Lembar</th>
            <th class="label">Qty</th>
            <th class="label">Harga Satuan</th>
            <th class="label">Jumlah Harga</th>
        </tr>
    </thead>

    <tbody>
        <?php $totalQty = 0; ?>
        @foreach($deliveryNoteDetails as $deliveryNoteDetail)
        <?php
            $salesDetail = $deliveryNoteDetail->shipping_instruction_detail->sales_detail;

            $vatD = $salesDetail->price*0.1;
            $priceExVat = $salesDetail->price - $vatD;
        ?>
        <tr>
            <td>{{ $salesDetail->item_material->name }} {{ $salesDetail->item_material->material->name }} {{ $salesDetail->item_material->thick }} {{ $salesDetail->item_material->color->name }}</td>
            <td class="text-align-center">{{ $salesDetail->length }}</td>
            <td class="text-align-center">{{ $deliveryNoteDetail->quantity/$salesDetail->length }}</td>
            <td class="text-align-center">{{ $deliveryNoteDetail->quantity }}</td>
            <td class="text-align-right">{{ \Rupiah::format($priceExVat) }}</td>
            <td class="text-align-right">{{ \Rupiah::format($priceExVat*$deliveryNoteDetail->quantity) }}</td>
        </tr>
        <?php $totalQty = $totalQty + $deliveryNoteDetail->quantity; ?>
        @endforeach
    </tbody>

    <tfoot>
        <tr>
            <td class="label text-align-center" colspan="3">Total</td>
            <td class="text-align-center">{{ $totalQty }}</td>
            <td></td>
            <td></td>
        </tr>
    </tfoot>
</table>

<table class="table-summaries" style="margin-top: 35px">
    <tr>
        <td>Jumlah Harga</td>
        <td>:</td>
        <td class="text-align-right">{{ \Rupiah::format($totalPriceExVat) }}</td>
    </tr>
    <tr>
        <td>Discount</td>
        <td>:</td>
        <td class="text-align-right">{{ \Rupiah::format($discountPrice) }}</td>
    </tr>
    <tr>
        <td>Uang Muka yang sudah difakturkan</td>
        <td>:</td>
        <td class="text-align-right">{{ \Rupiah::format($model->downpayment) }}</td>
    </tr>
    <tr>
        <td>Dasar pengenaan pajak</td>
        <td>:</td>
        <td class="text-align-right"></td>
    </tr>
    <tr>
        <td>PPN 10%</td>
        <td>:</td>
        <td class="text-align-right">{{ \Rupiah::format($vat) }}</td>
    </tr>
    <tr>
        <td>Jumlah setelah PPN</td>
        <td>:</td>
        <td class="text-align-right">{{ \Rupiah::format($model->grand_total_bill) }}</td>
    </tr>
    <tr>
        <td>Sisa</td>
        <td>:</td>
        <td class="text-align-right">{{ \Rupiah::format($unpaid) }}</td>
    </tr>
</table>

<table class="table-additional" style="width: 20%; margin-top: 35px">
    <tr>
        <td class="label text-align-center" width="20%">Hormat Kami</td>
    </tr>
    <tr>
        <td style="padding-bottom: 80px"></td>
    </tr>
</table>
@endsection
