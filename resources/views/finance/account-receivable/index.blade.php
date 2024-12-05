@extends('layouts.admin')

@section('title', 'Account Receivable')

@section('content_header')
  <h1> Account Receivable</h1>
@stop

@section('content')
  <div class="box box-danger">
    <div class="box-body">
      {!! $datatable->table() !!}
    </div>
  </div>
  <div class="modal fade" id="payModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form id="form_pay" method="post">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="payModalLabel">Pilih Akun Pembayaran</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <select class="form-control" name="kode_akun" id="kode_akun" style="width: 100%" required>
              <option></option>
              @foreach ($kode_akun as $akun)
                <option value="{{ $akun->kode_akun }}">{{ $akun->nama_akun }}</option>
              @endforeach
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
        </div>
      </form>
    </div>
  </div>
@stop

@push('js')
  {!! $datatable->scripts() !!}

  <script type="text/javascript">
  $(document).ready(function() {
    $("#kode_akun").select2({
      placeholder: "Pilih Akun",
      dropdownParent: $("#payModal"),
    });

    $(document).on("click", ".btn-pay", function() {
      var target = $(this).data("target");
      $.ajax({
        url: target,
        success: function(data){
          console.log(data)
          $("#kode_akun").val(data.sales.payment_bank_channel.kode_akun).trigger("change");
          $("#form_pay").attr("action", target);
          $("#payModal").modal("show");
        }
      })
    });

    $("#form_pay").submit(function (e) {
      e.preventDefault();
      var target = $(this).attr("action");
      var kode_akun = $(this).find('select[name="kode_akun"]').val();
      $.confirm({
        title: "Confirmation Update Transaction Paid!",
        content: "Data yang telah diperbarui tidak dapat di kembalikan lagi.",
        buttons: {
          confirm: {
            btnClass: "btn-success",
            action: function() {
              $.ajax({
                type: "PUT",
                url: target,
                data: {
                  _token: "{{ csrf_token() }}",
                  kode_akun: kode_akun
                },
                success: function() {
                  $("#payModal").modal("hide");
                  $.alert({
                    title: "Update Success",
                    content: "Data berhasil diperbarui.",
                    buttons: {
                      ok: function() { location.reload(); }
                    }
                  });
                },
                error: function(xhr, ajaxOptions, thrownError) {
                  $.alert("Gagal memperbarui data.");
                  console.log(xhr.status, thrownError);
                }
              });
            }
          },
          cancel: function() {}
        }
      });
    })
  });
</script>
@endpush
