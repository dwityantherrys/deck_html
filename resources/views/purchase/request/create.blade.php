@extends('layouts.admin')

@section('title', 'New Purchase Request')

@section('content_header')
<h1> New Purchase Request</h1>
@stop

@section('content')
<div class="box box-danger">
    <div class="box-header with-border">
    <a href="{{ url($route) }}"><button type="button" class="btn btn-default text-red pull-right"><i class="fa fa-close"></i> Cancel</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <form id="form" role="form" method="post" action="{{ url($route) }}" autocomplete="off">
        @component($routeView . '._form', [
            'prTypes' => $prTypes,
            'desinationTypes' => $desinationTypes,
            'route' => $route,
            'model' => $model
        ]) @endcomponent 

        <input type='hidden' name='_token' value='{{ csrf_token() }}'>      
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
        <input type="submit" class="btn btn-primary" name="submit" value="save">
    </div>
    </form>
</div>
@stop

@section('js')
<script>
    $('[data-toggle="tooltip"]').tooltip();
</script>
@endsection