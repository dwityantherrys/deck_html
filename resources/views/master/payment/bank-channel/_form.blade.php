@php
$isActive = !empty(old('is_active')) ? old('is_active') : (!is_null($model->is_active) ? $model->is_active : 1);
@endphp

<div class="form-group @if($errors->has('name')) has-error @endif">
  <label for="">Bank Name</label>
  <input type="text" class="form-control" name="name" placeholder="bank name" value="{{ !empty(old('name')) ? old('name') : $model->name }}">
  @if($errors->has('name'))
    <span class="help-block">{{ $errors->first('name') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('rekening_name')) has-error @endif">
  <label for="">Rekening Name</label>
  <input type="text" class="form-control" name="rekening_name" placeholder="Rekening Name" value="{{ !empty(old('rekening_name')) ? old('rekening_name') : $model->rekening_name }}">
  @if($errors->has('rekening_name'))
    <span class="help-block">{{ $errors->first('rekening_name') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('rekening_number')) has-error @endif">
  <label for="">Rekening Number</label>
  <input type="text" class="form-control" name="rekening_number" placeholder="Rekening Number" value="{{ !empty(old('rekening_number')) ? old('rekening_number') : $model->rekening_number }}">
  @if($errors->has('rekening_number'))
    <span class="help-block">{{ $errors->first('rekening_number') }}</span>
  @endif
</div>

<div class="form-group">
  <label>Akun Jurnal</label>
  <select class="form-control " name="kode_akun" id="kode_akun" style="width: 100%;">
    <option value=""></option>
    @if (isset($model->kode_akun))
      <option value="{{ $model->kode_akun }}" selected>{{ $model->coa->nama_akun }}</option>
    @endif
  </select>
</div>

<div class="form-group">
  <label>Active</label>
  <select class="form-control " name="is_active" id="" style="width: 100%;" tabindex="-1">
      <option value="1" {{ $isActive == 1 ? "selected" : "" }}>Yes</option>
      <option value="0" {{ $isActive == 0 ? "selected" : "" }}>No</option>
  </select>
</div>

@section('js')
  <script type="text/javascript">
  $("#kode_akun").select2({
    placeholder: "Pilih Akun Jurnal",
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
  </script>
@endsection
