<?php $baseBeApiUrl = url('/api/backend'); ?>

<?php $categoryId = !empty(old('item_category_id')) ? old('item_category_id') : $model->item_category_id; ?>
<?php $isActive = !empty(old('is_active')) ? old('is_active') : ($model->is_active ? $model->is_active : 1); ?>
<?php $hasLengthOptions = !empty(old('has_length_options')) ? old('has_length_options') : $model->has_length_options; ?>
<?php $itemImages = $model->images; ?>
<?php $itemMaterials = !empty(old('item_materials')) ? old('item_materials') : $model->item_materials ?>

<div class="form-group @if($errors->has('item_category_id')) has-error @endif">
  <label>Category</label>
  <select class="form-control" name="item_category_id" id="" style="width: 100%;" tabindex="-1"> </select>
  @if($errors->has('item_category_id'))
    <span class="help-block">{{ $errors->first('item_category_id') }}</span>
  @endif
  <span class="help-block">data category tidak ada? <a class="text-red" href="{{ url('/master/item/category/create') }}" target="_blank">new category</a></span>
</div>

<div class="form-group @if($errors->has('name')) has-error @endif">
  <label for="">Name</label>
  <input type="text" class="form-control" name="name" placeholder="item name" value="{{ !empty(old('name')) ? old('name') : $model->name }}">
  @if($errors->has('name'))
    <span class="help-block">{{ $errors->first('name') }}</span>
  @endif
</div>

<div class="form-group">
  <label>Description</label>
  <textarea class="form-control" rows="3" name="description" placeholder="item description">{{ $model->description }}</textarea>
</div>

<div class="row">
  <div class="col-sm-6">
    <div class="form-group @if($errors->has('height')) has-error @endif">
      <label for="">Height (mm)</label>
      <input type="number" class="form-control" name="height" placeholder="item height" value="{{ !empty(old('height')) ? old('height') : $model->height }}">
      @if($errors->has('height'))
        <span class="help-block">{{ $errors->first('height') }}</span>
      @endif
    </div>
  </div>
  <div class="col-sm-6">
    <div class="form-group @if($errors->has('width')) has-error @endif">
      <label for="">Width (mm)</label>
      <input type="text" class="form-control" name="width" placeholder="item width" value="{{ !empty(old('width')) ? old('width') : $model->width }}">
      @if($errors->has('width'))
        <span class="help-block">{{ $errors->first('width') }}</span>
      @endif
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-6">
    <div class="form-group @if($errors->has('length')) has-error @endif">

      <label for="">Length (m)</label>
      <input type="text" class="form-control" name="length" placeholder="item length" value="{{ !empty(old('length')) ? old('length') : $model->length }}">
      @if($errors->has('length'))
        <span class="help-block">{{ $errors->first('length') }}</span>
      @endif
      <div style="display: flex; text-align: center">
        <input type="checkbox" id="has_length_options" name="has_length_options" value="1" @if($hasLengthOptions) checked @endif>
        <label style="font-weight: normal; margin-left: 4px;" for="has_length_options">Has length options</label>
      </div>

    </div>
  </div>

  <div class="col-sm-6">
    <div class="form-group @if($errors->has('max_custom_length')) has-error @endif">
      <label for="">Maximum Custom Length (m)</label>
      <input type="text" class="form-control" name="max_custom_length" placeholder="maximum custom length" value="{{ !empty(old('max_custom_length')) ? old('max_custom_length') : $model->max_custom_length }}">
      @if($errors->has('max_custom_length'))
        <span class="help-block">{{ $errors->first('max_custom_length') }}</span>
      @endif
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-6">
    <div class="form-group @if($errors->has('charge_custom_length')) has-error @endif">
      <label for="">Charge Custom Length (Rp)</label>
      <input type="text" class="form-control" name="charge_custom_length" placeholder="charge custom length" value="{{ !empty(old('charge_custom_length')) ? old('charge_custom_length') : $model->charge_custom_length }}">
      @if($errors->has('charge_custom_length'))
        <span class="help-block">{{ $errors->first('charge_custom_length') }}</span>
      @endif
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-6">
    <div class="form-group @if($errors->has('item_az')) has-error @endif">
      <label>AZ</label>
      <input type="text" class="form-control" name="item_az" placeholder="az" value="{{ !empty(old("item_az")) ? old("item_az") : $model->item_az }}">
      @if($errors->has('item_az'))
        <span class="help-block">{{ $errors->first('item_az') }}</span>
      @endif
    </div>
  </div>
  <div class="col-sm-6">
    <div class="form-group @if($errors->has('item_grade')) has-error @endif">
      <label>Grade</label>
      <input type="text" class="form-control" name="item_grade" placeholder="grade" value="{{ !empty(old("item_grade")) ? old("item_grade") : $model->item_grade }}">
      @if($errors->has('item_grade'))
        <span class="help-block">{{ $errors->first('item_grade') }}</span>
      @endif
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-6">
    <div class="form-group  @if($errors->has('min_stock')) has-error @endif">
      <label>Minimal Stock</label>
      <input type="number" class="form-control" name="min_stock" placeholder="xx" value="{{ !empty(old("min_stock")) ? old("min_stock") : $model->min_stock }}">
      @if ($errors->has("min_stock"))
        <span class="help-block">{{ $errors->first("min_stock") }}</span>
      @endif
    </div>
  </div>
  <div class="col-sm-6">
    <div class="form-group  @if($errors->has('stock_planning')) has-error @endif">
      <label>Stock Planning</label>
      <input type="number" class="form-control" name="stock_planning" placeholder="xx" value="{{ !empty(old("stock_planning")) ? old("stock_planning") : $model->stock_planning }}">
      @if ($errors->has("stock_planning"))
        <span class="help-block">{{ $errors->first("stock_planning") }}</span>
      @endif
    </div>
  </div>
</div>

<hr>

<div id="vue-dynamic-element">
<div class="form-group">
  <label>Material Options</label>

  <table id="vue-dynamic-element" class="table table-bordered table-hover">
    <thead class="table-header-primary">
      <tr>
          <th>Material</th>
          <th>Color</th>
          <th width="15%">Thick / tebal (mm)</th>
          <th>Weight (Kg)</th>
          <th width="10%">Is Default</th>
          <th width="10%">Is active</th>
          <th width="10%"></th>
        </tr>
    </thead>

    <tbody>
      <tr v-for="(element, i) in elements" :key="element.id">
        <td>
          <input type="hidden" :name="`item_materials[${i}][id]`" v-model="element.id">
          <vue-select2
            :url="`{{ $baseBeApiUrl . '/material' }}`"
            :name="`item_materials[${i}][material_id]`"
            :value="element.material_id"
            :textFormater="(param) => { return param.is_color == 1 ? param.name + ' warna' : param.name + ' polos' }"
            v-on:selected="element.material_id = $event"/>
        </td>
        <td>
          <vue-select2
            :url="`{{ $baseBeApiUrl . '/color' }}`"
            :name="`item_materials[${i}][color_id]`"
            :value="element.color_id"
            v-on:selected="element.color_id = $event"/>
          </span>
        </td>
        <td>
          <input type="text" class="form-control" :name="`item_materials[${i}][thick]`" v-model="element.thick" placeholder="nilai ketebalan">
        </td>
        <td>
          <input type="text" class="form-control" :name="`item_materials[${i}][weight]`" v-model="element.weight" placeholder="est. berat">
        </td>
        <td>
          <select class="form-control " :name="`item_materials[${i}][is_default]`" v-model="element.is_default" @change="onlyOneSetTrue(i, element.is_default)" style="width: 100%;" tabindex="-1">
              <option value="0">No</option>
              <option value="1">Yes</option>
          </select>
        </td>
        <td>
          <select class="form-control " :name="`item_materials[${i}][is_active]`" v-model="element.is_active" style="width: 100%;" tabindex="-1">
              <option value="0">No</option>
              <option value="1" selected>Yes</option>
          </select>
        </td>
        <td>
          <button type="button" class="btn btn-default text-red" @click="removeElement(i)"><i class="fa fa-minus"></i></button>
        </td>
      </tr>

      <tr>
        <th colspan="6">
          @if($errors->has('colors.*'))
            <span class="help-block text-red">* {{ $errors->first('colors.*') }}</span>
          @endif
        </th>
        <th>
          <button type="button" class="btn btn-default text-success" @click="addElement()"><i class="fa fa-plus"></i></button>
        </th>
      </tr>
    </tbody>
  </table>
</div>

<hr>

@if(!empty($itemImages) && $itemImages->count() > 0)
<label for="">Images Uploaded</label>
<div class="form-group multiple-file-wrapper">
  @foreach($itemImages as $keyIU => $imageUploaded)
  <div class="file-uploaded">
    <div class="file-uploaded__preview">
      <img class="image-preview" src="{{ $imageUploaded->image_url }}" width="100%" alt="no image">
    </div>

    <div class="file-uploaded__form-{{ $keyIU }}">
      <div class="form" style="padding: 8px 0px 15px 8px;">
        {{ csrf_field() }}
        <input type="checkbox" name="is_thumbnail" value="1" @if($imageUploaded->is_thumbnail == 1) checked @endif> sebagai thumbnail <br>
        <input type="checkbox" name="is_active" value="1" @if($imageUploaded->is_active == 1) checked @endif> Tampilkan gambar <br>
      </div>

      <button
        type="button"
        class="confirmation-update btn btn-xs btn-block btn-success"
        data-formid="{{ $keyIU }}"
        data-target="{{ url($route . '/image/' . $imageUploaded->id) }}"
        >update</button>
    </div>

    <button
      type="button"
      class="confirmation-delete btn btn-xs btn-danger btn-delete"
      data-target="{{ url($route . '/image/' . $imageUploaded->id) }}"
      data-token={{ csrf_token() }}
      >
      <i class="fa fa-close"></i>
    </button>
  </div>
  @endforeach
</div>

<hr>
@endif

<label for="">New Images</label>
<div class="form-group multiple-file-wrapper">
  <div class="multiple-file btn-add" @click="addElement('images')">
    <i class="fa fa-plus fa-2x"></i>
  </div>

  <div class="multiple-file" v-for="(image, im) in images" :key="image.id">
    <vue-image-form
      :index="image.id"
      :name="`images[${im}]`"
      :image="image.image_url"
      :file="`{{ !empty(old('image.${im}.file')) ? old('image.${im}.file') : null }}`"
      :is-thumbnail="image.is_thumbnail"
      :is-active="image.is_active"
      v-on:change="onlyOneSetTrue(im, $event, 'images', 'is_thumbnail')"
      v-on:delete="removeElement(im, 'images')"
      />
  </div>

</div>
</div> <!-- end vue wrapper -->

<div class="form-group">
  <label>Active</label>
  <select class="form-control " name="is_active" id="" style="width: 100%;" tabindex="-1">
      <option value="1" @if($isActive == 1) selected @endif>Yes</option>
      <option value="0" @if($isActive == 0) selected @endif>No</option>
  </select>
</div>

@section('js')
<script src="{{ asset('vendor/autonumeric/autoNumeric.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/vue.js') }}"></script>
<script>
var hasLengthOptions = "{{ $hasLengthOptions }}";
var elements = <?php echo json_encode($itemMaterials); ?>;

var chargeCustomLengthField = new AutoNumeric($('input[name="charge_custom_length"]')[0], {
  currencySymbol : 'Rp. ',
  emptyInputBehavior: 'zero',
  unformatOnSubmit: true
});

if(hasLengthOptions === '1') $("input[name='length']").prop('readonly', true);

select2AjaxHandler('select[name="item_category_id"]', `{{ $baseBeApiUrl . '/item-category' }}`, '{{ $categoryId }}');

$("#has_length_options").change(function () {
  if($(this).is(":checked")) {
    $("input[name='length']").prop('readonly', true);
    return;
  }

  $("input[name='length']").prop('readonly', false);
})

Vue.component('vue-image-form', {
  template: `<div style="position: relative">
    <div style="height: 150px; overflow: hidden">
      <img :id="previewImage" class="image-preview" src="{{ asset('img/no-image.png') }}" width="100%" alt="no image">
    </div>
    <input type="file" class="form-control" id="" :name="name + '[file]'" ref="image" @change="setImage()">
    <div style="padding: 5px 0px 5px 5px;">
      <input type="checkbox" :name="name + '[is_thumbnail]'" value="1" :checked="isThumbnail" @change="$emit('change', !isThumbnail)"> sebagai thumbnail <br>
      <input type="checkbox" :name="name + '[is_active]'" value="1" :checked="isActive"> Tampilkan gambar <br>
    </div>
    <button type="button" class="btn btn-xs btn-danger btn-delete" @click="$emit('delete')"><i class="fa fa-close"></i></button>
  </div>`,
  props: {
   index: {default: null},
   name: {default: null},
   image: {default: null},
   isThumbnail: {default: null},
   isActive: {default: null}
  },
  data: function () {
    return {
      previewImage: `image-preview-${this.index}`,
      selectedImage: null,
    }
  },
  mounted: function () {
    if(this.image) {
      document.getElementById(this.previewImage).src = this.image;
    }
  },
  methods: {
    setImage () {
      this.selectedImage = this.$refs.image.files[0]
      this.setPreview(this.previewImage)
    },
    setPreview(id){
      var vm = this
      let reader  = new FileReader();

      reader.readAsDataURL(this.selectedImage)
      reader.onloadend = function (e) {
        document.getElementById(id).src = e.target.result;
        vm.fileAsUrl = e.target.result;
      }
    }
  }
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

var app = new Vue({
  el: '#vue-dynamic-element',
  data: {
    elements: elements,
    images: [],
  },
  mounted: function () {
    // check if vue working
    console.log(`${this.$el.id} mounted`)

    if(! this.elements.length) this.addElement()
  },
  methods: {
    _randomNumber () {
      return Math.floor(Math.random() * 10)
    },
    addElement (element = 'elements') {
      if(element === 'elements'){
        this.elements.push({
          id: this._randomNumber(),
          item_id: '',
          thick: 0,
          weight: 0,
          is_default: 0,
          is_active: 1,
          material: {}
        })

        return
      }

      this.images.push({
        id: this._randomNumber(),
        file: null,
        is_thumbnail: 0,
        is_active: true,
      })
    },
    removeElement (index, element = 'elements') {
      if(element == 'elements')
        if(this[element].length == 1) return

      this[element].splice(index, 1)
      console.log('after delete', this[element])
    },
    onlyOneSetTrue (index, value, element='elements', attribut='is_default') {
      if(value == 1) {
        this[element].forEach(function (element, i) {
          element[attribut] = 0
        })
      }

      this[element][index][attribut] = value
    },
    isObjectExist(obj) {
      if(typeof obj == 'undefined') {
        return false
      }

      if(Object.keys(obj).length === 0){
        return false
      }

      return true
    }
  }
})
</script>
@endsection
