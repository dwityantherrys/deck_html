<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php
  $isEdit = false;
  $itemType = old('item_type');
  $itemId = old('item_id');

  if($model->id) {
    $isEdit = true;
    $inventory = $model->inventory;
  
    $itemType = $inventory->type_inventory;
    $itemId = $inventory->reference_id;
  }
?>

<?php $createdBy = !empty(old('created_by')) ? old('created_by') : $model->created_by; ?>
<?php $warehouseDepartureId = !empty(old('warehouse_departure_id')) ? old('warehouse_departure_id') : $model->warehouse_departure_id; ?>
<?php $warehouseArrivalId = !empty(old('warehouse_arrival_id')) ? old('warehouse_arrival_id') : $model->warehouse_arrival_id; ?>

<div class="row">
  <div class="col-md-6">
    <div class="form-group @if($errors->has('warehouse_departure_id')) has-error @endif">
      <label>Warehouse Departure</label>
      <select class="form-control" name="warehouse_departure_id" style="width: 100%;" tabindex="-1" @if($isEdit) disabled @endif> </select>
      @if($errors->has('warehouse_departure_id'))
        <span class="help-block">{{ $errors->first('warehouse_departure_id') }}</span>
      @endif
      <span class="help-block">data warehouse tidak ada? <a class="text-red" href="{{ url('/master/warehouse/create') }}" target="_blank">new warehouse</a></span>
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group @if($errors->has('warehouse_arrival_id')) has-error @endif">
      <label>Warehouse Arrival</label>
      <select class="form-control" name="warehouse_arrival_id" style="width: 100%;" tabindex="-1" @if($isEdit) disabled @endif> </select>
      @if($errors->has('warehouse_arrival_id'))
        <span class="help-block">{{ $errors->first('warehouse_arrival_id') }}</span>
      @endif
      <span class="help-block">data warehouse tidak ada? <a class="text-red" href="{{ url('/master/warehouse/create') }}" target="_blank">new warehouse</a></span>
    </div>
  </div>

</div>

<div class="form-group @if($errors->has('item_type')) has-error @endif">
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
  @if($errors->has('item_type'))
    <span class="help-block">{{ $errors->first('item_type') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('item_id')) has-error @endif">
  <label for="">Inventory Item / Material</label>
  <select 
    class="form-control" 
    name="item_id" 
    style="width: 100%;" 
    tabindex="-1"
    @if($isEdit) disabled @endif> </select>
  @if($errors->has('item_id'))
    <span class="help-block">{{ $errors->first('item_id') }}</span>
  @endif  
</div>

<div class="row">
  <div class="col-md-6">
    <div class="form-group @if($errors->has('date_departure')) has-error @endif">
      <label>Date Departure</label>

      <div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input type="text" class="form-control datepicker pull-right" name="date_departure" value="{{ !empty(old('date_departure')) ? old('date_departure') : optional($model->date_departure)->format('m/d/Y') }}">
      </div>
      @if($errors->has('date_departure'))
        <span class="help-block">{{ $errors->first('date_departure') }}</span>
      @endif
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group @if($errors->has('date_arrival')) has-error @endif">
      <label>Date Arrival</label>

      <div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input type="text" class="form-control datepicker pull-right" name="date_arrival" value="{{ !empty(old('date_arrival')) ? old('date_arrival') : optional($model->date_arrival)->format('m/d/Y') }}">
      </div>
      @if($errors->has('date_arrival'))
        <span class="help-block">{{ $errors->first('date_arrival') }}</span>
      @endif
    </div>
  </div>
</div>

<div class="form-group @if($errors->has('quantity')) has-error @endif">
  <label>Quantity</label>
  <input type="text" class="form-control" name="quantity" value="{{ !empty(old('quantity')) ? old('quantity') : $model->quantity }}">
  @if($errors->has('quantity'))
    <span class="help-block">{{ $errors->first('quantity') }}</span>
  @endif
</div>

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endsection

@section('js')
<script src="{{ asset('vendor/autonumeric/autoNumeric.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>

<script>
var TYPE_INVENTORY_RAW = "{{ array_keys($typeInventoryOptions, 'raw material')[0] }}";
var baseBeApiUrl = "{{ $baseBeApiUrl }}";
var quantityMax = 0;
var isQuantityOver = false;

var quantityField = new AutoNumeric($('input[name="quantity"]')[0], {
  emptyInputBehavior: 'zero',
  unformatOnSubmit: true
});

$(".datepicker").datepicker({ autoClose: true });
$('select[name="item_type"]').select2();
$('select[name="item_type"]').change(function () {
  var type = $(this).val();
  var warehouseDepartureId = $('select[name="warehouse_departure_id"]').val();

  select2AjaxHandler('select[name="item_id"]', `{{ $baseBeApiUrl }}/inventory/${warehouseDepartureId}/${type}`);
});

$('select[name="item_id"]').change(function () {
  var type = $('select[name="item_type"]').val();
  var id = $(this).val();
  var warehouseDepartureId = $('select[name="warehouse_departure_id"]').val();
  
  var url = `{{ $baseBeApiUrl }}/inventory/${warehouseDepartureId}/${type}/${id}`;

  $.ajax({
      type: "GET",
      url: url,
      success: function(response) {
          quantityMax = response.stock
          quantityField.set(response.stock);
      },
      error: function(err) { console.log(`failed fetch : ${err}`) }
  });

});

$('input[name="quantity"]').change(function () {
  var quantityNow = $(this).val();

  var qtyCleanComa = isNaN(quantityNow) ? quantityNow.replace(/,/g, "") : quantityNow;
  var qty = Number(qtyCleanComa);

  var maxQtyCleanComa = isNaN(quantityMax) ? quantityMax.replace(/,/g, "") : quantityMax;
  var maxQty = Number(maxQtyCleanComa);

  if(qty > maxQty) {
    isQuantityOver = true
    quantityField.set(maxQty)

    $(this).parent().addClass('has-error');
    $(this).after(`<span class="help-block">max: ${quantityMax}</span>`)
    return
  }

  quantityField.set(qty)

  $(this).parent().removeClass('has-error');
  $(this).next().remove();
})

// select2AjaxHandler('select[name="item_id"]', `{{ $baseBeApiUrl }}/${warehouseDepartureId}/{{ $itemType }}`, '{{ $itemId }}');
select2AjaxHandler('select[name="warehouse_departure_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, '{{ $warehouseDepartureId }}');
select2AjaxHandler('select[name="warehouse_arrival_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, '{{ $warehouseArrivalId }}');
</script>
@endsection