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

            <p class="login-box-msg">Reset Password</p>

            <form class="form" method="post" action="{{ url('/customer/forget-password') }}" autocomplete="off">
                {{ csrf_field() }}
                <input type="hidden" name="id" value="{{ $customer->id }}">
                
                <div class="form-group">
                    <label>Customer</label>
                    <input class="form-control" value="{{ !empty($customer->name) ? $customer->name : $customer->email }}" readonly disabled>
                </div>

                <div class="form-group @if($errors->has('password')) has-error @endif">
                    <label>New Password</label>
                    <input class="form-control" type="password" name="password" placeholder="Enter New Password" minlength="8" required>
                    @if($errors->has('password'))
                        <span class="help-block">{{ $errors->first('password') }}</span>
                    @endif
                </div>

                <div class="form-group @if($errors->has('password_confirmation')) has-error @endif">
                    <label>Re-type New Password</label>
                    <input class="form-control" type="password" name="password_confirmation" placeholder="Retype New Password" minlength="8" required>
                    @if($errors->has('password_confirmation'))
                        <span class="help-block">{{ $errors->first('password_confirmation') }}</span>
                    @endif
                </div>

                <button class="btn btn-sm btn-block btn-danger" type="submit">
                    Submit
                </button>
            </form>

            @if(session()->has('notif'))
            <div 
                class="alert alert-{{ session()->get('notif')['type'] }}"
                style="z-index: 1; position: fixed; top: 35px; right: 35px;"
                role="alert">
                <strong>{{ session()->get('notif')['type'] }}</strong> : {{ session()->get('notif')['message']  }}
            </div>
            @endif

            <p class="login-box-msg fix-bottom">{{ date('d/m/Y') }}</p>
        </div>
        <!-- /.login-box-body -->
    </div><!-- /.login-box -->
@stop

@section('css')
<style>
.login-box-body{ height: 480px; }
</style>
@endsection
