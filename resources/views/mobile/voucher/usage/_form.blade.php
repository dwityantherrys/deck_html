<?php $baseBeApiUrl = url('/api/backend'); ?>
<?php $voucherId = !empty(old('voucher_id')) ? old('voucher_id') : $model->voucher_id; ?>
<?php $userId = !empty(old('user_id')) ? old('user_id') : $model->user_id; ?>
<?php $salesId = $model->user_id; ?>
<?php $isActive = !empty(old('is_active')) ? old('is_active') : ($model->is_active ? $model->is_active : 1); ?>
<?php $isMultipleUsage = !empty(old('is_multiple_usage')) ? old('is_multiple_usage') : ($model->is_multiple_usage ? $model->is_multiple_usage : 1); ?>

<div class="form-group @if($errors->has('voucher_id')) has-error @endif">
  <label>Voucher</label>
  <select class="form-control" name="voucher_id" id="" style="width: 100%;" tabindex="-1"> </select>
  @if($errors->has('voucher_id'))
    <span class="help-block">{{ $errors->first('voucher_id') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('user_id')) has-error @endif">
  <label>Customer</label>
  <select class="form-control" name="user_id" id="" style="width: 100%;" tabindex="-1"> </select>
  @if($errors->has('user_id'))
    <span class="help-block">{{ $errors->first('user_id') }}</span>
  @endif
</div>

@if(!empty($model->id))
  <div class="form-group @if($errors->has('status_usage')) has-error @endif">
    <label for="">Status Usage</label>
    <input type="text" class="form-control" name="status_usage" placeholder="status usage" value="{{ $model->status_usage_label }}" readonly>
  </div>

  @if($model->status_usage === $model::STATUS_USED)
  <div class="form-group @if($errors->has('sales_id')) has-error @endif">
    <label>Sales</label>
    <select class="form-control" name="sales_id" id="" style="width: 100%;" tabindex="-1"> </select>
  </div>
  @endif
@endif

@section('js')
<script>
  var baseBeApiUrl = "{{ $baseBeApiUrl }}";
  var voucherId = "{{ $voucherId }}";
  var userId = "{{ $userId }}";
  var salesId = "{{ $salesId }}";

  select2AjaxHandler('select[name="voucher_id"]', `{{ $baseBeApiUrl . '/voucher' }}`, voucherId);
  select2AjaxHandler('select[name="user_id"]', `{{ $baseBeApiUrl . '/customer' }}`, userId);
  select2AjaxHandler('select[name="sales_id"]', `{{ $baseBeApiUrl . '/sales' }}`, salesId);
</script>
@endsection