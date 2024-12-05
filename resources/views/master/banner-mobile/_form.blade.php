<?php $isActive = !empty(old('is_active')) ? old('is_active') : ($model->is_active ? $model->is_active : 1); ?>

<div class="row">
  <div class="col-md-8" style="border-right: 1px solid #d2d6de;">
    <div class="form-group @if($errors->has('name')) has-error @endif">
      <label for="">Name</label>
      <input type="text" class="form-control" name="name" placeholder="banner name" value="{{ !empty(old('name')) ? old('name') : $model->name }}">
      @if($errors->has('name'))
        <span class="help-block">{{ $errors->first('name') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('description')) has-error @endif">
      <label for="">Description</label>
      <textarea class="form-control" rows="3" name="description" placeholder="banner description">{{ !empty(old('description')) ? old('description') : $model->description }}</textarea>
      @if($errors->has('description'))
        <span class="help-block">{{ $errors->first('description') }}</span>
      @endif
    </div>
      
    <div class="form-group">
      <label>Image</label>
      <input type="file" class="has-image-preview form-control" id="" name="image" value="">
    </div>

    <div class="form-group">
      <label>Active</label>
      <select class="form-control " name="is_active" id="" style="width: 100%;" tabindex="-1">
          <option value="1" @if($isActive == 1) selected @endif>Yes</option>
          <option value="0" @if($isActive == 0) selected @endif>No</option>
      </select>
    </div>
  </div>

  <div class="col-md-4" style="height: 100%;">
    <label>Image Preview</label>
    <div style="margin-top: 1rem;">
      <img class="image-preview" src="{{ $model->image_url ? $model->image_url : asset('img/no-image.png') }}" width="100%" alt="image preview">
    </div>
  </div>
</div>