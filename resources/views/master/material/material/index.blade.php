@extends('layouts.admin')

@section('title', 'Material')

@section('content_header')
<h1> Material</h1>
@stop

@section('content')

<div class="box box-danger">
  <div class="box-header with-border">
      <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Material</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <table id="raw-table" class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Name</th>
          <th>Description</th>
          <th>Active ?</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($materials as $index => $material)
        <tr>
          <td>{{ $index+1 }}</td>
          <td>{{ $material->name }}</td>
          <td>{{ $material->description }}</td>
          <td>
            @if($material->is_active)
            <small class="label bg-green">Yes</small>
            @else
            <small class="label bg-red">No</small>
            @endif
          </td>
          <td>
            <div class="btn-group">
              <a class="btn btn-default" href="{{ url($route . '/' . $material->id . '/edit') }}"><i class="fa fa-pencil"></i></a>
              <button 
              class="confirmation-delete btn btn-default text-red"
              data-target="{{ url($route . '/' . $material->id) }}"
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
