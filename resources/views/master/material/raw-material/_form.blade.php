<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $materialId = !empty(old('material_id')) ? old('material_id') : $model->material_id; ?>
<?php $colorId = !empty(old('color_id')) ? old('color_id') : $model->color_id; ?>
<?php $isActive = !empty(old('is_active')) ? old('is_active') : ($model->is_active ? $model->is_active : 1); ?>

<div class="form-group @if($errors->has('number')) has-error @endif">
  <label for="">Number</label>
  <input type="text" class="form-control" name="number" placeholder="raw material number" value="{{ !empty(old('number')) ? old('number') : $model->number }}">
  @if($errors->has('number'))
    <span class="help-block">{{ $errors->first('number') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('name')) has-error @endif">
  <label for="">Name</label>
  <input type="text" class="form-control" name="name" placeholder="raw material name" value="{{ !empty(old('name')) ? old('name') : $model->name }}">
  @if($errors->has('name'))
    <span class="help-block">{{ $errors->first('name') }}</span>
  @endif
</div>

<div class="form-group">
  <label>Specification</label>
  <textarea class="form-control" rows="3" name="specification" placeholder="raw material specification">{{ $model->specification }}</textarea>
</div>

<div class="form-group @if($errors->has('thick')) has-error @endif">
  <label for="">Tebal (mm)</label>
  <input type="text" class="form-control" name="thick" placeholder="raw material thick" value="{{ !empty(old('thick')) ? old('thick') : $model->thick }}">
  @if($errors->has('thick'))
    <span class="help-block">{{ $errors->first('thick') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('material_id')) has-error @endif">
  <label>Material</label>
  <select class="form-control" name="material_id" id="" style="width: 100%;" tabindex="-1"> </select>
  @if($errors->has('material_id'))
    <span class="help-block">{{ $errors->first('material_id') }}</span>
  @endif
  <span class="help-block">data material tidak ada? <a class="text-red" href="{{ url('/master/material/material/create') }}" target="_blank">new material</a></span>
</div>

<div class="form-group @if($errors->has('color_id')) has-error @endif">
  <label>Color</label>
  <select class="form-control" name="color_id" id="" style="width: 100%;" tabindex="-1"> </select>
  @if($errors->has('color_id'))
    <span class="help-block">{{ $errors->first('color_id') }}</span>
  @endif
  <span class="help-block">data color tidak ada? <a class="text-red" href="{{ url('/master/material/color/create') }}" target="_blank">new color</a></span>
</div>

<div class="form-group @if($errors->has('raw_az')) has-error @endif">
  <label>AZ</label>
  <input type="text" class="form-control" name="raw_az" placeholder="AZ" id="raw_az" value="{{ !empty(old("raw_az")) ? old("raw_az") : $model->raw_az }}"/>
  @if($errors->has('raw_az'))
    <span class="help-block">{{ $errors->first('raw_az') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('raw_grade')) has-error @endif">
  <label>Grade</label>
  <input type="text" class="form-control" name="raw_grade" placeholder="Grade" id="raw_grade" value="{{ !empty(old("raw_grade")) ? old("raw_grade") : $model->raw_grade }}"/>
  @if($errors->has('raw_grade'))
    <span class="help-block">{{ $errors->first('raw_grade') }}</span>
  @endif
</div>

<div class="row">
  <div class="col-sm-6">
    <div class="form-group @if($errors->has('min_stock')) has-error @endif">
      <label>Minimal Stock</label>
      <input type="number" class="form-control" name="min_stock" placeholder="Min Stock" id="min_stock" value="{{ !empty(old("min_stock")) ? old("min_stock") : $model->min_stock }}"/>
      @if($errors->has('min_stock'))
        <span class="help-block">{{ $errors->first('min_stock') }}</span>
      @endif
    </div>
  </div>
    <div class="col-sm-6">
      <div class="form-group @if($errors->has('stock_planning')) has-error @endif">
        <label>Stock Planning</label>
        <input type="number" class="form-control" name="stock_planning" placeholder="Min Stock" id="stock_planning" value="{{ !empty(old("stock_planning")) ? old("stock_planning") : $model->stock_planning }}"/>
        @if($errors->has('stock_planning'))
          <span class="help-block">{{ $errors->first('stock_planning') }}</span>
        @endif
      </div>
    </div>
</div>

<div class="form-group">
  <label>Active</label>
  <select class="form-control " name="is_active" id="" style="width: 100%;" tabindex="-1">
      <option value="1" @if($isActive == 1) selected @endif>Yes</option>
      <option value="0" @if($isActive == 0) selected @endif>No</option>
  </select>
</div>

@section('js')
<script>
var baseBeApiUrl = "{{ url('/api/backend') }}";

select2AjaxHandler('select[name="color_id"]', `${baseBeApiUrl}/color`, "{{ $colorId }}");
select2AjaxHandler('select[name="material_id"]', `${baseBeApiUrl}/material`, "{{ $materialId }}");
</script>
@endsection
