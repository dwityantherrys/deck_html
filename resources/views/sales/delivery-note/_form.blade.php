<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $number = !empty(old('number')) ? old('number') : $model->number; ?>
<?php $salesId = !empty(old('sales_id')) ? old('sales_id') : $model->sales_id; ?>
<?php $joTypeId = !empty(old('type')) ? old('type') : (!is_null($model->type) ? $model->type : $model::TYPE_PRODUCTION); ?>
<?php $warehouseId = !empty(old('warehouse_id')) ? old('warehouse_id') : $model->warehouse_id; ?>
<?php $vendorId = !empty(old('vendor_id')) ? old('vendor_id') : $model->vendor_id; ?>
<?php $createdBy = !empty(old('created_by')) ? old('created_by') : $model->created_by; ?>
<?php $jobOrderDetais = !empty(old('delivery_note_details')) ? old('delivery_note_details') : $model->delivery_note_details ?>
<?php $jobOrderId = !empty(old('job_order_id')) ? old('job_order_id') : $model->job_order_id; ?>

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

    <div class="form-group @if($errors->has('number')) has-error @endif">
      <label for="">BAP Number</label>
      <input type="text" class="form-control" name="number" placeholder="order number" value="{{ !empty(old('number')) ? old('number') : $model->number }}" readonly>
      @if($errors->has('number'))
        <span class="help-block">{{ $errors->first('number') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('job_order_id')) has-error @endif">
      <label>SPK Number</label>
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

    <div class="form-group @if($errors->has('type')) has-error @endif">
      <label>Kategori Perbaikan</label>
      <select class="form-control" name="type" style="width: 100%;" tabindex="-1">
        <option value="">Pilih Kategori Perbaikan</option>
        @foreach($joTypes as $typeId => $type)
        <option value="{{ $typeId }}" @if($joTypeId == $typeId) selected @endif>{{ $type['label'] }}</option>
        @endforeach
      </select>
      @if($errors->has('type'))
        <span class="help-block">{{ $errors->first('type') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('remark')) has-error @endif">
      <label for="">Lokasi</label>
      <input type="text" class="form-control" name="location" placeholder="location" value="{{ !empty(old('location')) ? old('location') : $model->location }}">
      @if($errors->has('location'))
        <span class="help-block">{{ $errors->first('location') }}</span>
      @endif
    </div>
  

  </div>
  
  <div class="col-md-6">

    <div class="form-group @if($errors->has('created_by')) has-error @endif">
      <label>PIC</label>
      <select class="form-control" name="created_by" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('created_by'))
        <span class="help-block">{{ $errors->first('created_by') }}</span>
      @endif
      <span class="help-block">data pic tidak ada? <a class="text-red" href="{{ url('/master/employee/create') }}" target="_blank">new pic</a></span>
    </div>

    <div class="form-group @if($errors->has('vendor_id')) has-error @endif">
      <label>Kepada (Vendor)</label>
      <select class="form-control" name="vendor_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('vendor_id'))
        <span class="help-block">{{ $errors->first('warehouse_id') }}</span>
      @endif
      <span class="help-block">data vendor tidak ada? <a class="text-red" href="{{ url('/master/vendor/create') }}" target="_blank">buat vendor</a></span>
    </div>

  </div>

</div>

<hr>

<div id="vue-dynamic-element">
<!-- list sales order -->

<hr class="type-sales">

<div class="form-group">
  <label>Item Perbaikan</label>   
    
  <table id="vue-dynamic-element" class="table table-bordered table-hover">
    <thead class="table-header-primary">
      <tr>
        <th width="50%">Item Material</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Amount</th>
      </tr>
    </thead>

    <tbody>    
      <tr v-for="(element, i) in elements" :key="element.id">
        <td>               
          <input type="hidden" :name="`delivery_note_details[${i}][id]`" v-model="element.id">
          <vue-select2 
              :url="`{{ $baseBeApiUrl . '/items-service' }}`"
              :name="`delivery_note_details[${i}][item_material_id]`"
              :value="element.item_material_id"
              v-on:selected="getItemByName(i, $event)"
              :readonly="true" />
        </td>
          <!--<input type="text" class="form-control" :name="`job_order_details[${i}][item_name]`" v-model="element.item_name" placeholder="item Name">-->
        </td>
        <td>
          <input type="text" class="form-control" :name="`delivery_note_details[${i}][quantity]`" v-model="element.quantity" @change="isNumber(i, 'quantity')" placeholder="quantity">
        </td>
        <td>
          <input type="text" class="form-control" :name="`delivery_note_details[${i}][price]`" v-model="element.price" @change="isNumber(i, 'price')" placeholder="estimation price">
        </td>
        <td>
          <input type="text" class="form-control" :name="`delivery_note_details[${i}][amount]`" :value="getAmount(i)" placeholder="estimation price">
        </td>
        
      </tr>
      
      <tr>
        <th>
          @if($errors->has('delivery_note_details.*'))
            <span class="help-block text-red">* {{ $errors->first('delivery_note_details.*') }}</span>
          @endif
        </th>
        <th>
        <label for="">@{{ getTotalQuantity() | formatRupiah }}</label>
        </th>
        <th>
          <label for="">Rp. @{{ getTotalAmount() | formatRupiah }}</label>
          <input type="hidden" name="total_price" :value="getTotalAmount()">
        </th>
        <th>
          <label for="">Rp. @{{ getTotalAmount() | formatRupiah }}</label>
          <input type="hidden" name="total_price" :value="getTotalAmount()">
        </th>
        
      </tr>

      <tr>
        <th colspan="3"><label>Total Amount (Before Tax)</label></th>
        <th><label for="">Rp. @{{ getTotalAmount() | formatRupiah }}</label></th>
      </tr>
      
      <tr>
        <th colspan="3"><label>Amount Pajak</label></th>
        <th><label for="">Rp. @{{ getTax() | formatRupiah }}</label></th>
        <input type="hidden" name="amount_tax" :value="getTax()">
      </tr>

      <tr>
        <th colspan="3"><label>Grand Total (After Tax)</label></th>
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
<script src="{{ asset('vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/vue.js') }}"></script>
<script>

var isPrTypeSales = false;
var baseBeApiUrl = "{{ $baseBeApiUrl }}";
var elements = <?php echo json_encode($jobOrderDetais); ?>;
var tax_type = 0;
console.log("Elements : " + elements);

$(document).ready(function() {
  $('.type-sales').hide();
    app.isPrTypeSales = false;

  $(".datepicker").datepicker({ autoClose: true });
  $('[data-toggle="tooltip"]').tooltip();
  $('select[name="request_type"]').select2();

  select2AjaxHandler('select[name="vendor_id"]', `{{ $baseBeApiUrl . '/customer' }}`, '{{ $vendorId }}');
  select2AjaxHandler('select[name="created_by"]', `{{ $baseBeApiUrl . '/employee' }}`, '{{ $createdBy }}');
  select2AjaxHandler('select[name="job_order_id"]', `{{ $baseBeApiUrl . '/production/job-order' }}`, '{{ $jobOrderId }}');

  $('select[name="request_type"]').change(function () {
    var type = $(this).val();
    $('.type-sales').hide();
    app.isPrTypeSales = false;
    // togglePrType(type);
  });
  
  $(".has-ajax-form").change(function() {
      var url = $(this).data('load') + '/' + $(this).val()

      $.ajax({
          type: "GET",
          url: url,
          success: function(response) {
              // set value form
              select2AjaxHandler('select[name="created_by"]', `{{ $baseBeApiUrl . '/employee' }}`, response.created_by);
              select2AjaxHandler('select[name="vendor_id"]', `{{ $baseBeApiUrl . '/customer' }}`, response.vendor_id);
              $('input[name="location"]').val(response.location);
              $('select[name="type"]').val(response.type).trigger('change');
              app.elements = response.job_order_details
              app.tax_type = response.tax_type;

              $(`a[href="#collapseListOrders"]`).removeClass('disabled')
          },
          error: function(err) { console.log(`failed fetch : ${err}`) }
      });
  });
})

// function togglePrType(type) {  
//   if(type == TYPE_SALES) {
//     $('.type-sales').show();
//     app.isPrTypeSales = true;
//   } else if(type == TYPE_job_order) {
    // $('.type-sales').hide();
    // app.isPrTypeSales = false;
//   }
// }

Vue.filter('formatRupiah', function (value) {
  if (!value) return '';
  
  return new Intl.NumberFormat('en-US', {
    style: 'decimal',  // Menggunakan gaya decimal untuk menghindari simbol mata uang
    minimumFractionDigits: 0 // Jika kamu tidak ingin desimal, atur ini ke 0
  }).format(value);
});

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
    jobOrders: [],
    elements: elements,
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
    //sales orders method
    applyElement (applyAll = false) {
      var vm = this
      if((this._anyOrdersChecked() || applyAll) && !this.elements[0].job_order_detail_id) this.elements.pop()

      this.jobOrders.forEach(function (order, index) {
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
            job_order_detail_id: order.job_order_id,
            item_material_id: order.item_material_id,
            status: order.status,
            quantity: order.quantity,
            price: vm.$options.filters.formatRupiah(order.price),
            amount: vm.$options.filters.formatRupiah(order.amount)
          })

        }
      })
    },
    addElement () {
      this.elements.push({
        id: this._randomNumber(),
        raw_material_id: '',
        quantity: 0,
        price: 0,
        tax_type: 1,
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
      var estPrice = isNaN(this.elements[i].price) ? this.elements[i].price.replace(/,/g, "") : this.elements[i].price;

      return this.$options.filters.formatRupiah(qty*estPrice)
    },

    getTotalQuantity () {
      var result = 0;
      var quantities = this.elements.map((item) => {
        var quantity = isNaN(item.quantity) ? item.quantity : item.quantity;
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
    getItemByName (i, itemId) {
      var vm = this

      $.ajax({
        url: `${baseBeApiUrl}/items-service/${itemId}`,
        type: "GET",
        success: function (response) { 
          vm.elements[i].quantity = 1
          vm.elements[i].item_name = response.name
          vm.elements[i].price = response.price
          
        },
        error: function (err) { console.log(`[Item data] failed fetch : ${err}`) }
      });
    }
  }
})
</script>
@endsection