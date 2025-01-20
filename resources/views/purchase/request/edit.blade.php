@extends('layouts.admin')

@section('title', 'Edit Purchase Request')

@section('content_header')
<h1>Edit Purchase Request: {{ $model->request_number }}</h1>
@stop

@section('content')
<div class="box box-danger">
    <div class="box-header with-border">
        <a href="{{ url($route) }}"><button type="button" class="btn btn-default text-red pull-right"><i
                    class="fa fa-close"></i> Cancel</button></a>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <form id="form" role="form" method="post" action="{{ url($route . '/' . $model->id) }}" autocomplete="off">
            @component($routeView . '._form', [
            'prTypes' => $prTypes,
            'desinationTypes' => $desinationTypes,
            'route' => $route,
            'model' => $model
            ]) @endcomponent


            <input type='hidden' name='_token' value='{{ csrf_token() }}'>
            <input type='hidden' name='_method' value='PUT'>
            <input type='hidden' name='id' value="{{ $model->id }}">

    </div>
    <!-- /.box-body -->
    <div class="box-footer">
        <input type="submit" class="btn btn-primary" value="save">
    </div>
    </form>
</div>
@stop

@section('js')
<script>
$('[data-toggle="tooltip"]').tooltip();
</script>
@endsection