@extends('layouts.pdf')

@section('page:title')
Sales loan {{ $model->loan_number }}
@endsection

@section('page:content')
<h1 class="title">Sales loan</h1>
<h2 class="subtitle">No: {{ $model->loan_number }}</h2>

@if($isCopy)
<div class="watermark">
    <img src="{{ asset('img/watermark-copy-file.png') }}" style="opacity: 0.2" width="100%">
</div>
@endif

<table>
    <tr>
        <td colspan="3">&nbsp;</td>
        <td class="label">Date</td>
        <td class="field-separator">:</td>
        <td>{{ $model->loan_date_formated }}</td>
    </tr>
    <tr>
        <td colspan="3">&nbsp;</td>
        <td class="label">Reference Number</td>
        <td class="field-separator">:</td>
        <td></td>
    </tr>
</table>

<table class="table-information" border="0">
    <tr>
        <td class="label">Company</td>
        <td class="field-separator">:</td>
        <td>PT. Utomodeck Metal Work</td>
        <td class="label">Vendor</td>
        <td class="field-separator">:</td>
        <td>{{ $model->customer->name }}</td>
    </tr>
    <tr>
        <td class="label">Address</td>
        <td class="field-separator">:</td>
        <td></td>
        <td class="label">Address</td>
        <td class="field-separator">:</td>
        <td>{{ optional($model->customer->default_address)->address }}</td>
    </tr>
    <tr>
        <td class="label">Phone / FAX</td>
        <td class="field-separator">:</td>
        <td></td>
        <td class="label">Phone / FAX</td>
        <td class="field-separator">:</td>
        <td>{{ $model->customer->phone }}/{{ $model->customer->fax ? $model->customer->fax : '-' }}</td>
    </tr>    
    <tr>
        <td class="label">Contact</td>
        <td class="field-separator">:</td>
        <td>{{ $model->pic->name }}</td>
        <td class="label">Contact</td>
        <td class="field-separator">:</td>
        <td>{{ $model->customer->name }}</td>
    </tr>
</table>

<p class="text-align-right" style="margin: 20px 0px 5px;">Currency : IDR</p>

<table class="table-items">
    <thead>
        <tr>
            <th class="label">Qty (Length * Sheet)</th>
        </tr>
    </thead>

    <tbody>
        @foreach($model->loan_details as $loanDetail)
        <tr>
            {{-- <td>{{ $loanDetail->name }} {{ $loanDetail->item_material->material->name }} {{ $loanDetail->item_material->thick }} {{ $loanDetail->item_material->color->name }}</td> --}}
            <td class="text-align-center">{{ $loanDetail->quantity }}</td>
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
        <td>{{ $model->pic->name }}</td>
    </tr>
    <tr>
        <td></td>
        <td>{{ $model->pic->role->display_name }}</td>
    </tr>
</table>
@endsection