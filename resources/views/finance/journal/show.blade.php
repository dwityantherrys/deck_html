@extends('layouts.admin')

@section('title', 'Journal')

@section('content_header')
@endsection

@section('content')
  @php($debet = 0)
  @php($kredit = 0)
<div class="box box-danger">
  <div class="box-header">
    <p class="h3">Journal Periode: {{ Carbon\Carbon::parse($date[0])->format("d-m-Y") }} - Periode: {{ Carbon\Carbon::parse($date[1])->format("d-m-Y") }}
      <span><a href="{{ route("journal.index") }}" class="btn btn-primary btn-sm pull-right">Kembali</a></span>
    </p>
  </div>
  <div class="box-body">
    <div class="table-responsive">
      <table class="table dataTable no-footer text-center">
        <thead>
          <tr>
            <th style="width: 10%">No. Transaksi</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Ref</th>
            <th>Debet</th>
            <th>Kredit</th>
          </tr>
        </thead>
        <tbody>
          @if ($gl->count())
            @foreach ($gl as $key => $value)
              <tr>
                <td style="vertical-align: middle;" rowspan="{{ $value->count() }}">{{ $key }}</td>
                <td style="vertical-align: middle;" rowspan="{{ $value->count() }}">{{ Carbon\Carbon::parse($value[0]->created_at)->format("d-m-Y h:i:s") }}</td>
                <td class="pull-left">{{ $value[0]->coa->nama_akun }}</td>
                <td>{{ $value[0]->kode_akun }}</td>
                <td>
                  @if ($value[0]->pos == 1)
                    Rp. {{ number_format($value[0]->nominal, 0, ",", ".") }}
                    @php($debet = $debet + $value[0]->nominal)
                  @endif
                </td>
                <td>
                  @if ($value[0]->pos == 2)
                    Rp. {{ number_format($value[0]->nominal, 0, ",", ".") }}
                    @php($kredit = $kredit + $value[0]->nominal)
                  @endif
                </td>
              </tr>
              @for ($i=1; $i < $value->count(); $i++)
                <tr>
                  <td {{ $i >= 1 ? "style=display:none;" : "" }}></td>
                  <td {{ $i >= 1 ? "style=display:none;" : "" }}></td>
                  <td class="pull-left">{{ $value[$i]->coa->nama_akun }}</td>
                  <td>{{ $value[$i]->kode_akun }}</td>
                  <td>
                    @if ($value[$i]->pos == 1)
                      Rp. {{ number_format($value[$i]->nominal, 0, ",", ".") }}
                      @php($debet = $debet + $value[$i]->nominal)
                    @endif
                  </td>
                  <td>
                    @if ($value[$i]->pos == 2)
                      Rp. {{ number_format($value[$i]->nominal, 0, ",", ".") }}
                      @php($kredit = $kredit + $value[$i]->nominal)
                    @endif
                  </td>
                </tr>
              @endfor
            @endforeach
          @else
            <tr>
              <td colspan="6">Tidak tersedia data jurnal dalam periode ini</td>
            </tr>
          @endif
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-center">JUMLAH</th>
            <th>{{ "Rp. ". number_format($debet, 0, ",", ".") }}</th>
            <th>{{ "Rp. ". number_format($kredit, 0, ",", ".") }}</th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
@endsection
