@extends('layouts.admin')

@section('title', 'Shipping Instruction')

@section('css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
@endsection

@section('content_header')
  <h1> Shipping Instruction</h1>
@stop

@section('content')
  <div class="box box-danger">
    <div class="box-header with-border">
      <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Shipping Instruction</button></a>
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
    "filter_ship_no" => ["input", "text", "form-control", "Sales Instruction No", "INXXXXX"],
    "filter_so_no" => ["input", "text", "form-control", "Sales Order No", "SOXXXXX"],
    "filter_customer" => ["input", "text", "form-control", "Customer", "Masukkan nama Customer"],
    "filter_status" => ["select", [
      "0" => "Not Release",
      "1" => "Release",
    ], "form-control", "Progress Status", "Choose Status"],
    "filter_method" => ["select", [
      "1" => "Pickup",
      "2" => "Pickup Point",
      "3" => "Delivery",
    ], "form-control", "Shipping Method", "Choose Shipping Method"]
    ]])
    @slot("form_action")
      {{ route("shipping-instruction.show", round(microtime(true) * 1000)) }}
    @endslot

    @slot("form_method")
      get
    @endslot

    @slot("route_method")
      GET
    @endslot

    @slot("index")
      {{ route("shipping-instruction.index") }}
    @endslot
  @endcomponent

    @component('components.modal')
      @slot('title') Edit Shipping Instruction @endslot

        @slot('form')
          @component($routeView . '._form', [
            'model' => $model,
            'route' => $route,
            'shippingMethods' => $shippingMethods
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
        $(document).ready(function() {
          $(document).on('change', '.status-select', function() {
              var status = $(this).val();
              var id = $(this).data('id');
              var url = '/sales/shipping-instruction/' + id + '/update-status';
              
              $.ajax({
                  url: url,
                  type: 'POST',
                  data: {
                      _token: $('meta[name="csrf-token"]').attr('content'),
                      received: status
                  },
                  success: function(response) {
                      alert(response.message);
                      // Optionally, refresh the DataTable or update the UI
                  },
                  error: function(xhr) {
                      alert('Failed to update status: ' + xhr.responseJSON.message);
                  }
              });
          });
      });

        </script>
      @endpush
