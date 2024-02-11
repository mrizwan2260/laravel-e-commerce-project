@extends('front.layouts.app')

@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('front.index') }}">Home</a></li>
                    <li class="breadcrumb-item">Forgot Password</li>
                </ol>
            </div>
        </div>
    </section>

    <section class=" section-10">
        <div class="container">

            {{-- for success message --}}
            @if (Session::has('success'))
                <div class="col-md-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {!! session::get('success') !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            {{-- for error message --}}
            @if (Session::has('error'))
                <div class="col-md-12">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {!! session::get('error') !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            <div class="login-form">
                <form action="{{ route('fornt.processForgotPassword') }}" method="post">
                    @csrf
                    <h4 class="modal-title">Forgot Password</h4>
                    <div class="form-group">
                        <input type="text"
                            class="form-control @error('email')
                            is-invalid
                        @enderror"
                            placeholder="Email" name="email" value="{{ old('email') }}">
                        @error('email')
                            <p class="invalid-feedback">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group small">
                        <a href="{{ route('account.login') }}" class="forgot-link">Login</a>
                    </div>
                    <input type="submit" class="btn btn-dark btn-block btn-lg" value="submit">
                </form>
                <div class="text-center small">Don't have an account? <a href="{{ route('account.register') }}">Sign up</a>
                </div>
            </div>
        </div>
    </section>
@endsection
