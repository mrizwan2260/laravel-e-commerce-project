@extends('front.layouts.app')

@section('content')
    <section class="container mt-5">

        @if (Session::has('success'))
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {!! session::get('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <div class="col-md-12 text-center py-2">
            <h1>Thank You!</h1>
            <h3>Your Order Id is {{ $id }}</h3>
        </div>
    </section>
@endsection
