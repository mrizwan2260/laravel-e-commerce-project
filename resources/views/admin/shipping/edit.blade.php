@extends('admin.layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Shipping Edit</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('shipping.create') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="container-fluid">
            @include('admin.message')
            <form action="" method="put" id="shippingForm" name="shippingForm">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="">Country</label>
                                    <select name="country" id="country" class="form-control">
                                        <option value="" selected disabled>Select a Country</option>
                                        @if (count($countries) > 0)
                                            @foreach ($countries as $country)
                                                <option {{ ($shippingCharges->country_id == $country->id) ? 'selected' : '' }} value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                            <option {{ ($shippingCharges->country_id == 'rest_of_world') ? 'selected' : '' }} value="rest_of_world">Rest of the World</option>
                                        @endif
                                    </select>
                                    <p></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="">Amount</label>
                                    <input value="{{ $shippingCharges->amount }}" type="text" name="amount" id="amount" class="form-control"
                                        placeholder="Amount">
                                    <p></p>
                                </div>
                            </div>

                            <div class="col-md-4 mt-4">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="{{ route('shipping.create') }}" class="btn btn-outline-dark ml-3">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- /.card -->
    </section>
    <!-- /.content -->
@endsection

@section('customJs')
    <script>
        $('#shippingForm').submit(function(event) {
            event.preventDefault();
            var element = $(this);
            $("button[type=submit]").prop('disabled', true);
            $.ajax({
                url: '{{ route('shipping.update',$shippingCharges->id) }}',
                type: 'put',
                data: element.serializeArray(),
                dataType: 'json',
                success: function(response) {
                    $("button[type=submit]").prop('disabled', false);
                    if (response["status"] == true) {

                        window.location.href = "{{ route('shipping.create') }}";

                    } else {

                        var error = response['errors'];

                        if (error['country']) {
                            $("#country").addClass('is-invalid').siblings('p').addClass(
                                    'invalid-feedback')
                                .html(error['country']);
                        } else {
                            $("#country").removeClass('is-invalid').siblings('p').removeClass(
                                'invalid-feedback').html("");
                        }

                        if (error['amount']) {
                            $("#amount").addClass('is-invalid').siblings('p').addClass(
                                    'invalid-feedback')
                                .html(error['amount']);
                        } else {
                            $("#amount").removeClass('is-invalid').siblings('p').removeClass(
                                'invalid-feedback').html("");
                        }
                    }

                },
                error: function(jqXHR, exception) {
                    console.log("someting went wrong");
                }
            })
        });

        //
    </script>
@endsection
