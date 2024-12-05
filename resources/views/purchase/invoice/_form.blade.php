<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $receiptNumber = !empty(old('purchase_order_number')) ? old('purchase_order_number') : $model->id; ?>
<?php $vendorId = !empty(old('vendor_id')) ? old('vendor_id') : $model->vendor_id; ?>
<?php $invoiceDetails = !empty(old('purchase_details')) ? old('purchase_details') : $model->purchase_details ?>
<?php $taxType = !empty(old('tax_type')) ? old('tax_type') : ($model->tax_type ? $model->tax_type : 0); ?>

<div class="row">

  <div class="col-md-6">
    <div class="form-group @if($errors->has('date_of_issued')) has-error @endif">
      <label>Invoice Date</label>

      <div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input
          type="text"
          class="form-control pull-right"
          name="date_of_issued"
          value="{{ empty($model->date_of_issued) ? date('m/d/Y') : $model->date_of_issued->format('m/d/Y') }}"
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

    <div class="form-group @if($errors->has('purchase_order_number')) has-error @endif">
      <label>Purchase Order Number</label>
      <select
        class="has-ajax-form form-control"
        name="purchase_order_number"
        id="purchase_order_number"
        style="width: 100%;"
        tabindex="-1"
        data-load="{{ $baseBeApiUrl . '/purchase/order' }}"> </select>
      @if($errors->has('purchase_order_number'))
        <span class="help-block">{{ $errors->first('purchase_order_number') }}</span>
      @endif
    </div>

  </div>

  <div class="col-md-6">

    <div class="form-group @if($errors->has('vendor_id')) has-error @endif">
      <label>Customer</label>
      <select class="form-control" name="vendor_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('vendor_id'))
        <span class="help-block">{{ $errors->first('vendor_id') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('discount')) has-error @endif">
      <label for="">Discount (%)</label>
      <input type="text" class="form-control" name="discount" placeholder="Discount" value="{{ !empty(old('discount')) ? old('discount') : $model->discount }}">
      @if($errors->has('discount'))
        <span class="help-block">{{ $errors->first('discount') }}</span>
      @endif
    </div>
    
    <div class="form-group @if($errors->has('tax_type')) has-error @endif">
      <label>Pilih Pajak</label>
      <select class="form-control " name="tax_type" id="" style="width: 100%;" tabindex="-1">
            <option value="0" @if($taxType == "0") selected @endif>None</option>
            <option value="1" @if($taxType == "1") selected @endif>PPn 11%</option>
            <option value="2" @if($taxType == "2") selected @endif>PPn 11% Include</option>
          </select>
      @if($errors->has('tax_type'))
        <span class="help-block">{{ $errors->first('tax_type') }}</span>
      @endif
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
        <th colspan="2">Item Material</th>
        <th width="10%">Qty</th>
        <th width="10%">Est. Price</th>
        <th width="10%">Amount</th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="(element, i) in elements" :key="element.id">
        <td colspan="2">
          <input readonly type="text" class="form-control" :name="`purchase_details[${i}][item_name]`" v-model="element.item_name" placeholder="Nama Item">
        </td>
        <td>
          <input readonly type="text" class="form-control" :name="`purchase_details[${i}][quantity]`" v-model="element.quantity" @change="isNumber(i, 'quantity')" placeholder="quantity">
        </td>
        <td>
          <input readonly type="text" class="form-control" :name="`purchase_details[${i}][estimation_price]`" v-model="element.estimation_price" @change="isNumber(i, 'estimation_price')" placeholder="estimation price">
        </td>
        <td>
          <input readonly type="text" class="form-control" :name="`purchase_details[${i}][amount]`" v-model="element.amount" :value="getAmount(i)" placeholder="amount">
        </td>
      </tr>

      <tr>
        <th>
          @if($errors->has('purchase_details.*'))
            <span class="help-block text-red">* {{ $errors->first('purchase_details.*') }}</span>
          @endif
        </th>
        <th></th>
        <th>
          <label for="">@{{ getTotalQuantity() | formatRupiah }}</label>
        </th>
        <th></th>
        <th>
          <label for="">Rp. @{{ getTotalAmount() | formatRupiah }}</label>
        </th>
      </tr>

      <tr>
        <th colspan="3"><label>Discount</label></th>
        <th></th>
        <th><label for="">Rp. @{{ getDiscount() | formatRupiah }}</label></th>
        <input type="hidden" name="amount_discount" :value="getDiscount()">

      </tr>

      <tr>
        <th colspan="3"><label>Pajak</label></th>
        <th></th>
        <th><label for="">Rp. @{{ getTax() | formatRupiah }}</label></th>
        <input type="hidden" name="amount_tax" :value="getTax()">
      </tr>

      <tr>
        <th colspan="3"><label>Grand Total</label></th>
        <th></th>
        <th><label for="">Rp. @{{ getGrandAmount() | formatRupiah }}</label></th>
        <input type="hidden" name="bill" :value="getGrandAmount()">
        
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
var baseBeApiUrl = "{{ $baseBeApiUrl }}";
var elements = <?php echo json_encode($invoiceDetails); ?>;
var discount = "{{ !empty(old('discount')) ? old('discount') : $model->discount }}";
var tax_type = "{{ !empty(old('tax_type')) ? old('tax_type') : $model->tax_type }}";
var discountField = new AutoNumeric($('input[name="discount"]')[0], {
  emptyInputBehavior: 'zero',
  unformatOnSubmit: true
});

$(".datepicker").datepicker({ autoClose: true });
$('[data-toggle="tooltip"]').tooltip();

$('input[name="discount"]').change(function() {
  app.discount = $(this).val();
});

$('input[name="use_tax"]').change(function () {
  if($(this).is(":checked")) {
    app.use_tax = 1;

    return;
  }

  app.use_tax = 0;
});

$('select[name="tax_type"]').change(function () {
    var tax_type = $(this).val();
    app.tax_type = tax_type;
});

$(".has-ajax-form").change(function() {
  var url = $(this).data('load') + '/' + $(this).val()

    $.ajax({
        type: "GET",
        url: url,
        success: function(response) {
            // set value form
            // $('input[name="id"]').val(response.id);
            $('input[name="id"]').val(response.id);
            $('input[name="discount"]').val(response.discount);
            $('select[name="tax_type"]').val(response.tax_type);
            select2AjaxHandler('select[name="request_by"]', `{{ $baseBeApiUrl . '/employee' }}`, response.request_by);
            select2AjaxHandler('select[name="vendor_id"]', `{{ $baseBeApiUrl . '/customer' }}`, response.vendor_id);
            select2AjaxHandler('select[name="warehouse_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, response.warehouse_id);
            app.elements = response.purchase_details
            app.discount = response.discount
            app.tax_type = response.tax_type

        },
        error: function(err) { console.log(`failed fetch : ${err}`) }
    });
});

select2AjaxHandler('select[name="purchase_order_number"]', `{{ $baseBeApiUrl . '/purchase/order/' }}`, '{{ $receiptNumber }}');
select2AjaxHandler('select[name="vendor_id"]', `{{ $baseBeApiUrl . '/customer' }}`, '{{ $vendorId }}', true);

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
    discount: discount,
    tax_type: tax_type,
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
    addElement (element = 'elements') {
      this.elements.push({
        id: this._randomNumber(),
        item_material_id: '',
        // raw_material_id: '',
        quantity: 0,
        estimation_price: 0,
        discount: 0,
        use_tax: 0,
        tax_type: 0,
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
    // calculation method
    recalcQuantityElement(arrayOfElement) {
      var result = 0
      var quantities = arrayOfElement.map((element) => {
        var quantity = isNaN(element.quantity) ? element.quantity.replace(/,/g, "") : element.quantity;
        return Number(quantity)
      })

      result = quantities.reduce((result, quantity) => {
        return result + quantity
      }, 0)

      return result
    },
    getAmount(i, element = 'elements') {
      var result = 0
      
      var qty = isNaN(this.elements[i].quantity) ? this.elements[i].quantity.replace(/,/g, "") : this.elements[i].quantity;
      var estPrice = isNaN(this.elements[i].estimation_price) ? this.elements[i].estimation_price.replace(/,/g, "") : this.elements[i].estimation_price;
      result = qty*estPrice;
      
      return this.$options.filters.formatRupiah(result)
    
    },
    
    getTotalQuantity (element = 'elements') {
      return this.recalcQuantityElement(this[element])
    },
    getTotalAmount (element = 'elements') {
      var vm = this

        var amounts = this[element].map((item, i) => {
        var amount = vm.getAmount(i, element).replace(/,/g, "")
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
        var resultWithTaxInclude = totalAmountWithDiscount - ((11/100) * totalAmountWithDiscount);
        if(vm.tax_type == 1){
          return resultWithTax;
        }else if(vm.tax_type == 2){
          return totalAmountWithDiscount -resultWithTaxInclude;
        }else{
          return 0;
        }

    },

    getGrandAmount() {
      if(this.tax_type == 1){
        var totalAmountWithDiscount = this.getTotalAmount() - this.getDiscount()
        var resultWithTax = (totalAmountWithDiscount*(11/100))
        return (this.getTotalAmount() - this.getDiscount()) + resultWithTax
      }else if(this.tax_type == 2){
        var totalAmountWithDiscount = this.getTotalAmount() - this.getDiscount()
        var resultWithTaxInclude = totalAmountWithDiscount - ((11/100) * totalAmountWithDiscount);
        var ppnnotinclude = (11/111) * totalAmountWithDiscount;

        return resultWithTaxInclude
      }else{
        return this.getTotalAmount() - this.getDiscount()
      }
    }
    
  }
})
</script>
@endsection
