@extends('front.layouts.app')

@section('content')
<section class="section-5 pt-3 pb-3 mb-3 bg-white">
    <div class="container">
        <div class="light-font">
            <ol class="breadcrumb primary-color mb-0">
                <li class="breadcrumb-item"><a class="white-text" href="#">My Account</a></li>
                <li class="breadcrumb-item">Settings</li>
            </ol>
        </div>
    </div>
</section>

<section class=" section-11 ">
    <div class="container  mt-5">
        <div class="row">
            <div class="col-md-12">
                @include('admin.message')
            </div>
            <div class="col-md-3">
                @include('front.common.sidebar')
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h2 class="h5 mb-0 pt-2 pb-2">Wish List</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">

                                @if (count($wishlists)>0)
                                    @foreach ($wishlists as $wishlist)
                                    <div class="d-sm-flex justify-content-between mt-lg-4 mb-4 pb-3 pb-sm-2 border-bottom">
                                        <div class="d-block d-sm-flex align-items-start text-center text-sm-start">
                                            {{-- <a class="d-block flex-shrink-0 mx-auto me-sm-4" href="#" style="width: 10rem;"><img src="images/product-1.jpg" alt="Product"></a> --}}
                                            <a style="width: 5rem;" href="{{ route('front.product', $wishlist->product->slug) }}" class="product-img">
                                                @php
                                                    $productImage = getProductImage($wishlist->product_id);
                                                @endphp
                                                @if ($productImage != '')
                                                <img src="{{ asset('uploads/product/large/' . $productImage->image) }}" alt="">
                                                @else
                                                <img src="{{ asset('admin-assets/img/no-image.png') }}"
                                                alt="">
                                                @endif
                                                </a>

                                            <div class="pt-2">
                                                <h5 class="product-title fs-base mb-2"><a href="{{ route('front.product',$wishlist->product->slug) }}">{{ $wishlist->product->title }}</a></h5>
                                                <div class="fs-lg text-accent pt-2">
                                                    <span class="h5"><strong>{{ $wishlist->product->price }}</strong></span>
                                                        @if ($wishlist->product->compare_price > 0)
                                                            <span
                                                                class="h6 text-underline"><del>{{ $wishlist->product->compare_price }}</del></span>
                                                        @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pt-2 ps-sm-3 mx-auto mx-sm-0 text-center">
                                            <button onclick="removeProduct({{ $wishlist->product_id }})" class="btn btn-outline-danger btn-sm" type="button"><i class="fas fa-trash-alt me-2"></i>Remove</button>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <h5 class="text-danger text-center">Not Found Product in WishList</h5>
                                @endif

                        </div>
                    </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('customJs')
    <script>
        function removeProduct(id){
            $.ajax({
                url: "{{ route('account.removeProductfromWishlist') }}",
                type: "post",
                data: {id:id},
                dataType: "json",
                success: function (response) {
                    if (response.status == true) {
                        window.location.href="{{ route('account.wishlist') }}";
                    }
                }
            });
        }
    </script>
@endsection
