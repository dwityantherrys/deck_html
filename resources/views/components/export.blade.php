<div class="modal fade" id="export" style="display: none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ $form_action }}" method="GET">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
          <h4 class="modal-title">Export Data</h4>
        </div>
        <div class="modal-body">
          @foreach ($form as $key => $value)
            <div class="form-group row">
              <div class="col-md-3">
                <label>{{ $value[3] }}</label>
              </div>
              <div class="col-md-9">
                @if ($value[0] == "input")
                  <input type="{{ $value[1] }}" name="{{ $key }}" class="{{ $value[2] }}" @isset($value[4]) placeholder="{{ $value[4] }}" @endisset value="{{ request()->query($key) }}">
                @elseif ($value[0] == "select")
                  <select class="{{ $value[2] }}" name="{{ $key }}">
                    <option value="" disabled selected hidden>{{ $value[4] }}</option>
                    @foreach ($value[1] as $option_key => $option)
                      <option value="{{ $option_key }}" {{ isset($_GET[$key]) && $option_key == request()->query($key) ? "selected" : null }}>{{ $option }}</option>
                    @endforeach
                  </select>
                @endif
              </div>
            </div>
          @endforeach
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Export</button>
        </div>
      </form>
    </div>
  </div>
</div>
