<?php $code = !empty(old('code')) ? old('code') : $model->code; ?>
<?php $isActive = !empty(old('is_active')) ? old('is_active') : $model->is_active; ?>

<div class="form-group @if($errors->has('name')) has-error @endif">
  <label for="">Name</label>
  <input type="text" class="form-control" name="name" placeholder="material name" value="{{ !empty(old('name')) ? old('name') : $model->name }}">
  @if($errors->has('name'))
    <span class="help-block">{{ $errors->first('name') }}</span>
  @endif
</div>
  
<div class="form-group">
  <label for="">Color</label>
  <div class="input-group my-colorpicker">
    <div class="input-group-addon"> <i></i> </div>
    <input type="text" class="form-control" name="code" value="{{ $code }}">
  </div>
  @if($errors->has('code'))
    <span class="help-block">{{ $errors->first('code') }}</span>
  @endif
</div> 

<div class="form-group">
  <label>Active</label>
  <select class="form-control " name="is_active" id="" style="width: 100%;" tabindex="-1">
      <option value="1" @if($isActive == 1) selected @endif>Yes</option>
      <option value="0" @if($isActive == 0) selected @endif>No</option>
  </select>
</div>

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css') }}">
@endsection

@section('js')
<script src="{{ asset('vendor/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js') }}"></script>
<script>
var colorCode = "{{ $code }}";

$(document).ready(function () {
  colorCode ? 
    $('.my-colorpicker').colorpicker({ color: `${colorCode}` }) :
    $('.my-colorpicker').colorpicker();
});
</script>
@endsection