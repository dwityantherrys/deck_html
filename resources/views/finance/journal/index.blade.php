@extends('layouts.admin')

@section('title', 'Journal')

@section('content_header')
  <h1>Journal</h1>
@endsection

@section('css')
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endsection

@section('content')
  <div class="box box-danger">
    <form action="{{ route("journal.show", round(microtime(true))) }}" method="get">
      <div class="box-body">
        <div class="form-group text-center">
          <h3>Pilih Periode</h3>
        </div>
        <div class="form-group">
          <div class="input-group input-daterange col-md-6" style="margin-left: auto; margin-right: auto;">
            <input type="text" name="tanggal_awal" class="form-control" autocomplete="off">
            <div class="input-group-addon">-</div>
            <input type="text" name="tanggal_akhir" class="form-control" autocomplete="off">
          </div>
        </div>
        <div class="form-group text-center">
          <button type="submit" class="btn btn-primary">Lihat Jurnal</button>
        </div>
      </div>
    </form>
  </div>
@endsection

@section('js')
  <script src="{{ asset('vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
  <script type="text/javascript">
  $(document).ready(function() {
    $('.input-daterange').datepicker({
      format: 'yyyy/mm/dd',
    });
  })
  </script>
@endsection
