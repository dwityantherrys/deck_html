@extends('layouts.admin')

@section('title', 'Account Payable')

@section('content_header')
  <h1> Account Payable</h1>
@stop

@section('css')
  <style media="screen">
  .select2-close-mask{
    z-index: 999999999;
  }
  .select2-dropdown{
    z-index: 999999999;
  }
  </style>
@endsection

@section('content')
  <div class="box box-danger">
    <!-- /.box-header -->
    <div class="box-body">
      <div class="table-responsive">
        {!! $datatable->table() !!}
      </div>
    </div>
    <!-- /.box-body -->
  </div>

@stop

@push('js')
  {!! $datatable->scripts() !!}

  <script type="text/javascript">
  $(document).ready(function() {
    $(document).on("click", ".payable-paid", function() {
      var _token = $(this).data("token");
      var target = $(this).data("target");

      $.confirm({
        title: "Confirmation Update Transaction Paid!",
        content: "Data yang telah diperbarui tidak dapat di kembalikan lagi. " +
        '<form action="" class="formName">' +
        '<div class="form-group">' +
        '<label>Purchase Invoice Number</label>' +
        '<input type="text" placeholder="Purchase Invoice Number" class="purchaseInvoiceNumber form-control" required />' +
        '</div>' +
        `<select class="form-control" name="kode_akun" id="kode_akun" style="width: 100%" required>
        <option></option>
        @foreach ($kode_akun as $akun)
        <option value="{{ $akun->kode_akun }}">{{ $akun->nama_akun }}</option>
        @endforeach
        </select>` +
        '</form>' +
        `<script>$("#kode_akun").select2({
          placeholder: "Pilih Akun",
        })<\/script>`,
        buttons: {
          formSubmit: {
            text: 'confirm',
            btnClass: "btn-success",
            action: function () {
              var purchaseInvoiceNumber = this.$content.find('.purchaseInvoiceNumber').val();
              var kode_akun = this.$content.find('#kode_akun').val();
              if(!purchaseInvoiceNumber){
                $.alert('provide a valid invoice number');
                return false;
              }

              if(!kode_akun){
                $.alert('provide a valid account number');
                return false;
              }

              $.ajax({
                type: "PUT",
                url: target,
                data: {
                  _token: _token,
                  purchase_invoice_number: purchaseInvoiceNumber,
                  kode_akun: kode_akun
                },
                success: function() {
                  $.alert({
                      title: "Update Success",
                      content: "Data berhasil diperbarui.",
                      buttons: {
                          ok: function() { location.reload(); }
                      }
                  });
                },
                error: function(xhr, ajaxOptions, thrownError) {
                  if(xhr.responseJSON.message) {
                    $.alert("Gagal memperbarui data, " + xhr.responseJSON.message);
                    return
                  }

                  $.alert("Gagal memperbarui data.");
                  console.log(xhr, thrownError);
                }
              });
            }
          },
          cancel: function () { },
        },
        onContentReady: function () {
          // bind to events
          var jc = this;
          this.$content.find('form').on('submit', function (e) {
            // if the user submits the form by pressing enter in the field.
            e.preventDefault();
            jc.$$formSubmit.trigger('click'); // reference the button and click it
          });
        }
      });
    });
  })
</script>
@endpush
