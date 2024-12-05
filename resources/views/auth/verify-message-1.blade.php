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

            <p class="login-box-msg">Verifikasi Customer Email</p>

            @if($message != null)
            <div class="alert alert-{{ $type }}" role="alert">
                <strong>{{ $type }}</strong> : {{ $message }}
            </div>
            @endif

            <p class="login-box-msg fix-bottom">{{ date('d/m/Y') }}</p>
        </div>
        <!-- /.login-box-body -->
    </div><!-- /.login-box -->
@stop