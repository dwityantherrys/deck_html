@extends('layouts.admin')

@section('title', ' Chart of Accounts')

@section('content_header')
<h1>Edit Accounts: {{ $coa->nama_akun }}</h1>
@endsection

@section('content')
  <div class="box box-danger">
    <form action="{{ route("coa.update", $coa->id) }}" method="post">
      <div class="box-body">
        @csrf
        @method("PUT")
        <div class="form-group row">
          <div class="col-md-2">
            <label for="kode_akun">Kode Akun</label>
          </div>
          <div class="col-md-10">
            <input type="text" class="form-control" name="kode_akun" id="kode_akun" value="{{ $coa->kode_akun }}" required>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label for="nama_akun">Nama Akun</label>
          </div>
          <div class="col-md-10">
            <input type="text" class="form-control" name="nama_akun" id="nama_akun" value="{{ $coa->nama_akun }}" required>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label for="kode_akun_parent">Kategori Akun</label>
          </div>
          <div class="col-md-10">
            <select class="form-control" name="kode_akun_parent" id="kode_akun_parent">
              <option></option>
              <option value="{{ $coa->kode_akun_parent }}" selected>({{ $coa->kode_akun_parent }}) {{ $coa->parent() }}</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label for="pos">Pos Akun</label>
          </div>
          <div class="col-md-10">
            <select class="form-control" name="pos" id="pos" required>
              <option value="" selected disabled hidden>Pilih POS</option>
              <option value="1" {{ $coa->pos == "1" ? "selected" : null }}>Debet</option>
              <option value="2" {{ $coa->pos == "2" ? "selected" : null }}>Kredit</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label>Laporan Keuangan</label>
          </div>
          <div class="col-md-10">
            <label class="radio-inline"><input type="radio" name="lk" value="neraca" {{ isset($coa->lk) && $coa->lk == "neraca" ? "checked" : null }}/> Neraca</label>
            <label class="radio-inline"><input type="radio" name="lk" value="labarugi" {{ isset($coa->lk) && $coa->lk == "labarugi" ? "checked" : null }}/> Laba Rugi</label>
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
                <option value="{{ $value }}" {{ isset($coa->lk_kategori) && $coa->lk_kategori == $value ? "selected" : null }}>{{ $value }}</option>
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
                <option value="{{ $value }}" {{ isset($coa->lk_pos) && $coa->lk_pos == $value ? "selected" : null }}>{{ $value }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label for="saldo">Saldo Akun</label>
          </div>
          <div class="col-md-10">
            <input type="text" class="form-control" name="saldo" id="saldo" value="{{ $coa->saldo }}" required>
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
