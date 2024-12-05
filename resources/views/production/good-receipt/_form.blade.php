<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $number = !empty(old('number')) ? old('number') : $model->number; ?>
<?php $goodIssuedId = !empty(old('good_issued_id')) ? old('good_issued_id') : $model->good_issued_id; ?>
<?php $warehouseId = !empty(old('warehouse_id')) ? old('warehouse_id') : $model->warehouse_id; ?>
<?php $factoryId = !empty(old('factory_id')) ? old('factory_id') : $model->factory_id; ?>
<?php $createdBy = !empty(old('created_by')) ? old('created_by') : $model->created_by; ?>
<?php $goodReceiptDetails = !empty(old('good_receipt_details')) ? old('good_receipt_details') : $model->good_receipt_details ?>

<div class="row">

  <div class="col-md-6">
    <div class="form-group @if($errors->has('date')) has-error @endif">
      <label>Date</label>

      <div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input 
          type="text" 
          class="form-control pull-right" 
          name="date" 
          value="{{ empty($model->date) ? date('m/d/Y') : optional($model->date)->date('m/d/Y') }}"
          readonly>
      </div>
      @if($errors->has('date'))
        <span class="help-block">{{ $errors->first('date') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('good_issued_id')) has-error @endif">
      <label>Good Issued Number</label>
      <select 
        class="has-ajax-form form-control" 
        name="good_issued_id"
        style="width: 100%;" 
        tabindex="-1"
        data-load="{{ $baseBeApiUrl . '/production/good-issued' }}"> </select>
      @if($errors->has('good_issued_id'))
        <span class="help-block">{{ $errors->first('good_issued_id') }}</span>
      @endif
    </div>
     
    <div class="form-group @if($errors->has('number')) has-error @endif">
      <label for="">Good Receipt Number</label>
      <input type="text" class="form-control" name="number" placeholder="order number" value="{{ !empty(old('number')) ? old('number') : $model->number }}" readonly>
      @if($errors->has('number'))
        <span class="help-block">{{ $errors->first('number') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('created_by')) has-error @endif">
      <label>PIC</label>
      <select class="form-control" name="created_by" id="" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('created_by'))
        <span class="help-block">{{ $errors->first('created_by') }}</span>
      @endif
      <span class="help-block">data pic tidak ada? <a class="text-red" href="{{ url('/master/employee/create') }}" target="_blank">new pic</a></span>
    </div>

  </div>
  
  <div class="col-md-6">

    <div class="form-group @if($errors->has('factory_id')) has-error @endif">
      <label>Factory</label>
      <select class="form-control" name="factory_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('factory_id'))
        <span class="help-block">{{ $errors->first('factory_id') }}</span>
      @endif
      <span class="help-block">data warehouse (factory) tidak ada? <a class="text-red" href="{{ url('/master/warehouse/create') }}" target="_blank">new warehouse (factory)</a></span>
    </div>

    <div class="form-group @if($errors->has('warehouse_id')) has-error @endif">
      <label>Location In</label>
      <select class="form-control" name="warehouse_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('warehouse_id'))
        <span class="help-block">{{ $errors->first('warehouse_id') }}</span>
      @endif
      <span class="help-block">data warehouse tidak ada? <a class="text-red" href="{{ url('/master/warehouse/create') }}" target="_blank">new warehouse</a></span>
    </div>

  </div>

</div>

<hr>

<div id="vue-dynamic-element">
<!-- list order -->
<div class="panel">
    <a data-toggle="collapse" data-parent="#accordion" href="#collapseListOrders" class="btn btn-outline disabled" style="color: red; border: 1px solid">
      Job Orders <i class="fa fa-caret-down" style="margin-left: 5px"></i>
    </a>
  <div id="collapseListOrders" class="panel-collapse collapse" style="margin: 15px 0px;">

    <table id="vue-dynamic-element" class="table table-bordered table-hover">
      <thead class="table-header-primary">
        <tr>
          <th class="text-center" width="5%"> </th>
          <th width="50%">Item Material</th>
          <th>Length</th>
          <th>Sheet</th>
          <th>Qty (m)</th>
          <th>Qty. Left (m)</th>
        </tr>
      </thead>

      <tbody>    
        <tr v-for="(order, i) in orders" :key="order.id">
          <td class="text-center">
            <input type="checkbox" v-model="order.is_check">
          </td>
          <td>
            <input type="hidden" :name="`order_details[${i}][id]`" v-model="order.id">
            <vue-select2 
              :url="`{{ $baseBeApiUrl . '/item-material' }}`"
              :name="`order_details[${i}][item_material_id]`"
              :value="order.item_material_id"
              v-on:selected="order.item_material_id = $event"
              :readonly="true" />
          </td>
          <td>
            <input type="text" class="form-control" :name="`order_details[${i}][length]`" :value="order.length | formatRupiah" placeholder="length" readonly>
          </td>
          <td>
            <input type="text" class="form-control" :name="`order_details[${i}][sheet]`" :value="order.sheet | formatRupiah" placeholder="sheet" readonly>
          </td>
          <td>
            <input type="text" class="form-control" :name="`order_details[${i}][quantity]`" :value="order.quantity | formatRupiah" placeholder="quantity" readonly>
          </td>
          <td>
            <input type="text" class="form-control" :name="`order_details[${i}][quantity_left]`" :value="order.quantity_left | formatRupiah" placeholder="quantity left" readonly>
          </td>
        </tr>

        <tr>
          <th colspan="2"> </th>
          <th>
            <label for=""></label>
          </th>
          <th>
            <label for=""></label>
          </th>
          <th></th>
          <th>
            <label for=""></label>
          </th>
        </tr>
      </tbody>

      <tfoot>
        <tr>
          <td colspan="5"> 
            <button type="button" class="btn btn-sm btn-primary" @click="applyElement()">Apply</button> 
            <button type="button" class="btn btn-sm btn-default text-red" @click="applyElement(true)">Apply All</button> 
          </td>
        </tr>
      </tfoot>
    </table>

  </div>
  <!-- end collapse body -->
</div>

<hr>

<div id="vue-dynamic-element">
<div class="form-group">
  <label>List Material</label>   
    
  <table id="vue-dynamic-element" class="table table-bordered table-hover">
    <thead class="table-header-primary">
      <tr>
        <th width="50%">Material</th>
        <th>length</th>
        <th>sheet</th>
        <th>Qty (m)</th>
        <th width="10%"></th>
      </tr>
    </thead>

    <tbody>    
      <tr v-for="(element, i) in elements" :key="element.id">
        <td> 
          <input type="hidden" :name="`good_receipt_details[${i}][id]`" v-model="element.id">
          <input type="hidden" :name="`good_receipt_details[${i}][job_order_detail_id]`" v-model="element.job_order_detail_id">
          <vue-select2 
            :url="`{{$baseBeApiUrl}}/item-material`"
            :name="`good_receipt_details[${i}][item_material_id]`"
            :value="element.item_material_id"
            v-on:selected="element.item_material_id = $event"
            :readonly="true" />
        </td>
        <td>
          <input type="text" class="form-control" :name="`good_receipt_details[${i}][length]`" v-model="element.length" placeholder="length" readonly>
        </td>
        <td>
          <div :class="['form-group', {'has-error': element.is_quantity_over}]">
            <input
              type="text"
              class="form-control"
              :name="`good_receipt_details[${i}][sheet]`"
              v-model="element.sheet"
              @change="isNumber(i, 'sheet')"
              placeholder="sheet">

              <span class="help-block" v-if="element.is_quantity_over">max: @{{ element.sheet_max }}</span>
          </div>
        </td>
        <td>
          <div :class="['form-group', {'has-error': element.is_quantity_over}]">
            <input
              type="text"
              class="form-control"
              :name="`good_receipt_details[${i}][quantity]`"
              v-model="element.quantity"
              @change="isNumber(i, 'quantity')" 
              placeholder="quantity"
              readonly>

            <span class="help-block" v-if="element.is_quantity_over">max: @{{ element.quantity_max }}</span>
          </div>
        </td>
        <td>
          <button type="button" class="btn btn-default text-red" @click="removeElement(i)"><i class="fa fa-minus"></i></button>
        </td>
      </tr>

      <tr>
        <th>
          @if($errors->has('good_receipt_details.*'))
            <span class="help-block text-red">* {{ $errors->first('good_receipt_details.*') }}</span>
          @endif
        </th>
        <th></th>
        <th></th>
        <th>
          <label for="">@{{ getTotalQuantity() }}</label>
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
var elements = <?php echo json_encode($goodReceiptDetails); ?>;
var warehouse_id = 0;


$(".datepicker").datepicker({ autoClose: true });
$('[data-toggle="tooltip"]').tooltip();

$('select[name="warehouse_id"]').change(function() {
  app.warehouse_id = $(this).val();
});

$(".has-ajax-form").change(function() {
    var url = $(this).data('load') + '/' + $(this).val()

    $.ajax({
        type: "GET",
        url: url,
        success: function(response) {
            // set value form
            select2AjaxHandler('select[name="created_by"]', `{{ $baseBeApiUrl . '/employee' }}`, response.created_by);
            select2AjaxHandler('select[name="factory_id"]', `{{ $baseBeApiUrl . '/factory' }}`, response.factory_id);

            //set list orders
            $.ajax({
                type: "GET",
                url: `{{ $baseBeApiUrl }}/production/job-order/issued/${response.id}`,
                success: function(response) {
                    app.orders = response.job_order_details
                    $(`a[href="#collapseListOrders"]`).removeClass('disabled')
                },
                error: function(err) { console.log(`failed fetch : ${err}`) }
            });

        },
        error: function(err) { console.log(`failed fetch : ${err}`) }
    });
});

select2AjaxHandler('select[name="created_by"]', `{{ $baseBeApiUrl . '/employee' }}`, '{{ $createdBy }}');
select2AjaxHandler('select[name="warehouse_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, '{{ $warehouseId }}');
select2AjaxHandler('select[name="factory_id"]', `{{ $baseBeApiUrl . '/factory' }}`, '{{ $factoryId }}');
select2AjaxHandler('select[name="good_issued_id"]', `{{ $baseBeApiUrl . '/production/good-issued' }}`, '{{ $goodIssuedId }}');

Vue.filter('formatRupiah', function (value) {
  return new Intl.NumberFormat('IDR', {}).format(value)
})

Vue.component('vue-select2', {
  template: `<select class="form-control" :name="name" style="width: 100%"> </select>`,
  props: [ 'url', 'name', 'value', 'readonly' ],
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
    console.log(vm.url)

    // jika readonly, tidak perlu menampilkan selection
    if(!this.readonly) {
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
    }

    if(this.value) await this.getDataById (this.value) 
  },
  destroyed: function () {
    if(this.readonly) return
    $(this.$el).off().select2('destroy')
  },
  watch: {
    value: function (value) {
      // update value
      $(this.$el).val(value).trigger('change')
    },
    options: function (options) {
      if(this.readonly) return
      $(this.$el).empty().select2({ data: options })
    }
  },
});

var app = new Vue({
  el: '#vue-dynamic-element',
  data: {
    orders: [],
    elements: elements,
    warehouse_id: warehouse_id
  },
  mounted: function () {
    // check if vue working
    console.log(`${this.$el.id} mounted`)

    if(!this.orders.length) this.addElement('orders')
    if(!this.elements.length) this.addElement()
  },
  methods: {
    // value validation method
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

        if(this.elements[index][attribut] == 0) {
          this.elements[index][attribut] = this.elements[index].quantity_max;
          return
        }

        this.elements[index].is_quantity_over = false
      }else if((attribut == 'sheet')) {
        if(this._isSheetOver(index)) {
          this.elements[index][attribut] = this.elements[index].sheet_max;
          this.elements[index].is_quantity_over = true
          return
        }

        this.elements[index].quantity = this.elements[index].sheet*this.elements[index].length
        this.elements[index].is_quantity_over = false
      }

      this.elements[index].sheet = this.elements[index].quantity/this.elements[index].length
      this.elements[index][attribut] = this.$options.filters.formatRupiah(castNumber)
    },
    // internal method, only used by another method not direct in component
    _randomNumber () {
      return Math.floor(Math.random() * 10)
    },
    _anyOrdersChecked () {
      return this.orders.find(function (order, elindex) {
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
    _getAdjstotalQuantity (adjs) {
      return this.$options.filters.formatRupiah(this.recalcQuantityElement(adjs))
    },
    _getItemBoms (itemMaterialId) {
      return $.ajax({
        type: "GET",
        url: `${baseBeApiUrl}/production/bom/${itemMaterialId}`,
        success: function (response) {
          return response
        },
        error: function (err) {
          console.log(`failed fetch : ${err}`)
        }
      });
    },
    // orders method
    applyElement (applyAll = false) {
      var vm = this
      if((this._anyOrdersChecked() || applyAll) && !this.elements[0].sales_detail_id) this.elements.pop()

      this.orders.forEach(function (order, index) {
        /** 
         * applyAll is true, loop all orders and push into elements
         * is element exist (has pushed) ?
         * if not exist push into element
         * */ 
        elementExist = this.elements.find(function (element, elindex) {
          return element.item_material_id == order.item_material_id
        })
        
        if(!elementExist && (applyAll || order.is_check)) {

          this.elements.push({
            id: vm._randomNumber(),
            job_order_detail_id: order.id,
            item_material_id: order.item_material_id,
            length: order.length,
            sheet: order.quantity_left/order.length,
            sheet_max: order.quantity_left/order.length,
            quantity: order.quantity_left,
            quantity_max: order.quantity_left,
            balance: 0
          })

        }
      })
    },
    // adjustments method
    toggleAdjustment (i) {
      var hasAdjustment = this.elements[i].has_adjustment

      !hasAdjustment ? this.addAdjsElement(i) : this.elements[i].adjs = []
      this.elements[i].quantity = this.elements[i].quantity_max
      this.elements[i].has_adjustment = !hasAdjustment
    },
    onchangeAdjs(ie, ia) {
      var element = this.elements[ie]

      var maxQuantity = isNaN(element.quantity_max) ? element.quantity_max.replace(/,/g, "") : element.quantity_max;
      var maxQuantity = Number(maxQuantity);

      var adjsTotalQuantity = this._getAdjstotalQuantity(element.adjs);
      var adjsTotalQuantity = isNaN(adjsTotalQuantity) ? adjsTotalQuantity.replace(/,/g, "") : adjsTotalQuantity;

      var overPlusQuantity = adjsTotalQuantity-maxQuantity

      element.quantity = adjsTotalQuantity

      if( this._isQuantityOver(ie) ) {
        element.adjs[ia].quantity = element.adjs[ia].quantity-overPlusQuantity
        element.quantity = this._getAdjstotalQuantity(element.adjs)
      }

      // this.isNumber(ia, 'quantity'); 
    },
    addAdjsElement(index) {
      var targetElement = this.elements[index]

      this.elements[index].adjs.push({
        id: this._randomNumber(),
        raw_material_id: targetElement.raw_material_id,
        quantity: 0,
      });
    },
    removeAdjsElement (ie, ia) {
      this.elements[ie].adjs.splice(ia, 1)

      recalcQuantity = this.recalcQuantityElement(this.elements[ie].adjs)
      this.elements[ie].quantity = recalcQuantity <= 0 ? this.elements[ie].quantity_max : recalcQuantity 
    },
    // elements method
    addElement (element = 'elements') {
      if(element == 'orders') {
        this.orders.push({
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
        sales_detail_id: null,
        raw_material_id: '',
        length: 0,
        sheet: 0,
        quantity: 0,
        balance: 0
      })
    },
    removeElement (index) {
      if(this.elements.length == 1) return

      this.elements.splice(index, 1)
    },
    getAmount(i, element = 'elements') {
      var result = 0
      var qty = isNaN(this[element][i].quantity) ? this[element][i].quantity.replace(/,/g, "") : this[element][i].quantity;
      var estPrice = isNaN(this[element][i].estimation_price) ? this[element][i].estimation_price.replace(/,/g, "") : this[element][i].estimation_price;

      result = qty*estPrice
      if(element === 'elements') {
        var discount = isNaN(this[element][i].discount) ? this[element][i].discount.replace(/,/g, "") : this[element][i].discount;
        discount = isNaN((estPrice*discount)/100) ? 0 : (estPrice*discount)/100

        result = qty*(estPrice-discount)
      }

      return this.$options.filters.formatRupiah(result)
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
    }
  }
})
</script>
@endsection