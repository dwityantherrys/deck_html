@extends('layouts.admin')

@section('title', 'New Company')

@section('content_header')
<h1> New Company</h1>
@stop

@section('content')
<div class="box box-danger">
  <form id="form" role="form" method="POST" action="{{ url($route . '/store') }}">
  <div class="box-header with-border">
    <a href="{{ url($route) }}"><button type="button" class="btn btn-default text-red pull-right"><i class="fa fa-close"></i> Cancel</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
        @component($routeView . '._form', [
            'model' => $model
        ]) @endcomponent 

        <input type='hidden' name='_token' value='{{ csrf_token() }}'>   
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
        <input type="submit" class="btn btn-primary btn-block" value="Submit">
    </div>
    </form>
</div>
@stop