<form action="{{ $action }}" method="post">
  @csrf
  @method($method)
  <div class="form-group row">
    <div class="col-md-2">
      <label>Periode</label>
    </div>
    <div class="col-md-10">
      <div class="input-group date">
        <span class="input-group-addon"> <i class="fa fa-calendar"></i> </span>
        <input type="text" placeholder="Pilih Periode" class="form-control date-picker" name="periode" autocomplete="off" value="{{ !empty($data->periode) ? Carbon\Carbon::parse($data->periode)->format("m-Y") : old("periode") }}" required>
      </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-md-2">
      <label>Target per Bulan <small>*dalam juta</small></label>
    </div>
    <div class="col-md-10">
      <div class="input-group">
        <span class="input-group-addon"> Rp. </span>
        <input type="number" class="form-control" name="target" value="{{ !empty($data->target) ? $data->target : old("target") }}" min="0" required>
      </div>
    </div>
  </div>
  <div class="form-group row text-center">
    <button type="submit" class="btn btn-primary">{{ !empty($data) ? "Simpan" : "Tambah" }}</button>
    <a href="{{ route("sales-target.index") }}" class="btn btn-default">Kembali</a>
  </div>
</form>
