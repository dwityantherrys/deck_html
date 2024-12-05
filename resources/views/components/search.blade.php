<div class="modal fade" id="filter" style="display: none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ $form_action }}" method="{{ $form_method }}">
        @if ($route_method != "GET")
          @csrf
          @if ($route_method != "POST")
            @method($route_method)
          @endif
        @endif
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
          <h4 class="modal-title">Filter Data</h4>
        </div>
        <div class="modal-body">
          @foreach ($form as $key => $value)
            <div class="form-group row">
              <div class="col-md-3">
                <label>{{ $value[3] }}</label>
              </div>
              <div class="col-md-9">
                @if ($value[0] == "input")
                  @if ($value[1] == "daterange")
                    <div class="input-daterange row">
                      <div class="col-md-5 form-group">
                        <div class="input-group date">
                          <span class="input-group-addon"> <i class="fa fa-calendar"></i> </span>
                          <input type="text" placeholder="Pilih Tanggal Awal" class="form-control date-picker" name="{{ str_replace("filter_", "", $key) }}_awal" autocomplete="off" value="{{ request()->query("periode_awal") }}">
                        </div>
                      </div>
                      <div class="col-md-2 form-group">
                        <label>To</label>
                      </div>
                      <div class="col-md-5 form-group">
                        <div class="input-group date">
                          <span class="input-group-addon"> <i class="fa fa-calendar"></i> </span>
                          <input type="text" placeholder="Pilih Tanggal Akhir" class="form-control date-picker" name="{{ str_replace("filter_", "", $key) }}_akhir" autocomplete="off" value="{{ request()->query("periode_akhir") }}">
                        </div>
                      </div>
                    </div>
                  @else
                    <input type="{{ $value[1] }}" name="{{ $key }}" class="{{ $value[2] }}" @isset($value[4]) placeholder="{{ $value[4] }}" @endisset value="{{ request()->query($key) }}">
                  @endif
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
          <button type="submit" class="btn btn-primary">Filter</button>
          @if (count($_GET))
            <a href="{{ $index }}" class="btn btn-default">Clear Filter</a>
          @endif
        </div>
      </form>
    </div>
  </div>
</div>
