@extends('layouts.admin')

@section('title', 'Inventory Adjustment')

@section('content_header')
<h1>Inventory Adjustment :  {{ $model->item_name }}</h1>
@stop

@section('content')
<div class="box box-danger">
    <div class="box-header with-border">
    <a href="{{ url($route . '/' . $model->id) }}"><button type="button" class="btn btn-default text-red pull-right"><i class="fa fa-close"></i> Cancel</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    @if(!empty($model->last_adjustment))
    <div class="callout">
        <h4>Last adjustment</h4>
        <p>at {{ $model->last_adjustment->created_at->format('d/m/Y') }} by {{ $model->last_adjustment->pic->name }}</p>
    </div>
    @endif

    <form id="form" role="form" method="post" action="{{ url($route . '/' . $model->id) }}" autocomplete="off">
        @component($routeView . '._form', [
            'route' => $route,
            'model' => $model,
            'typeInventoryOptions' => $typeInventoryOptions
        ]) @endcomponent 


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
<script>
    $('[data-toggle="tooltip"]').tooltip();
</script>
@endsection