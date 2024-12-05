@extends('layouts.admin')

@section('title', 'Bill of Material')

@section('content_header')
<h1> Bill of Material</h1>
@stop

@section('content')
<div class="box box-danger">
  <div class="box-header with-border">
      <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Bill of Material</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    {!! $datatable->table() !!}
  </div>
  <!-- /.box-body -->
</div>

@component('components.modal')
  @slot('title') Edit Bill of Material @endslot

  @slot('form')
    @component($routeView . '._form', [
        'model' => $model,
        'route' => $route,
        'defaultProductionCategory' => $defaultProductionCategory
    ]) @endcomponent
  @endslot
@endcomponent

@stop

@push('js')
{!! $datatable->scripts() !!}

<script type="text/javascript">
$(document).ready(function() {
  @if(request()->print && (session()->has('notif')  && session()->get('notif')['code'] == 'success'))
    window.location.href = "{{ url($route . '/' . request()->print. '/print') }}"
    
    $.alert({
        title: "Print Success",
        content: "tunggu sampai indicator loading pada browser hilang. lalu click ok",
        buttons: {
            ok: function() {
                window.stop();
                window.location.replace("{{ url($route) }}");
            }
        }
    });
  @endif

  @if($errors->any())
    $("#ajax-form").modal('show');
  @endif
} );
</script>
@endpush
