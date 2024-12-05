@extends('layouts.pdf')

@section('page:title')
Purchase Request {{ $model->request_number }}
@endsection

@section('page:content')
<h1 class="title">Purchase Request</h1>
<h2 class="subtitle">No: {{ $model->request_number }}</h2>

<table>
    <tr>
        <td colspan="3">&nbsp;</td>
        <td class="label">Date</td>
        <td class="field-separator">:</td>
        <td>{{ $model->request_date_formated }}</td>
    </tr>
    <tr>
        <td colspan="3">&nbsp;</td>
        <td class="label">No PAT</td>
        <td class="field-separator">:</td>
        <td>{{ $model->pat_number }}</td>
    </tr>
</table>

<table class="table-information" border="0">  
    <tr>
        <td class="label">Contact</td>
        <td class="field-separator">:</td>
        <td>{{ $model->pic->name }}</td>
        <td class="label">Contact</td>
        <td class="field-separator">:</td>
        <td>{{ $model->vendor->name }}</td>
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
        @foreach($model->purchase_details as $purchase_detail)
        <tr>
            <td>{{ $purchase_detail->item_name }}</td>
            <td class="text-align-center">{{ $purchase_detail->quantity }}</td>
            <td class="text-align-right">{{ \Rupiah::format($purchase_detail->estimation_price) }}</td>
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
                    } elseif ($model->tax_type == 2) {
                        $vatLabel = 'VAT 11% (Included)';
                    }
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

<table class="table-signment">
    <tr>
        <td width="75%">
            <span class="label">Catatan:</span>
            <div style="border: 1px solid black; width: 75%; height: 100px">
            {{ $model->remark }}

            </div>
        </td>
        <td valign="top">tertanda,</td>
    </tr>
    <tr>
        <td></td>
        <td>{{ $model->pic->name }}</td>
    </tr>
    <tr>
        <td></td>
        <td>PIC Role</td>
    </tr>
</table>
@endsection