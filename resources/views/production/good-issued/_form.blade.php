<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $number = !empty(old('number')) ? old('number') : $model->number; ?>
<?php $jobOrderId = !empty(old('job_order_id')) ? old('job_order_id') : $model->job_order_id; ?>
<?php $warehouseId = !empty(old('warehouse_id')) ? old('warehouse_id') : $model->warehouse_id; ?>
<?php $factoryId = !empty(old('factory_id')) ? old('factory_id') : $model->factory_id; ?>
<?php $createdBy = !empty(old('created_by')) ? old('created_by') : $model->created_by; ?>
<?php $jobOrderDetails = !empty(old('order_details')) ? old('order_details') : [] ?>
<?php $goodIssuedDetails = !empty(old('good_issued_details')) ? old('good_issued_details') : $model->good_issued_details ?>

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
          value="{{ empty($model->date) ? date('m/d/Y') : $model->date_formated }}"
          readonly>
      </div>
      @if($errors->has('date'))
        <span class="help-block">{{ $errors->first('date') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('job_order_id')) has-error @endif">
      <label>Job Order Number</label>
      <select 
        class="has-ajax-form form-control" 
        name="job_order_id"
        style="width: 100%;" 
        tabindex="-1"
        data-load="{{ $baseBeApiUrl . '/production/job-order' }}"> </select>
      @if($errors->has('job_order_id'))
        <span class="help-block">{{ $errors->first('job_order_id') }}</span>
      @endif
    </div>
     
    <div class="form-group @if($errors->has('number')) has-error @endif">
      <label for="">Good Issued Number</label>
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

    <div class="form-group @if($errors->has('warehouse_id')) has-error @endif">
      <label>Warehouse</label>
      <select class="form-control" name="warehouse_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('warehouse_id'))
        <span class="help-block">{{ $errors->first('warehouse_id') }}</span>
      @endif
      <span class="help-block">data warehouse tidak ada? <a class="text-red" href="{{ url('/master/warehouse/create') }}" target="_blank">new warehouse</a></span>
    </div>

    <div class="form-group @if($errors->has('factory_id')) has-error @endif">
      <label>Factory</label>
      <select class="form-control" name="factory_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('factory_id'))
        <span class="help-block">{{ $errors->first('factory_id') }}</span>
      @endif
      <span class="help-block">data warehouse (factory) tidak ada? <a class="text-red" href="{{ url('/master/warehouse/create') }}" target="_blank">new warehouse (factory)</a></span>
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
          <th>Sheet Issued</th>
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
            <input type="text" class="form-control" :name="`order_details[${i}][balance_issued]`" :value="order.balance_issued | formatRupiah" placeholder="balance issued" readonly>
          </td>
          <td>
            <div :class="['form-group', {'has-error': order.is_quantity_over}]">
              <input 
                type="text" 
                class="form-control" 
                :name="`order_details[${i}][sheet_issued]`" 
                v-model="order.sheet_issued"
                @change="isNumber(i, null, 'sheet_issued')" 
                placeholder="sheet issued">

              <span class="help-block" v-if="order.is_quantity_over">max: @{{ order.sheet_issued }}</span>
            </div>

            <input type="hidden" :name="`order_details[${i}][quantity_issued]`" v-model="order.quantity_issued">
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
            <button type="button" class="btn btn-sm btn-primary" @click="applyElement()" :disabled="disableApplyElement()">Apply Bom</button> 
            <button type="button" class="btn btn-sm btn-default text-red" @click="applyElement(true)" :disabled="disableApplyElement()">Apply All Bom</button> 
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
        <th width="25%">Material</th>
        <th>Qty (kg)</th>
        <th width="25%">Material Use</th>
        <th>Qty. Material Use (kg)</th>
        <th width="10%"></th>
      </tr>
    </thead>

    <tbody>    
      <template v-for="(element, i) in elements" >
        <tr :key="element.id">
          <td> 
            <input type="hidden" :name="`good_issued_details[${i}][id]`" v-model="element.id">
            <input type="hidden" :name="`good_issued_details[${i}][job_order_detail_id]`" v-model="element.job_order_detail_id">
            <input type="hidden" :name="`good_issued_details[${i}][raw_material_id]`" v-model="element.raw_material_id">
            <input type="hidden" :name="`good_issued_details[${i}][api_uri]`" v-model="element.api_uri">
            <input type="hidden" :name="`good_issued_details[${i}][api_uri_id]`" v-model="element.api_uri_id">
            <input type="hidden" :name="`good_issued_details[${i}][has_adjustment]`" v-model="element.has_adjustment">
            
            <input 
              v-for="(invUsed, iu) in element.inventory_used"
              type="hidden" 
              :name="`good_issued_details[${i}][inventory_used][]`" 
              :value="invUsed">

            <vue-select2 
              :url="`{{$baseBeApiUrl}}/${element.api_uri}`"
              :value="element.api_uri_id"
              v-on:selected="element.api_uri_id = $event"
              :readonly="true" />
          </td>
          <td>
            <input type="text" class="form-control" :name="`good_issued_details[${i}][quantity_need]`" v-model="element.quantity_need" readonly>
          </td>
          <td> 
            <input type="text" class="form-control" :name="`good_issued_details[${i}][inventory_warehouse_id]`" readonly>
            <!-- <vue-select2 
              :url="`{{$baseBeApiUrl}}/inventory-warehouse/${warehouse_id}/${element.raw_material_id}`"
              :name="`good_issued_details[${i}][inventory_warehouse_id]`"
              :value="element.inventory_warehouse_id"
              v-on:selected="element.inventory_warehouse_id = $event"
              /> -->
          </td>
          <td>
            <input type="text" class="form-control" :name="`good_issued_details[${i}][quantity]`" v-model="element.quantity" @change="isNumber(i, 'quantity')" placeholder="quantity" readonly>
          </td>
          <td>
          <button type="button" class="btn btn-default" @click="toggleAdjustment(i)"><i class="fa fa-sliders"></i></button>
            <button type="button" class="btn btn-default text-red" @click="removeElement(i)"><i class="fa fa-minus"></i></button>
          </td>
        </tr>
      
        <!-- if adjustment not empty -->
        <template v-if="element.has_adjustment">
          <tr v-for="(adj, ia) in element.adjs" :key="`adj-${element.id}-${adj.id}`">
            <td width="5%"></td>
            <td></td>
            <td>
              <input type="hidden" :name="`good_issued_details[${i}][adjs][${ia}][id]`" v-model="adj.id">
              <vue-select2 
                :url="`{{$baseBeApiUrl}}/inventory-warehouse/${warehouse_id}/${adj.raw_material_id}`"
                :name="`good_issued_details[${i}][adjs][${ia}][inventory_warehouse_id]`"
                :value="adj.inventory_warehouse_id"
                :whitelist="element.inventory_used"
                v-on:selected="getInventoryWHById(i, ia, $event)"
                />
            </td>
            <td>
              <div :class="['form-group', {'has-error': adj.is_quantity_over}]">
                <input 
                  type="text" 
                  class="form-control" 
                  :name="`good_issued_details[${i}][adjs][${ia}][quantity]`" 
                  v-model="adj.quantity" 
                  @change="onchangeAdjs(i, ia)" 
                  placeholder="quantity">

                <span class="help-block" v-if="adj.is_quantity_over">max: @{{ adj.quantity_max }}</span>
              </div>
            </td>
            <td>
              <button type="button" class="btn btn-default text-red" @click="removeAdjsElement(i, ia)"><i class="fa fa-minus"></i></button>
            </td>
          </tr>

          <tr>
            <td colspan="4"></td>
            <td>
              <button type="button" class="btn btn-default text-success" @click="addAdjsElement(i)"><i class="fa fa-plus"></i></button>
            </td>
          </tr>
        </template>

      </template>

      <tr>
        <th>
          @if($errors->has('good_issued_details.*'))
            <span class="help-block text-red">* {{ $errors->first('good_issued_details.*') }}</span>
          @endif
        </th>
        <th>
          <label for=""></label>
        </th>
        <th></th>
        <th>
          <label for="">@{{ getTotalQuantity() }}</label>
        </th>
        <th>
          <!-- <button type="button" class="btn btn-default text-success" @click="addElement()"><i class="fa fa-plus"></i></button>         -->
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
var elements = <?php echo json_encode($goodIssuedDetails); ?>;
var orders = <?php echo json_encode($jobOrderDetails); ?>;
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
            select2AjaxHandler('select[name="warehouse_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, response.warehouse_id);
            if(orders.length <= 0) app.orders = response.job_order_details

            $(`a[href="#collapseListOrders"]`).removeClass('disabled')
        },
        error: function(err) { console.log(`failed fetch : ${err}`) }
    });
});

select2AjaxHandler('select[name="created_by"]', `{{ $baseBeApiUrl . '/employee' }}`, '{{ $createdBy }}');
select2AjaxHandler('select[name="warehouse_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, '{{ $warehouseId }}');
select2AjaxHandler('select[name="factory_id"]', `{{ $baseBeApiUrl . '/factory' }}`, '{{ $factoryId }}');
select2AjaxHandler('select[name="job_order_id"]', `{{ $baseBeApiUrl . '/production/job-order' }}`, '{{ $jobOrderId }}');

Vue.filter('formatRupiah', function (value) {
  return new Intl.NumberFormat('IDR', {}).format(value)
})

Vue.component('vue-select2', {
  template: `<select class="form-control" :name="name" style="width: 100%"> </select>`,
  props: [ 'url', 'name', 'value', 'readonly', 'whitelist' ],
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

    // jika readonly, tidak perlu menampilkan selection
    if(!this.readonly) {
      await $(this.$el).select2({
        ajax: {
          url: this.url,
          data: function (params) {
            var query = { 
              searchKey: params.term,
              whitelist: vm.whitelist
            }
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
    orders: orders,
    elements: elements,
    warehouse_id: warehouse_id
  },
  mounted: function () {
    // check if vue working
    console.log(`${this.$el.id} mounted`)

    // if(!this.orders.length) this.addElement('orders')
    if(!this.elements.length) this.addElement()
  },
  methods: {
    // value validation method
    disableApplyElement () {
      return this.orders.some(function (order, io) {
        return order.is_quantity_over
      })
    },
    isNumber(index, indexChild = null, attribut) {    
      if(attribut == 'quantity') {
        var element = indexChild !== null ? this.elements[index].adjs[indexChild] : this.elements[index]

        var cleanComa = element[attribut].replace(/,/g, "");
        var castNumber = Number(cleanComa);

        if (isNaN(castNumber)) {
          element[attribut] = 0;
          return  
        }

        if(this._isQuantityOver(index)) {
          element[attribut] = 0;
          element.is_quantity_over = true
          return
        }

        element.is_quantity_over = false
      }else if(attribut == 'sheet_issued') {
        var element = indexChild !== null ? this.orders[index].adjs[indexChild] : this.orders[index]

        var cleanComa = isNaN(element[attribut]) ? element[attribut].replace(/,/g, "") : element[attribut];
        var castNumber = Number(cleanComa);

        if (isNaN(castNumber)) {
          element[attribut] = 0;
          return  
        }

        var sheetCleanComa = isNaN(this.orders[index].sheet_issued) ? this.orders[index].sheet_issued.replace(/,/g, "") : this.orders[index].sheet_issued;
        var sheet = Number(sheetCleanComa);

        var maxSheetCleanComa = isNaN(this.orders[index].sheet_max) ? this.orders[index].sheet_max.replace(/,/g, "") : this.orders[index].sheet_max;
        var maxSheet = Number(maxSheetCleanComa);

        if(sheet > maxSheet) {
          element[attribut] = element.sheet_max;
          element.is_quantity_over = true;
          return;
        }

        element.quantity_issued = element[attribut]*element.length
        element.is_quantity_over = false
      }

      element[attribut] = this.$options.filters.formatRupiah(castNumber)
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
    _isQuantityOver(i) {
      var qtyCleanComa = isNaN(this.elements[i].quantity) ? this.elements[i].quantity.replace(/,/g, "") : this.elements[i].quantity;
      var qty = Number(qtyCleanComa);

      var maxQtyCleanComa = isNaN(this.elements[i].quantity_need) ? this.elements[i].quantity_need.replace(/,/g, "") : this.elements[i].quantity_need;
      var maxQty = Number(maxQtyCleanComa);

      return qty > maxQty
    },
    _getAdjstotalQuantity (adjs) {
      return this.$options.filters.formatRupiah(this.recalcQuantityElement(adjs))
    },
    _getItemBoms (itemMaterialId) {
      return $.ajax({
        type: "GET",
        url: `${baseBeApiUrl}/production/bom/item-material/${itemMaterialId}`,
        success: function (response) {
          return response
        },
        error: function (err) {
          console.log(`failed fetch : ${err}`)
        }
      });
    },
    getInventoryWHById (ip, ic, inventoryWHId) {
      var vm = this
      var parentItem = vm.elements[ip]
      var targetItem = parentItem.adjs[ic]

      if(!parentItem.inventory_used.includes(inventoryWHId)) {
        parentItem.inventory_used.push(inventoryWHId)
      }

      $.ajax({
        url: `${baseBeApiUrl}/inventory-warehouse/${vm.warehouse_id}/${parentItem.raw_material_id}/${inventoryWHId}`,
        type: "GET",
        success: function (response) { 
          targetItem.inventory_warehouse_id = response.id
          // targetItem.quantity = response.stock
          targetItem.quantity_max = response.stock
        },
        error: function (err) { console.log(`[material data] failed fetch : ${err}`) }
      });
    },
    // orders method
    applyElement (applyAll = false) {
      var vm = this
      // if((this._anyOrdersChecked() || applyAll) && !this.elements[0].job_order_detail_id) this.elements.pop()
      vm.elements = []

      vm.orders.forEach(function (order, index) {
        /** 
         * applyAll is true, loop all orders and push into elements
         * is element exist (has pushed) ?
         * if not exist push into element
         * */ 
        elementExist = vm.elements.find(function (element, elindex) {
          return element.job_order_detail_id == order.id
        })

        
        if(!elementExist && (applyAll || order.is_check)) {
          // this.elements.push({
          //   id: vm._randomNumber(),
          //   sales_detail_id: order.id,
          //   raw_material_id: null,
          //   api_uri: 'item-material',
          //   api_uri_id: order.item_material_id,
          //   length: order.length,
          //   sheet: order.sheet,
          //   quantity: order.quantity,
          //   balance: 0
          // })

          vm._getItemBoms(order.item_material_id).then(function (bom) {
            // check response is empty
            if(Object.entries(bom).length === 0 && bom.constructor === Object) return;

            bom.bom_details.forEach(function (bom, inbom) {
                var quantityNeed = order.quantity_issued*bom.quantity;

                vm.elements.push({
                  id: vm._randomNumber(),
                  job_order_detail_id: order.id,
                  raw_material_id: bom.material_id,
                  api_uri: 'raw-material',
                  api_uri_id: bom.material_id,
                  quantity: 0,
                  quantity_need: quantityNeed,
                  balance: 0,
                  has_adjustment: false,
                  adjs: [],
                  inventory_used: []
                })
            })
          
          });

        }
      })
    },
    // adjustments method
    toggleAdjustment (i) {
      var hasAdjustment = this.elements[i].has_adjustment

      !hasAdjustment ? this.addAdjsElement(i) : this.elements[i].adjs = []
      this.elements[i].quantity = this.elements[i].quantity
      this.elements[i].has_adjustment = !hasAdjustment
    },
    onchangeAdjs(ie, ia) {
      var element = this.elements[ie]

      if(element.adjs[ia].quantity > element.adjs[ia].quantity_max) {
        element.adjs[ia].quantity = element.adjs[ia].quantity_max
        element.adjs[ia].is_quantity_over = true
      }

      var maxQuantity = isNaN(element.quantity_need) ? element.quantity_need.replace(/,/g, "") : element.quantity_need;
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
        inventory_warehouse_id: null,
        quantity: 0,
        quantity_max: 0,
        is_quantity_over: false
      });
    },
    removeAdjsElement (ie, ia) {
      inventoryWH = this.elements[ie].adjs[ia]

      if(inventoryWH.inventory_warehouse_id != undefined) {        
        indexInv = this.elements[ie].inventory_used.indexOf(inventoryWH.inventory_warehouse_id)
        this.elements[ie].inventory_used.splice(indexInv, 1)
      }

      this.elements[ie].adjs.splice(ia, 1)

      recalcQuantity = this.recalcQuantityElement(this.elements[ie].adjs)
      this.elements[ie].quantity = recalcQuantity <= 0 ? 0 : recalcQuantity 
    },
    // elements method
    addElement (element = 'elements') {
      if(element == 'orders') {
        this.orders.push({
          id: this._randomNumber(),
          item_material_id: '',
          length: 0,
          sheet: 0,
          quantity: 0,
          balance: 0,
          balance_issued: 0,
          is_quantity_over: false
        })

        return
      }

      this.elements.push({
        id: this._randomNumber(),
        job_order_detail_id: '',
        raw_material_id: '',
        api_uri: 'raw-material',
        api_uri_id: '',
        quantity: 0,
        quantity_need: 0,
        balance: 0,
        has_adjustment: false,
        adjs: [],
        inventory_used: []
      })
    },
    removeElement (index) {
      if(this.elements.length == 1) return

      this.elements.splice(index, 1)
    },
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
        if(item.quantity == undefined) return Number(0)

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