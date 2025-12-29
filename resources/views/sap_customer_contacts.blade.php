@extends('layouts.app')
@section('content')
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-1">
                    <button type="button" onclick="history.back()" class="btn btn-inverse-dark btn-icon">
                        <i class="ti-arrow-circle-left"></i>
                </div>
                <div class="col-md-9">
                    <h4 class="card-title company-heading">
                        </button> {{ $commonName }}
                    </h4>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-primary"
                        href="{{ url('get-customer-contacts/' . $commonName) }}">
                        <i class="ti-user"></i>
                        Contacts</a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12"> <!-- MainContent Col-12 -->
                    <div class="row cust_data_form visibility-hidden">
                        <div class="col-md-2">
                            <div class="form-group">
                                <select class="form-control" style="width: 100%;" tabindex="-1" aria-hidden="true"
                                    id="branch_count">
                                    <option selected="selected">500</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3"></div>
                        <div class="col-md-6 margin pull-right no-m-top">
                            <div class="input-group">
                                <input type="hidden" class="form-control no-border-right"
                                value="{{ $commonName }}" id="common_name">
                                <input type="text" class="form-control no-border-right"
                                    value="{{ $commonName }}" id="search_user" placeholder="Search...">
                                <div class="input-group-addon">
                                    <i class="fa fa-search sear"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="branch_table" class="table dataTable no-footer">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>DBName</th>
                                            <th>CardCode</th>
                                            <th>Name</th>
                                            <th>FirstName</th>
                                            <th>MiddleName</th>
                                            <th>LastName</th>
                                            <th>Designation</th>
                                            <th>Tel1</th>
                                            <th>Cellular</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Created Date</th>
                                            <th>Created Time</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>

                        </div>
                    </div>
                </div> <!-- MainContent Col-12 -->
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
    $(document).ready(function() {
        var BranchListTable = $('#branch_table').DataTable({
            "dom": '',
            "bServerSide": true,
            "serverSide": true,
            "processing": true,
            "bRetrieve": true,
            "ordering": false,
            "paging": true,
            "ajax": {
                "url": public_path + '/get-customer-contacts-json',
                "type": "GET",
                "data": function(d) {
                    return $.extend({}, d, {
                        'branch_count': $('#branch_count').val() || '',
                        "search_user": $('#search_user').val() || '',
                        "common_name": $('#common_name').val() || '',
                    });
                }
            },
            "columns": [
                {
                    "data": "id",
                    "name": "id",
                    "defaultContent": '-'
                },
                {
                    "data": "db_name",
                    "name": "db_name",
                     "defaultContent": '-'
                },
                {
                    "data": "card_code",
                    "name": "card_code",
                    "defaultContent": '-'
                },
                {
                    "data": "name",
                    "name": "name",
                    "defaultContent": '-'
                },

                {
                    "data": "first_name",
                    "name": "first_name",
                    "defaultContent": '-'
                },

                {
                    "data": "middle_name",
                    "name": "middle_name",
                    "defaultContent": '-'
                },
                {
                    "data": "last_name",
                    "name": "last_name",
                    "defaultContent": '-'
                },
                {
                    "data": "designation",
                    "name": "designation",
                    "defaultContent": '-'
                },
                {
                    "data": "tel1",
                    "name": "tel1",
                    "defaultContent": '-'
                },
                {
                    "data": "cellolar",
                    "name": "cellolar",
                    "defaultContent": '-'
                },
                {
                    "data": "e_mail_l",
                    "name": "e_mail_l",
                    "defaultContent": '-'
                },
                {
                    "data": "active",
                    "name": "active",
                    "defaultContent": '-'
                },
                {
                    "data": "create_date",
                    "name": "create_date",
                    "defaultContent": '-'
                },
                {
                    "data": "create_ts",
                    "name": "create_ts",
                    "defaultContent": '-'
                },

            ],
            "order": [
                [1, "asc"]
            ],
            "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var page = this.fnPagingInfo().iPage;
                var length = this.fnPagingInfo().iLength;
                var index = (page * length + (iDisplayIndex + 1));
                $('td:eq(0)', nRow).html(index);


            }
        });


        $('#search_user').on('keyup', function() {
            BranchListTable.draw();
        });

        $('#branch_count').change(function() {
            BranchListTable.page.len($('#branch_count').val()).draw();
        });


        $("#update_pan_form").validate({
            errorClass: "state-error",
            validClass: "state-success",
            errorElement: "em",
            ignore: [],

            /* @validation rules
            ------------------------------------------ */
            rules: {
                new_pan: {
                    required: true,
                },
            },
            /* @validation error messages
            ---------------------------------------------- */

            messages: {
                new_pan: {
                    required: 'This field is required'
                }

            },
            submitHandler: function(form) {

                if (!confirm('Are you sure to Update?'))
                    return false;

                $.ajax({
                    url: public_path + '/update_customer_pan',
                    method: 'post',
                    data: new FormData($("#update_pan_form")[0]),
                    dataType: 'json',
                    async: false,
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        if (result.success == 1) {
                            // alert("Your Password Updated Successfully.!")
                            Swal.fire({
                                type: 'success',
                                title: result.message,
                                showConfirmButton: true,
                                // timer: 1500
                            });
                            //  location.reload();

                            $('#update_pan').modal('hide');
                            $('#branch_table').DataTable().ajax.reload();

                        } else {
                            swal("Error", result.message, "warning");
                        }
                    },
                    error: function(error) {
                        if (error) {
                            var error_status = error.responseText;
                            alert(error_status.message);
                        }
                    }
                });

            }
        });

    });



</script>
@endpush
