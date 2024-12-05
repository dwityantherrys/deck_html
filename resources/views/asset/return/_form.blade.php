@php
$baseBeApiUrl = url('/api/backend');
$createdBy = !empty(old('created_by')) ? old('created_by') : $model->created_by;
$customerId = !empty(old('customer_id')) ? old('customer_id') : $model->customer_id;
$warehouseId = !empty(old('warehouse_id')) ? old('warehouse_id') : $model->warehouse_id;
$loanDetails = !empty(old('loan_details')) ? old('loan_details') : $model->loan_details
@endphp
<div class="row">
  <div class="col-md-6">
    <div class="form-group @if($errors->has('loan_date')) has-error @endif">
      <label>Loan Date</label>

      <div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input
        type="text"
        class="form-control pull-right"
        name="loan_date"
        value="{{ empty($model->loan_date) ? date('m/d/Y') : $model->loan_date->format('m/d/Y') }}"
        readonly
        disabled>
      </div>
      @if($errors->has('loan_date'))
        <span class="help-block">{{ $errors->first('loan_date') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('created_by')) has-error @endif">
      <label>PIC</label>
      <select class="form-control" name="created_by" id="" style="width: 100%;" tabindex="-1" readonly disabled> </select>
      @if($errors->has('created_by'))
        <span class="help-block">{{ $errors->first('created_by') }}</span>
      @endif
      {{-- <span class="help-block">data pic tidak ada? <a class="text-red" href="{{ url('/master/employee/create') }}" target="_blank">new pic</a></span> --}}
    </div>

    <div class="form-group @if($errors->has('loan_number')) has-error @endif">
      <label for="">Loan Number</label>
      <input type="text" class="form-control" name="loan_number" placeholder="loan number" value="{{ !empty(old('loan_number')) ? old('loan_number') : $model->loan_number }}"  readonly disabled>
      @if($errors->has('quotation_number'))
        <span class="help-block">{{ $errors->first('loan_number') }}</span>
      @endif
    </div>
  </div>

  <div class="col-md-6">

    <div class="form-group @if($errors->has('loan_date')) has-error @endif">
      <label>Loan Return Date</label>
      <div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input
        type="text"
        class="form-control pull-right"
        name="loan_date"
        value="{{ empty($model->loan_date) ? date('m/d/Y') : $model->loan_date->format('m/d/Y') }}"
        readonly disabled>
      </div>
      @if($errors->has('loan_date'))
        <span class="help-block">{{ $errors->first('loan_date') }}</span>
      @endif
    </div>

    <div class="form-group @if($errors->has('customer_id')) has-error @endif">
      <label>Customer</label>
      <select class="form-control" name="customer_id" style="width: 100%;" tabindex="-1" readonly disabled> </select>
      @if($errors->has('customer_id'))
        <span class="help-block">{{ $errors->first('customer_id') }}</span>
      @endif
      {{-- <span class="help-block">data Customer tidak ada? <a class="text-red" href="{{ url('/master/customer/create') }}" target="_blank">new Customer</a></span> --}}
    </div>

    <div class="form-group @if($errors->has('warehouse_id')) has-error @endif">
      <label>Warehouse</label>
      <select class="form-control" name="warehouse_id" style="width: 100%;" tabindex="-1" readonly disabled> </select>
      @if($errors->has('warehouse_id'))
        <span class="help-block">{{ $errors->first('warehouse_id') }}</span>
      @endif
      {{-- <span class="help-block">data warehouse tidak ada? <a class="text-red" href="{{ url('/master/warehouse/create') }}" target="_blank">new warehouse</a></span> --}}
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
          <th style="width: 50%">Item Material</th>
          <th style="width: 100%">Qty</th>
          {{-- <th width="30%">Action</th> --}}
        </tr>
      </thead>

      <tbody>
        <tr v-for="(element, i) in elements" :key="element.id">
          <td style="padding: 8px 0px;">
            <input type="hidden" :name="`loan_details[${i}][id]`" v-model="element.id">
            <vue-select2
            :url="`{{ $baseBeApiUrl . '/asset-stock' }}`"
            :name="`loan_details[${i}][asset_stock_id]`"
            :value="element.asset_stock_id"
            :selected="`loan_details[${i}][asset_stock_id]`"
            v-on:selected="getItemMaterialById(i, $event)"
            readonly disabled/>
          </td>
          <td style="padding: 8px 0px;">
            <input type="text" class="form-control" :name="`loan_details[${i}][quantity]`" v-model="element.quantity" @change="isNumber(i, 'quantity')" placeholder="quantity" readonly disabled>
          </td>
          {{-- <td>
            <button type="button" class="btn btn-default text-red" @click="removeElement(i)"><i class="fa fa-minus"></i></button>
          </td> --}}
        </tr>

        <tr>
          <th>
            @if($errors->has('loan_details.*'))
              <span class="help-block text-red">* {{ $errors->first('loan_details.*') }}</span>
            @endif
          </th>
          <th>
            <label for="">Total: @{{ getTotalQuantity() | formatRupiah }}</label>
          </th>
          <th>
            {{-- <button type="button" class="btn btn-default text-success" @click="addElement()"><i class="fa fa-plus"></i></button> --}}
          </th>
        </tr>

        {{-- <tr>
          <th colspan="1"><label>Grand Total</label></th>
          <th><label for="">Rp. @{{ getGrandAmount() | formatRupiah }}</label></th>
          <input type="hidden" name="total_price" :value="getGrandAmount()">
        </tr> --}}
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
  var elements = <?php echo json_encode($loanDetails); ?>;
  var warehouse_id = 0;
  var discount = 0;

  $(".datepicker").datepicker({ autoClose: true });
  $('[data-toggle="tooltip"]').tooltip();

  $('select[name="warehouse_id"]').change(function() {
    app.warehouse_id = $(this).val();
  });

  select2AjaxHandler('select[name="customer_id"]', `{{ $baseBeApiUrl . '/customer' }}`, '{{ $customerId }}');
  select2AjaxHandler('select[name="warehouse_id"]', `{{ $baseBeApiUrl . '/warehouse' }}`, '{{ $warehouseId }}');
  select2AjaxHandler('select[name="created_by"]', `{{ $baseBeApiUrl . '/employee' }}`, '{{ $createdBy }}');

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
            var newOption = new Option(response.asset.name, response.id, true, true);
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
      warehouse_id: warehouse_id
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
          length_options: [],
          is_custom_length: false,
          length: '',
          sheet: 0,
          quantity: 0,
          price: 0,
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
      getItemMaterialById (i, itemMaterialId) {
        var vm = this

        $.ajax({
          url: `${baseBeApiUrl}/item-material/${itemMaterialId}/warehouse/${vm.warehouse_id}`,
          type: "GET",
          success: function (response) {
            vm.elements[i].item_material_id = response.id
            vm.elements[i].length_options = response.item.length_options
            vm.elements[i].charge_custom_length = response.item.charge_custom_length
            vm.elements[i].price = vm.$options.filters.formatRupiah(response.price)

            if(response.item.length) vm.elements[i].length = response.item.length
          },
          error: function (err) { console.log(`[material data] failed fetch : ${err}`) }
        });
      },
      calcQuantity (i) {
        this.elements[i].is_custom_length = this._isCustomLength(i);

        var length = isNaN(this.elements[i].length) ? this.elements[i].length.replace(/,/g, "") : this.elements[i].length
        var sheet = isNaN(this.elements[i].sheet) ? this.elements[i].sheet.replace(/,/g, "") : this.elements[i].sheet

        this.elements[i].quantity = length * sheet;
      },
      getAmount (i) {
        var qty = isNaN(this.elements[i].quantity) ? this.elements[i].quantity.replace(/,/g, "") : this.elements[i].quantity;
        var estPrice = isNaN(this.elements[i].price) ? this.elements[i].price.replace(/,/g, "") : this.elements[i].price;
        var chargeCustomLength = 0;

        if(this.elements[i].is_custom_length == 'true' || this.elements[i].is_custom_length == true) {
          chargeCustomLength = qty*this.elements[i].charge_custom_length
        }

        return this.$options.filters.formatRupiah((qty*estPrice) + chargeCustomLength)
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
      },
      getDiscount () {
        var vm = this
        var totalAmount = vm.getTotalAmount()

        return totalAmount * (vm.discount/100);
      },
      getGrandAmount() {
        return this.getTotalAmount() - this.getDiscount()
      }
    }
  })
</script>
@endsection
