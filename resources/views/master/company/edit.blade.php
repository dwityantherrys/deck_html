@extends('layouts.admin')

@section('title', 'Edit Company')

@section('content_header')
<h1> Edit Company:  {{ $model->name }}</h1>
@stop

@section('content')
<div class="box box-danger">
    <div class="box-header with-border">
    <a href="{{ url($route) }}"><button type="button" class="btn btn-default text-red pull-right"><i class="fa fa-close"></i> Cancel</button></a>
  </div>
  <!-- /.box-header -->
  <form id="form" role="form" method="POST" action="{{ url($route . '/' . $model->id) }}">
  <div class="box-body">
        @component($routeView . '._form', [
            'model' => $model
        ]) @endcomponent 


        <input type='hidden' name='_token' value='{{ csrf_token() }}'>
        <input type='hidden' name='_method' value='PUT'>

        <input type='hidden' name='id' value="{{ $model->id }}">
      
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
        <input type="submit" class="btn btn-primary btn-block" value="Submit">
    </div>
    </form>
</div>
@stop