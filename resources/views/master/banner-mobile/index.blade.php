@extends('layouts.admin')

@section('title', 'Master Banners')

@section('content_header')
<h1> Banners</h1>
@stop

@section('content')

<div class="box box-danger">
  <div class="box-header with-border">
    <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Banner</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <table id="banners-table" class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Name</th>
          <th>Description</th>
          <th>Image</th>
          <th>Active ?</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($banners as $index => $banner)
        <tr>
          <td>{{$index+1}}</td>
          <td>{{$banner->name}}</td>
          <td>{{$banner->description}}</td>
          <td><img src="{{ $banner->image_url }}" width="100" height="100" /></td>
          <td>
            @if($banner->is_active)
            <small class="label bg-green">Yes</small>
            @else
            <small class="label bg-red">No</small>
            @endif
          </td>
          <td>
            <div class="btn-group">
              <a class="btn btn-default" href="{{ url($route . '/' . $banner->id . '/edit') }}"><i class="fa fa-pencil"></i></a>
              <button 
              class="confirmation-delete btn btn-default text-red"
              data-target="{{ url($route . '/' . $banner->id) }}"
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
  $('#banners-table').DataTable();
} );
</script>
@endpush
