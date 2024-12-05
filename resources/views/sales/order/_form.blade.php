<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $quotationNumber = !empty(old('quotation_number')) ? old('quotation_number') : $model->id; ?>
<?php $createdBy = !empty(old('created_by')) ? old('created_by') : $model->created_by; ?>
<?php $shMethodId = !empty(old('shipping_method_id')) ? old('shipping_method_id') : $model->shipping_method_id; ?>
<?php
  $paymentMethodId = (!empty(old('payment_method_id')) ?
                        old('payment_method_id') : !empty($model->payment_method_id)) ? 
                                                      $model->payment_method_id : 1;
?>
<?php $paymentBankChannelId = !empty(old('payment_bank_channel_id')) ? old('payment_bank_channel_id') : $model->payment_bank_channel_id; ?>
<?php $customerId = !empty(old('customer_id')) ? old('customer_id') : $model->customer_id; ?>
<?php $warehouseId = !empty(old('warehouse_id')) ? old('warehouse_id') : $model->warehouse_id; ?>
<?php $salesDetails = !empty(old('job_order_details')) ? old('job_order_details') : $model->job_order_details ?>

<div class="row">

  <div class="col-md-6">
    <div class="form-group @if($errors->has('order_date')) has-error @endif">
      <label>Order Date</label>

      <div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input
          type="text"
          class="form-control pull-right"
          name="order_date"
          value="{{ empty($model->order_date) ? date('m/d/Y') : $model->order_date->format('m/d/Y') }}"
          readonly>
      </div>
      @if($errors->has('order_date'))
        <span class="help-block">{{ $errors->first('order_date') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('created_by')) has-error @endif">
      <label>PIC</label>
      <select class="form-control" name="created_by" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('created_by'))
        <span class="help-block">{{ $errors->first('created_by') }}</span>
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

    <div class="form-group @if($errors->has('warehouse_id')) has-error @endif">
      <label>Warehouse</label>
      <select class="form-control" name="warehouse_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('warehouse_id'))
        <span class="help-block">{{ $errors->first('warehouse_id') }}</span>
      @endif
      <span class="help-block">data warehouse tidak ada? <a class="text-red" href="{{ url('/master/warehouse/create') }}" target="_blank">new warehouse</a></span>
    </div>

    <div class="form-group @if($errors->has('customer_id')) has-error @endif">
      <label>Customer</label>
      <select class="form-control" name="customer_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('customer_id'))
        <span class="help-block">{{ $errors->first('customer_id') }}</span>
      @endif
      <span class="help-block">data customer tidak ada? <a class="text-red" href="{{ url('/master/customer/create') }}" target="_blank">new customer</a></span>
    </div>

  </div>

  <div class="col-md-6">

    <div class="form-group @if($errors->has('discount')) has-error @endif">
      <label for="">Discount (%)</label>
      <input type="text" class="form-control" name="discount" placeholder="Discount" value="{{ !empty(old('discount')) ? old('discount') : $model->discount }}">
      @if($errors->has('discount'))
        <span class="help-block">{{ $errors->first('discount') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('payment_method_id')) has-error @endif">
      <label>Payment Method</label>
      <select class="form-control" name="payment_method_id" style="width: 100%;" tabindex="-1">
        <option value="">Pilih Payment Method</option>
        @foreach($paymentMethods as $paymentMethod)
        <option value="{{ $paymentMethod->id }}" @if($paymentMethodId == $paymentMethod->id) selected @endif>{{ $paymentMethod->name }}</option>
        @endforeach
      </select>
      @if($errors->has('payment_method_id'))
        <span class="help-block">{{ $errors->first('payment_method_id') }}</span>
      @endif
    </div>

    <label for="">Transaction Channel</label>
    <div class="transaction_channel">
      @if(!empty($model->transaction_channel))
      <small class="label bg-{{ $transaction_channels[$model->transaction_channel]['label-color'] }}">
            <i class="{{ $transaction_channels[$model->transaction_channel]['icon'] }}" style="margin-right: 5px;"></i>
            {{ $transaction_channels[$model->transaction_channel]['label'] }}
      </small>
      @else
      <small class="label bg-green">
            <i class="fa fa-desktop" style="margin-right: 5px;"></i>
            Website
      </small>
      @endif
    </div>

    @if(empty($model->transaction_channel))
    <div class="form-group @if($errors->has('payment_bank_channel_id')) has-error @endif" style="margin-top: 15px;">
      <label>Payment Bank</label>
      <select class="form-control" name="payment_bank_channel_id" style="width: 100%;" tabindex="-1">
        <option value="">Pilih Bank</option>
        @foreach($paymentBankChannels as  $paymentBankChannel)
        <option value="{{ $paymentBankChannel->id }}" @if($paymentBankChannelId == $paymentBankChannel->id) selected @endif>
          {{ $paymentBankChannel->name }} A/N {{ $paymentBankChannel->rekening_name }}
        </option>
        @endforeach
      </select>
      @if($errors->has('payment_bank_channel_id'))
        <span class="help-block">{{ $errors->first('payment_bank_channel_id') }}</span>
      @endif
    </div>
    @endif

    <div class="form-group">
      <label for="">Pajak ?</label><br/>
      <input type="checkbox" id="use_tax" name="use_tax" value="1">
    </div>

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
        <th width="10%">Qty</th>
        <th width="10%">Price</th>
        <th width="10%">Amount</th>
        <th width="10%"></th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="(element, i) in elements" :key="element.id">
        <td>
          <input type="hidden" :name="`job_order_details[${i}][id]`" v-model="element.id">
          <vue-select2 
              :url="`{{ $baseBeApiUrl . '/items' }}`"
              :name="`job_order_details[${i}][item_material_id]`"
              :value="element.item_material_id"
              v-on:selected="getItemByName(i, $event)"
              :readonly="true" />
        </td>
        <td>
          <input type="text" class="form-control" :name="`job_order_details[${i}][quantity]`" v-model="element.quantity" @change="isNumber(i, 'quantity')" placeholder="quantity">
        </td>
        <td>
          <input type="text" class="form-control" style="margin-bottom: 5px" :name="`job_order_details[${i}][estimation_price]`" v-model="element.estimation_price" @change="isNumber(i, 'estimation_price')" placeholder="price">
        </td>
        <td>
          <input type="text" class="form-control" :name="`job_order_details[${i}][total_price]`" :value="getAmount(i)" placeholder="price">
        </td>
        <td>
          <button type="button" class="btn btn-default text-red" @click="removeElement(i)"><i class="fa fa-minus"></i></button>
        </td>
      </tr>

      <tr>
        <th>
          @if($errors->has('job_order_details.*'))
            <span class="help-block text-red">* {{ $errors->first('job_order_details.*') }}</span>
          @endif
        </th>
        <th>
        </th>
        <th>
        <label for="">@{{ getTotalQuantity() | formatRupiah }}</label>
        </th>
        <th>
          <label for="">Rp. @{{ getTotalAmount() | formatRupiah }}</label>
          <input type="hidden" name="total_price" :value="getTotalAmount()">
        </th>
        <th>
          <button type="button" class="btn btn-default text-success" @click="addElement()"><i class="fa fa-plus"></i></button>
        </th>
      </tr>

      <tr>
        <th colspan="3"><label>Pajak</label></th>
        <th><label for="">Rp. @{{ getTax() | formatRupiah }}</label></th>
        <input type="hidden" name="amount_tax" :value="getTax()">
      </tr>

      <tr>
        <th colspan="3"><label>Discount</label></th>
        <th><label for="">Rp. @{{ getDiscount() | formatRupiah }}</label></th>
      </tr>

      <tr>
        <th colspan="3"><label>Grand Total</label></th>
        <th><label for="">Rp. @{{ getGrandAmount() | formatRupiah }}</label></th>
        <input type="hidden" name="grand_total_price" :value="getGrandAmount()">
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
const METHOD_PICKUP_POINT = 2;
const METHOD_DELIVERY = 3;

var baseBeApiUrl = "{{ $baseBeApiUrl }}";
var transactionChannels = <?php echo json_encode($transaction_channels); ?>;
var elements = <?php echo json_encode($salesDetails); ?>;
var warehouse_id = 0;
var discount = 0;

var discountField = new AutoNumeric($('input[name="discount"]')[0], {
  emptyInputBehavior: 'zero',
  unformatOnSubmit: true
});

$(".datepicker").datepicker({ autoClose: true });
$('[data-toggle="tooltip"]').tooltip();
$('select[name="payment_bank_channel_id"]').select2();

$('select[name="warehouse_id"]').change(function() {
  app.warehouse_id = $(this).val();
});

$('input[name="discount"]').change(function() {
  app.discount = $(this).val();
});

$('select[name="shipping_method_id"], select[name="payment_method_id"]').select2();
$('select[name="shipping_method_id"]').change(function () {
  var itemUri = '';
  var type = $(this).val();

  if(type == METHOD_PICKUP_POINT) {
    itemUri = 'warehouse';
  } else if(type == METHOD_DELIVERY) {
    var userId = $('select[name="customer_id"]').val();
    itemUri = 'customer/' + userId + '/address';
  }

  select2AjaxHandler('select[name="shipping_address_id"]', `{{ $baseBeApiUrl }}/${itemUri}`);
});

$('select[name="shipping_address_id"]').change(function () {
  var shippingMethod = $('select[name="shipping_method_id"]').val();

  if(shippingMethod == METHOD_DELIVERY) {
    var params = {
      sales_id: $('input[name="id"]').val(),
      address_id: $('select[name="shipping_address_id"]').val()
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

            select2AjaxHandler('select[name="created_by"]', `{{ $baseBeApiUrl . '/employee' }}`, response.created_by);
            select2AjaxHandler('select[name="customer_id"]', `{{ $baseBeApiUrl . '/customer' }}`, response.customer_id);
            select2AjaxHandler('select[name="warehouse_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, response.warehouse_id);

            $('select[name="payment_bank_channel_id"]').val(response.payment_bank_channel_id);
            $('select[name="payment_bank_channel_id"]').trigger('change');

            app.elements = response.job_order_details
            app.warehouse_id = response.warehouse_id
            app.discount = response.discount

            discountField.set(response.discount);

            $(".transaction_channel").html(`<small class="label bg-${transactionChannels[response.transaction_channel]['label-color']}">
                <i class="${transactionChannels[response.transaction_channel]['icon']}" style="margin-right: 5px;"></i>
                ${transactionChannels[response.transaction_channel]['label']}
            </small>`)
        },
        error: function(err) { console.log(`failed fetch : ${err}`) }
    });
});

select2AjaxHandler('select[name="customer_id"]', `{{ $baseBeApiUrl . '/customer' }}`, '{{ $customerId }}');
select2AjaxHandler('select[name="warehouse_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, '{{ $warehouseId }}');
select2AjaxHandler('select[name="created_by"]', `{{ $baseBeApiUrl . '/employee' }}`, '{{ $createdBy }}');
select2AjaxHandler('select[name="quotation_number"]', `{{ $baseBeApiUrl . '/sales/quotation' }}`, '{{ $quotationNumber }}');

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
  mounted: function() {
    var vm = this

    if(this.value) { this.getDataById (this.value) }

    $(this.$el).select2({
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

$('input[name="use_tax"]').change(function () {
  if($(this).is(":checked")) {
    app.use_tax = 1;

    return;
  }

  app.use_tax = 0;
});

var app = new Vue({
  el: '#vue-dynamic-element',
  data: {
    elements: elements,
    warehouse_id: warehouse_id,
    discount: discount,
  },
  mounted: function () {
    // check if vue working
    console.log(`${this.$el.id} mounted`)

    if(!this.elements.length) this.addElement()
  },
  methods: {
    _isCustomLength (index)
    {
      var currentLength = this.elements[index].length
      var lengthInOption = this.elements[index].length_options.find(function (length, ilo) {
        return currentLength == length.length
      });

      return currentLength == 0 || !lengthInOption
    },
    _randomNumber () {
      return Math.floor(Math.random() * 10)
    },
    addElement () {
      this.elements.push({
        id: this._randomNumber(),
        item_material_id: '',
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
    calcQuantity (i) {
      this.elements[i].is_custom_length = this._isCustomLength(i);

      var length = isNaN(this.elements[i].length) ? this.elements[i].length.replace(/,/g, "") : this.elements[i].length
      var sheet = isNaN(this.elements[i].sheet) ? this.elements[i].sheet.replace(/,/g, "") : this.elements[i].sheet

      this.elements[i].quantity = length * sheet;
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
    getAmount(i) {
      var qty = isNaN(this.elements[i].quantity) ? this.elements[i].quantity.replace(/,/g, "") : this.elements[i].quantity;
      var estPrice = isNaN(this.elements[i].estimation_price) ? this.elements[i].estimation_price.replace(/,/g, "") : this.elements[i].estimation_price;
      var chargeCustomLength = 0;

      if(this.elements[i].is_custom_length == 'true' || this.elements[i].is_custom_length == true) {
        chargeCustomLength = qty*this.elements[i].charge_custom_length
      }

      return this.$options.filters.formatRupiah((qty*estPrice) + chargeCustomLength)
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
    },
    getDiscount () {
        var vm = this
        var totalAmount = vm.getTotalAmount()

        return totalAmount * (vm.discount/100);
    },
    getTax () {
        var vm = this
        var totalAmountWithDiscount = vm.getTotalAmount() - vm.getDiscount()
        var resultWithTax = totalAmountWithDiscount*(11/100)
        if(vm.use_tax == 1){
          return resultWithTax;
        }else{
          return 0;
        }

    },
    getGrandAmount() {
      return (this.getTotalAmount() - this.getDiscount())
    },
    getItemMaterialById (i, itemMaterialId) {
      var vm = this

      $.ajax({
        url: `${baseBeApiUrl}/item-material/${itemMaterialId}/warehouse/${vm.warehouse_id}`,
        type: "GET",
        success: function (response) {
          vm.elements[i].item_material_id = response.id
          vm.elements[i].length_options = response.item.length_options
          vm.elements[i].charge_custom_length = response.item.charge_custom_length
          vm.elements[i].estimation_price = vm.$options.filters.formatRupiah(response.price)

          if(response.item.length != '0.00') vm.elements[i].length = response.item.length
        },
        error: function (err) { console.log(`[material data] failed fetch : ${err}`) }
      });
    },
    getItemByName (i, itemId) {
      var vm = this

      $.ajax({
        url: `${baseBeApiUrl}/items/${itemId}`,
        type: "GET",
        success: function (response) { 
          vm.elements[i].quantity = 1
          vm.elements[i].item_name = response.name
          vm.elements[i].estimation_price = response.price
          
        },
        error: function (err) { console.log(`[Item data] failed fetch : ${err}`) }
      });
    }
  }
})
</script>
@endsection
