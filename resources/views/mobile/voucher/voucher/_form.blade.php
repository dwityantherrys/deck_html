<?php $isActive = !empty(old('is_active')) ? old('is_active') : $model->is_active; ?>
<?php $limitTypeId = !empty(old('limit_type')) ? old('limit_type') : $model->limit_type; ?>

<div class="form-group @if($errors->has('name')) has-error @endif">
  <label for="">Name</label>
  <input type="text" class="form-control" name="name" placeholder="voucher name" value="{{ !empty(old('name')) ? old('name') : $model->name }}">
  @if($errors->has('name'))
    <span class="help-block">{{ $errors->first('name') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('code')) has-error @endif">
  <label for="">Code</label>
  <input type="text" class="form-control" name="code" placeholder="voucher code" value="{{ !empty(old('code')) ? old('code') : $model->code }}">
  @if($errors->has('code'))
    <span class="help-block">{{ $errors->first('code') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('value')) has-error @endif">
  <label for="">Value (%)</label>
  <input type="number" class="form-control" name="value" placeholder="voucher value" value="{{ !empty(old('value')) ? old('value') : $model->value }}">
  @if($errors->has('value'))
    <span class="help-block">{{ $errors->first('value') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('minimum_sales')) has-error @endif">
  <label for="">Minimum Sales (Rp.)</label>
  <input type="text" class="form-control" name="minimum_sales" placeholder="minimum sales" value="{{ !empty(old('minimum_sales')) ? old('minimum_sales') : $model->minimum_sales }}">
  @if($errors->has('minimum_sales'))
    <span class="help-block">{{ $errors->first('minimum_sales') }}</span>
  @endif
</div>

<div class="row">
  <div class="col-sm-4">
    <div class="form-group @if($errors->has('limit_type')) has-error @endif">
      <label>Limit Type</label>
      <select class="form-control " name="limit_type" id="" style="width: 100%;" tabindex="-1">
          @foreach($limitTypes as $keyLimitType => $limitType)
          <option value="{{ $keyLimitType }}" @if($limitTypeId == $limitType) selected @endif>{{ $limitType }}</option>
          @endforeach
      </select>
    </div>  
  </div>

  <div class="col-sm-4">
    <div class="form-group @if($errors->has('limit_usage')) has-error @endif">
      <label for="">Limit Usage</label>
      <input type="number" class="form-control" name="limit_usage" placeholder="limit usage" value="{{ !empty(old('limit_usage')) ? old('limit_usage') : $model->limit_usage }}">
      @if($errors->has('limit_usage'))
        <span class="help-block">{{ $errors->first('limit_usage') }}</span>
      @endif
    </div>  
  </div>

  <div class="col-sm-4">
    <div class="form-group @if($errors->has('limit_customer')) has-error @endif">
      <label for="">Limit Customer</label>
      <input type="number" class="form-control" name="limit_customer" placeholder="limit customer" value="{{ !empty(old('limit_customer')) ? old('limit_customer') : $model->limit_customer }}">
      @if($errors->has('limit_customer'))
        <span class="help-block">{{ $errors->first('limit_customer') }}</span>
      @endif
    </div>  
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="form-group @if($errors->has('start_date')) has-error @endif">
      <label>Start Date</label>

      <div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input type="text" class="form-control datepicker pull-right" name="start_date" value="{{ !empty(old('start_date')) ? old('start_date') : optional($model->start_date)->format('m/d/Y') }}">
      </div>
      @if($errors->has('start_date'))
        <span class="help-block">{{ $errors->first('start_date') }}</span>
      @endif
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group @if($errors->has('expiration_date')) has-error @endif">
      <label>Expired Date</label>

      <div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input type="text" class="form-control datepicker pull-right" name="expiration_date" value="{{ !empty(old('expiration_date')) ? old('expiration_date') : optional($model->expiration_date)->format('m/d/Y') }}">
      </div>
      @if($errors->has('expiration_date'))
        <span class="help-block">{{ $errors->first('expiration_date') }}</span>
      @endif
    </div>
  </div>
</div>

<div class="form-group @if($errors->has('notes')) has-error @endif">
  <label for="">Notes</label>
  <textarea class="form-control" rows="3" name="notes" placeholder="description / note">{{ !empty(old('notes')) ? old('notes') : $model->notes }}</textarea>
  @if($errors->has('notes'))
    <span class="help-block">{{ $errors->first('notes') }}</span>
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
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endsection

@section('js')
<script src="{{ asset('vendor/autonumeric/autoNumeric.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script>
  new AutoNumeric($('input[name="minimum_sales"]')[0], {
    currencySymbol : 'Rp. ',
    emptyInputBehavior: 'zero',
    unformatOnSubmit: true
  });
  $(".datepicker").datepicker({ autoClose: true });
</script>
@endsection