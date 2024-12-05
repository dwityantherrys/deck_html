<?php $isReadOnly = $model->name === 'super_admin' ? 'readonly' : ''; ?>
<?php $isActive = !empty(old('is_active')) ? old('is_active') : ($model->is_active ? $model->is_active : 1); ?>
<?php $checkedmenus = !empty(old('menus')) ? old('menus') : ($model->menus ? $model->menus()->get()->pluck('menu_key')->toArray() : []); ?>

<div class="form-group @if($errors->has('display_name') || $errors->has('name')) has-error @endif">
  <label for="">Display Name</label>
  <input type="text" class="form-control" name="display_name" placeholder="display name" value="{{ !empty(old('display_name')) ? old('display_name') : $model->display_name }}" {{ $isReadOnly }}>
  @if($errors->has('display_name') || $errors->first('name'))
    <span class="help-block">{{ $errors->first('display_name') | $errors->first('name') }}</span>
  @endif
</div>

<div class="form-group @if($errors->has('description')) has-error @endif">
  <label for="">Description</label>
  <textarea class="form-control" rows="3" name="description" placeholder="description" {{ $isReadOnly }}>{{ !empty(old('description')) ? old('description') : $model->description }}</textarea>
  @if($errors->has('description'))
    <span class="help-block">{{ $errors->first('description') }}</span>
  @endif
</div>
  
<div class="form-group">
  <label>Active</label>

  <select class="form-control " name="is_active" id="" style="width: 100%;" tabindex="-1" {{ $isReadOnly ? 'disabled' : '' }}>
      <option value="1" @if($isActive == 1) selected @endif>Yes</option>
      <option value="0" @if($isActive == 0) selected @endif>No</option>
  </select>
  @if($isReadOnly)
  <input type="hidden" name="is_active" value="{{ $isActive }}">
  @endif
</div>

<hr>

<div class="form-group">
  <label style="margin-bottom: 15px;">Menus</label>
  <?php $menuHeaders = collect($menus)->keys(); ?>
  <div class="row">
  @foreach($menuHeaders->chunk(4) as $menuChunk)
    <div class="col-md-6">
    @foreach($menuChunk as $menuHeader)
        <ul class="list-group">
          <li class="list-group-item" style="background: #d3d9e1">
            <b>{{ str_replace("_", " ", $menuHeader) }}</b>
            <input class="toggleCheck pull-right" type="checkbox" name="menus[]" value="{{ $menuHeader }}" @if(is_numeric(array_search($menuHeader, $checkedmenus))) checked @endif>
          </li>
          @foreach($menus[$menuHeader] as $menu)
          @php $value = isset($menu['submenu']) ? Str::slug($menu['text'], '_') : $menu['model']; @endphp
          <li class="list-group-item" style="background: #ecf0f5">
            {{ $menu['text'] }}
            <input 
              class="toggleCheck pull-right" 
              type="checkbox" 
              name="menus[]" 
              value="{{ $value }}"
              data-parent="{{ $menuHeader }}"
              @if(is_numeric(array_search($value, $checkedmenus))) checked @endif>
          </li>
          @if (isset($menu['submenu']))
            @foreach($menu['submenu'] as $submenu)
            <li class="list-group-item">
              {{ $submenu['text'] }}
              <input 
                class="toggleCheck pull-right" 
                type="checkbox" 
                name="menus[]" 
                value="{{ $submenu['model'] }}"
                data-parent="{{ $value }}"
                @if(is_numeric(array_search($submenu['model'], $checkedmenus))) checked @endif>
            </li>
            @endforeach
          @endif
          @endforeach
        </ul>
    @endforeach
    </div>
  @endforeach
  </div>
</div>

@section('js')
<script>
var recursiveCheckedParent = function (hasParent, isChecked) {
  if(!hasParent) return;

  var parent = $(`input[value='${hasParent}']`);
  var parentChilds = $(`[data-parent='${hasParent}']`);
  var parentChildSize = parentChilds.length;
  
  var parentChildChecked = 0;
  
  $.each(parentChilds, function(index, child) {
    if(child.checked) parentChildChecked++;
  });
  
  if(parentChildSize === parentChildChecked) parent.prop('checked', isChecked);
  if(!isChecked) parent.prop('checked', isChecked);

  recursiveCheckedParent($(`input[value='${hasParent}']`).data('parent'), isChecked);
}
var recursiveCheckedChilds = function (hasChilds, isChecked) {
  if(!hasChilds) return

  $.each(hasChilds, function(index, child) {
    child.checked = isChecked
    recursiveCheckedChilds($(`[data-parent='${child.value}']`), isChecked);
  });
}

$(document).ready(function () {
  $('.toggleCheck').change(function() {
    var hasParent = $(this).data('parent')
    var hasChilds = $(`[data-parent='${$(this).val()}']`)

    if ($(this).is(':checked')) {
      // if has child, update child to checked
      recursiveCheckedChilds(hasChilds, true)
      // if has parent, update parent to checked, if all parent child checked
      recursiveCheckedParent(hasParent, true)
      return
    }
   
    recursiveCheckedChilds(hasChilds, false)
    // check is has parent
    recursiveCheckedParent(hasParent, false)
  });
});
</script>
@endsection