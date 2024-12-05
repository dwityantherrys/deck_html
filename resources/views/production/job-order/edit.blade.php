@extends('layouts.admin')

@section('title', 'Edit SPK')

@section('content_header')
<h1>Edit Project:  {{ $model->order_number }}</h1>
@stop

@section('content')
<?php $printInformation = !empty($model->order_log_print) ? 'di print oleh ' . $model->order_log_print->employee->name . ' pada tanggal ' . $model->order_log_print->date->format('m/d/Y')  : ''; ?>

<div class="box box-danger">
    <div class="box-header with-border">
    <a href="{{ url($route) }}"><button type="button" class="btn btn-default text-red pull-right"><i class="fa fa-close"></i> Cancel</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <form id="form" role="form" method="post" action="{{ url($route . '/' . $model->id) }}" autocomplete="off">
        @component($routeView . '._form', [
            'route' => $route,
            'joTypes' => $joTypes,
            'model' => $model
        ]) @endcomponent 


        <input type='hidden' name='_token' value='{{ csrf_token() }}'>
        <input type='hidden' name='_method' value='PUT'>
        <input type='hidden' name='id' value="{{ $model->id }}">
      
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
        <input type="submit" class="btn btn-primary" value="save">
        <input type="button" class="btn btn-default text-red" value="save & print" title="{{ $printInformation }}" data-toggle="tooltip">
        <input type="button" class="btn btn-default text-red" value="print"
            title="{{ $printInformation }}"
            data-target="{{ url($route . '/' . $model->id . '/print') }}"
            data-toggle="tooltip"
            data-information="{{ $printInformation }}">
    </div>
    </form>
</div>
@stop

@section('js')
<script>
    $('[data-toggle="tooltip"]').tooltip();
</script>
@endsection