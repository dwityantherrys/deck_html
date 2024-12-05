@extends('adminlte::page')

@section('title', 'Balance Sheet')

@section('content_header')
  <h1>Edit Balance Sheet Template: <b>{{ $template->pos }}</b></h1>
@endsection

@section('content')
  @inject('coa', "App\Models\Finance\COA")
  <div class="box box-danger">
    <form action="{{ route("finance.template.balance-sheet.update", $template->id) }}" method="post">
      @csrf
      @method("put")
      <div class="box-body">
        <div class="form-group row">
          <div class="col-md-2">
            <label>Nama Pos</label>
          </div>
          <div class="col-md-10">
            <input type="text" name="pos" class="form-control" placeholder="Masukkan nama pos (Biaya Operasional, Penjualan Kotor, dll)" value="{{ $template->pos }}" required>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label>Posisi</label>
          </div>
          <div class="col-md-10">
            <select class="form-control" name="posisi" required>
              <option value="" selected disabled hidden>Pilih Posisi Akun</option>
              <option value="1" {{ $template->posisi == "1" ? "selected" : null }}>Aset</option>
              <option value="2" {{ $template->posisi == "2" ? "selected" : null }}>Liabilitas</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label>Akun Penambah</label>
          </div>
          <div class="col-md-10">
            <select class="form-control select2" name="akun_penambah[]" data-placeholder="Masukkan Akun Penambah" multiple style="width:100%;">
              @isset($template->akun_penambah)
                @foreach (explode(",", $template->akun_penambah) as $kode_akun)
                  <option value="{{ $kode_akun }}" selected>{{ $coa->find($kode_akun)->nama_akun }}</option>
                @endforeach
              @endisset
            </select>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-2">
            <label>Akun Pengurang</label>
          </div>
          <div class="col-md-10">
            <select class="form-control select2" name="akun_pengurang[]" data-placeholder="Masukkan Akun Pengurang" multiple style="width:100%;">
              @isset($template->akun_pengurang)
                @foreach (explode(",", $template->akun_pengurang) as $kode_akun)
                  <option value="{{ $kode_akun }}" selected>{{ $coa->find($kode_akun)->nama_akun }}</option>
                @endforeach
              @endisset
            </select>
          </div>
        </div>
      </div>
      <div class="box-footer text-center">
        <button type="submit" class="btn btn-primary">Tambah</button>
        <a href="{{ route("finance.template.balance-sheet.index") }}" class="btn btn-default">Kembali</a>
      </div>
    </form>
  </div>
@endsection

@section('js')
  <script type="text/javascript">
  $(document).ready(function() {
    $(".select2").select2({
      closeOnSelect: false,
      ajax: {
        url: "{{ url('/finance/coa/ajax/getCOAAjax') }}",
        type: "post",
        data: function(params) {
          return {
            _token: "{{ csrf_token() }}",
            search: $.trim(params.term)
          }
        },
        processResults: function (data) {
          return {
            results:  $.map(data, function (item) {
              return {
                text: "(" + item.kode_akun + ") " + item.nama_akun,
                id: item.id
              }
            })
          };
        },
      },
      delay: 500
    });
  })
  </script>
@endsection
