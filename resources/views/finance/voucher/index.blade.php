@extends('adminlte::page')

@section('title', 'Voucher')

@section('content')
  <div class="box box-danger">
    <div class="box-body">
      <div class="form-group">
        <a href="{{ route("voucher.create") }}" class="btn btn-primary"><i class="fa fa-plus"></i> New Voucher</a>
      </div>
      <div class="table-responsive">
        <table class="table dataTable no-footer" id="table_vouchers">
          <thead>
            <tr>
              <th>No.</th>
              <th>Keterangan</th>
              <th>Nama Penanggungjawab</th>
              <th>Tanggal Transaksi</th>
              <th>Aksi</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="showModal" tabindex="-1" role="dialog" aria-labelledby="showModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="showModalLabel">Detail Voucher</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group row">
            <div class="col-md-3">
              <label>Masukkan Keterangan Transaksi</label>
            </div>
            <div class="col-md-9">
              <textarea name="keterangan" class="form-control" placeholder="Masukkan Keterangan" readonly></textarea>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-3">
              <label>Penanggungjawab</label>
            </div>
            <div class="col-md-9">
              <input type="text" name="nama_pic" class="form-control" placeholder="Masukkan Nama Penanggungjawab" readonly/>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-3">
              <label>Tanggal Transaksi</label>
            </div>
            <div class="col-md-9">
              <input type="date" name="tanggal_transaksi" class="form-control" readonly/>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-3">
              <label>Jenis Biaya</label>
            </div>
            <div class="col-md-9">
              <input type="text" name="kode_biaya" class="form-control" readonly/>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-3">
              <label>Sumber Pembiayaan</label>
            </div>
            <div class="col-md-9">
              <input type="text" name="sumber_pembiayaan" class="form-control" readonly/>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-3">
              <label>Nominal Biaya</label>
            </div>
            <div class="col-md-9">
              <input type="text" name="nominal_biaya" class="form-control" placeholder="Masukkan nominal biaya" readonly>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('js')
  <script type="text/javascript">
  $(document).ready(function() {
    $("#table_vouchers").dataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ url("/finance/voucher/voucherTable") }}",
        type: 'post',
        data: {
          _token: "{{ csrf_token() }}",
        },
      },
      columns: [
        { data: 'id', name: 'id' },
        { data: 'keterangan', name: 'keterangan' },
        { data: 'nama_pic', name: 'nama_pic' },
        { data: 'tanggal_transaksi', name: 'tanggal_transaksi' },
        { data: "action", name: "action" },
      ]
    });

    $(document).on("click", ".btn-modal", function() {
      $.ajax({
        url: "{{ url("/finance/voucher")}}/" + $(this).data("id"),
        success: function(data) {
          $("textarea[name=keterangan]").html(data.keterangan);
          $("input[name=nama_pic]").val(data.nama_pic);
          $("input[name=tanggal_transaksi]").val(data.tanggal_transaksi);
          $("input[name=kode_biaya]").val(data.journal[0].coa.nama_akun);
          $("input[name=sumber_pembiayaan]").val(data.journal[1].coa.nama_akun);
          $("input[name=nominal_biaya]").val("Rp. " + format(data.journal[0].nominal));
        }
      });
      $("#showModal").modal("show")
    })

    var format = function(num){
      var str = num.toString().replace("", ""), parts = false, output = [], i = 1, formatted = null;
      if(str.indexOf(".") > 0) {
        parts = str.split(".");
        str = parts[0];
      }
      str = str.split("").reverse();
      for(var j = 0, len = str.length; j < len; j++) {
        if(str[j] != ",") {
          output.push(str[j]);
          if(i%3 == 0 && j < (len - 1)) {
            output.push(",");
          }
          i++;
        }
      }
      formatted = output.reverse().join("");
      return("" + formatted + ((parts) ? "." + parts[1].substr(0, 2) : ""));
    };
  })
  </script>
@endsection
