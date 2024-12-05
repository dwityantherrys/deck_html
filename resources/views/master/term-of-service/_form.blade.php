<?php $isActive = !empty(old('is_active')) ? old('is_active') : ($model->is_active ? $model->is_active : 1); ?>

<div class="form-group @if($errors->has('title')) has-error @endif">
  <label for="">Title</label>
  <input type="text" class="form-control" name="title" placeholder="title" value="{{ !empty(old('title')) ? old('title') : $model->title }}" readonly>
  @if($errors->has('title'))
    <span class="help-block">{{ $errors->first('title') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('term')) has-error @endif">
  <label for="">Term of Service</label>
  <textarea id="term" class="form-control" rows="3" name="term" placeholder="term of service">{{ !empty(old('term')) ? old('term') : $model->term }}</textarea>
  @if($errors->has('term'))
    <span class="help-block">{{ $errors->first('term') }}</span>
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
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-lite.css" rel="stylesheet">
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-lite.js"></script>
<script>
$(document).ready(function () {
  $('#term').summernote();
})
</script>
@endsection
