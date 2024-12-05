@extends('layouts.admin')

@section('title', 'Application Paylater')

@section('css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
@endsection

@section('content_header')
<h1> Application Paylater</h1>
@stop

@section('content')
<div class="box box-danger">
  <div class="box-header with-border">
      <!-- <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Application Paylater</button></a> -->
      <button type="button" class="btn btn-info" data-target="#filter" data-toggle="modal">Filter</button>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    {!! $datatable->table() !!}
  </div>
  <!-- /.box-body -->
</div>

@stop

@component("components.search", ["form" => [
  "filter_customer_name" => ["input", "text", "form-control", "Customer Name", "Masukkan nama customer"],
  "filter_date_app" => ["input", "daterange", "form-control", "Date Application"],
  "filter_status_app" => ["select", [
    "0" => "Pending",
    "1" => "Accept",
    "2" => "Decline",
    "3" => "Belum Daftar"
  ], "form-control", "Status Application", "Choose Status"],
  "filter_date_valid" => ["input", "daterange", "form-control", "Date Validation"],
  ]])
  @slot("form_action")
    {{ url($route, round(microtime(true) * 1000)) }}
  @endslot

  @slot("form_method")
    get
  @endslot

  @slot("route_method")
    GET
  @endslot

  @slot("index")
    {{ route("quotation.index") }}
  @endslot
@endcomponent

@push('js')
{!! $datatable->scripts() !!}
<script src="{{ asset('vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function() {
  $(".input-daterange").datepicker();
});
</script>
@endpush
