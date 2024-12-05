<?php
use App\Models\Master\Item\Item;
// use App\Models\Master\Material\RawMaterial;
?>

@extends('layouts.pdf')

@section('page:title')
Purchase Receive {{ $model->number }}
@endsection

@section('page:content')
<h1 class="title">Purchase Receive</h1>
<h2 class="subtitle">No: {{ $model->number }}</h2>

<table>
    <tr>
        <td colspan="3">&nbsp;</td>
        <td class="label">Date</td>
        <td class="field-separator">:</td>
        <td>{{ $model->date_formated }}</td>
    </tr>
    <tr>
        <td colspan="3">&nbsp;</td>
        <td class="label">Reference Number</td>
        <td class="field-separator">:</td>
        <td>{{ $model->purchase->order_number }}</td>
    </tr>
</table>

<table class="table-information" border="0">
    <tr>
        <td class="label">Penerima</td>
        <td class="field-separator">:</td>
        <td>{{ $model->purchase->pic->name }}</td>
        <td class="label">Vendor</td>
        <td class="field-separator">:</td>
        <td>{{ $model->purchase->vendor->name }}</td>
    </tr>
    <tr>
        <td class="label">Phone / FAX</td>
        <td class="field-separator">:</td>
        <td></td>
        <td class="label">Phone / FAX</td>
        <td class="field-separator">:</td>
        <td>{{ $model->purchase->vendor->profile->phone }}/{{ $model->purchase->vendor->profile->fax ? $model->purchase->vendor->profile->fax : '-' }}</td>
    </tr>
    <tr>
        <td class="label">Contact</td>
        <td class="field-separator">:</td>
        <td>{{ $model->purchase->pic->name }}</td>
        <td class="label">Contact</td>
        <td class="field-separator">:</td>
        <td>{{ $model->purchase->vendor->name }}</td>
    </tr>
</table>

<p class="text-align-right" style="margin: 20px 0px 5px;">Currency : IDR</p>

<table class="table-items">
    <thead>
        <tr>
          <th class="label">Item Material</th>
            <th class="label">Qty</th>
            <th class="label">Price</th>
            <th class="label">Amount</th>
        </tr>
    </thead>

    <tbody>
        @foreach($model->receipt_detail_adjs as $receiptDetail)
        <?php
        $itemMaterial = Item::find($receiptDetail['item_material_id']);
        // $rawMaterial = RawMaterial::find($receiptDetail['raw_material_id']);
        ?>
        <?php
        $itemMaterialName = $itemMaterial->name;
        // $rawMaterialName = $rawMaterial->name . ' ' . $rawMaterial->material->name . ' ' . $rawMaterial->thick . 'mm ' . $rawMaterial->color->name . ' '
        ?>
        <tr style="background: lightgrey">
          <td width="35%">{{ $itemMaterialName }}</td>
            {{-- <td width="35%">{{ $rawMaterialName }}</td> --}}
            <td class="text-align-center">{{ $receiptDetail['quantity'] }}</td>
            <td class="text-align-right">{{ \Rupiah::format($receiptDetail['estimation_price']) }}</td>
            <td class="text-align-right">{{ \Rupiah::format($receiptDetail['amount']) }}</td>
        </tr>

        @if($receiptDetail['has_adjustment'])
            @foreach($receiptDetail['adjs'] as $keyRA => $receiptAdjustment)
            <tr>
              <td> {{ $itemMaterialName }} </td>
                <td class="text-align-center"> {{ $receiptAdjustment->quantity }} </td>
            </tr>
            @endforeach
        @endif
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
                    if ($model->purchase->tax_type == 1) {
                        $vatLabel = 'VAT 11%';
                    } elseif ($model->purchase->tax_type == 2) {
                        $vatLabel = 'VAT 11% (Included)';
                    }
                ?>
                {{ $vatLabel }}
            </td>
            <?php
                $vat = 0; // Default VAT is 0
                $totalPrice = $model->total_price;

                // Determine VAT based on tax_type
                if ($model->purchase->tax_type == 1) {
                    // VAT 11%
                    $vat = $totalPrice * 0.11;
                } elseif ($model->purchase->tax_type == 2) {
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
        <td>{{ $model->purchase->pic->name }}</td>
    </tr>
    <tr>
        <td></td>
        <td>{{ $model->purchase->pic->role->display_name }}</td>
    </tr>
</table>
@endsection
