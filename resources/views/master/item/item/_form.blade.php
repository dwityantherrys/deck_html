<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $categoryId = !empty(old('item_category_id')) ? old('item_category_id') : $model->item_category_id; ?>
<?php $unitId = !empty(old('unit_id')) ? old('unit_id') : $model->unit_id; ?>
<?php $vendorId = !empty(old('item_vendor_id')) ? old('item_vendor_id') : $model->item_vendor_id; ?>
<?php $isActive = !empty(old('is_active')) ? old('is_active') : ($model->is_active ? $model->is_active : 1); ?>
<?php $hasLengthOptions = !empty(old('has_length_options')) ? old('has_length_options') : $model->has_length_options; ?>
<?php $itemImages = $model->images; ?>
<?php $itemMaterials = !empty(old('item_materials')) ? old('item_materials') : $model->item_materials ?>

<div class="row">
    <div class="col-md-6">
        <div class="form-group @if($errors->has('name')) has-error @endif">
          <label for="">Name</label>
          <input type="text" class="form-control" name="name" placeholder="item name" value="{{ !empty(old('name')) ? old('name') : $model->name }}" required>
          @if($errors->has('name'))
            <span class="help-block">{{ $errors->first('name') }}</span>
          @endif
        </div>
    </div>
    
</div>

<div class="form-group">
  <label>Description / Spesification</label>
  <textarea class="form-control" rows="3" name="description" placeholder="item description">{{ $model->description }}</textarea>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group @if($errors->has('item_vendor_id')) has-error @endif">
          <label>Vendor</label>
          <select class="form-control" name="item_vendor_id" style="width: 100%;" tabindex="-1"> </select>
          @if($errors->has('item_vendor_id'))
            <span class="help-block">{{ $errors->first('item_vendor_id') }}</span>
          @endif
          <span class="help-block">data vendor tidak ada? <a class="text-red" href="{{ url('/master/customer/create') }}" target="_blank">new vendor</a></span>
        </div>  
    </div>
    <div class="col-md-6">
        <div class="form-group @if($errors->has('purchase_price')) has-error @endif">
          <label for="">Purchase Price</label>
            <div id="app">
              <idr v-model="total" name="purchase_price" placeholder="Purchase Price" value="{{ !empty(old('purchase_price')) ? old('purchase_price') : $model->purchase_price }}" required></idr>
            </div>
          @if($errors->has('purchase_price'))
            <span class="help-block">{{ $errors->first('purchase_price') }}</span>
          @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
          <label>Jenis Pajak</label>
          <select class="form-control " name="jenis_pajak" id="" style="width: 100%;" tabindex="-1">
            <option value="0" @if($isActive == 0) selected @endif>None</option>
            <option value="1" @if($isActive == 1) selected @endif>11%</option>
            <option value="2" @if($isActive == 2) selected @endif>11% Include</option>
          </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group @if($errors->has('item_category_id')) has-error @endif">
          <label>Category</label>
          <select class="form-control" name="item_category_id" id="" style="width: 100%;" tabindex="-1"> </select>
          @if($errors->has('item_category_id'))
            <span class="help-block">{{ $errors->first('item_category_id') }}</span>
          @endif
          <span class="help-block">data category tidak ada? <a class="text-red" href="{{ url('/master/item/category/create') }}" target="_blank">new category</a></span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
    <div class="form-group @if($errors->has('unit_id')) has-error @endif">
          <label>Unit</label>
          <select class="form-control" name="unit_id" id="" style="width: 100%;" tabindex="-1"> </select>
          @if($errors->has('unit_id'))
            <span class="help-block">{{ $errors->first('unit_id') }}</span>
          @endif
          <span class="help-block">data unit tidak ada? <a class="text-red" href="{{ url('/master/unit/create') }}" target="_blank">new unit</a></span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group @if($errors->has('price')) has-error @endif">
          <label for="">Quantity</label>
          <input type="text" class="form-control" name="quantity" placeholder="item Quantity" value="{{ !empty(old('quantity')) ? old('quantity') : $model->quantity }}">
          @if($errors->has('quantity'))
            <span class="help-block">{{ $errors->first('quantity') }}</span>
          @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
          <label>Type</label>
          <select class="form-control " name="type" id="" style="width: 100%;" tabindex="-1">
            <option value="sparepart" @if($isActive == "sparepart") selected @endif>SPAREPART</option>
            <option value="service" @if($isActive == "service") selected @endif>SERVICE</option>
          </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Foto Produk</label>
            <span class="show-image-preview pull-right" data-url="{{ $model->product_image_url }}" @if($model->product_image) style="display:block" @endif>image preview</span>
            <input type="file" class="has-image-preview form-control" name="product_image" value="">
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
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endsection


@section('js')

<script src="{{ asset('vendor/autonumeric/autoNumeric.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/vue.js') }}"></script>
<script>
$(".datepicker").datepicker({ autoClose: true });
select2AjaxHandler('select[name="item_category_id"]', `{{ $baseBeApiUrl . '/item-category' }}`, '{{ $categoryId }}');
select2AjaxHandler('select[name="unit_id"]', `{{ $baseBeApiUrl . '/unit' }}`, '{{ $unitId }}');

select2AjaxHandler('select[name="item_vendor_id"]', `{{ $baseBeApiUrl . '/customer' }}`, '{{ $vendorId }}');

Vue.component('idr', {
    template: '<input type="text" class="form-control" v-model="currentValue" @input="handleInput" />',
    props: {
      value: {
        type: [String, Number],
        default: ""
      },
    },
    data: () => ({
      currentValue: ''
    }),
    watch: {
      value: {
        handler(after) {
          this.currentValue = this.format(after)
        },
        immediate: true
      }
    },
    methods: {
      format: value => (value + '').replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, "."),
      
      handleInput() {
        this.currentValue = this.format(this.currentValue)
        this.$emit('input', (this.currentValue + '').replace(/[^0-9]/g, ""))
      }
    }
})

new Vue({
    el: '#app',
    data: {
        total: 0,
    }
})

</script>
@endsection
