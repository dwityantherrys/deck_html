@extends('layouts.admin')

@section('title', 'Edit Costumer / Vendor')

@section('content_header')
<h1> Edit Costumer / Vendor:  {{ $model->name }}</h1>
@stop

@section('content')

@if(!empty($model->application_paylater) && $model->application_paylater['status'] == 0)
<div class="callout callout-warning">
    <h4>Application Paylater, need validation!</h4>

    <p>Customer mengajukan pembayaran dengan menggunakan paylater.</p>
    <ul>
        <li>Periksa kelengkapan data</li>
        <li>Atur limit dan tempo pada tab <code>limit/price</code></li>
    </ul>
</div>
@endif

<form id="form" role="form" method="post" action="{{ url($route . '/' . $model->id) }}" enctype="multipart/form-data">
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#profile" data-toggle="tab">general</a></li>
        <li><a href="#address" data-toggle="tab">address</a></li>
        <li><a href="#company" data-toggle="tab">company</a></li>
        <li><a href="#setting" data-toggle="tab">limit / price</a></li>

        <li class="pull-right"><a href="{{ url($route) }}" class="btn btn-default text-red"><i class="fa fa-close"></i> Cancel</a></li>
    </ul>

    <div class="tab-content" style="height: 440px; overflow-x: hidden; overflow-y: auto;">
        @component($routeView . '._form', [
            'routeView' => $routeView,
            'tempoTypes' => $tempoTypes,
            'paymentMethodOptions' => $paymentMethodOptions,
            'model' => $model,
            'region_type' => $region_type
        ]) @endcomponent 
    </div>
    
    <div class="box-footer">
        <input type='hidden' name='_token' value='{{ csrf_token() }}'>
        <input type='hidden' name='id' value="{{ $model->id }}">

        <input type="submit" class="btn btn-primary btn-block" value="Submit">
    </div>      
</div>
</form>
@stop