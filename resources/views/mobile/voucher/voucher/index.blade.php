@extends('layouts.admin')

@section('title', 'Voucher')

@section('content_header')
<h1> Voucher</h1>
@stop

@section('content')

<div class="box box-danger">
  <div class="box-header with-border">
      <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Voucher</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <table id="raw-table" class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Name</th>
          <th>Code</th>
          <th>Value</th>
          <th>Active ?</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($vouchers as $index => $voucher)
        <tr>
          <td>{{ $index+1 }}</td>
          <td>{{ $voucher->name }}</td>
          <td>{{ $voucher->code }}</td>
          <td>{{ $voucher->value }}%</td>
          <td>
            @if($voucher->is_active)
            <small class="label bg-green">Yes</small>
            @else
            <small class="label bg-red">No</small>
            @endif
          </td>
          <td>
            <div class="btn-group">
              <a class="btn btn-default" href="{{ url($route . '/' . $voucher->id . '/edit') }}"><i class="fa fa-pencil"></i></a>
              <button 
              class="confirmation-delete btn btn-default text-red"
              data-target="{{ url($route . '/' . $voucher->id) }}"
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
