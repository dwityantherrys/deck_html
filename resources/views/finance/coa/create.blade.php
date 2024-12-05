@extends('layouts.admin')

@section('title', ' Chart of Accounts')

@section('content_header')
  <h1>New Accounts</h1>
@endsection

@section('content')
  <div class="box box-danger">
    <form action="{{ route("coa.store") }}" method="post">
      <div class="box-body">
        @csrf
        <div class="form-group row">
          <div class="col-md-2">
            <label for="kode_akun">Kode Akun (<span class="text-red">*</span>)</label>
          </div>
          <div class="col-md-10">
            <input type="text" class="form-control" name="kode_akun" id="kode_akun" required>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label for="nama_akun">Nama Akun (<span class="text-red">*</span>)</label>
          </div>
          <div class="col-md-10">
            <input type="text" class="form-control" name="nama_akun" id="nama_akun" required>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label for="kode_akun_parent">Kategori Akun</label>
          </div>
          <div class="col-md-10">
            <select class="form-control" name="kode_akun_parent" id="kode_akun_parent">
              <option value=""></option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label for="pos">Pos Akun (<span class="text-red">*</span>)</label>
          </div>
          <div class="col-md-10">
            <select class="form-control" name="pos" id="pos" required>
              <option value="" selected disabled hidden>Pilih POS</option>
              <option value="1">Debet</option>
              <option value="2">Kredit</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label>Laporan Keuangan</label>
          </div>
          <div class="col-md-10">
            <label class="radio-inline"><input type="radio" name="lk" value="neraca"/> Neraca</label>
            <label class="radio-inline"><input type="radio" name="lk" value="labarugi"/> Laba Rugi</label>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label>Nama Pos Kategori Laporan Keuangan</label>
          </div>
          <div class="col-md-10">
            <select class="form-control" name="lk_kategori" id="lk_kategori">
              <option></option>
              @foreach ($lk_kategori as $key => $value)
                <option value="{{ $value }}">{{ $value }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label>Nama Pos Laporan Keuangan</label>
          </div>
          <div class="col-md-10">
            <select class="form-control" name="lk_pos" id="lk_pos">
              <option></option>
              @foreach ($lk_pos as $key => $value)
                <option value="{{ $value }}">{{ $value }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label for="saldo">Saldo Akun (<span class="text-red">*</span>)</label>
          </div>
          <div class="col-md-10">
            <input type="number" class="form-control" name="saldo" id="saldo" required>
          </div>
        </div>
      </div>
      <div class="box-footer">
        <input type="submit" class="btn btn-dropbox" name="submit" value="save">
        <a href="{{ route("coa.index") }}" class="btn btn-default">kembali</a>
      </div>
    </form>
  </div>
@endsection

@section('js')
  <script type="text/javascript">
  $(document).ready(function() {
    $("#kode_akun_parent").select2({
      placeholder: "Pilih Kategori Akun",
      ajax: {
        url: "{{ url("/finance/coa/ajax/getCOAAjax") }}",
        type: "post",
        data: function(params) {
          return {
            _token: "{{ csrf_token() }}",
            search: params.term
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
    });
    $("#lk_pos").select2({
      tags: true,
      placeholder: "Masukkan nama pos"
    })
    $("#lk_kategori").select2({
      tags: true,
      placeholder: "Masukkan nama pos kategori"
    })
  })
  </script>
@endsection
