@extends('layouts.admin')

@section('title', 'Inventory Balance')

@section('content_header')
<h1> Inventory Balance Item : {{ $itemName }}</h1>
@stop

@section('content')
<div class="box box-danger">
  <div class="box-header with-border">
    <a href="{{ url($route) }}"><button type="button" class="btn btn-default text-red pull-right"><i class="fa fa-arrow-left"></i> Kembali</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    {!! $datatable->table() !!}
  </div>
  <!-- /.box-body -->
</div>

@stop

@push('js')
{!! $datatable->scripts() !!}

<script type="text/javascript">
</script>
@endpush
