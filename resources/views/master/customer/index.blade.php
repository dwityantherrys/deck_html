@extends('layouts.admin')

@section('title', 'Customer / Vendor')

@section('content_header')
<h1> Customer / Vendor</h1>
@stop

@section('content')

<div class="box box-danger">
  <div class="box-header with-border">
      <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Customer / Vendor</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <table id="raw-table" class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Image</th>
          <th>Name</th>
          <th>Phone</th>
          <th>Bank Name</th>
          <th>Bank Account</th>
          <th>Active ?</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($profiles as $index => $profile)
        <tr>
          <td>{{ $index+1 }}</td>
          <td>
            <img src="{{ $profile->image_url }}" width="100" heigth="100" alt="image category">
          </td>
          <td>{{ $profile->name }}</td>
          <td>{{ $profile->phone }}</td>
          <td>{{ $profile->bank_name }}</td>
          <td>{{ $profile->bank_account }}</td>
          <td>
            @if($profile->is_active)
            <small class="label bg-green">Yes</small>
            @else
            <small class="label bg-red">No</small>
            @endif
          </td>
          <td>
            <div class="btn-group">
              <a class="btn btn-default" href="{{ url($route . '/' . $profile->id . '/edit') }}"><i class="fa fa-pencil"></i></a>
              <button 
              class="confirmation-delete btn btn-default text-red"
              data-target="{{ url($route . '/' . $profile->id) }}"
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
