@extends('layouts.app')
@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Update SubCategory Name</h4>
                <div class="row">
                    <div class="col-md-12"> <!-- MainContent Col-12 -->


                        <form id="subcategory_update_form" action="javascript:void(0)" method="POST">
                            {{ csrf_field() }}


                            <div class="row" id="contactNumberGroup">
                                <div class="col-md-4 ml-auto">
                                    <div class="form-group">
                                        <label for="domain_update">SubCategory:</label>
                                        <select class="form-control" name="subcategory" id="subcategory"
                                            data-live-search="true">
                                            <option selected value="">Select SubCategory</option>
                                            @foreach ($subcategories as $scategory)
                                                <option value="{{ $scategory->new_u_csubcat2 }}">{{ $scategory->new_u_csubcat2 }}
                                                </option>
                                            @endForeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 ml-auto">
                                    <div class="form-group">
                                        <label for="domain_update">New SubCategory:</label>
                                        <select class="form-control" name="new_subcategory" id="new_subcategory"
                                            data-live-search="true">
                                            <option selected value="">Select SubCategory</option>
                                            @foreach ($subcategories as $scategory)
                                                <option value="{{ $scategory->new_u_csubcat2 }}">{{ $scategory->new_u_csubcat2 }}
                                                </option>
                                            @endForeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button class="btn btn-primary" type="submit" id="submitForm">Submit</button>
                                        <span id="processingTextSubmit" style="display: none;">Processing...</span>
                                    </div>
                                </div>

                            </div>
                        </form>


                    </div> <!-- MainContent Col-12 -->
                </div>
            </div>
        </div>
    </div>
@endsection



@push('scripts')
    <script>
        $(document).ready(function() {
            // Handle radio button change



            $("#subcategory_update_form").validate({
                errorClass: "state-error",
                validClass: "state-success",
                errorElement: "em",
                ignore: [],

                /* @validation rules
                ------------------------------------------ */
                rules: {
                    subcategory: {
                        required: true,
                    },
                    new_subcategory: {
                        required: true,
                    }
                },
                /* @validation error messages
                ---------------------------------------------- */

                messages: {

                },
                submitHandler: function(form) {

                    if (!confirm('Are you sure to Update Subcategory?'))
                        return false;

                    $('#processingTextSubmit').show();
                    $.ajax({
                        url: public_path + '/update-subcategory-names',
                        method: 'post',
                        data: new FormData($("#subcategory_update_form")[0]),
                        dataType: 'json',
                        async: false,
                        cache: false,
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            $('#processingTextSubmit').hide();
                            if (result.success == 1) {

                                Swal.fire({
                                    type: 'success',
                                    title: result.message,
                                    showConfirmButton: true,
                                    // timer: 1500
                                });
                                location.reload();

                            } else {
                                Swal.fire({
                                    type: 'warning',
                                    title: result.message,
                                    showConfirmButton: true,
                                    // timer: 1500
                                });
                                // swal("Error", result.message, "warning");
                            }
                        },
                        error: function(error) {
                            if (error) {
                                var error_status = error.responseText;
                                alert(error_status.message);
                            }
                            $('#processingTextSubmit').hide();
                        }
                    });

                }
            });


        });
 </script>

    <script type="text/javascript">
        $(document).ready(function() {

            $("#subcategory").select2();
            $("#new_subcategory").select2();

        });
    </script>
@endpush
