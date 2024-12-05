<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php
  $isEdit = false;
  $itemType = old('item_type');
  $itemId = old('item_id');
  $itemUri = null;

  if($model->id) {
    $isEdit = true;
    $inventory = $model->inventory;

    $itemType = $inventory->type_inventory;
    $itemId = $inventory->reference_id;
    $itemUri = ($inventory->type_inventory === $inventory::TYPE_INVENTORY_RAW) ? 'raw-material' : 'item-material';
  }
?>

<?php $createdBy = !empty(old('created_by')) ? old('created_by') : $model->created_by; ?>
<?php $warehouseId = !empty(old('warehouse_id')) ? old('warehouse_id') : $model->warehouse_id; ?>

<input type="hidden" class="form-control" name="inventory_warehouse_id" value="{{ $model->id }}">

<div class="form-group">
  <label for="">Type Inventory</label>
  <select
    class="form-control"
    name="item_type"
    style="width: 100%;"
    tabindex="-1"
    @if($isEdit) disabled @endif>
    <option value=""></option>
    @foreach($typeInventoryOptions as $keyOption => $option)
    <option value="{{ $keyOption }}" @if($keyOption === $itemType) selected @endif>{{ $option }}</option>
    @endforeach
  </select>
</div>

<div class="form-group">
  <label for="">Item / Material</label>
  <select
    class="form-control"
    name="item_id"
    style="width: 100%;"
    tabindex="-1"
    @if($isEdit) disabled @endif> </select>
</div>

<div class="form-group @if($errors->has('created_by')) has-error @endif">
  <label>PIC</label>
  <select class="form-control" name="created_by" style="width: 100%;" tabindex="-1"> </select>
  @if($errors->has('created_by'))
    <span class="help-block">{{ $errors->first('created_by') }}</span>
  @endif
  <span class="help-block">data pic tidak ada? <a class="text-red" href="{{ url('/master/employee/create') }}" target="_blank">new pic</a></span>
</div>

<div class="form-group @if($errors->has('warehouse_id')) has-error @endif">
  <label>Warehouse</label>
  <select class="form-control" name="warehouse_id" style="width: 100%;" tabindex="-1" @if($isEdit) disabled @endif> </select>
  @if($errors->has('warehouse_id'))
    <span class="help-block">{{ $errors->first('warehouse_id') }}</span>
  @endif
  <span class="help-block">data warehouse tidak ada? <a class="text-red" href="{{ url('/master/warehouse/create') }}" target="_blank">new warehouse</a></span>
</div>

<div class="form-group @if($errors->has('cost_of_good_before_adjustment')) has-error @endif">
  <label>Current cost of good</label>
  <input type="text" class="form-control" name="cost_of_good_before_adjustment" value="{{ optional($model->inventory)->cost_of_good }}" readonly>
  @if($errors->has('cost_of_good_before_adjustment'))
    <span class="help-block">{{ $errors->first('cost_of_good_before_adjustment') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('cost_of_good_after_adjustment')) has-error @endif">
  <label>Adjustment cost of good</label>
  <input type="text" class="form-control" name="cost_of_good_after_adjustment" value="{{ !empty(old('cost_of_good_after_adjustment')) ? old('cost_of_good_after_adjustment') : optional($model->inventory)->cost_of_good }}">
  @if($errors->has('cost_of_good_after_adjustment'))
    <span class="help-block">{{ $errors->first('cost_of_good_after_adjustment') }}</span>
  @endif
</div>

<div class="row">

  <div class="col-md-3">
    <div class="form-group @if($errors->has('stock_before_adjustment')) has-error @endif">
      <label>Current stock</label>
      <input type="text" class="form-control" name="stock_before_adjustment" value="{{ $model->stock }}" readonly>
      @if($errors->has('stock_before_adjustment'))
        <span class="help-block">{{ $errors->first('stock_before_adjustment') }}</span>
      @endif
    </div>
  </div>

  <div class="col-md-3">
    <div class="form-group">
      <label>Minimal Stock</label>
      @isset($model->id)
        @if ($inventory->type_inventory == $inventory::TYPE_INVENTORY_FINISH)
          <input type="number" name="min_stock" class="form-control" value="{{ !empty(old('min_stock')) ? old('min_stock') : optional($inventory->item_material->item)->min_stock }}" readonly>
        @else
          <input type="number" name="min_stock" class="form-control" value="{{ !empty(old('min_stock')) ? old('min_stock') : optional($inventory->raw_material)->min_stock }}" readonly>
        @endif
      @else
        <input type="number" name="min_stock" class="form-control" readonly>
      @endisset
    </div>
  </div>

  <div class="col-md-3">
    <div class="form-group @if($errors->has('stock_after_adjustment')) has-error @endif">
      <label>change stock</label>
      <input type="text" class="form-control" name="stock_after_adjustment" value="{{ !empty(old('stock_after_adjustment')) ? old('stock_after_adjustment') : $model->stock }}">
      @if($errors->has('stock_after_adjustment'))
        <span class="help-block">{{ $errors->first('stock_after_adjustment') }}</span>
      @endif
    </div>
  </div>

  <div class="col-md-3">
    <div class="form-group">
      <label>Adjustment stock</label>
      <input type="text" class="form-control" name="different_stock" value="{{ old('different_stock', 0) }}" readonly>
    </div>
  </div>

</div>

@section('js')
<script src="{{ asset('vendor/autonumeric/autoNumeric.min.js') }}" type="text/javascript"></script>

<script>
var TYPE_INVENTORY_RAW = "{{ array_keys($typeInventoryOptions, 'raw material')[0] }}";
var baseBeApiUrl = "{{ $baseBeApiUrl }}";
var itemUri = "{{ $itemUri }}";

priceBefore = $('input[name="cost_of_good_before_adjustment"]')
priceAfter = $('input[name="cost_of_good_after_adjustment"]')

formatOptions = {
  currencySymbol : 'Rp. ',
  emptyInputBehavior: 'zero',
  unformatOnSubmit: true
}

priceBeforeFormated = new AutoNumeric(priceBefore[0], formatOptions);
priceAfterFormated = new AutoNumeric(priceAfter[0], formatOptions);

$('select[name="item_type"]').select2();
$('select[name="item_type"]').change(function () {
  var type = $(this).val();

  var itemUri = (type === TYPE_INVENTORY_RAW) ? 'raw-material' : 'item-material';
  select2AjaxHandler('select[name="item_id"]', `{{ $baseBeApiUrl }}/${itemUri}`);
});

select2AjaxHandler('select[name="item_id"]', `{{ $baseBeApiUrl }}/${itemUri}`, '{{ $itemId }}');
select2AjaxHandler('select[name="warehouse_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, '{{ $warehouseId }}');
select2AjaxHandler('select[name="created_by"]', `{{ $baseBeApiUrl . '/employee' }}`, '{{ $createdBy }}');

$('input[name="stock_after_adjustment"]').change(function () {
  var stockBefore = $('input[name="stock_before_adjustment"]').val();
  var stockAfter = isNaN($(this).val()) ? 0 : $(this).val();

  if(isNaN($(this).val())) $(this).val(0)

  $('input[name="different_stock"]').val(Number(stockAfter)-Number(stockBefore))
});
</script>
@endsection
