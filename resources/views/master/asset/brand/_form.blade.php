<?php $isActive = !empty(old('is_active')) ? old('is_active') : ($model->is_active ? $model->is_active : 1); ?>

<div class="row">
  <div class="col-md-12" style="border-right: 1px solid #d2d6de;">
    <div class="form-group @if($errors->has('name')) has-error @endif">
      <label for="">Name</label>
      <input type="text" class="form-control" name="name" placeholder="brand name" value="{{ !empty(old('name')) ? old('name') : $model->name }}">
      @if($errors->has('name'))
        <span class="help-block">{{ $errors->first('name') }}</span>
      @endif
    </div>

    <div class="form-group">
      <label>Active</label>
      <select class="form-control " name="is_active" id="" style="width: 100%;" tabindex="-1">
          <option value="1" @if($isActive == 1) selected @endif>Yes</option>
          <option value="0" @if($isActive == 0) selected @endif>No</option>
      </select>
    </div>
  </div>
</div>