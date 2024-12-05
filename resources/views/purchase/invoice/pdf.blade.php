@extends('layouts.pdf')

@section('page:title')
Purchase Order {{ $model->order_number }}
@endsection

@section('page:content')

<div>
    <div class="row">
    <h3 class="text-align-left">
        PURCHASE INVOICE
        <span class="brxsmall"></span>

    </h3>
        <div class="col-md-8">
            <p class="text-align-left">
                Nomor : {{ $model->number }}
                <span class="brxsmall"></span>
                Lampiran :-
                <span class="brxsmall"></span>
                Perihal : Penawaran Harga {{ $model->purchase_order->vendor->name }}
            </p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <p class="text-align-left">
                Kepada Yth.
                <span class="brxsmall"></span>
                <b>{{ $model->purchase_order->vendor->name }}</b>
                <span class="brxsmall"></span>
                <b>{{ optional($model->purchase_order->vendor->profile->default_address)->address }}</b>
                <span class="brxsmall"></span>
                <b>{{ optional($model->purchase_order->vendor->profile->default_address->region_city)->type }}  {{ optional($model->purchase_order->vendor->profile->default_address->region_city)->name }}</b>
            </p>
        </div>
    </div>
</div>
<div>
    <table class="table-items">
        <thead>
            <tr>
                <th bgcolor="#4479ff" width="25px" class="label">No</th>
                <th bgcolor="#4479ff" class="label">Item</th>
                <th bgcolor="#4479ff" class="label">Jumlah</th>
                <th bgcolor="#4479ff" class="label">Harga Satuan</th>
                <th bgcolor="#4479ff" class="label">Total Harga</th>
            </tr>
        </thead>

        <tbody>
            @foreach($model->purchase_order->purchase_details as $index=> $purchase_detail)
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
                <td class="text-align-right">{{ \Rupiah::format($model->purchase_order->total_price) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-align-right">Discount {{ $model->discount }} %</td>
                <td class="text-align-right">{{ \Rupiah::format($model->amount_discount) }}</td>
            </tr>
            @if($model->use_tax == 1)
            <tr>
                <td class="label text-align-right" colspan="4">Pajak</td>
                <td class="label text-align-right">{{ \Rupiah::format($model->amount_tax) }}</td>
            </tr>
            @endif
            <tr>
                <td class="label text-align-right" colspan="4">Total</td>
                <td class="label text-align-right">{{ \Rupiah::format($model->bill) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
<div>
<b><p>Terbilang :
<span class="brxsmall"></span>

<i>{{ Str::title(Terbilang::make($model->bill)) }}</p></i>
</b>
<p>Mohon di Transfer ke Rekening Kami : </p>
<p>
    <b>Bank Mandiri<br/>
    Nomor Rekening : 1410023313125<br/>
    Atas Nama : CV Damarnam    
</b>
</p>
<div class="text-align-right">
    <p >Sidoarjo , {{ $model->date_of_issued->format('d M Y') }}</p>
    <p>Hormat Kami,</p>
    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('/img/qr.png'))) }}" width="50px">
    <p>{{ $model->purchase_order->pic->name }}</p>
</div>


<p>Catatan : </p>
<span class="brxsmall"></span>
<i><ul>
  <li>Kwitansi, Invoice & Faktur Pajak Diterbitkan Setelah Pembayaran Diterima / Barang Dikirim.</li>
  <li>Tidak termasuk Biaya Kirim</li>
  <li>Harga Include PPN 11%</li>
  <li>Barang Tidak Dapat Dikembalikan (Retur) Karena Termasuk Barang Custom (Pesanan Khusus).</li>
  <li>Transaksi Tidak Dapat Dibatalkan Apabila Sudah Ada Uang Muka, Apabila Ada Pembatalan, Uang Muka Tidak Dapat Dikembalikan</li>
</ul> </i>
</div>


@endsection
