@extends('adminlte::page')

@section('title', 'Buat Voucher')

@section('content_header')
  <h1>New Voucher</h1>
@endsection

@section('content')
  <div class="box box-danger">
    <form action="{{ route("voucher.store") }}" method="post">
      @csrf
      <div class="box-body">
        <div class="form-group row">
          <div class="col-md-3">
            <label>Masukkan Keterangan Transaksi</label>
          </div>
          <div class="col-md-9">
            <textarea name="keterangan" class="form-control" placeholder="Masukkan Keterangan" required></textarea>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-3">
            <label>Penanggungjawab</label>
          </div>
          <div class="col-md-9">
            <input type="text" name="nama_pic" class="form-control" placeholder="Masukkan Nama Penanggungjawab" required/>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-3">
            <label>Tanggal Transaksi</label>
          </div>
          <div class="col-md-9">
            <input type="date" name="tanggal_transaksi" class="form-control" required/>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-3">
            <label>Jenis Biaya</label>
          </div>
          <div class="col-md-9">
            <select class="form-control" name="kode_biaya" id="kode_biaya" style="width: 100%" required>
              <option></option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-3">
            <label>Sumber Pembiayaan</label>
          </div>
          <div class="col-md-9">
            <select class="form-control" name="sumber_pembiayaan" id="sumber_pembiayaan" style="width: 100%" required>
              <option></option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-3">
            <label>Nominal Biaya</label>
          </div>
          <div class="col-md-9">
            <input type="number" name="nominal_biaya" class="form-control" min="0" placeholder="Masukkan nominal biaya" required>
          </div>
        </div>
      </div>
      <div class="box-footer">
        <button type="submit" class="btn btn-dropbox">Simpan</button>
        <a href="{{ route("voucher.index") }}" class="btn btn-default">kembali</a>
      </div>
    </form>
  </div>
@endsection

@section('js')
  <script type="text/javascript">
  $(document).ready(function() {
    $("#kode_biaya").select2({
      placeholder: "Pilih jenis biaya",
      ajax: {
        url: "{{ url('/finance/voucher/ajaxVoucher') }}",
        type: "post",
        data: function(params) {
          return {
            _token: "{{ csrf_token() }}",
            type: "biaya",
            search: $.trim(params.term)
          }
        },
        processResults: function (data) {
          return {
            results:  $.map(data, function (item) {
              return {
                text: "(" + item.kode_akun + ") " + item.nama_akun,
                id: item.kode_akun
              }
            })
          };
        },
      }
    })
    $("#sumber_pembiayaan").select2({
      placeholder: "Pilih sumber pembiayaan",
      ajax: {
        url: "{{ url('/finance/voucher/ajaxVoucher') }}",
        type: "post",
        data: {
          _token: "{{ csrf_token() }}",
          type: "sumber_pembiayaan"
        },
        processResults: function (data) {
          return {
            results:  $.map(data, function (item) {
              return {
                text: "(" + item.kode_akun + ") " + item.nama_akun,
                id: item.kode_akun
              }
            })
          };
        },
      }
    })
  })
  </script>
@endsection
