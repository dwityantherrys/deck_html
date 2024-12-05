<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $requestNumber = !empty(old('request_number')) ? old('request_number') : $model->request_number; ?>
<?php $requestBy = !empty(old('request_by')) ? old('request_by') : $model->request_by; ?>
<?php $vendorId = !empty(old('vendor_id')) ? old('vendor_id') : $model->vendor_id; ?>
<?php $warehouseId = !empty(old('warehouse_id')) ? old('warehouse_id') : $model->warehouse_id; ?>
<?php $purchaseDetails = !empty(old('purchase_details')) ? old('purchase_details') : $model->purchase_details ?>

<div class="row">

  <div class="col-md-6">
    <div class="form-group @if($errors->has('order_date')) has-error @endif">
      <label>Order Date</label>

      <div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input type="text" class="form-control datepicker pull-right" name="order_date" value="{{ !empty(old('order_date')) ? old('order_date') : optional($model->order_date)->format('m/d/Y') }}">
      </div>
      @if($errors->has('order_date'))
        <span class="help-block">{{ $errors->first('order_date') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('request_number')) has-error @endif">
      <label>Purchase Request Number</label>
      <select 
        class="has-ajax-form form-control" 
        name="request_number" 
        id="" 
        style="width: 100%;" 
        tabindex="-1"
        data-load="{{ $baseBeApiUrl . '/purchase/request' }}"> </select>
      @if($errors->has('request_number'))
        <span class="help-block">{{ $errors->first('request_number') }}</span>
      @endif
    </div>
     
    <div class="form-group @if($errors->has('request_by')) has-error @endif">
      <label>PIC</label>
      <select class="form-control" name="request_by" id="" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('request_by'))
        <span class="help-block">{{ $errors->first('request_by') }}</span>
      @endif
      <span class="help-block">data pic tidak ada? <a class="text-red" href="{{ url('/master/employee/create') }}" target="_blank">new pic</a></span>
    </div>

    <div class="form-group @if($errors->has('order_number')) has-error @endif">
      <label for="">Order Number</label>
      <input type="text" class="form-control" name="order_number" placeholder="order number" value="{{ !empty(old('order_number')) ? old('order_number') : $model->order_number }}" readonly>
      @if($errors->has('order_number'))
        <span class="help-block">{{ $errors->first('order_number') }}</span>
      @endif
    </div>

  </div>
  
  <div class="col-md-6">

    <div class="form-group @if($errors->has('vendor_id')) has-error @endif">
      <label>Vendor</label>
      <select class="form-control" name="vendor_id" id="" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('vendor_id'))
        <span class="help-block">{{ $errors->first('vendor_id') }}</span>
      @endif
      <span class="help-block">data vendor tidak ada? <a class="text-red" href="{{ url('/master/customer/create') }}" target="_blank">new vendor</a></span>
    </div>  

    <div class="form-group @if($errors->has('warehouse_id')) has-error @endif">
      <label>Warehouse</label>
      <select class="form-control" name="warehouse_id" id="" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('warehouse_id'))
        <span class="help-block">{{ $errors->first('warehouse_id') }}</span>
      @endif
      <span class="help-block">data warehouse tidak ada? <a class="text-red" href="{{ url('/master/warehouse/create') }}" target="_blank">new warehouse</a></span>
    </div>

  </div>

</div>

<hr>

<div id="vue-dynamic-element">
<div class="form-group">
  <label>List Raw Material</label>   
    
  <table id="vue-dynamic-element" class="table table-bordered table-hover">
    <thead class="table-header-primary">
      <tr>
        <th width="50%">Raw Material</th>
        <th>Qty</th>
        <th>Est. Price</th>
        <th>Amount</th>
        <th width="10%"></th>
      </tr>
    </thead>

    <tbody>    
      <tr v-for="(element, i) in elements" :key="element.id">
        <td> 
          <input type="hidden" :name="`purchase_details[${i}][id]`" v-model="element.id">
          <vue-select2 
            :url="`{{ $baseBeApiUrl . '/raw-material' }}`"
            :name="`purchase_details[${i}][raw_material_id]`"
            :value="element.raw_material_id"
            v-on:selected="element.raw_material_id = $event"/>
        </td>
        <td>
          <input type="text" class="form-control" :name="`purchase_details[${i}][quantity]`" v-model="element.quantity" @change="isNumber(i, 'quantity')" placeholder="quantity">
        </td>
        <td>
          <input type="text" class="form-control" :name="`purchase_details[${i}][estimation_price]`" v-model="element.estimation_price" @change="isNumber(i, 'estimation_price')" placeholder="estimation price">
        </td>
        <td>
          <input type="text" class="form-control" :name="`purchase_details[${i}][amount]`" :value="getAmount(i)" placeholder="estimation price">
        </td>
        <td>
          <button type="button" class="btn btn-default text-red" @click="removeElement(i)"><i class="fa fa-minus"></i></button>
        </td>
      </tr>

      <tr>
        <th>
          @if($errors->has('purchase_details.*'))
            <span class="help-block text-red">* {{ $errors->first('purchase_details.*') }}</span>
          @endif
        </th>
        <th>
          <label for="">@{{ getTotalQuantity() | formatRupiah }}</label>
        </th>
        <th></th>
        <th>
          <label for="">Rp. @{{ getTotalAmount() | formatRupiah }}</label>
          <input type="hidden" name="total_price" :value="getTotalAmount()">
        </th>
        <th>
          <button type="button" class="btn btn-default text-success" @click="addElement()"><i class="fa fa-plus"></i></button>        
        </th>
      </tr>
    </tbody>
  </table>
</div>

</div> <!-- end vue wrapper -->

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endsection

@section('js')
<script src="{{ asset('vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/vue.js') }}"></script>
<script>
var baseBeApiUrl = "{{ $baseBeApiUrl }}";
var elements = <?php echo json_encode($purchaseDetails); ?>;

$(".datepicker").datepicker({ autoClose: true });
$('[data-toggle="tooltip"]').tooltip();

$(".has-ajax-form").change(function() {
    var url = $(this).data('load') + '/' + $(this).val()

    $.ajax({
        type: "GET",
        url: url,
        success: function(response) {
            // set value form
            $('input[name="id"]').val(response.id);
            select2AjaxHandler('select[name="request_by"]', `{{ $baseBeApiUrl . '/employee' }}`, response.request_by);
            select2AjaxHandler('select[name="vendor_id"]', `{{ $baseBeApiUrl . '/customer' }}`, response.vendor_id);
            select2AjaxHandler('select[name="warehouse_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, response.warehouse_id);
            app.elements = response.purchase_details
        },
        error: function(err) { console.log(`failed fetch : ${err}`) }
    });
});

select2AjaxHandler('select[name="vendor_id"]', `{{ $baseBeApiUrl . '/customer' }}`, '{{ $vendorId }}');
select2AjaxHandler('select[name="warehouse_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, '{{ $warehouseId }}');
select2AjaxHandler('select[name="request_by"]', `{{ $baseBeApiUrl . '/employee' }}`, '{{ $requestBy }}');
select2AjaxHandler('select[name="request_number"]', `{{ $baseBeApiUrl . '/purchase/request' }}`, '{{ $requestNumber }}');

Vue.filter('formatRupiah', function (value) {
  return new Intl.NumberFormat('IDR', {}).format(value)
})

Vue.component('vue-select2', {
  template: `<select class="form-control" :name="name" style="width: 100%"> </select>`,
  props: [ 'url', 'name', 'value' ],
  methods: {
    getDataById (id) {
      var vm = this

      $.ajax({
        type: "GET",
        url: `${this.url}/${id}`,
        success: function (response) { 
          var newOption = new Option(response.name, response.id, true, true);
          $(vm.$el).append(newOption).trigger('change');
        },
        error: function (err) {
          console.log(`failed fetch : ${err}`)
        }
      });
    },
  },
  mounted: async function() {
    var vm = this

    await $(this.$el).select2({
      ajax: {
        url: this.url,
        data: function (params) {
          var query = { searchKey: params.term }
          return query;
        },
        processResults: function (data) {
          return {
            results: data.results.map((param) => {
              param.text = param.name
              return param
            })
          }
        }
      },
    })
    .val(this.value)
    .trigger('change')
    .on('change', function () { 
      vm.$emit('selected', this.value)
    })

    if(this.value) await this.getDataById (this.value) 

  },
  destroyed: function () {
    $(this.$el).off().select2('destroy')
  },
  watch: {
    value: function (value) {
      // update value
      $(this.$el).val(value).trigger('change')
    },
    options: function (options) {
      // update options
      $(this.$el).empty().select2({ data: options })
    }
  },
});

var app = new Vue({
  el: '#vue-dynamic-element',
  data: {
    elements: elements,
  },
  mounted: function () {
    // check if vue working
    console.log(`${this.$el.id} mounted`)

    if(!this.elements.length) this.addElement()
  },
  methods: {
    _randomNumber () {
      return Math.floor(Math.random() * 10)
    },
    addElement () {
      this.elements.push({
        id: this._randomNumber(),
        raw_material_id: '',
        quantity: 0,
        estimation_price: 0,
      })
    },
    removeElement (index) {
      if(this.elements.length == 1) return

      this.elements.splice(index, 1)
    },
    isObjectExist(obj) {
      if(typeof obj == 'undefined') {
        return false
      }
        
      if(Object.keys(obj).length === 0){
        return false
      }

      return true  
    },
    isNumber(index, attribut) {
      var cleanComa = this.elements[index][attribut].replace(/,/g, "");
      var castNumber = Number(cleanComa);

      if (isNaN(castNumber)) {
        this.elements[index][attribut] = 0;
        return  
      }

      this.elements[index][attribut] = this.$options.filters.formatRupiah(castNumber)
    },
    getAmount(i) {
      var qty = isNaN(this.elements[i].quantity) ? this.elements[i].quantity.replace(/,/g, "") : this.elements[i].quantity;
      var estPrice = isNaN(this.elements[i].estimation_price) ? this.elements[i].estimation_price.replace(/,/g, "") : this.elements[i].estimation_price;

      return this.$options.filters.formatRupiah(qty*estPrice)
    },
    getTotalQuantity () {
      var result = 0;
      var quantities = this.elements.map((item) => {
        var quantity = isNaN(item.quantity) ? item.quantity.replace(/,/g, "") : item.quantity;
        return Number(quantity)
      })
      
      result = quantities.reduce((result, quantity) => {
        return result + quantity
      }, 0)

      return result
    },
    getTotalAmount () {
        var vm = this
        var amounts = this.elements.map((item, i) => {
          var amount = vm.getAmount(i).replace(/,/g, "")
          return Number(amount)
        })

        var result = amounts.reduce((result, amount) => {
          return result + amount
        }, 0)

        return result
    }
  }
})
</script>
@endsection