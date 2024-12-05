@extends('adminlte::page')

@section('title', 'Sales Target')

@section('content_header')
  <h1>Sales Target</h1>
@endsection

@section('content')
  <div class="box box-danger">
    <div class="box-body">
      <div class="form-group">
        <a href="{{ route("sales-target.create") }}" class="btn btn-primary"><i class="fa fa-plus"></i> New Sales Target</a>
      </div>
      <div class="table-responsive">
        <table class="table dataTable no-footer" id="table">
          <thead>
            <tr>
              <th>Periode</th>
              <th>Target</th>
              <th style="width: 20%">Action</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
@endsection

@section('js')
  <script type="text/javascript">
  $(document).ready(function() {
    $("#table").dataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ url("/master/sales-target/ajaxDataTable") }}",
        type: 'post',
        data: {
          _token: "{{ csrf_token() }}",
        },
      },
      columns: [
        { data: 'periode', name: 'periode' },
        { data: 'target', name: 'target' },
        { data: "action", name: "action" },
      ]
    });
  })
  </script>
@endsection
