<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $deliveryNumber = !empty(old('delivery_number')) ? old('delivery_number') : $model->delivery_number; ?>
<?php $customerId = !empty(old('customer_id')) ? old('customer_id') : $model->customer_id; ?>  
<?php $shMethodId = !empty(old('shipping_method_id')) ? old('shipping_method_id') : $model->shipping_method_id; ?>  
<?php $paymentMethodId = !empty(old('payment_method_id')) ? old('payment_method_id') : $model->payment_method_id; ?>  
<?php $warehouseId = !empty(old('warehouse_id')) ? old('warehouse_id') : $model->warehouse_id; ?>
<?php $invoiceDetails = !empty(old('invoice_details')) ? old('invoice_details') : $model->invoice_details ?>

<div class="row">

  <div class="col-md-6">
    <div class="form-group @if($errors->has('created_at')) has-error @endif">
      <label>Invoice Date</label>

      <div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input 
          type="text" 
          class="form-control pull-right" 
          name="created_at" 
          value="{{ empty($model->created_at) ? date('m/d/Y') : $model->created_at->format('m/d/Y') }}"
          readonly>
      </div>
      @if($errors->has('instruction_date'))
        <span class="help-block">{{ $errors->first('instruction_date') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('due_date')) has-error @endif">
      <label>Due Date</label>

      <div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input 
          type="text" 
          class="form-control datepicker pull-right" 
          name="due_date" 
          value="{{ !empty(old('due_date')) ? old('due_date') : date('m/d/Y') }}">
      </div>
      @if($errors->has('due_date'))
        <span class="help-block">{{ $errors->first('due_date') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('number')) has-error @endif">
      <label for="">Invoice Number</label>
      <input type="text" class="form-control" name="number" placeholder="order number" value="{{ !empty(old('number')) ? old('number') : $model->number }}" readonly>
      @if($errors->has('number'))
        <span class="help-block">{{ $errors->first('number') }}</span>
      @endif
    </div>
     
    <div class="form-group @if($errors->has('delivery_number')) has-error @endif">
      <label>Delivery Note Number</label>
      <select 
        class="has-ajax-form form-control" 
        name="delivery_number" 
        id="delivery_number" 
        style="width: 100%;" 
        tabindex="-1"
        data-load="{{ $baseBeApiUrl . '/sales/delivery' }}"> </select>
      @if($errors->has('delivery_number'))
        <span class="help-block">{{ $errors->first('delivery_number') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('customer_id')) has-error @endif">
      <label>Customer</label>
      <select class="form-control" name="customer_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('customer_id'))
        <span class="help-block">{{ $errors->first('customer_id') }}</span>
      @endif
    </div>

  </div>
  
  <div class="col-md-6">

    <div class="form-group @if($errors->has('shipping_method_id')) has-error @endif">
      <label>Shipping Method</label>
      <select class="form-control" name="shipping_method_id" style="width: 100%;" tabindex="-1">
        <option value="">Pilih Shipping Method</option>
        @foreach($shippingMethods as $shippingMethodId => $shippingMethod)
        <option value="{{ $shippingMethodId }}" @if($shMethodId == $shippingMethodId) selected @endif>{{ $shippingMethod['label'] }}</option>
        @endforeach
      </select>
      @if($errors->has('shipping_method_id'))
        <span class="help-block">{{ $errors->first('shipping_method_id') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('address_id')) has-error @endif">
      <label>Shipping Address</label>
      <select class="form-control" name="address_id" style="width: 100%;" tabindex="-1"> 
        <option value=""></option>
      </select>
      @if($errors->has('address_id'))
        <span class="help-block">{{ $errors->first('address_id') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('shipping_cost')) has-error @endif">
      <label for="">Shipping Cost</label>
      <input type="text" class="form-control" name="shipping_cost" placeholder="shipping cost" value="{{ !empty(old('shipping_cost')) ? old('shipping_cost') : $model->shipping_cost }}" readonly>
      @if($errors->has('shipping_cost'))
        <span class="help-block">{{ $errors->first('shipping_cost') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('payment_method_id')) has-error @endif">
      <label>Payment Method</label>
      <select class="form-control" name="payment_method_id" style="width: 100%;" tabindex="-1">
        <option value="">Pilih Payment Method</option>
        @foreach($paymentMethods as $paymentMethodId => $paymentMethod)
        <option value="{{ $paymentMethod->id }}" @if($paymentMethodId == $paymentMethod->id) selected @endif>{{ $paymentMethod->name }}</option>
        @endforeach
      </select>
      @if($errors->has('payment_method_id'))
        <span class="help-block">{{ $errors->first('payment_method_id') }}</span>
      @endif
    </div>
    
    <div class="form-group @if($errors->has('discount')) has-error @endif">
      <label for="">Discount (%)</label>
      <input type="text" class="form-control" name="discount" placeholder="Discount" value="{{ !empty(old('discount')) ? old('discount') : $model->discount }}">
      @if($errors->has('discount'))
        <span class="help-block">{{ $errors->first('discount') }}</span>
      @endif
    </div>

    <!-- <div class="form-group @if($errors->has('downpayment')) has-error @endif">
      <label for="">Downpayment (Rp.)</label>
      <input type="text" class="form-control" name="downpayment" placeholder="Downpayment" value="{{ !empty(old('downpayment')) ? old('downpayment') : $model->downpayment }}">
      @if($errors->has('downpayment'))
        <span class="help-block">{{ $errors->first('downpayment') }}</span>
      @endif
    </div> -->

  </div>

</div>

<hr>

<div id="vue-dynamic-element">
<div class="form-group">
  <label>List Item Material</label>   
    
  <table id="vue-dynamic-element" class="table table-bordered table-hover">
    <thead class="table-header-primary">
      <tr>
        <th>Item Material</th>
        <th width="15%">Length</th>
        <th width="10%">Sheet</th>
        <th width="10%">Qty</th>
        <th width="10%">Amount</th>
      </tr>
    </thead>

    <tbody>    
      <tr v-for="(element, i) in elements" :key="element.id">
        <td> 
          <input type="hidden" :name="`invoice_details[${i}][id]`" v-model="element.id">
          <input type="hidden" :name="`invoice_details[${i}][sales_detail_id]`" v-model="element.sales_detail_id">
          <input class="form-control" type="text" v-model="element.item_name" readonly>
        </td>
        <td>
          <input type="hidden" v-model="element.length" readonly>
          <input class="form-control" type="text" v-model="element.length_formated" readonly>
        </td>
        <td>
          <input class="form-control" type="text" v-model="element.sheet" readonly>
        </td>
        <td>
          <input class="form-control" type="text" :name="`invoice_details[${i}][quantity]`" v-model="element.quantity" @change="isNumber(i, 'quantity')" placeholder="quantity" readonly>
        </td>
        <td>
          <input class="form-control" type="text" :name="`invoice_details[${i}][total_price]`" v-model="element.total_price" @change="isNumber(i, 'total_price')" placeholder="total" readonly>
        </td>
      </tr>

      <tr>
        <th>
          @if($errors->has('invoice_details.*'))
            <span class="help-block text-red">* {{ $errors->first('invoice_details.*') }}</span>
          @endif
        </th>
        <th></th>
        <th></th>
        <th>
          <label for="">@{{ getTotalQuantity() | formatRupiah }}</label>
        </th>
        <th>
          <label for="">Rp. @{{ getTotalAmount() | formatRupiah }}</label>
          <input type="hidden" name="total_bill" v-model="getTotalAmount()">
        </th>
      </tr>
      <tr>
        <th> Shipping Cost </th>
        <th></th>
        <th></th>
        <th></th>
        <th>
          <label for="">Rp. @{{ shipping_cost | formatRupiah }}</label>
        </th>
      </tr>
      <tr>
        <th><label>Discount</label></th>
        <th></th>
        <th></th>
        <th></th>
        <th><label for="">Rp. @{{ getDiscount() | formatRupiah }}</label></th>
      </tr>
      <tr>
        <th> Grand Total </th>
        <th></th>
        <th></th>
        <th></th>
        <th>
          <label for="">Rp. @{{ getGrandTotal() | formatRupiah }}</label>
          <input type="hidden" name="grand_total_bill" v-model="getGrandTotal()">
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
<script src="{{ asset('vendor/autonumeric/autoNumeric.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/vue.js') }}"></script>
<script>
var METHOD_PICKUP_POINT = 2;
var METHOD_DELIVERY = 3;
var baseBeApiUrl = "{{ $baseBeApiUrl }}";
var elements = <?php echo json_encode($invoiceDetails); ?>;
var shipping_cost = 0;
var discount = 0;

shippingCostFormated = new AutoNumeric($('input[name="shipping_cost"]')[0], {
  emptyInputBehavior: 'zero',
  unformatOnSubmit: true
});

$(".datepicker").datepicker({ autoClose: true });
$('[data-toggle="tooltip"]').tooltip();

$('select[name="payment_method_id"]').select2();

$('input[name="discount"]').change(function() {
  app.discount = $(this).val();
});

$('select[name="shipping_method_id"]').select2();
$('select[name="shipping_method_id"]').change(function () {
  var itemUri = '';
  var type = $(this).val();

  if(type == METHOD_PICKUP_POINT) {
    itemUri = 'warehouse';
  } else if(type == METHOD_DELIVERY) {
    var userId = $('select[name="customer_id"]').val();
    itemUri = 'customer/' + userId + '/address';
  }

  select2AjaxHandler('select[name="address_id"]', `{{ $baseBeApiUrl }}/${itemUri}`);
});
$('select[name="address_id"]').change(function () {
  var shippingMethod = $('select[name="shipping_method_id"]').val();

  if(shippingMethod == METHOD_DELIVERY) {
    var params = {
      sales_id: $('select[name="sales_id"]').val(),
      address_id: $('select[name="address_id"]').val()
    };
    $.post( "{{ $baseBeApiUrl }}/shipping/cost", params)
        .done(function( data ) {
          var message = `charge ${data.shipping_cost_enforce.charge_per_km}/km, total distance ${data.distance_in_km} km`
          shippingCostFormated.set(data.total_charge)
          app.shipping_cost = data.total_charge
          // app.shipping_cost = 1000
          $(`input[name="shipping_cost"]`).after('<span class="help-block">' + message + '</span>')
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
          $(`input[name="shipping_cost"]`).after('<span class="help-block">' + jqXHR.responseJSON.message + '</span>')
        });
  }
});

$(".has-ajax-form").change(function() {
    var url = $(this).data('load') + '/' + $(this).val()

    $.ajax({
        type: "GET",
        url: url,
        success: function(response) {
            // set value form
            $('input[name="id"]').val(response.id);
            $('input[name="discount"]').val(response.discount);

            $('select[name="shipping_method_id"]').val(response.shipping_method_id);
            $('select[name="shipping_method_id"]').trigger('change');
            
            $('select[name="payment_method_id"]').val(response.payment_method_id);
            $('select[name="payment_method_id"]').trigger('change');
            // $('input[name="downpayment"]').val(response.downpayment);            

            select2AjaxHandler('select[name="customer_id"]', `{{ $baseBeApiUrl . '/customer' }}`, response.customer_id, true);
            select2AjaxHandler('select[name="warehouse_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, response.warehouse_id, true);
            
            app.discount = response.discount
            app.elements = response.delivery_note_details

        },
        error: function(err) { console.log(`failed fetch : ${err}`) }
    });
});

select2AjaxHandler('select[name="delivery_number"]', `{{ $baseBeApiUrl . '/sales/delivery' }}`, '{{ $deliveryNumber }}');
select2AjaxHandler('select[name="customer_id"]', `{{ $baseBeApiUrl . '/customer' }}`, '{{ $customerId }}', true);
select2AjaxHandler('select[name="warehouse_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, '{{ $warehouseId }}', true);

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
    shipping_cost: shipping_cost,
    discount: discount
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
        sales_detai_id: '',
        quantity: 0,
        item_name: '',
        length: 0,
        sheet: 0,
        total_price: 0,
        length_formated: ''
      })
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
    getDiscount () {
        var vm = this
        var totalAmount = vm.getTotalAmount()

        return totalAmount * (vm.discount/100);
    },
    getTotalAmount () {
        var vm = this

        var amounts = this.elements.map((item, i) => {
          var amount = isNaN(vm.elements[i].total_price) ? vm.elements[i].total_price.replace(/,/g, "") : vm.elements[i].total_price;

          return Number(amount)
        })

        var result = amounts.reduce((result, amount) => {
          return result + amount
        }, 0)

        return result
    },
    getGrandTotal () {
        return (this.getTotalAmount () + this.shipping_cost) - this.getDiscount()
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
    }
  }
})
</script>
@endsection