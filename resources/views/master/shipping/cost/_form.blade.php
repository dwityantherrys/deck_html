<?php $isActive = !empty(old('is_active')) ? old('is_active') : ($model->is_active ? $model->is_active : 1); ?>
 
<div class="row">
  <div class="col-md-6">
    <div class="form-group @if($errors->has('min_length')) has-error @endif">
      <label>Minimum Length (m)</label>
      <input type="text" class="form-control" name="min_length" value="{{ !empty(old('min_length')) ? old('min_length') : $model->min_length }}">
      @if($errors->has('min_length'))
        <span class="help-block">{{ $errors->first('min_length') }}</span>
      @endif
    </div>      
  </div>

  <div class="col-md-6">
    <div class="form-group @if($errors->has('max_length')) has-error @endif">
      <label>Maximum Length (m)</label>
      <input type="text" class="form-control" name="max_length" value="{{ !empty(old('max_length')) ? old('max_length') : $model->max_length }}">
      @if($errors->has('max_length'))
        <span class="help-block">{{ $errors->first('max_length') }}</span>
      @endif
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="form-group @if($errors->has('min_weight')) has-error @endif">
      <label>Minimum Weight (kg)</label>
      <input type="text" class="form-control" name="min_weight" value="{{ !empty(old('min_weight')) ? old('min_weight') : $model->min_weight }}">
      @if($errors->has('min_weight'))
        <span class="help-block">{{ $errors->first('min_weight') }}</span>
      @endif
    </div>      
  </div>

  <div class="col-md-6">
    <div class="form-group @if($errors->has('max_weight')) has-error @endif">
      <label>Maximum Weight (kg)</label>
      <input type="text" class="form-control" name="max_weight" value="{{ !empty(old('max_weight')) ? old('max_weight') : $model->max_weight }}">
      @if($errors->has('max_weight'))
        <span class="help-block">{{ $errors->first('max_weight') }}</span>
      @endif
    </div>
  </div>
</div>

<div class="form-group @if($errors->has('charge_per_km')) has-error @endif">
  <label for="">Charge per kilometer (Rp)</label>
  <input type="text" class="form-control" name="charge_per_km" placeholder="charge per km" value="{{ !empty(old('charge_per_km')) ? old('charge_per_km') : $model->charge_per_km }}">
  @if($errors->has('charge_per_km'))
    <span class="help-block">{{ $errors->first('charge_per_km') }}</span>
  @endif
</div>

@section('js')
<script src="{{ asset('vendor/autonumeric/autoNumeric.min.js') }}" type="text/javascript"></script>

<script>
minLength = $('input[name="min_length"]');
maxLength = $('input[name="max_length"]');
minWeight = $('input[name="min_weight"]');
maxWeight = $('input[name="max_weight"]');
charge = $('input[name="charge_per_km"]');

formatOptions = { emptyInputBehavior: 'zero', unformatOnSubmit: true }

minLengthFormated = new AutoNumeric(minLength[0], formatOptions);
maxLengthFormated = new AutoNumeric(maxLength[0], formatOptions);
minWeightFormated = new AutoNumeric(minWeight[0], formatOptions);
maxWeightFormated = new AutoNumeric(maxWeight[0], formatOptions);
chargeFormated = new AutoNumeric(charge[0], formatOptions);
</script>
@stop