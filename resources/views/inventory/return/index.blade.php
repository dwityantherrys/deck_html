@extends('layouts.admin')

@section('title', 'Asset Loan')

@section('content_header')
<h1> Asset Return Loan</h1>
@stop

@section('content')
<div class="box box-danger">
  <div class="box-header with-border">
      <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Return Asset Loan</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    {!! $datatable->table() !!}
  </div>
  <!-- /.box-body -->
</div>

@component('components.modal')
  @slot('title') Edit Asset Loan @endslot

  @slot('form')
    @component($routeView . '._form', [
        'model' => $model,
        'route' => $route,
    ]) @endcomponent
  @endslot
@endcomponent

@stop

@push('js')
{!! $datatable->scripts() !!}

<script type="text/javascript">
$(document).ready(function() {
  @if(request()->print)
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
