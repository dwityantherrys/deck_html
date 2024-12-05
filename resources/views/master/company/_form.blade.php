<div class="form-group @if($errors->has('name')) has-error @endif">
  <label for="">Name</label>
  <input type="text" class="form-control" name="name" placeholder="company name" value="{{ !empty(old('name')) ? old('name') : $model->name }}">
  @if($errors->has('name'))
    <span class="help-block">{{ $errors->first('name') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('address')) has-error @endif">
  <label for="">Address</label>
  <textarea class="form-control" rows="3" name="address" placeholder="company address">{{ !empty(old('address')) ? old('address') : $model->address }}</textarea>
  @if($errors->has('address'))
    <span class="help-block">{{ $errors->first('address') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('phone')) has-error @endif">
  <label for="">Phone</label>
  <input type="number" class="form-control" name="phone" placeholder="company phone" value="{{ !empty(old('phone')) ? old('phone') : $model->phone }}">
  @if($errors->has('phone'))
    <span class="help-block">{{ $errors->first('phone') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('npwp')) has-error @endif">
  <label for="">Npwp</label>
  <input type="number" class="form-control" name="npwp" placeholder="company npwp" value="{{ !empty(old('npwp')) ? old('npwp') : $model->npwp }}">
  @if($errors->has('npwp'))
    <span class="help-block">{{ $errors->first('npwp') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('business_field')) has-error @endif">
  <label for="">Business field</label>
  <input type="text" class="form-control" name="business_field" placeholder="company business field" value="{{ !empty(old('business_field')) ? old('business_field') : $model->business_field }}">
  @if($errors->has('business_field'))
    <span class="help-block">{{ $errors->first('business_field') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('ceo_name')) has-error @endif">
  <label for="">CEO name</label>
  <input type="text" class="form-control" name="ceo_name" placeholder="company ceo name" value="{{ !empty(old('ceo_name')) ? old('ceo_name') : $model->ceo_name }}">
  @if($errors->has('ceo_name'))
    <span class="help-block">{{ $errors->first('ceo_name') }}</span>
  @endif
</div>

<div class="form-group">
  <label>Active</label>
  <?php $isActive = !empty(old('is_active')) ? old('is_active') : $model->is_active; ?>
  <select class="form-control " name="is_active" id="" style="width: 100%;" tabindex="-1">
      <option value="1" @if($isActive == 1) selected @endif>Yes</option>
      <option value="0" @if($isActive == 0) selected @endif>No</option>
  </select>
</div>