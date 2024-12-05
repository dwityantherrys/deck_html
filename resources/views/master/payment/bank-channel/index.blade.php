@extends('layouts.admin')

@section('title', 'Master Bank Channel')

@section('content_header')
<h1> Bank Channels</h1>
@stop

@section('content')

<div class="box box-danger">
  <div class="box-header with-border">
    <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Bank Channel</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <table id="bank-table" class="table">
      <thead>
        <tr>
          <th>No</th>
          <th>Name</th>
          <th>Rekening Name</th>
          <th>Rekening Number</th>
          <th>Active ?</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($bankChannels as $index => $bankChannel)
        <tr>
          <td>{{$index+1}}</td>
          <td>{{$bankChannel->name}}</td>
          <td>{{$bankChannel->rekening_name}}</td>
          <td>{{$bankChannel->rekening_number}}</td>
          <td>
            @if($bankChannel->is_active)
            <small class="label bg-green">Yes</small>
            @else
            <small class="label bg-red">No</small>
            @endif
          </td>
          <td>
            <div class="btn-group">
              <a class="btn btn-default" href="{{ url($route . '/' . $bankChannel->id . '/edit') }}"><i class="fa fa-pencil"></i></a>
              <button 
              class="confirmation-delete btn btn-default text-red"
              data-target="{{ url($route . '/' . $bankChannel->id) }}"
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
  $('#bank-table').DataTable();
} );
</script>
@endpush
