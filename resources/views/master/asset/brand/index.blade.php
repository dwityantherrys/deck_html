@extends('layouts.admin')

@section('title', 'Master Province')

@section('content_header')
<h1> Brand</h1>
@stop

@section('content')

<div class="box box-danger">
  <div class="box-header with-border">
      <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Brand</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <table id="provinces-table" class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Name</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($brands as $index => $brand)
        <tr>
          <td>{{$index+1}}</td>
          <td>{{$brand->name}}</td>
          <td>
            <div class="btn-group">
              <a class="btn btn-default" href="{{ url($route . '/' . $brand->id . '/edit') }}"><i class="fa fa-pencil"></i></a>
              <button 
              class="confirmation-delete btn btn-default text-red"
              data-target="{{ url($route . '/' . $brand->id) }}"
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
  $('#provinces-table').DataTable();
} );
</script>
@endpush
