@extends('adminlte::page')

@section('title', 'Balance Sheet')

@section('css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
@endsection

@section('content')
  <div class="box box-danger">
    <div class="box-header with-border">
      <h3>Balance Sheet</h3>
    </div>
    <div class="box-body">
      <form action="{{ route("balance-sheet.show", round(microtime(true) / 1000)) }}" method="get">
        <div class="form-group row">
          <div class="col-md-2">
            <label>Pilih Periode</label>
          </div>
          <div class="col-md-10">
            <div class="row">
              <div class="input-daterange">
                <div class="col-md-5 form-group">
                  <div class="input-group date">
                    <span class="input-group-addon"> <i class="fa fa-calendar"></i> </span>
                    <input type="text" placeholder="Pilih Tanggal Awal" class="form-control date-picker" name="periode_awal" autocomplete="off" required>
                  </div>
                </div>
                <div class="col-md-2 form-group">
                  <label>Sampai</label>
                </div>
                <div class="col-md-5 form-group">
                  <div class="input-group date">
                    <span class="input-group-addon"> <i class="fa fa-calendar"></i> </span>
                    <input type="text" placeholder="Pilih Tanggal Akhir" class="form-control date-picker" name="periode_akhir" autocomplete="off" required>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <div class="col-md-12 text-center">
            <button type="submit" class="btn btn-primary">Lihat Income Statement</button>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('js')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" charset="utf-8"></script>
  <script type="text/javascript">
  $(document).ready(function() {
    $(".input-daterange").datepicker();
  })
  </script>
@endsection
