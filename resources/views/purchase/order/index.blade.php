@extends('layouts.admin')

@section('title', 'Purchase Order')

@section('css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
@endsection

@section('content_header')
<h1> Purchase Order</h1>
@stop

@section('content')
<div class="box box-danger">
  <div class="box-header with-border">
      <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Purchase Order</button></a>
      <button type="button" class="btn btn-info" data-target="#filter" data-toggle="modal">Filter</button>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    {!! $datatable->table() !!}
  </div>
  <!-- /.box-body -->
</div>

@component("components.search", ["form" => [
  "filter_date" => ["input", "date", "form-control", "Date"],
  "filter_po_no" => ["input", "text", "form-control", "Purchase Order No", "POXXXXX"],
  "filter_pr_no" => ["input", "text", "form-control", "Purchase Request No", "PRXXXXX"],
  "filter_customer" => ["input", "text", "form-control", "Customer", "Masukkan nama Customer"],
  "filter_status" => ["select", [
    "0" => "On Request",
    "1" => "On Process",
    "2" => "Finish",
    "3" => "Cancel",
  ], "form-control", "Progress Status", "Choose Status"],
  ]])
  @slot("form_action")
    {{ route("purchase.order.show", round(microtime(true) * 1000)) }}
  @endslot

  @slot("form_method")
    get
  @endslot

  @slot("route_method")
    GET
  @endslot

  @slot("index")
    {{ route("purchase.order.index") }}
  @endslot
@endcomponent

@component('components.modal')
  @slot('title') Edit Purchase Order @endslot

  @slot('form')
    @component($routeView . '._form', [
        'model' => $model,
        'route' => $route,
    ]) @endcomponent
  @endslot
@endcomponent

@stop

@push('js')
{!! $datatable->scripts() !!}

<script type="text/javascript">
$(document).ready(function() {
  $(".input-daterange").datepicker();
  @if(request()->print && (session()->has('notif')  && session()->get('notif')['code'] == 'success'))
    window.location.href = "{{ url($route . '/' . request()->print. '/print') }}"

    $.alert({
        title: "Print Success",
        content: "tunggu sampai indicator loading pada browser hilang. lalu click ok",
        buttons: {
            ok: function() {
                window.stop();
                window.location.replace("{{ url($route) }}");
            }
        }
    });
  @endif

  @if($errors->any())
    $("#ajax-form").modal('show');
  @endif
} );
</script>
@endpush
