@extends('layouts.admin')

@section('title', ' Chart of Accounts')

@section('content_header')
  <h1>Chart of Accounts</h1>
@endsection

@section('content')
  <div class="box box-danger">
    <div class="box-body">
      <div class="form-group">
        <a href="{{ route("coa.create") }}" class="btn btn-primary"><i class="fa fa-plus"></i> New Accounts</a>
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#importModal"><i class="fa fa-plus"></i> Import Accounts</button>
      </div>
      <div class="table-responsive">
        <table class="table dataTable no-footer" id="table_accounts">
          <thead>
            <tr>
              <th>Kode Akun</th>
              <th>Nama Akun</th>
              <th>Parent</th>
              <th>Pos</th>
              <th>Saldo</th>
              <th>Action</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form action="{{ url("/finance/coa/import") }}" method="post" enctype="multipart/form-data">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="importModalLabel">Import Accounts</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="col-12">
              <div class="row">
                <div class="form-group">
                  <div class="col-md-3">
                    <label>Pilih File Import</label>
                  </div>
                  <div class="col-md-9">
                    <input type="file" name="importfile" class="form-control" required>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal fade" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="modalDeleteLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDeleteLabel">Delete Account</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Apakah anda yakin untuk menghapus akun: <b><span id="delete_nama_akun"></span></b>
        </div>
        <div class="modal-footer">
          <form method="post" id="delete_form">
            @csrf
            @method("DELETE")
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@push("js")
  <script type="text/javascript">
  $(document).ready(function() {
    $("#table_accounts").dataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ url("/finance/coa/ajax/listCOA") }}",
        type: 'post',
        data: {
          _token: "{{ csrf_token() }}",
        },
      },
      columns: [
        { data: 'kode_akun', name: 'kode_akun' },
        { data: 'nama_akun', name: 'nama_akun' },
        { data: 'kode_akun_parent', name: 'kode_akun_parent' },
        { data: 'pos', name: 'pos' },
        { data: 'saldo', name: 'saldo' },
        { data: "action", name: "action" },
      ]
    });
    $(document).on("click", ".btn-delete", function() {
      var id = $(this).data("id");
      $.ajax({
        url: "{{ url("/finance/coa/") }}/" + id,
        type: "get",
        success: function(data) {
          $("#delete_nama_akun").html(data.nama_akun);
          $("#delete_form").attr("action", "{{ url("/finance/coa") }}/" + data.id);
          $("#modalDelete").modal("show");
        }
      });
    });
  });
</script>
@endpush
