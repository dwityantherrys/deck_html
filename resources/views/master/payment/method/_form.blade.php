<div class="row">
  <div class="col-md-8" style="border-right: 1px solid #d2d6de;">
    <div class="form-group @if($errors->has('name')) has-error @endif">
      <label for="">Name</label>
      <input type="text" class="form-control" name="name" placeholder="method name" value="{{ !empty(old('name')) ? old('name') : $model->name }}">
      @if($errors->has('name'))
        <span class="help-block">{{ $errors->first('name') }}</span>
      @endif
    </div>

    <div class="form-group">
      <label>Image</label>
      <input type="file" class="has-image-preview form-control" id="" name="image" value="">
    </div>

    <div class="form-group">
      <label>Has VA code prefix</label>
      <?php $codeRule = !empty(old('has_code_rule')) ? old('has_code_rule') : $model->has_code_rule; ?>
      <select class="form-control " name="has_code_rule" id="" style="width: 100%;" tabindex="-1">
          <option value="1" @if($codeRule == 1) selected @endif>Yes</option>
          <option value="0" @if($codeRule == 0) selected @endif>No</option>
      </select>
    </div>

    <div class="form-group @if($errors->has('code')) has-error @endif">
      <label for="">VA code</label>
      <input type="number" class="form-control" name="code" placeholder="method va code" value="{{ !empty(old('code')) ? old('code') : $model->code }}">
      @if($errors->has('code'))
        <span class="help-block">{{ $errors->first('code') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('rekening_number')) has-error @endif">
      <label for="">Rekening number</label>
      <input type="number" class="form-control" name="rekening_number" placeholder="method rekening number" value="{{ !empty(old('rekening_number')) ? old('rekening_number') : $model->rekening_number }}">
      @if($errors->has('rekening_number'))
        <span class="help-block">{{ $errors->first('rekening_number') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('channel')) has-error @endif">
      <label for="">Channel</label>
      <input type="text" class="form-control" name="channel" placeholder="method channel (ex: transfer, mobile banking)" value="{{ !empty(old('channel')) ? old('channel') : $model->channel }}">
      @if($errors->has('channel'))
        <span class="help-block">{{ $errors->first('channel') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('guide')) has-error @endif">
      <label for="">Guide</label>
      <textarea id="guide" class="form-control" rows="3" name="guide" placeholder="method guide">{{ !empty(old('guide')) ? old('guide') : $model->guide }}</textarea>
      @if($errors->has('guide'))
        <span class="help-block">{{ $errors->first('guide') }}</span>
      @endif
    </div>
      
    <div class="form-group">
      <label>Available at</label>
      <?php $availableAt = !empty(old('available_at')) ? old('available_at') : $model->available_at; ?>
      <select class="form-control " name="available_at" id="" style="width: 100%;" tabindex="-1">
          @foreach($availableOptions as $key => $availableOption)
            <option value="{{ $key }}" @if($availableAt == $key) selected @endif>{{ $availableOption }}</option>
          @endforeach  
      </select>
    </div>

    <div class="form-group">
      <label>Active</label>
      <?php $isActive = !empty(old('is_active')) ? old('is_active') : $model->is_active; ?>
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

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-lite.css" rel="stylesheet">
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-lite.js"></script>
<script>
$(document).ready(function () {
  $('#guide').summernote();
})
</script>
@endsection
