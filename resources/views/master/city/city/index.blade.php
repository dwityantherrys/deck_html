@extends('layouts.admin')

@section('title', 'Master City')

@section('content_header')
<h1> City</h1>
@stop



@section('content')

<div class="box box-danger">
  <div class="box-header with-border">
      <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New City</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <table id="cities-table" class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Name</th>
          <th>Province</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($cities as $index => $city)
        <tr>
          <td>{{$index+1}}</td>
          <td>{{$city->name}}</td>
          <td>{{$city->province->name}}</td>
          <td>
            <div class="btn-group">
              <a class="btn btn-default" href="{{ url($route . '/' . $city->id . '/edit') }}"><i class="fa fa-pencil"></i></a>
              <button 
              class="confirmation-delete btn btn-default text-red"
              data-target="{{ url($route . '/' . $city->id) }}"
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
  <!-- /.box-body ---->
</div>
@stop

@push('js')
<script type="text/javascript">
$(document).ready(function() {
  $('#cities-table').DataTable();
} );
</script>
@endpush
