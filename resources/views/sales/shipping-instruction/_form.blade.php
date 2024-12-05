<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $orderNumber = !empty(old('sales_id')) ? old('sales_id') : $model->sales_id; ?>
<?php $orderNumberUrl = !empty($orderNumber) ? $baseBeApiUrl . '/sales/order/' . $orderNumber . '/shipping-format' : $baseBeApiUrl . '/sales/order'; ?>
<?php $orderNumber = 'custom'; ?>

<?php $branchId = !empty(old('branch_id')) ? old('branch_id') : $model->branch_id; ?>  
<?php $shMethodId = !empty(old('shipping_method_id')) ? old('shipping_method_id') : $model->shipping_method_id; ?>  
<?php $shippingInstructionDetails = !empty(old('shipping_instruction_details')) ? old('shipping_instruction_details') : $model->shipping_instruction_details ?>

<div class="row">

  <div class="col-md-6">
    <div class="form-group @if($errors->has('date')) has-error @endif">
      <label>Instruction Date</label>

      <div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input 
          type="text" 
          class="form-control pull-right" 
          name="date" 
          value="{{ empty($model->date) ? date('m/d/Y') : $model->date->format('m/d/Y') }}"
          readonly>
      </div>
      @if($errors->has('date'))
        <span class="help-block">{{ $errors->first('date') }}</span>
      @endif
    </div>
     
    <div class="form-group @if($errors->has('purchase_receipt_id')) has-error @endif">
      <label>Purchase Receipt Number</label>
      <select 
        class="has-ajax-form form-control" 
        name="purchase_receipt_id" 
        id="purchase_receipt_id" 
        style="width: 100%;" 
        tabindex="-1"
        data-load="{{ $baseBeApiUrl . '/purchase/receipt' }}"> </select>
      @if($errors->has('purchase_receipt_id'))
        <span class="help-block">{{ $errors->first('purchase_receipt_id') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('number')) has-error @endif">
      <label for="">Instruction Number</label>
      <input type="text" class="form-control" name="number" placeholder="quotation number" value="{{ !empty(old('number')) ? old('number') : $model->number }}" readonly>
      @if($errors->has('number'))
        <span class="help-block">{{ $errors->first('number') }}</span>
      @endif
    </div>

  </div>
  
  <div class="col-md-6">

    <div class="form-group @if($errors->has('branch_id')) has-error @endif">
      <label>Cabang</label>
      <select class="form-control" name="branch_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('branch_id'))
        <span class="help-block">{{ $errors->first('branch_id') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('remark')) has-error @endif">
      <label for="">Remark</label>
      <input type="text" class="form-control" name="remark" placeholder="remark" value="{{ !empty(old('remark')) ? old('remark') : $model->remark }}">
      @if($errors->has('remark'))
        <span class="help-block">{{ $errors->first('remark') }}</span>
      @endif
    </div>

  </div>

</div>

<hr>

<div id="vue-dynamic-element">

<hr>

<div class="form-group">
  <label>List Item Material</label>   
    
  <table id="vue-dynamic-element" class="table table-bordered table-hover">
    <thead class="table-header-primary">
      <tr>
        <th>Item Material</th>
        <th width="10%">Qty</th>
        <th width="5%"></th>
      </tr>
    </thead>

    <tbody>    
      <tr v-for="(element, i) in elements" :key="element.id">
        <td> 
          <input type="hidden" :name="`shipping_instruction_details[${i}][id]`" v-model="element.id">
          <input type="hidden" :name="`shipping_instruction_details[${i}][purchase_detail_id]`" v-model="element.purchase_detail_id">
          <input class="form-control" type="text" v-model="element.item_name" readonly>
        </td>
        <td>
          <div :class="['form-group', {'has-error': element.is_quantity_over}]">
            <input
              class="form-control"
              type="number"
              :name="`shipping_instruction_details[${i}][quantity]`"
              v-model="element.quantity"
              @change="isNumber(i, 'quantity')" readonly>

              <span class="help-block" v-if="element.is_quantity_over">max: @{{ element.quantity_max }}</span>
          </div>
        </td>
        
        <td>
          <button type="button" class="btn btn-default text-red" @click="removeElement(i)"><i class="fa fa-minus"></i></button>
        </td>
      </tr>

      <tr>
      <th> Grand Total </th>
        <th>
        <label for="">@{{ getTotalQuantity() | formatRupiah }}</label>

        </th>
        
        <th>
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
var itemUri = ''; //url untuk shipping address
var elements = <?php echo json_encode($shippingInstructionDetails); ?>;

$(".datepicker").datepicker({ autoClose: true });
$('[data-toggle="tooltip"]').tooltip();

select2AjaxHandler('select[name="branch_id"]', `{{ $baseBeApiUrl . '/branch' }}`, '{{ $branchId }}');
select2AjaxHandler('select[name="purchase_receipt_id"]', `{{ $baseBeApiUrl . '/purchase/receipt' }}`, '');


$(".has-ajax-form").change(function() {
    var activeField = $(this).attr('name');
    var url = $(this).data('load') + '/' + $(this).val()
    
    // if(activeField == 'good_receipt_id') url = $(this).data('load') + '/' + $(this).val()

    $.ajax({
        type: "GET",
        url: url,
        success: function(response) {
          console.log(response);
          // set value form
          // $('input[name="id"]').val(response.id);

        
          app.elements = response.receipt_detail_adjs

          $(`a[href="#collapseListOrders"]`).removeClass('disabled')
        },
        error: function(err) { console.log(`failed fetch : ${err}`) }
    });
});


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
    salesOrders: [],
    elements: elements,
  },
  mounted: function () {
    // check if vue working
    console.log(`${this.$el.id} mounted`)

    if(!this.salesOrders.length) this.addElement('salesOrders')
    if(!this.elements.length) this.addElement()
  },
  methods: {
    _randomNumber () {
      return Math.floor(Math.random() * 10)
    },
    _anyOrdersChecked () {
      return this.salesOrders.find(function (order, elindex) {
          return order.is_check
      })
    },
    _isSheetOver(i) {
      var SheetCleanComa = isNaN(this.elements[i].sheet) ? this.elements[i].sheet.replace(/,/g, "") : this.elements[i].sheet;
      var sheet = Number(SheetCleanComa);

      var maxSheetCleanComa = isNaN(this.elements[i].sheet_max) ? this.elements[i].sheet_max.replace(/,/g, "") : this.elements[i].sheet_max;
      var maxSheet = Number(maxSheetCleanComa);

      return sheet > maxSheet
    },
    _isQuantityOver(i) {
      var qtyCleanComa = isNaN(this.elements[i].quantity) ? this.elements[i].quantity.replace(/,/g, "") : this.elements[i].quantity;
      var qty = Number(qtyCleanComa);

      var maxQtyCleanComa = isNaN(this.elements[i].quantity_max) ? this.elements[i].quantity_max.replace(/,/g, "") : this.elements[i].quantity_max;
      var maxQty = Number(maxQtyCleanComa);

      return qty > maxQty
    },
    applyElement (applyAll = false) {
      var vm = this
      if((this._anyOrdersChecked() || applyAll) && !this.elements[0].purchase_detail_id) this.elements.pop()

      this.salesOrders.forEach(function (order, index) {
        /** 
         * applyAll is true, loop all orders and push into elements
         * is element exist (has pushed) ?
         * if not exist push into element
         * */ 
        elementExist = this.elements.find(function (element, elindex) {
          return element.sales_material_id == order.sales_material_id
        })
        
        if(!elementExist && (applyAll || order.is_check)) {

          this.elements.push({
            id: vm._randomNumber(),
            purchase_detail_id: order.id,
            item_material_id: order.item_material_id,
            item_name: order.item_name,
            length: order.length,
            length_formated: order.length_formated,
            sheet: order.quantity_left/order.length,
            sheet_max: order.quantity_left/order.length,
            quantity: order.quantity_left,
            quantity_max: order.quantity_left,
            price: vm.$options.filters.formatRupiah(order.price),
            total_price: vm.$options.filters.formatRupiah(order.total_price)
          })

        }
      })
    },
    addElement (element = 'elements') {
      if(element == 'salesOrders') {
        this.salesOrders.push({
          id: this._randomNumber(),
          item_material_id: '',
          length: 0,
          sheet: 0,
          quantity: 0
        })

        return
      }

      this.elements.push({
        id: this._randomNumber(),
        purchase_detail_id: '',
        item_material_id: '',
        item_name: '',
        length: 0,
        length_formated: '',
        sheet: 0,
        sheet_max: 0,
        quantity: 0,
        quantity_max: 0,
        price: 0,
        total_price: 0
      })
    },
    removeElement (index) {
      if(this.elements.length == 1) return

      this.elements.splice(index, 1)
    },
    isNumber(index, attribut) {
      var cleanComa = this.elements[index][attribut].replace(/,/g, "");
      var castNumber = Number(cleanComa);

      if (isNaN(castNumber)) {
        this.elements[index][attribut] = 0;
        return  
      }

      if((attribut == 'quantity')) {
        if(this._isQuantityOver(index)) {
          this.elements[index][attribut] = this.elements[index].quantity_max;
          this.elements[index].is_quantity_over = true
          return
        }

        this.elements[index].sheet = this.elements[index].quantity/this.elements[index].length
        this.elements[index].is_quantity_over = false
      } else if((attribut == 'sheet')) {
        if(this._isSheetOver(index)) {
          this.elements[index][attribut] = this.elements[index].sheet_max;
          this.elements[index].is_quantity_over = true
          return
        }

        this.elements[index].quantity = this.elements[index].sheet*this.elements[index].length
        this.elements[index].is_quantity_over = false
      }

      this.elements[index][attribut] = this.$options.filters.formatRupiah(castNumber)
    },
    getAmount(i) {
      var qty = isNaN(this.elements[i].quantity) ? this.elements[i].quantity.replace(/,/g, "") : this.elements[i].quantity;

      return this.$options.filters.formatRupiah(qty)
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
    getGrandTotal () {
        return this.getTotalAmount ()
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