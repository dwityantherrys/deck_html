@extends('layouts.admin')

@section('title', 'Master City')

@section('content_header')
<h1><i class="fa fa-circle-o"></i> city</h1>
@stop

@section('content')
<div class="box box-danger">
    <div class="box-header with-border">
    <a href="{{url('master/cities/cities')}}"><button type="button" class="btn btn-danger pull-right"><i class="fa fa-close"></i> Cancel</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <form id="form" role="form" method="post" action="{{url($url)}}" >
        
    <div class="form-group">
        <label>Province</label>
        <select class="form-control select2 select2-hidden-accessible" name="province_id" style="width: 100%;" tabindex="-1">
          @foreach($provinces as $index => $province)
            <option value="{{$province->id}}" @if(isset($city))  @if($city->province_id == $province->id) selected @endif @endif>{{$province->name}}</option>
          @endforeach
        </select>
      </div>
        
      <div class="form-group">
        <label for="exampleInputEmail1">Name</label>
        <input type="text" class="form-control" id="" name="city" placeholder="Enter city" value="@if(isset($city)) {{$city->name}} @endif">
      </div>
    

      <!-- /.box-body -->
        <input type='hidden' name='_token' value='{{ csrf_token() }}'>
        <input type='hidden' name='_method' value='{{$method}}'>
        <input type='hidden' name='id' value="@if(isset($city)) {{$city->id}} @endif"> <!-- id_prov -->
      
      <div class="box-footer">
        <input type="submit" class="btn btn-primary btn-block" value="Submit">
      </div>
    </form>
 </div>
  <!-- /.box-body -->
</div>
@stop
@push('js')
<script type="text/javascript">
$(document).ready(function() {
    
    $('.select2').select2();
} );
</script>
@endpush
