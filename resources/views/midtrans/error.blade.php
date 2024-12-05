@extends('layouts.guest')

@section('body')
    <div class="login-box">
        <!-- baground red -->
        <div style="position: absolute; top: 0; left: 0; z-index: -1; width: 100%; height: 250px; background: #e36159;"></div>

        <!-- /.login-logo -->
        <div class="login-box-body with-shadow">
            <div class="login-logo">
                <a href="{{ url(config('adminlte.dashboard_url', 'home')) }}">{!! config('adminlte.logo', '<b>Admin</b>LTE') !!}</a>
            </div>

            <p class="login-box-msg">Error Payment Process</p>

            <div class="alert alert-danger" role="alert">
                <strong>Oops</strong> terjadi kesalahan, sistem tidak dapat memproses pembayaran
            </div>

            <p class="login-box-msg fix-bottom">{{ date('d/m/Y H:i:s') }}</p>
        </div>
        <!-- /.login-box-body -->
    </div><!-- /.login-box -->
@stop


