@extends('layouts.admin')

@section('title', 'Master District')

@section('content_header')
<h1> District</h1>
@stop

@section('content')

<div class="box box-danger">
  <div class="box-header with-border">
      <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New District</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <table id="districts-table" class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Name</th>
          <th>City</th>
          <th>Province</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($districts as $index => $district)
        <tr>
          <td>{{$index+1}}</td>
          <td>{{$district->name}}</td>
          <td>{{$district->city->name}}</td>
          <td>{{$district->city->province->name}}</td>
          <td>
            <div class="btn-group">
              <a class="btn btn-default" href="{{ url($route . '/' . $district->id . '/edit') }}"><i class="fa fa-pencil"></i></a>
              <button 
              class="confirmation-delete btn btn-default text-red"
              data-target="{{ url($route . '/' . $district->id) }}"
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
  $('#districts-table').DataTable();
} );
</script>
@endpush
