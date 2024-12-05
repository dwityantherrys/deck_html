@extends('layouts.admin')

@section('title', 'Payment Method')

@section('content_header')
<h1> Payment Method</h1>
@stop

@section('content')

<div class="box box-danger">
  <div class="box-header with-border">
      <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Payment Method</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <table id="raw-table" class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Image</th>
          <th>Name</th>
          <th>Code</th>
          <th>Channel</th>
          <th>Available at</th>
          <th>Active ?</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($paymentMethods as $index => $paymentMethod)
        <tr>
          <td>{{ $index+1 }}</td>
          <td>
            <img src="{{ $paymentMethod->image_url }}" width="100" heigth="100" alt="image payment method">
          </td>
          <td>{{ $paymentMethod->name }}</td>
          <td>{{ $paymentMethod->code }}</td>
          <td>{{ $paymentMethod->channel }}</td>
          <td>{{ $availableOptions[$paymentMethod->available_at] }}</td>
          <td>
            @if($paymentMethod->is_active)
            <small class="label bg-green">Yes</small>
            @else
            <small class="label bg-red">No</small>
            @endif
          </td>
          <td>
            <div class="btn-group">
              <a class="btn btn-default" href="{{ url($route . '/' . $paymentMethod->id . '/edit') }}"><i class="fa fa-pencil"></i></a>
              <button 
              class="confirmation-delete btn btn-default text-red"
              data-target="{{ url($route . '/' . $paymentMethod->id) }}"
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
} );
</script>
@endpush
