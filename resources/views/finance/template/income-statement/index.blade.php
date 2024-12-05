@extends('adminlte::page')

@section('title', 'Template Income Statement')

@section('content_header')
  <h1>Income Statement Template</h1>
@endsection

@section('content')
  <div class="box box-danger">
    <div class="box-body">
      <div class="form-group">
        <a href="{{ route("finance.template.income-statement.create") }}" class="btn btn-primary"><i class="fa fa-plus"></i> New Template</a>
      </div>
      <div class="table-responsive">
        <table class="table dataTable no-footer">
          <thead>
            <tr>
              <th style="width: 10%">ID</th>
              <th>Pos Akun</th>
              <th style="width: 10%">Action</th>
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
      $(".table").dataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: "{{ url("/finance/template/income-statement/ajaxDataTable") }}",
          type: 'post',
          data: {
            _token: "{{ csrf_token() }}",
          },
        },
        columns: [
          { data: 'id', name: 'id' },
          { data: 'pos', name: 'pos' },
          { data: 'action', name: 'action' },
        ]
      });
    });
  </script>
@endsection
