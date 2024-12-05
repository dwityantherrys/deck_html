<div class="modal fade" id="ajax-form" style="display: none;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>

        <h4 class="modal-title">{{ $title }}</h4>
      </div>

      <form id="form-ajax-form" method="post" autocomplete="off">
        <div class="modal-body" style="max-height: 77vh; overflow-x: hidden; overflow-y: auto">
          {{ $form }}
        </div>

        <input type='hidden' name='_token' value='{{ csrf_token() }}'>
        <input type='hidden' name='_method' value='PUT'>
        <input type='hidden' name='id'>

        <div class="modal-footer" id="modal-footer" style="text-align: left">
          <input type="submit" class="btn btn-primary" name="submit" value="Save">

          <button
          type="submit"
          class="btn btn-default text-red"
          name="submit"
          value="save_print"
          data-toggle="tooltip"
          title="Klik Tombol Print untuk mencetak dokumen ini">
          Save & Print
        </button>

        <input
        type="button"
        class="confirmation-print btn btn-default text-red"
        value="Print"
        data-toggle="tooltip"
        title="Klik Tombol Print untuk mencetak dokumen ini">
      </div>
    </form>
  </div>
  <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
