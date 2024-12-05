@extends('layouts.admin')

@section('title', 'Term of Service')

@section('content_header')
<h1> Term of Service</h1>
@stop

@section('content')

<div class="box box-danger">
  <!-- /.box-header -->
  <div class="box-body">
    <table id="raw-table" class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Title</th>
          <th>Active ?</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($termOfServices as $index => $tos)
        <tr>
          <td>{{ $index+1 }}</td>
          <td>{{ $tos->title }}</td>
          <td>
            @if($tos->is_active)
            <small class="label bg-green">Yes</small>
            @else
            <small class="label bg-red">No</small>
            @endif
          </td>
          <td>
            <div class="btn-group">
              <a class="btn btn-default" href="{{ url($route . '/' . $tos->id . '/edit') }}"><i class="fa fa-pencil"></i></a>
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
