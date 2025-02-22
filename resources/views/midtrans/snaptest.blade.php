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

            <p class="login-box-msg">Test Midtrans SNAP</p>

            <button id="pay-button" class="btn btn-block btn-primary" value="{{ $snapToken }}">checkout</button>

            <p class="login-box-msg fix-bottom">{{ date('d/m/Y') }}</p>
        </div>
        <!-- /.login-box-body -->
    </div><!-- /.login-box -->
@stop

@section('js')
<script type="text/javascript"
            src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('services.midtrans.clientKey') }}"></script> 
<script type="text/javascript">
    var payButton = document.getElementById('pay-button');

    payButton.addEventListener('click', function () {
        console.log(this.value)
        snap.pay(this.value); // store your snap token here
    });
</script>
@endsection


