@extends('layouts.admin')

@section('title', 'New Customer / Vendor')

@section('content_header')
<h1> New Customer / Vendor</h1>
@stop

@section('content')
<form id="form" role="form" method="post" action="{{ url($route) }}" enctype="multipart/form-data">
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#profile" data-toggle="tab">general</a></li>
        <li><a href="#address" data-toggle="tab">address</a></li>
        <li><a href="#company" data-toggle="tab">company</a></li>

        <li class="pull-right"><a href="{{ url($route) }}" class="btn btn-default text-red"><i class="fa fa-close"></i> Cancel</a></li>
    </ul>

    <div class="tab-content" style="height: 70vh; overflow-x: hidden; overflow-y: auto;">
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
        <input type="submit" class="btn btn-primary btn-block" value="Submit">
    </div>      
</div>
</form>
@stop