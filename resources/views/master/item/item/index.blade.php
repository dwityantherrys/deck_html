@extends('layouts.admin')

@section('title', 'Item')

@section('content_header')
<h1> Item</h1>
@stop

@section('content')

<div class="box box-danger">
  <div class="box-header with-border">
      <a href="{{ url($route . '/create') }}" ><button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> New Item</button></a>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <!-- Tambahkan div pembungkus untuk membuat scroll horizontal -->
    <div class="table-responsive">
      <table id="raw-table" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>No</th>
            <th>Foto</th>
            <th>Code</th>
            <th>Name</th>
            <th>Description</th>
            <th>Category</th>
            <th>Vendor</th>
            <th>Purchase Price</th>
            <th>Purchase Date</th>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Type</th>
            <th>Jenis Pajak</th>
            <th>Active ?</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($items as $index => $item)
          <tr>
            <td>{{ $index+1 }}</td>
            <td>
              <img src="{{ url('storage/'.$item->product_image) }}" width="100" height="100" alt="image product">
            </td>          
            <td>{{ $item->item_code }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->description }}</td>
            <td>{{ $item->item_category->name }}</td>
            <td>{{ $item->vendor->name }}</td>
            <td>{{ $item->purchase_price }}</td>
            <td>{{ $item->purchase_date }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->unit->name }}</td>
            <td>
    {{ $item->type === 'service' ? 'Surat Perintah Kerja' : $item->type }}
</td>

            <td>
              @if($item->jenis_pajak == '0')
              <small class="label bg-green">None</small>
              @elseif($item->jenis_pajak == '1')
              <small class="label bg-green">11%</small>
              @else
              <small class="label bg-green">11% Included</small>
              @endif
            </td>
            <td>
              @if($item->is_active)
              <small class="label bg-green">Yes</small>
              @else
              <small class="label bg-red">No</small>
              @endif
            </td>
            <td>
              <div class="btn-group">
                <a class="btn btn-default" href="{{ url($route . '/' . $item->id . '/edit') }}"><i class="fa fa-pencil"></i></a>
                <button 
                class="confirmation-delete btn btn-default text-red"
                data-target="{{ url($route . '/' . $item->id) }}"
                data-token={{ csrf_token() }}
                >
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <!-- /.box-body -->
</div>
@stop

@push('js')
<script type="text/javascript">
$(document).ready(function() {
  $('#raw-table').DataTable({
    "scrollX": true // Mengaktifkan horizontal scroll di DataTables
  });
  $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endpush
