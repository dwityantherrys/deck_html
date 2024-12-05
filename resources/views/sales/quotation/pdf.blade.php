@extends('layouts.pdf')

@section('page:title')
Sales Quotation {{ $model->quotation_number }}
@endsection

@section('page:header-content')
<h1 class="title">Sales Quote</h1>
@endsection

@section('page:content')
@if($isCopy)
<div class="watermark">
    <img src="{{ asset('img/watermark-copy-file.png') }}" style="opacity: 0.2" width="100%">
</div>
@endif

<table class="table-information" border="0">
    <tr>
        <td class="label">Sales Quote No</td>
        <td class="field-separator">:</td>
        <td>{{ $model->quotation_number }}</td>
        <td class="label">Tanggal</td>
        <td class="field-separator">:</td>
        <td>{{ $model->quotation_date_formated }}</td>
    </tr>
</table>

<table class="table-information" border="0">
    <tr>
        <td class="label" colspan="6">Pemesan :</td>
    </tr>
    <tr>
        <td colspan="3">{{ $model->customer->name }}</td>
        <td class="label">No. Pel</td>
        <td class="field-separator">:</td>
        <td>{{ $model->customer->id }}</td>
    </tr>
    <tr>
        <td colspan="3">{{ optional($model->customer->default_address)->address }}</td>
        <td class="label">TOP</td>
        <td class="field-separator">:</td>
        <td>
            <?php if($model->transaction_channel == $model::TRANSACTION_CHANNEL_WEB): ?>
                Cash Transfer
            <?php else: ?>
                Transfer
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td colspan="3">{{ $model->customer->phone }}/{{ $model->customer->fax ? $model->customer->fax : '-' }}</td>
        <td class="label"></td>
        <td class="field-separator"></td>
        <td></td>
    </tr>    
    <tr>
        <td class="label">KTP ID</td>
        <td class="field-separator">:</td>
        <td>{{ $model->customer->identity_number }}</td>
        <td class="label"></td>
        <td class="field-separator"></td>
        <td></td>
    </tr>
    <tr>
        <td class="label">NPWP</td>
        <td class="field-separator">:</td>
        <td>{{ $model->customer->identity_number }}</td>
        <td class="label"></td>
        <td class="field-separator"></td>
        <td></td>
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
            <th class="label">Harga</th>
            <th class="label">Total Harga</th>
        </tr>
    </thead>

    <tbody>
        @foreach($model->sales_details as $salesDetail)
        <?php 
            $vat = $salesDetail->price*0.1;
            $priceExVat = $salesDetail->price - $vat; 
        ?>

        <tr>
            <td>{{ $salesDetail->item_material->id }}</td>
            <td>{{ $salesDetail->item_material->name }} {{ $salesDetail->item_material->material->name }} {{ $salesDetail->item_material->thick }}mm {{ $salesDetail->item_material->color->name }}</td>
            <td class="text-align-center">{{$salesDetail->length}}</td>
            <td class="text-align-center">{{$salesDetail->sheet}}</td>
            <td class="text-align-center">{{ $salesDetail->quantity }}</td>
            <td class="text-align-right">{{ \Rupiah::format($priceExVat) }}</td>
            <td class="text-align-right">{{ \Rupiah::format($priceExVat*$salesDetail->quantity) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="table-signment">
    <tr>
        <td width="55%">
            <div style="border: 1px solid black; width: 100%; height: auto">
                <span class="label">Keterangan:</span>
                <ul style="padding-left: 18px">
                    <li>Harga dapat berubah sewaktu-waktu</li>
                    <li>Harga dalam penawaran ini hanya berlaku 3 hari sejak tanggal penerbitan dokumen</li>
                    <li>Produksi dilakukan setelah menerima pembayaran baik DP 30% maupun LUNAS</li>
                    <li>Barang dapat diambil (+/-) H+1 setelah pembayaran di terima</li>
                    <li>Pengiriman dilakukan min H+1 dari penerimaan pembayaran (tergantung load pengiriman)</li>
                </ul>
            </div>
        </td>

        <?php 
            $vat = $model->total_price*0.1;
            $totalPriceExVat = $model->total_price-$vat;
        ?>
        <td valign="top">
            <table class="table-summaries">
                <tr>
                    <td>Jumlah Harga Jual</td>
                    <td>:</td>
                    <td class="text-align-right">{{ \Rupiah::format($totalPriceExVat) }}</td>
                </tr>
                <tr>
                    <td>Discount</td>
                    <td>:</td>
                    <td class="text-align-right">{{ \Rupiah::format($model->discount) }}</td>
                </tr>
                <tr>
                    <td>PPN</td>
                    <td>:</td>
                    <td class="text-align-right">{{ \Rupiah::format($vat) }}</td>
                </tr>
                <tr>
                    <td class="label">Grand Total</td>
                    <td>:</td>
                    <td class="label text-align-right">{{ \Rupiah::format($model->total_price) }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="2" height="3%"></td>
    </tr>

    <tr>
        <td class="label text-align-center">
            @if($model->transaction_channel == $model::TRANSACTION_CHANNEL_WEB)   
            Juragan Atap <br>
            tidak menerima pembayaran TUNAI. <br>
            pembayaran harus ditransfer ke rekening <br>
            A/N {{ $model->payment_bank_channel->rekening_name }} <br>
            {{ $model->payment_bank_channel->name }} : {{ $model->payment_bank_channel->rekening_number }}
            @endif
        </td>
        <td valign="top">Hormat kami,</td>
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