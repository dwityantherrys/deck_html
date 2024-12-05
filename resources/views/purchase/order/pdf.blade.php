@extends('layouts.pdf')

@section('page:title')
Purchase Order {{ $model->order_number }}
@endsection

@section('page:content')

<div>
    <div class="row">
        <div class="col-md-8">
            <p class="text-align-left">
                Nomor : {{ $model->request_number }}
                <span class="brxsmall"></span>
                Lampiran :-
                <span class="brxsmall"></span>
                Perihal : Penawaran Harga {{ $model->vendor->name }}
            </p>
        </div>
        <div class="col-md-4">
            <p class="text-align-right">
                Sidoarjo , {{ $model->order_date->format('d M Y') }}
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <p class="text-align-left">
                Kepada Yth.
                <span class="brxsmall"></span>
                <b>{{ $model->vendor->name }}</b>
                <span class="brxsmall"></span>
                <b>{{ optional($model->vendor->profile->default_address)->address }}</b>
                <span class="brxsmall"></span>
                <b>{{ optional($model->vendor->profile->default_address->region_city)->type }}  {{ optional($model->vendor->profile->default_address->region_city)->name }}</b>
            </p>
        </div>
    </div>

    <br/>
    <div class="row">
        <div class="col-md-8">
            <p class="text-align-left">
                Di Tempat
                <span class="brxsmall"></span>
                Dengan Hormat,
                <span class="brxsmall"></span>
                Berikut kami kirimkan penawaran harga terkait kebutuhan bahan sebagai berikut,
            
            </p>
        </div>
    </div>
</div>
<span class="brxsmall"></span>

<div>
    <table class="table-items">
        <thead>
            <tr>
                <th bgcolor="#82ca3f" width="25px" class="label">No</th>
                <th bgcolor="#82ca3f" class="label">Deskripsi</th>
                <th bgcolor="#82ca3f" class="label">Jumlah</th>
                <th bgcolor="#82ca3f" class="label">Harga Satuan</th>
                <th bgcolor="#82ca3f" class="label">Total Harga</th>
            </tr>
        </thead>

        <tbody>
            @foreach($model->purchase_details as $index=> $purchase_detail)
            <tr>
                <td>{{ $index+1 }}</td>
                <td>{{ $purchase_detail->item_name }}</td>
                <td class="text-align-center">{{ $purchase_detail->quantity }}</td>
                <td class="text-align-right">{{ \Rupiah::format($purchase_detail->estimation_price) }}</td>
                <td class="text-align-right">{{ \Rupiah::format($purchase_detail->amount) }}</td>
            </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="4" class="text-align-right">Sub Total</td>
                <td class="text-align-right">{{ \Rupiah::format($model->total_price) }}</td>
            </tr>
            <tr>
                <td class="label text-align-right" colspan="4">Total</td>
                <td class="label text-align-right">{{ \Rupiah::format($model->total_price) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
<br/>
<div>
<p>Catatan : </p>
<span class="brxsmall"></span>
<ol>
  <li>Penawaran diluar pengiriman.</li>
  <li>Harga penawaran Include PPN.</li>
  <li>Harga dan stock tidak mengikat sebelum ada DP dan PO yang kami terima.</li>
  <li>Pembayaran: DP 50 % , sisa pelunasan 50% Sebelum barang dikirim.</li>
  <li>Masa berlaku penawaran adalah 7 hari kalender sejak penawaran ini dibuat.</li>
</ol> 
</div>

<div>
    <p>Hormat Kami : </p>
    <br/>
    <br/>
    <p>{{ $model->pic->name }}</p>
</div>


@endsection
