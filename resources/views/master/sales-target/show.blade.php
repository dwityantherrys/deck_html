@extends('adminlte::page')

@section('title', 'Sales Target')

@section('css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
@endsection

@section('content')
  <div class="box box-danger">
    <div class="box-body">
      @component($routeView . '._form', [
        'action' => route("sales-target.update", $model->id),
        'method' => "PUT",
        'data' => $model
      ])
    @endcomponent
  </div>
</div>
@endsection

@section('js')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" charset="utf-8"></script>
  <script type="text/javascript">
  $(document).ready(function() {
    $(".date").datepicker({
      format: "mm-yyyy",
      startView: "months",
      minViewMode: "months"
    });
  })
  </script>
@endsection
