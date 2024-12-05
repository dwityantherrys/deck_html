@extends('adminlte::master')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/iCheck/square/blue.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/utomodeck.css') }}">
    <style>
        .login-box{ margin: 10% auto; }
        .login-box-body{
            position: relative;
            height: 300px;
        }
        .login-box-body.with-shadow{ box-shadow: 2px 2px 12px #c7c7c7; }
        .login-box-body .login-logo{ margin-bottom: 40px; }
        .login-box-msg.fix-bottom{
            border-top: 1px solid #c7c7c7;
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding-top: 12px;
            text-align: center;
            color: #c7c7c7;
        }
    </style>
    @yield('css')
@stop

@section('adminlte_js')
    <script src="{{ asset('vendor/adminlte/plugins/iCheck/icheck.min.js') }}"></script>
    <script>
        $(function () {
            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });
    </script>
    @yield('js')
@stop