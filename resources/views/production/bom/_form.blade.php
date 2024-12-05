<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $itemId = !empty(old('item_id')) ? old('item_id') : $model->item_id; ?>
<?php $bomDetails = !empty(old('bom_details')) ? old('bom_details') : $model->bom_details ?>

<div class="row">

  <div class="col-md-6">

    <div class="form-group">
      <label>Category</label>
      <input class="form-control" type="text" name="production_category_label" value="{{ $defaultProductionCategory['label'] }}" readonly>
      <input type="hidden" name="production_category" value="{{ $defaultProductionCategory['id'] }}">
    </div>

    <div class="form-group @if($errors->has('item_id')) has-error @endif">
      <label>Item Material</label>
      <select class="form-control" name="item_id" style="width: 100%;" tabindex="-1"> </select>
      @if($errors->has('item_id'))
        <span class="help-block">{{ $errors->first('item_id') }}</span>
      @endif
    </div>

  </div>
  
  <div class="col-md-6">

    <div class="form-group @if($errors->has('manufacture_quantity')) has-error @endif">
      <label for="">Manufacture quantity (m)</label>
      <input type="number" class="form-control" name="manufacture_quantity" placeholder="manufacture quantity" value="{{ !empty(old('manufacture_quantity')) ? old('manufacture_quantity') : $model->manufacture_quantity }}">
      @if($errors->has('manufacture_quantity'))
        <span class="help-block">{{ $errors->first('manufacture_quantity') }}</span>
      @endif
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
        <th>Item Material</th>
        <!-- <th width="10%">Unit</th> -->
        <th width="10%">Qty (kg)</th>
        <th width="10%">Process</th>
        <th width="10%">Costing</th>
        <th width="10%">Remark</th>
        <th width="10%"></th>
      </tr>
    </thead>

    <tbody>    
      <tr v-for="(element, i) in elements" :key="element.id">
        <td> 
          <input type="hidden" :name="`bom_details[${i}][id]`" v-model="element.id">
          <vue-select2 
            :url="`{{ $baseBeApiUrl . '/raw-material' }}`"
            :name="`bom_details[${i}][material_id]`"
            :value="element.material_id"
            v-on:selected="getItemMaterialById(i, $event)"/>
        </td>
        <!-- <td>
          <input type="text" class="form-control" :name="`bom_details[${i}][unit]`" v-model="element.unit" placeholder="unit">
        </td> -->
        <td>
          <input type="text" class="form-control" :name="`bom_details[${i}][quantity]`" v-model="element.quantity" @change="isNumber(i, 'quantity')" placeholder="quantity">
        </td>
        <td>
          <input type="text" class="form-control" :name="`bom_details[${i}][production_process]`" v-model="element.production_process" placeholder="process">
        </td>
        <td>
          <input type="text" class="form-control" :name="`bom_details[${i}][costing]`" v-model="element.costing" @change="isNumber(i, 'costing')" placeholder="estimation costing">
        </td>
        <td>
          <input type="text" class="form-control" :name="`bom_details[${i}][remark]`" v-model="element.remark" placeholder="remark">
        </td>
        <td>
          <button type="button" class="btn btn-default text-red" @click="removeElement(i)"><i class="fa fa-minus"></i></button>
        </td>
      </tr>

      <tr>
        <th>
          @if($errors->has('bom_details.*'))
            <span class="help-block text-red">* {{ $errors->first('bom_details.*') }}</span>
          @endif
        </th>
        <!-- <th></th> -->
        <th>
          <label for="">@{{ getTotalQuantity() | formatRupiah }}</label>
        </th>
        <th></th>
        <th>
          <label for="">Rp. @{{ getTotalAmount() | formatRupiah }}</label>
          <input type="hidden" name="total_costing" :value="getTotalAmount()">
        </th>
        <th></th>
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
<script src="{{ asset('vendor/autonumeric/autoNumeric.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('js/vue.js') }}"></script>
<script>
var baseBeApiUrl = "{{ $baseBeApiUrl }}";
var elements = <?php echo json_encode($bomDetails); ?>;

$(".datepicker").datepicker({ autoClose: true });
$('[data-toggle="tooltip"]').tooltip();

select2AjaxHandler('select[name="item_id"]', `{{ $baseBeApiUrl . '/item-material' }}`, '{{ $itemId }}');

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
        material_id: '',
        unit: '',
        production_process: '',
        remark: '',
        costing: 0,
        quantity: 0
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
        url: `${baseBeApiUrl}/raw-material/${itemMaterialId}`,
        type: "GET",
        success: function (response) { 
          vm.elements[i].material_id = response.id
        },
        error: function (err) { console.log(`[material data] failed fetch : ${err}`) }
      });
    },
    getAmount (i) {
      var qty = isNaN(this.elements[i].quantity) ? this.elements[i].quantity.replace(/,/g, "") : this.elements[i].quantity;
      var estCosting = isNaN(this.elements[i].costing) ? this.elements[i].costing.replace(/,/g, "") : this.elements[i].costing;

      return this.$options.filters.formatRupiah(estCosting)
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