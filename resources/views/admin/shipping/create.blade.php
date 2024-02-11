@extends('admin.layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Shipping Management</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('category.index') }}" class="btn btn-primary">Back</a>
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
            <form action="" method="post" id="shippingForm" name="shippingForm">
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
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                            <option value="rest_of_world">Rest of the World</option>
                                        @endif
                                    </select>
                                    <p></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="">Amount</label>
                                    <input type="text" name="amount" id="amount" class="form-control"
                                        placeholder="Amount">
                                    <p></p>
                                </div>
                            </div>

                            <div class="col-md-4 mt-4">
                                <button type="submit" class="btn btn-primary">Create</button>
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark ml-3">Cancel</a>
                            </div>

                        </div>
                    </div>

                </div>


            </form>

            <div class="card">
                <div class="card-body">
                    <div class="row col-md-12">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Country</th>
                                    <th>Shipping</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($shippingCharges)>0)
                                @foreach ($shippingCharges as $shippingCharge)
                                <tr>
                                    <td>{{ $shippingCharge->id }}</td>
                                    <td>{{ ($shippingCharge->country_id == 'rest_of_world') ? 'Rest of the World' : $shippingCharge->name }}</td>
                                    <td>{{ $shippingCharge->amount }}</td>
                                    <td>
                                        <a href="{{ route('shipping.edit',$shippingCharge->id) }}" class="btn btn-warning btn sm">Edit</a>
                                        <a href="javascript:void(0)" onclick="shippingDelete({{ $shippingCharge->id }})" class="btn btn-danger btn sm">Delete</a>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
                url: '{{ route('shipping.store') }}',
                type: 'post',
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

        //shipping delete
        function shippingDelete(id) {
            var url = '{{ route('shipping.destroy', 'ID') }}'
            var newUrl = url.replace("ID", id);

            if (confirm("Are You Sure Want to Delete")) {
                $.ajax({
                    url: newUrl,
                    type: 'delete',
                    data: {},
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response["status"] == true) {
                            window.location.href = "{{ route('shipping.create') }}";
                        } else {
                            window.location.href = "{{ route('shipping.create') }}";
                        }
                    }
                });
            }
        }
    </script>
@endsection
