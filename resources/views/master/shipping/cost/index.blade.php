@extends('layouts.admin')

@section('title', 'Shipping Cost')

@section('content_header')
<h1> Shipping Cost</h1>
@stop

@section('content')

<div class="box box-danger">
  <div class="box-header with-border">
      <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Shipping Cost</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <table id="raw-table" class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Min. Length</th>
          <th>Max. Length</th>
          <th>Min. Weight</th>
          <th>Max. Weight</th>
          <th>Charge / km</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($shippingCosts as $index => $shippingCost)
        <tr>
          <td>{{ $index+1 }}</td>
          <td>{{ Rupiah::format($shippingCost->min_length) }}</td>
          <td>{{ Rupiah::format($shippingCost->max_length) }}</td>
          <td>{{ Rupiah::format($shippingCost->min_weight) }}</td>
          <td>{{ Rupiah::format($shippingCost->max_weight) }}</td>
          <td>Rp. {{ Rupiah::format($shippingCost->charge_per_km) }}</td>
          <td>
            <div class="btn-group">
              <a class="btn btn-default" href="{{ url($route . '/' . $shippingCost->id . '/edit') }}"><i class="fa fa-pencil"></i></a>
              <button 
              class="confirmation-delete btn btn-default text-red"
              data-target="{{ url($route . '/' . $shippingCost->id) }}"
              data-token={{ csrf_token() }}
              >
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <!-- /.box-body -->
</div>
@stop

@push('js')
<script type="text/javascript">
$(document).ready(function() {
  $('#raw-table').DataTable();
  $('[data-toggle="tooltip"]').tooltip();
} );
</script>
@endpush
