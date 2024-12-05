@extends('layouts.admin')

@section('title', 'Master District')

@section('content_header')
<h1><i class="fa fa-circle-o"></i> city</h1>
@stop

@section('content')
<div class="box box-danger">
    <div class="box-header with-border">
    <a href="{{url('master/cities/districts')}}"><button type="button" class="btn btn-danger pull-right"><i class="fa fa-close"></i> Cancel</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <form id="form" role="form" method="post" action="{{url($url)}}" >
        
    <div class="form-group">
        <label>Province</label>
        <select class="form-control select2 select2-hidden-accessible" name="province_id" id="province_id" style="width: 100%;" tabindex="-1">
          @foreach($provinces as $index => $province)
            <option value="{{$province->id}}" @if(isset($district))  @if($district->city->province->id == $province->id) selected @endif @endif>{{$province->name}}</option>
          @endforeach
        </select>
      </div>
        
    <div class="form-group">
        <label>City</label>
        <select class="form-control select2 select2-hidden-accessible" name="city_id" id="city_id" style="width: 100%;" tabindex="-1">
          
        </select>
      </div>
        
      <div class="form-group">
        <label for="exampleInputEmail1">District</label>
        <input type="text" class="form-control" id="" name="district" placeholder="Enter city" value="@if(isset($district)) {{$district->name}} @endif">
      </div>
    

      <!-- /.box-body -->
        <input type='hidden' name='_token' value='{{ csrf_token() }}'>
        <input type='hidden' name='_method' value='{{$method}}'>
        <input type='hidden' name='id' value="@if(isset($district)) {{$district->id}} @endif"> <!-- id_prov -->
      
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
    
    var url = "{{url('master/cities/cities/getCity/')}}";
    
    var province_id = $('#province_id').val()
    
    $.ajax({
        url: url+'/' + province_id,
        context: document.body,
        method: 'get',
        success: function (data) {
            $('#city_id').html(data);
        }
    });
    
    
    $('#province_id').change(function () {
                $.ajax({
                    url: url+'/' + $(this).val(),
                    method: 'get',
                    success: function (data) {
                        $('#city_id').html(data);
                    }
                });
            });
    
} );
</script>
@endpush
