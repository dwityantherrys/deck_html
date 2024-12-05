@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Verifikasi Customer Email</div>

                <div class="card-body">
                    
                    @if($message != null)
                    <div class="alert alert-{{ $type }}" role="alert">
                        <strong>{{ $type }}</strong> : {{ $message }}
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
