@extends('layouts.admin')

@section('title', 'Review')

@section('content_header')
<h1> Customer Review Item : {{ $model->name }}</h1>
@stop

@section('content')
<div class="box">
  <div class="box-body">
    <div class="row">
      <div class="col-md-4">
        <?php $initialActiveSlide = 0; ?>
        <div id="carousel-item-image" class="carousel slide" data-ride="carousel">
          <ol class="carousel-indicators">
            @foreach($model->images as $key => $image)
            <li data-target="#carousel-item-image" data-slide-to="{{ $key }}" @if($initialActiveSlide === $key) class="active" @endif></li>
            @endforeach
          </ol>
          <div class="carousel-inner">
            @foreach($model->images as $key => $image)
            <div class="item @if($initialActiveSlide === $key) active @endif">
              <img src="{{ $image->image_url }}" alt="image {{ $image->id }}" width="100%">
              <!-- <div class="carousel-caption"> First Slide </div> -->
            </div>
            @endforeach
          </div>
          <a class="left carousel-control" href="#carousel-item-image" data-slide="prev">
            <span class="fa fa-angle-left"></span>
          </a>
          <a class="right carousel-control" href="#carousel-item-image" data-slide="next">
            <span class="fa fa-angle-right"></span>
          </a>
        </div>      
      </div>
      
      <div class="col-md-8">
        <small class="label bg-green">{{ $model->item_category->name }}</small>
        <h2 class="title">{{ $model->name }}</h2>

        <div class="row">
          <div class="col-md-3 border-right">
            <label for="">Avg. Rate</label>
            <div style="display: flex; align-items: center">
                <?php $starImage = $model->review_rate > 0 ? 'filled' : 'null'; ?>
                <div style="margin-right: 5px">{{ $model->review_rate }}</div>
                <img src="{{ asset('img/stars-'. $starImage .'.png') }}" width="15px" height="15px"> 
            </div>
          </div>
          <div class="col-md-3">
            <label for="">Total Review</label>
            <div>{{ $model->review_total }}</div>
          </div>
        </div>

        <label style="margin-top: 30px;" for="">Description</label>
        <p>{{ $model->description }}</p>

        <table class="table table-bordered table-striped">
          <tbody>
            <tr>
              <td><label for="">Height</label></td>
              <td>{{ $model->height }}</td>
              <td><label for="">Width</label></td>
              <td>{{ $model->width }}</td>
            </tr>
          </tbody>
        </table>
        
        <label style="margin-top: 30px;" for="">Material</label>
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Material</th>
              <th>Color</th>
              <th>Thick / Tebal (mm)</th>
              <th>Weight (Kg)</th>
            </tr>
          </thead>

          <tbody>
            @foreach($model->item_materials as $itemMaterial)
            <tr>
              <td>{{ $itemMaterial->material->name }}</td>
              <td>{{ $itemMaterial->color->name }}</td>
              <td>{{ $itemMaterial->thick }}</td>
              <td>{{ $itemMaterial->weight }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<div class="nav-tabs-custom">
	<ul class="nav nav-tabs">
    <li class="active"><a href="#reviews" data-toggle="tab">Reviews ({{ $model->review_total }})</a></li>
  </ul>

	<div class="tab-content">
		<div class="active tab-pane" id="reviews">
      {!! $datatable->table() !!}
    </div>
  </div>
</div>
@stop


@section('css')
<style>
  .title{
    padding-bottom: 5px;
    margin: 5px 0px 10px; 
    font-weight: 600;
    border-bottom: 1px solid #f4f4f4;
  }
</style>
@stop

@push('js')
{!! $datatable->scripts() !!}
</script>
@endpush