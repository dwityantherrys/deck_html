@extends('adminlte::page')

@php
use Carbon\Carbon;
@endphp

@section('title', 'Balance Sheet')

@section('content_header')
  <div class="form-group row">
    <div class="col-md-6">
      <span class="h3">Balance Sheet</b></span>
    </div>
    <div class="col-md-6 text-right">
      <a href="{{ route("balance-sheet.index") }}" class="btn btn-primary">Kembali</a>
    </div>
  </div>
@endsection

@section('content')
  <div class="box box-danger">
    <div class="box-body">
      <div class="row">
        <div class="col-md-12 text-center">
          <p class="h3">JURAGAN ATAP</p>
          <p class="h4">LAPORAN NERACA</p>
          <p class="h4"><b>Periode {{ Carbon::parse(request()->get("periode_awal"))->format("d-m-Y") }} - {{ Carbon::parse(request()->get("periode_akhir"))->format("d-m-Y") }}</b></p>
        </div>
      </div>
      <div class="col-12">
        <table class="table table-striped table-bordered">
          <tbody>
            <tr>
              <td colspan="3" style="text-align: center;"><b>KETERANGAN</b></td>
              <td style="text-align: center;"><b>JUMLAH</b></td>
            </tr>
            @php($grand_total = 0)
            @foreach ($data as $key => $value)
              <tr>
                <td colspan="3"><b>{{ $key }}</b></td>
                <td></td>
              </tr>
              @foreach ($value as $key2 => $value2)
                <tr>
                  <td colspan="3"><span style="padding-left: 20px;"><b>{{ $key2 }}:</b></span></td>
                  <td></td>
                </tr>
                @php($sub_total = 0)
                @foreach ($value2 as $key3 => $value3)
                  @foreach ($value3 as $key4 => $value4)
                    <tr>
                      <td><span style="padding-left: 30px;">{{ $value4["nama_akun"] }}</span></td>
                      <td>
                        @if ($key4 == "kurang")
                          @if ($value4["total"] >= 0)
                            ( Rp. {{ number_format($value4["total"], 0, ",", ".") }} )
                          @else
                            Rp. {{ number_format($value4["total"], 0, ",", ".") }}
                          @endif
                          @php($sub_total -= $value4["total"])
                        @elseif ($key4 == "tambah")
                          @if ($value4["total"] >= 0)
                            Rp. {{ number_format($value4["total"], 0, ",", ".") }}
                          @else
                            ( Rp. {{ number_format(-$value4["total"], 0, ",", ".") }} )
                          @endif
                          @php($sub_total += $value4["total"])
                        @endif
                      </td>
                      <td></td>
                      <td></td>
                    </tr>
                  @endforeach
                @endforeach
                <tr>
                  <td colspan="2"><span style="padding-left: 20px;"><b>{{ $key2 }}</b></span></td>
                  <td>
                    @if ($sub_total >= 0)
                      Rp. {{ number_format($sub_total, 0, ",", ".") }}
                    @else
                      ( Rp. {{ number_format(-$sub_total, 0, ",", ".") }} )
                    @endif
                  </td>
                  <td></td>
                </tr>
                @php($grand_total += $sub_total)
                @php($sub_total = 0)
              @endforeach
            @endforeach
            <tr>
              <td colspan="3" style="text-align: center;"><b>JUMLAH</b></td>
              <td style="text-align: center;"><b>Rp. {{ number_format($grand_total, 0, ",", ".") }}</b></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
