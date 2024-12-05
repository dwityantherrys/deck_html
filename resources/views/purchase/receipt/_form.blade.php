<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $purchaseId = !empty(old('purchase_id')) ? old('purchase_id') : $model->purchase_id; ?>
<?php $receiptBy = !empty(old('receive_by')) ? old('receive_by') : $model->receive_by; ?>
<?php $vendorId = !empty(old('vendor_id')) ? old('vendor_id') : $model->vendor_id; ?>
<?php $branchId = !empty(old('branch_id')) ? old('branch_id') : $model->branch_id; ?>
<?php $receiptDetails = !empty(old('receipt_details')) ? old('receipt_details') : $model->receipt_details ?>

<div class="row">

  <div class="col-md-6">
    <div class="form-group @if($errors->has('date')) has-error @endif">
      <label>Receipt Date</label>

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

    <div class="form-group @if($errors->has('purchase_id')) has-error @endif">
      <label>Purchase Order Number</label>
      <select
        class="has-ajax-form form-control"
        name="purchase_id"
        style="width: 100%;"
        tabindex="-1"
        data-load="{{ $baseBeApiUrl . '/purchase/request' }}"> </select>
      @if($errors->has('purchase_id'))
        <span class="help-block">{{ $errors->first('purchase_id') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('receive_by')) has-error @endif">
      <label>Receive By</label>
      <select class="form-control" name="receive_by" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('receive_by'))
        <span class="help-block">{{ $errors->first('receive_by') }}</span>
      @endif
      <span class="help-block">data employee tidak ada? <a class="text-red" href="{{ url('/master/employee/create') }}" target="_blank">new employee</a></span>
    </div>

    <div class="form-group @if($errors->has('number')) has-error @endif">
      <label for="">Number</label>
      <input type="text" class="form-control" name="number" placeholder="request number" value="{{ !empty(old('number')) ? old('number') : $model->number }}" readonly>
      @if($errors->has('number'))
        <span class="help-block">{{ $errors->first('number') }}</span>
      @endif
    </div>

  </div>

  <div class="col-md-6">

    <div class="form-group @if($errors->has('vendor_id')) has-error @endif">
      <label>Vendor</label>
      <select class="form-control" name="vendor_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('vendor_id'))
        <span class="help-block">{{ $errors->first('vendor_id') }}</span>
      @endif
      <span class="help-block">data vendor tidak ada? <a class="text-red" href="{{ url('/master/customer/create') }}" target="_blank">new vendor</a></span>
    </div>

    <div class="form-group @if($errors->has('branch_id')) has-error @endif">
      <label>Branch</label>
      <select class="form-control" name="branch_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('branch_id'))
        <span class="help-block">{{ $errors->first('branch_id') }}</span>
      @endif
      <span class="help-block">data cabang tidak ada? <a class="text-red" href="{{ url('/master/branch/create') }}" target="_blank">new branch</a></span>
    </div>

  </div>

</div>

<hr>

<div id="vue-dynamic-element">
<!-- list order -->
<div class="panel">
    <a data-toggle="collapse" data-parent="#accordion" href="#collapseListOrders" class="btn btn-outline disabled" style="color: red; border: 1px solid">
      Purchase Orders <i class="fa fa-caret-down" style="margin-left: 5px"></i>
    </a>
  <div id="collapseListOrders" class="panel-collapse collapse" style="margin: 15px 0px;">

    <table id="vue-dynamic-element" class="table table-bordered table-hover">
      <thead class="table-header-primary">
        <tr>
          <th class="text-center" width="5%"> </th>
          {{-- <th width="50%">Raw Material</th> --}}
          <th width="50%">Item Material</th>
          <th>Qty</th>
          <th>Qty Left</th>
          <th>Est. Price</th>
          <th>Amount</th>
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
              {{-- :url="`{{ $baseBeApiUrl . '/items-sparepart' }}`"
              :name="`order_details[${i}][item_material_id]`"
              :value="order.item_material_id"
              v-on:selected="order.item_material_id = $event" --}}
              :url="`{{ $baseBeApiUrl . '/items-sparepart' }}`"
              :name="`order_details[${i}][item_material_id]`"
              :value="order.item_material_id"
              v-on:selected="order.item_material_id = $event"
              :readonly="true" />
          </td>
          <td>
            <input type="text" class="form-control" :name="`order_details[${i}][quantity]`" :value="order.quantity | formatRupiah" placeholder="quantity" readonly>
          </td>
          <td>
            <input type="text" class="form-control" :name="`order_details[${i}][quantity_left]`" :value="order.quantity_left | formatRupiah" placeholder="quantity" readonly>
          </td>
          <td>
            <input type="text" class="form-control" :name="`order_details[${i}][estimation_price]`" :value="order.estimation_price | formatRupiah" placeholder="estimation price" readonly>
          </td>
          <td>
            <input type="text" class="form-control" :name="`order_details[${i}][amount]`" :value="order.amount | formatRupiah" readonly>
          </td>
        </tr>

        <tr>
          <th colspan="2"> </th>
          <th>
            <label for="">@{{ getTotalQuantity('orders') | formatRupiah }}</label>
          </th>
          <th> </th>
          <th></th>
          <th>
            <label for="">Rp. @{{ getTotalAmount('orders') | formatRupiah }}</label>
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

<!-- list receive -->
<div class="form-group">
  {{-- <label>List Raw Materials</label> --}}
  <label>List Item Materials</label>

  <table id="vue-dynamic-element" class="table table-bordered table-hover">
    <thead class="table-header-primary">
      <tr>
        {{-- <th colspan="2" width="50%">Raw Material</th> --}}
        <th colspan="2" width="50%">Item Material</th>
        <th>Qty Receipt</th>
        <th>Price</th>
        <th>Amount</th>
        <th width="10%"></th>
      </tr>
    </thead>

    <tbody>
      <template v-for="(element, i) in elements">

        <tr :key="element.id">
          <td colspan="2">
            <input type="hidden" :name="`receipt_details[${i}][id]`" v-model="element.id">
            <input type="hidden" :name="`receipt_details[${i}][purchase_detail_id]`" v-model="element.purchase_detail_id">
            <input type="hidden" :name="`receipt_details[${i}][has_adjustment]`" v-model="element.has_adjustment">
            <vue-select2
              {{-- :url="`{{ $baseBeApiUrl . '/items-sparepart' }}`"
              :name="`receipt_details[${i}][item_material_id]`"
              :value="element.item_material_id"
              v-on:selected="element.item_material_id = $event" --}}
              :url="`{{ $baseBeApiUrl . '/items-sparepart' }}`"
              :name="`receipt_details[${i}][item_material_id]`"
              :value="element.item_material_id"
              v-on:selected="element.item_material_id = $event"
              :readonly="true" />
          </td>
          <td>
            <div :class="['form-group', {'has-error': element.is_quantity_over}]">
              <input
                type="text"
                class="form-control"
                :name="`receipt_details[${i}][quantity]`"
                v-model="element.quantity"
                @change="isNumber(i, 'quantity')"
                placeholder="quantity"
                :readonly="element.has_adjustment">
              <span class="help-block" v-if="element.is_quantity_over">max: @{{ element.quantity_max }}</span>
            </div>
          </td>
          <td>
            <input type="text" class="form-control" :name="`receipt_details[${i}][estimation_price]`" v-model="element.estimation_price" @change="isNumber(i, 'estimation_price')" placeholder="estimation price">
          </td>
          <td>
            <input type="text" class="form-control" :name="`receipt_details[${i}][amount]`" :value="getAmount(i)" placeholder="estimation price">
          </td>
          <td>
            <button type="button" class="btn btn-default" @click="toggleAdjustment(i)"><i class="fa fa-sliders"></i></button>
            <button type="button" class="btn btn-default text-red" @click="removeElement(i)"><i class="fa fa-minus"></i></button>
          </td>
        </tr>

        <!-- if adjustment not empty -->
        <template v-if="element.has_adjustment">
          <tr v-for="(adj, ia) in element.adjs" :key="`adj-${element.id}-${adj.id}`">
            <td width="5%">@{{ ia+1 }}</td>
            <td>
              <input type="hidden" :name="`receipt_details[${i}][adjs][${ia}][id]`" v-model="adj.id">
              <vue-select2
                {{-- :url="`{{ $baseBeApiUrl . '/items-sparepart' }}`"
                :name="`receipt_details[${i}][adjs][${ia}][item_material_id]`"
                :value="adj.item_material_id"
                v-on:selected="adj.item_material_id = $event" --}}
                :url="`{{ $baseBeApiUrl . '/items-sparepart' }}`"
                :name="`receipt_details[${i}][adjs][${ia}][item_material_id]`"
                :value="adj.item_material_id"
                v-on:selected="adj.item_material_id = $event"
                :readonly="true"/>
            </td>
            <td>
              <div :class="['form-group', {'has-error': element.is_quantity_over}]">
                <input
                  type="text"
                  class="form-control"
                  :name="`receipt_details[${i}][adjs][${ia}][quantity]`"
                  v-model="adj.quantity"
                  @change="onchangeAdjs(i, ia)"
                  placeholder="quantity">

                <span class="help-block" v-if="adj.is_quantity_over">max: @{{ adj.quantity_max }}</span>
              </div>
            </td>
            <td></td>
            <td></td>
            <td>
              <button type="button" class="btn btn-default text-red" @click="removeAdjsElement(i, ia)"><i class="fa fa-minus"></i></button>
            </td>
          </tr>

          <tr>
            <td colspan="5"></td>
            <td>
              <button type="button" class="btn btn-default text-success" @click="addAdjsElement(i)"><i class="fa fa-plus"></i></button>
            </td>
          </tr>
        </template>

      </template>

      <tr>
        <th colspan="2">
          @if($errors->has('receipt_details.*'))
            <span class="help-block text-red">* {{ $errors->first('receipt_details.*') }}</span>
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
        <th> </th>
      </tr>

      <tr>
        <th colspan="4"><label>Total Amount (Before Tax)</label></th>
        <th><label for="">Rp. @{{ getTotalAmount() | formatRupiah }}</label></th>
      </tr>
      
      <tr>
        <th colspan="4"><label>Amount Pajak</label></th>
        <th><label for="">Rp. @{{ getTax() | formatRupiah }}</label></th>
        <input type="hidden" name="amount_tax" :value="getTax()">
      </tr>

      <tr>
        <th colspan="4"><label>Grand Total (After Tax)</label></th>
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
  var elements = <?php echo json_encode($receiptDetails); ?>;
  var tax_type = 0;

  $(".datepicker").datepicker({ autoClose: true });
  $('[data-toggle="tooltip"]').tooltip();


  $(".has-ajax-form").change(function() {
      var url = $(this).data('load') + '/' + $(this).val()

      $.ajax({
          type: "GET",
          url: url,
          success: function(response) {
            console.log(response.tax_type);
              // set value form
              select2AjaxHandler('select[name="vendor_id"]', `{{ $baseBeApiUrl . '/customer' }}`, response.vendor_id);
              select2AjaxHandler('select[name="branch_id"]', `{{ $baseBeApiUrl . '/branch' }}`, response.branch_id);
              app.orders = response.purchase_details;
              app.tax_type = response.tax_type;

              $(`a[href="#collapseListOrders"]`).removeClass('disabled')
          },
          error: function(err) { console.log(`failed fetch : ${err}`) }
      });
  });

  select2AjaxHandler('select[name="vendor_id"]', `{{ $baseBeApiUrl . '/customer' }}`, '{{ $vendorId }}');
  select2AjaxHandler('select[name="branch_id"]', `{{ $baseBeApiUrl . '/branch' }}`, '{{ $branchId }}');
  select2AjaxHandler('select[name="receive_by"]', `{{ $baseBeApiUrl . '/employee' }}`, '{{ $receiptBy }}');
  select2AjaxHandler('select[name="purchase_id"]', `{{ $baseBeApiUrl . '/purchase/request' }}`, '{{ $purchaseId }}');

  Vue.filter('formatRupiah', function (value) {
    if (!value) return '';
    
    return new Intl.NumberFormat('en-US', {
      style: 'decimal',  // Menggunakan gaya decimal untuk menghindari simbol mata uang
      minimumFractionDigits: 0 // Jika kamu tidak ingin desimal, atur ini ke 0
    }).format(value);
  });


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
      tax_type: tax_type
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
            this.elements[index][attribut] = 0;
            this.elements[index].is_quantity_over = true
            return
          }

          this.elements[index].is_quantity_over = false
        }

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
      // orders method
      applyElement (applyAll = false) {
        var vm = this
        if((this._anyOrdersChecked() || applyAll) && !this.elements[0].item_material_id) this.elements.pop()
        // if((this._anyOrdersChecked() || applyAll) && !this.elements[0].raw_material_id) this.elements.pop()

        this.orders.forEach(function (order, index) {
          /**
           * applyAll is true, loop all orders and push into elements
           * is element exist (has pushed) ?
           * if not exist push into element
           * */
          elementExist = this.elements.find(function (element, elindex) {
            // return element.raw_material_id == order.raw_material_id
            return element.item_material_id == order.item_material_id
          })

          if(!elementExist && (applyAll || order.is_check)) {
          // if((!elementExist && applyAll) || (!elementExist && order.is_check)) {
            this.elements.push({
              id: vm._randomNumber(),
              purchase_detail_id: order.id,
              // raw_material_id: order.raw_material_id,
              item_material_id: order.item_material_id,
              quantity: vm.$options.filters.formatRupiah(order.quantity_left),
              quantity_max: vm.$options.filters.formatRupiah(order.quantity_left),
              estimation_price: vm.$options.filters.formatRupiah(order.estimation_price),
              has_adjustment: false,
              adjs: []
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
          item_material_id: targetElement.item_material_id,
          // raw_material_id: targetElement.raw_material_id,
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
            // raw_material_id: '',
            item_material_id: '',
            quantity: 0,
            estimation_price: 0,
            tax_type: 1,
          })

          return
        }

        this.elements.push({
          id: this._randomNumber(),
          purchase_detail_id: null,
          raw_material_id: '',
          quantity: 0,
          quantity_max: 0,
          estimation_price: 0,
          has_adjustment: false,
          tax_type: 1,
          adjs: []
        })
      },
      removeElement (index) {
        if(this.elements.length == 1) return

        this.elements.splice(index, 1)
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
        var qty = isNaN(this[element][i].quantity) ? this[element][i].quantity.replace(/,/g, "") : this[element][i].quantity;
        var estPrice = isNaN(this[element][i].estimation_price) ? this[element][i].estimation_price.replace(/,/g, "") : this[element][i].estimation_price;

        result = qty*estPrice
        // if(element === 'elements') {
        //   var discount = isNaN(this[element][i].discount) ? this[element][i].discount.replace(/,/g, "") : this[element][i].discount;
        //   discount = isNaN((estPrice*discount)/100) ? 0 : (estPrice*discount)/100

        //   result = qty*(estPrice-discount)
        // }

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
      getTax () {
          var vm = this
          var totalAmountWithDiscount = vm.getTotalAmount()
          var resultWithTax = totalAmountWithDiscount*(11/100)
          var resultWithTaxInclude = totalAmountWithDiscount - ((11/100) * totalAmountWithDiscount);
          if(vm.tax_type == 1){
            return resultWithTax;
          }else if(vm.tax_type == 2){
            return totalAmountWithDiscount -resultWithTaxInclude;
          }else{
            return 0
          }

      },
      getGrandAmount() {
        if(this.tax_type == 1){
          var totalAmountWithDiscount = this.getTotalAmount()
          var resultWithTax = (totalAmountWithDiscount*(11/100))
          return (this.getTotalAmount()) + resultWithTax
        }else if(this.tax_type == 2){
          var totalAmountWithDiscount = this.getTotalAmount()
          var resultWithTaxInclude = totalAmountWithDiscount - ((11/100) * totalAmountWithDiscount);
          var ppnnotinclude = (11/111) * totalAmountWithDiscount;

          return resultWithTaxInclude
        }else{
          return this.getTotalAmount()
        }
      },
    }
  })
  </script>
  @endsection
