@extends('layouts.admin')

@section('title', 'Inventory Balance')

@section('content_header')
<h1> Inventory Balance</h1>
@stop

@section('content')
<div class="box box-danger">
  <div class="box-header with-border">
    <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Inventory Adjustment</button></a>
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
