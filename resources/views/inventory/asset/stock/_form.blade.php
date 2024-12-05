<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $assetId = !empty(old('asset_id')) ? old('asset_id') : $model->asset_id; ?>
<?php $warehouseId = !empty(old('warehouse_id')) ? old('warehouse_id') : $model->warehouse_id; ?>
<?php $isActive = !empty(old('is_active')) ? old('is_active') : ($model->is_active ? $model->is_active : 1); ?>

<div class="row">
  <div class="col-md-12">

    <div class="form-group @if($errors->has('asset_id')) has-error @endif">
      <label>Asset</label>
      <select class="form-control" name="asset_id" id="" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('asset_id'))
        <span class="help-block">{{ $errors->first('asset_id') }}</span>
      @endif
      <span class="help-block">data asset tidak ada? <a class="text-red" href="{{ url('/inventory/asset/asset/create') }}" target="_blank">new asset</a></span>
    </div>

    <div class="form-group @if($errors->has('warehouse_id')) has-error @endif">
      <label>Warehouse</label>
      <select class="form-control" name="warehouse_id" id="" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('brand_id'))
        <span class="help-block">{{ $errors->first('warehouse_id') }}</span>
      @endif
      <span class="help-block">data warehouse tidak ada? <a class="text-red" href="{{ url('/master/warehouse/create') }}" target="_blank">new warehouse</a></span>
    </div>

    <div class="form-group @if($errors->has('stock')) has-error @endif">
      <label for="">Stock</label>
      <input type="text" class="form-control" name="stock" placeholder="Asset Stock" value="{{ !empty(old('stock')) ? old('stock') : $model->stock }}">
      @if($errors->has('stock'))
        <span class="help-block">{{ $errors->first('stock') }}</span>
      @endif
    </div>
  </div>
</div>

@section('js')
<script src="{{ asset('js/vue.js') }}"></script>
<script>

select2AjaxHandler('select[name="asset_id"]', `{{ $baseBeApiUrl . '/asset' }}`, '{{ $assetId }}');

select2AjaxHandler('select[name="warehouse_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, '{{ $warehouseId }}');


</script>
@endsection