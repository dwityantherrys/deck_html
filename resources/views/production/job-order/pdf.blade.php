@extends('layouts.pdf')

@section('page:title')
Surat Perintah Kerja {{ $model->number }}
@endsection

@section('page:content')
<h1 class="title" style="margin-top : -5px; margin-bottom : -20px;">Surat Perintah Kerja</h1>

<table class="table-information" border="0" style ="margin-top : 25px;margin-bottom : -20px; ">
    <tr>
        <td class="label">Nomor</td>
        <td class="field-separator">:</td>
        <td>{{ $model->number }}</td>
        <td class="label">Kepada</td>
        <td class="field-separator">:</td>
        <td>{{ $model->vendor->name }}</td>
        
    </tr>

    <tr>
        <td class="label">Tanggal</td>
        <td class="field-separator">:</td>
        <td>{{ $model->date_formated }}</td>
        <td class="label">Lokasi</td>
        <td class="field-separator">:</td>
        <td>{{ $model->location }}</td>
    </tr>

    
</table>

<p class="text-align-right" style="margin: 20px 0px 5px;">Currency : IDR</p>

<table class="table-items">
    <thead>
        <tr>
            <th class="label">Item</th>
            <th class="label">Qty</th>
            <th class="label">Price</th>
            <th class="label">Amount</th>
        </tr>
    </thead>

    <tbody>
        @foreach($model->job_order_details as $purchase_detail)
        <tr>
            <td>{{ $purchase_detail->item_material->name }}</td>
            <td class="text-align-center">{{ $purchase_detail->quantity }}</td>
            <td class="text-align-right">{{ \Rupiah::format($purchase_detail->price) }}</td>
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
            <td>
                <?php
                    // Set default VAT label
                    $vatLabel = 'No VAT';

                    // Determine VAT label and calculation based on tax_type
                    if ($model->tax_type == 1) {
                        $vatLabel = 'VAT 11%';
                    }
                    //  elseif ($model->tax_type == 2) {
                    //     $vatLabel = 'VAT 11% (Included)';
                    // }
                ?>
                {{ $vatLabel }}
            </td>
            <?php
                $vat = 0; // Default VAT is 0
                $totalPrice = $model->total_price;

                // Determine VAT based on tax_type
                if ($model->tax_type == 1) {
                    // VAT 11%
                    $vat = $totalPrice * 0.11;
                } elseif ($model->tax_type == 2) {
                    // VAT included, calculate reverse VAT from total price
                    $vat = $totalPrice - ($totalPrice / 1.11); // VAT portion
                }

                // Format VAT value
            ?>
            <td class="text-align-right">{{ \Rupiah::format($vat) }}</td>
        </tr>


        <tr>
            <td class="label">Total</td>
            <td class="label text-align-right">{{ \Rupiah::format($model->total_price+$vat) }}</td>
        </tr>
    </tfoot>
</table>

<table class="table-signment" border = "0">
    <tr rowspan = "4">
        <td width="60%">
            <span class="label">Catatan:</span>
            <div style="border: 1px solid black; width: 80%; height: 100px; padding-left : 5px;">
            {{ $model->remark }}
            </div>
        </td>
        <td valign="top"  colspan = "3"  width="40%" >tertanda,</td>
    </tr>
    <tr>
        <td></td>
        <td>{{ $model->pic->name }}</td>
        <td width = "30px"></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>PIC Role</td>
        <td width = "30px"></td>
        <td>Head Departement</td>
    </tr>
    
</table>
@endsection