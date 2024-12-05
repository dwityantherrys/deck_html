<?php $baseBeApiUrl = url('/api/backend'); ?>
<?php $isActive = !empty(old('is_active')) ? old('is_active') : ($model->is_active ? $model->is_active : 1); ?>
<?php
  $provinceId = old('province_id');
  $cityId = old('city_id');

  if(!empty($model->id) && ($model->region_type == $model::REGION_TYPE_CITY)):
    $provinceId = !empty(old('province_id')) ? old('province_id') : $model->region_city->province_id;
    $cityId = !empty(old('city_id')) ? old('city_id') : $model->region_id;
  endif;
?>

<div class="form-group @if($errors->has('name')) has-error @endif">
  <label for="">Name</label>
  <input type="text" class="form-control" name="name" placeholder="warehouse name" value="{{ !empty(old('name')) ? old('name') : $model->name }}">
  @if($errors->has('name'))
    <span class="help-block">{{ $errors->first('name') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('address')) has-error @endif">
  <label for="">Address</label>
  <textarea class="form-control" rows="3" name="address" placeholder="warehouse address">{{ !empty(old('address')) ? old('address') : $model->address }}</textarea>
  @if($errors->has('address'))
    <span class="help-block">{{ $errors->first('address') }}</span>
  @endif
</div>

<div class="row">
  <div class="col-md-6">
    <div class="form-group @if($errors->has('province_id')) has-error @endif">
      <label>Province</label>
      <select class="form-control" name="province_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('province_id'))
        <span class="help-block">{{ $errors->first('province_id') }}</span>
      @endif
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-group @if($errors->has('city_id')) has-error @endif">
      <label>City</label>
      <select class="form-control" name="city_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('city_id'))
        <span class="help-block">{{ $errors->first('city_id') }}</span>
      @endif
    </div>
  </div>
</div>

<div class="form-group">
  <label>Type</label>
  <select class="form-control " name="type" style="width: 100%;" tabindex="-1">
      <option value="1" @if($model->type == 1) selected @endif>Factory</option>
      <option value="0" @if($model->type == 0) selected @endif>Inventory</option>
  </select>
</div>

<div class="form-group">
  <label>Active</label>
  <select class="form-control " name="is_active" style="width: 100%;" tabindex="-1">
      <option value="1" @if($isActive == 1) selected @endif>Yes</option>
      <option value="0" @if($isActive == 0) selected @endif>No</option>
  </select>
</div>

@section('js')
<script>
var loadCityFirstTime = true;
var loadDistrictFirstTime = true;
var baseBeApiUrl = "{{ $baseBeApiUrl }}";
var provinceId = "{{ $provinceId }}";
var cityId = "{{ $cityId }}";

select2AjaxHandler('select[name="province_id"]', `{{ $baseBeApiUrl . '/province' }}`, provinceId);

$('select[name="province_id"]').change(function () {

  if(cityId && loadCityFirstTime) {
    select2AjaxHandler('select[name="city_id"]', `{{ $baseBeApiUrl . '/city' }}`, cityId)
    loadCityFirstTime = false;
    return;
  }

  var provinceId = $(this).val();
  $('select[name="city_id"]').val('').trigger('change');
  select2AjaxHandler('select[name="city_id"]', `{{ $baseBeApiUrl . '/city' . '?province=${provinceId}' }}`, '');
});
</script>
@endsection
