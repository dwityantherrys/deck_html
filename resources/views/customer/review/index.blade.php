@extends('layouts.admin')

@section('title', 'Review')

@section('content_header')
<h1> Customer Review</h1>
@stop

@section('content')
<div class="box box-danger">
  <!-- /.box-header -->
  <div class="box-body">
    {!! $datatable->table() !!}
  </div>
  <!-- /.box-body -->
</div>

@stop

@push('js')
{!! $datatable->scripts() !!}
</script>
@endpush
