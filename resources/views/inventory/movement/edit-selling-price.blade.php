@extends('layouts.admin')

@section('title', 'Inventory Update Selling Price')

@section('content_header')
<h1>Inventory Update Selling Price :  {{ $model->item_name }}</h1>
@stop

@section('content')
<div class="box box-danger">
    <div class="box-header with-border">
    <a href="{{ url($route . '/' . $model->id) }}"><button type="button" class="btn btn-default text-red pull-right"><i class="fa fa-close"></i> Cancel</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    @if(!empty($model->updated_at))
    <div class="callout">
        <h4>Last Update</h4>
        <p><i class="fa fa-calendar" style="margin-right: 5px;"></i> {{ $model->updated_at->format('d/m/Y') }}</p>
    </div>
    @endif

    <form id="form" role="form" method="post" action="{{ url($route . '/' . $model->id . '/update-selling-price') }}" autocomplete="off">
        <div class="form-group">
            <label for="">Item / Material</label>
            <input type="text" class="form-control" value="{{ $model->item_name }}" readonly>
        </div>
        
        <div class="form-group @if($errors->has('current_selling_price')) has-error @endif">
            <label>Current selling price</label>
            <input type="text" class="form-control" name="current_selling_price" value="{{ $model->selling_price }}" readonly>
            @if($errors->has('current_selling_price'))
                <span class="help-block">{{ $errors->first('current_selling_price') }}</span>
            @endif
        </div>

        <div class="form-group @if($errors->has('selling_price')) has-error @endif">
            <label>Update selling price</label>
            <input type="text" class="form-control" name="selling_price" value="{{ !empty(old('selling_price')) ? old('selling_price') : $model->selling_price }}">
            @if($errors->has('selling_price'))
                <span class="help-block">{{ $errors->first('selling_price') }}</span>
            @endif
        </div>

        <input type='hidden' name='_token' value='{{ csrf_token() }}'>
        <input type='hidden' name='_method' value='PUT'>
      
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
        <input type="submit" class="btn btn-primary btn-block" value="save">
    </div>
    </form>
</div>
@stop

@section('js')
<script src="{{ asset('vendor/autonumeric/autoNumeric.min.js') }}" type="text/javascript"></script>
<script>
formatOptions = {
  currencySymbol : 'Rp. ',
  emptyInputBehavior: 'zero',
  unformatOnSubmit: true
}

new AutoNumeric($('input[name="current_selling_price"]')[0], formatOptions);
new AutoNumeric($('input[name="selling_price"]')[0], formatOptions);
</script>
@endsection