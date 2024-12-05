@extends('layouts.admin')

@section('title', 'Edit Sales Quotation')

@section('content_header')
<h1>Edit Sales Quotation:  {{ $model->quotation_number }}</h1>
@stop

@section('content')
<div class="box box-danger">
    <div class="box-header with-border">
    <a href="{{ url($route) }}"><button type="button" class="btn btn-default text-red pull-right"><i class="fa fa-close"></i> Cancel</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <form id="form" role="form" method="post" action="{{ url($route . '/' . $model->id) }}" autocomplete="off">
        @component($routeView . '._form', [
            'route' => $route,
            'model' => $model,
            'paymentBankChannels' => $paymentBankChannels,
            'transaction_channels' => $transaction_channels
        ]) @endcomponent 


        <input type='hidden' name='_token' value='{{ csrf_token() }}'>
        <input type='hidden' name='_method' value='PUT'>
        <input type='hidden' name='id' value="{{ $model->id }}">
      
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
        {!! \TransAction::form($route, $model, 'order_number', $model->quotation_log_print) !!}
    </div>
    </form>
</div>
@stop

@section('js')
<script>
    $('[data-toggle="tooltip"]').tooltip();
</script>
@endsection