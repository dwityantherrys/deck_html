<?php $roleId = !empty(old('role_id')) ? old('role_id') : $model->role_id; ?>
<?php $isActive = !empty(old('is_active')) ? old('is_active') : ($model->is_active ? $model->is_active : 1); ?>

<div class="row">
  <div class="col-md-8" style="border-right: 1px solid #d2d6de;">
    <div class="form-group @if($errors->has('name')) has-error @endif">
      <label for="">Name</label>
      <input type="text" class="form-control" name="name" placeholder="name" value="{{ !empty(old('name')) ? old('name') : $model->name }}">
      @if($errors->has('name'))
        <span class="help-block">{{ $errors->first('name') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('email')) has-error @endif">
      <label for="">Email</label>
      <input type="text" class="form-control" name="email" placeholder="email" value="{{ !empty(old('email')) ? old('email') : $model->email }}">
      @if($errors->has('email'))
        <span class="help-block">{{ $errors->first('email') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('password')) has-error @endif">
      <label for="">Password</label>
      <input type="password" class="form-control" name="password" placeholder="password" value="{{ !empty(old('name')) ? old('name') : $model->name }}">
      @if($errors->has('password'))
        <span class="help-block">{{ $errors->first('password') }}</span>
      @endif
    </div>
      
    <div class="form-group">
      <label>Image</label>
      <input type="file" class="has-image-preview form-control" id="" name="image" value="">
    </div>

    <div class="form-group @if($errors->has('role_id')) has-error @endif">
      <label>Role</label>
      <select class="form-control " name="role_id" id="" style="width: 100%;" tabindex="-1" data-placeholder="pilih role untuk employee">
          <option value=""></option>
          @foreach($roles as $role)
          <option value="{{ $role->id }}" @if($roleId == $role->id) selected @endif>{{ $role->display_name }}</option>
          @endforeach
      </select>
      @if($errors->has('role_id'))
        <span class="help-block">{{ $errors->first('role_id') }}</span>
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

  <div class="col-md-4" style="height: 100%;">
    <label>Image Preview</label>
    <div style="margin-top: 1rem;">
      <img class="image-preview" src="{{ $model->image_url ? $model->image_url : asset('img/no-image.png') }}" width="100%" alt="image preview">
    </div>
  </div>
</div>

@section('js')
<script>
  $("select").select2();
</script>
@endsection
