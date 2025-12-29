@extends('layouts.app')
<style>
    .form-group .select2-container {
        width: 240px !important;
    }

    #user .form-group .select2-container, #update_user .form-group .select2-container {
        width: 100% !important;
    }
     
</style>
@section('content')
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Sales Employees</h4>
                <div class="row">


                    <div class="col-md-12"> <!-- MainContent Col-12 -->
                        <div class="row cust_data_form">
                            <div class="col-md-1">
                                <div class="form-group">
                                    <select class="form-control" style="width: 100%;" tabindex="-1" aria-hidden="true"
                                        id="branch_count">
                                        <option value="10" selected="selected">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 margin pull-right no-m-top">

                                <div class="input-group">
                                    <input type="text" class="form-control no-border-right" id="search_user"
                                        placeholder="Search...">
                                </div>
                            </div>
                            <div class="col-md-2"></div>
                            <div class="col-md-2"></div>
                            <div class="col-md-2 pull-right">
                                <button class="btn btn-primary" data-toggle="modal" data-target="#user" type="submit">New Sales Emp</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">

                                <div class="loading-spinner" style="display:none;">
                                    <div class="jumping-dots-loader">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="branch_table" class="table  no-footer">
                                        <thead>
                                            <tr>
                                                <th style = "background-color: #296780;color: #fff;"></th>
                                                <th>Employee Name</th>
                                                <th style = "background-color: #296780;color: #fff;">Code</th>
                                                <th style = "background-color: #296780;color: #fff;">Email ID</th>
                                                <th style = "background-color: #296780;color: #fff;">Phone No</th>
                                                <th style = "background-color: #296780;color: #fff;">Memo</th>
                                                <th style = "background-color: #296780;color: #fff;">Actions</th>
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

    <div class="modal fade" id="user">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Employee Details</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form id="add_employee" action="javascript:void(0)" method="POST">
                        {{ csrf_field() }}

                        <div class="form-group">
                            <label for="employee_name" class="required">Employee Name:</label>
                            <input type="text" class="form-control" id="employee_name" name="employee_name">
                        </div>
                        <div class="form-group">
                            <label for="employee_code" class="required">Employee Code:</label>
                            <input type="text" class="form-control" id="employee_code" name="employee_code">
                        </div>
                        <div class="form-group">
                            <label for="phone_num" class="required">Phone Number:</label>
                            <input type="tel" class="form-control" id="phone_num" name="phone_num">
                        </div>
                        <div class="form-group">
                            <label for="email" class="required">Email:</label>
                            <input type="email" class="form-control" id="email" name="employee_email">
                        </div>
                        <div class="form-group">
                            <label for="memo" class="required">Employee Memo:</label>
                            <input type="text" class="form-control" id="memo" name="memo">
                        </div>

                        <button type="submit" class="btn btn-primary" name="button">Submit</button>
                    </form>
                </div>


                

            </div>
        </div>
    </div>

    <!-- Edit -->
    <div class="modal fade" id="update_user">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Update Details</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form id="update_users" action="javascript:void(0)" method="POST">
                        {{ csrf_field() }}
                        <input type="hidden" name="user_hidden" id="user_hidden">
                        <div class="form-group">
                            <input hidden type="text" class="form-control" id="update_id" name="update_id">
                            <label for="update_name" class="required">Employee Name:</label>
                            <input type="text" class="form-control" id="update_name" name="update_name">
                        </div>
                        <div class="form-group">
                            <label for="update_code" class="required">Employee Code:</label>
                            <input type="text" class="form-control" id="update_code" name="update_code">
                        </div>
                        <div class="form-group">
                            <label for="update_num" class="required">Phone Number:</label>
                            <input type="tel" class="form-control" id="update_num" name="update_num">
                        </div>
                        <div class="form-group">
                            <label for="update_email" class="required">Email:</label>
                            <input type="email" class="form-control" id="update_email" name="update_email">
                        </div>
                        <div class="form-group">
                            <label for="update_memo" class="required">Employee Memo:</label>
                            <input type="text" class="form-control" id="update_memo" name="update_memo">
                        </div>


                        <button type="submit" class="btn btn-primary" name="button">Submit</button>
                    </form>
                </div>

                <!-- Modal footer -->
                {{-- <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div> --}}

            </div>
        </div>
    </div>


@endsection

@push('scripts')
       

    <script>
        $(document).ready(function() {

            var buttons = [];

            // Check if the user role is 1
            if (userRole === 1) {
                buttons = [{
                        extend: 'copy',
                        text: 'Copy',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'csv',
                        text: 'CSV',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'excel',
                        text: 'Excel',
                        className: 'btn btn-secondary'
                    }
                ];
            }


            var BranchListTable = $('#branch_table').DataTable({
                "dom": '<"html5buttons"B>tp',
                "bServerSide": true,
                "serverSide": true,
                "buttons": buttons,
                "processing": true,
                "bRetrieve": true,
                "paging": true,
                "ordering": true,
                "ajax": {
                    "url": public_path + '/sales-employees-json',
                    "type": "GET",
                    "data": function(d) {
                        return $.extend({}, d, {
                            'branch_count': $('#branch_count').val() || '',
                            "search_user": $('#search_user').val() || '',
                            "sales_emp_role": $('#sales_emp_role').val() || ''
                        });
                    },
                    "beforeSend": function() {
                        // Show the loading spinner
                        $('.loading-spinner').show();
                    },
                    "complete": function() {
                        // Hide the loading spinner
                        $('.loading-spinner').hide();
                    }
                },
                "columns": [{
                        "data": "id",
                        "name": "id",
                        "defaultContent": '-',
                        "orderable": false, 
                    },
                    {
                        "data": "name",
                        "name": "name",
                        "defaultContent": '-',
                        "orderable": true, 
                    },
                    {
                        "data": "code",
                        "name": "code",
                        "defaultContent": '-',
                        "orderable": false, 
                    },
                    {
                        "data": "email",
                        "name": "email",
                        "defaultContent": '-',
                        "orderable": false, 
                    },
                    {
                        "data": "mobile",
                        "name": "mobile",
                        "defaultContent": '-',
                        "orderable": false, 
                    },
                    {
                        "data": "memo",
                        "name": "memo",
                        "defaultContent": '-',
                        "orderable": false, 
                    },    
                    {
                        "data": null,
                        "name": "actions",
                        "defaultContent": '',
                        "orderable": false,
                        "render": function(data, type, row) {
                            return `<button class="btn btn-primary btn-sm edit-btn" data-id="${row.id}">Edit</button>
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="${row.id}">Delete</button>`;
                        }
                    }
                ],
                "order": [
                    [1, "asc"]
                ],

            });

            $('#branch_table tbody').on('click', 'button.toggle-details', function() {
                var tr = $(this).closest('tr');
                var row = BranchListTable.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });



            $('#search_user').on('keyup', function() {
                BranchListTable.draw();
            });



            $('#sales_emp_role').change(function() {
                BranchListTable.draw();
            });


            $('#branch_count').change(function() {
                BranchListTable.page.len($('#branch_count').val()).draw();
            });
        });

        function appendUserId(user_id)
        {
                    $('#user_hidden').val(user_id);

                        $.ajax({
                                url:public_path + '/get_user/'+user_id,
                                method:'get',
                                dataType:'json',
                                cache: false,
                                processData:false,
                                contentType:false,
                                success:function(result){
                                if(result.success==1){
                                    $('#update_id').val(result.user.id);
                                    $('#update_name').val(result.user.name);
                                    $('#update_code').val(result.user.code);
                                    $('#update_num').val(result.user.mobile);
                                    $('#update_email').val(result.user.email);
                                    $('#update_memo').val(result.user.memo);
                                    

                                }else{
                                    swal("Error", result.message, "warning");
                                }
                                },
                                error: function(error){
                                if(error){
                                        var error_status = error.responseText;
                                        alert(error_status.message) ;
                                }
                                }
                        });
        }

    </script>
    <script>
        

        $(document).ready(function() {
            $("#add_employee").validate({
                errorClass: "state-error",
                validClass: "state-success",
                errorElement: "em",
                ignore: [],

                rules: {
                    employee_name: {
                        required: true
                    },
                    memo: {
                        required: true
                    },
                },

                messages: {
                    employee_name: {
                        required: 'Please add Employee Name'
                    },
                    memo: {
                        required: 'Please add Employee memo'
                    },
                },

                submitHandler: function(form) {
                    $.ajax({
                        url: public_path + '/add_user',
                        method: 'post',
                        data: new FormData($("#add_employee")[0]),
                        dataType: 'json',
                        async: false,
                        cache: false,
                        processData: false,
                        contentType: false,
                        success: function(result) {
                            if (result.success == 1) {
                                Swal.fire({
                                    type: 'success',
                                    title: result.message,
                                    showConfirmButton: true,
                                });
                                location.reload();
                            } else {
                                Swal.fire("Error", result.message, "warning");
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


            $(document).on('click', '.edit-btn', function() {
                var userId = $(this).data('id');
                appendUserId(userId);
                $('#update_user').modal('show');
            });


            $("#update_users").validate({
                errorClass: "state-error",
                validClass: "state-success",
                errorElement: "em",
                ignore: [],

                /* @validation rules
                ------------------------------------------ */
                rules: {
                    // update_password: {
                    //     required: true,
                    // },
                },
                /* @validation error messages
                ---------------------------------------------- */

                messages: {
                    // update_password: {
                    //     required: 'Please Enter Password'
                    // }

                },
                submitHandler: function(form) {

                    $.ajax({
                        url: public_path + '/update_users',
                        method: 'post',
                        data: new FormData($("#update_users")[0]),
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
                                location.reload();
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


        $(document).on('click', '.delete-btn', function() {
                var userId = $(this).data('id');
                deleteUser(userId);
            });
             
       

        function deleteUser(id)
        {  
            Swal.fire({
                title: 'Are you sure?',
                text: "You Want to Delete SalesEmployee! ",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#5947B2',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Change it!'
                }).then((result) => {
                if (result.value == true) {

            $.ajax({
                    url:public_path + '/delete_users/'+id,
                    method:'get',
                    data:new FormData($("#delete_user")[0]), 
                    dataType:'json',
                    async:false,
                    cache: false,
                    processData:false,
                    contentType:false,
                    success:function(result){                
                    if(result.success==1){
                        // alert(result.message);
                        Swal.fire({
                                type: 'warning',
                                title: result.message,
                                showConfirmButton: true,
                                // timer: 1500
                            });
                        $('#branch_table').DataTable().ajax.reload();
                                                        
                    }else{
                        
                        alert(result.message);
                        //swal("Error", result.message, "warning");
                    }   
                    },
                    error: function(error){
                    if(error){
                            var error_status = error.responseText;
                            alert(error_status.message) ;              
                    }
                    }
            });
            }
                })

        }  

    </script>

    

<script type="text/javascript">
        $(document).ready(function() {

            $("#quotation_sales_emps").select2();
            $("#update_manager_id").select2();
            $("#contact").select2();
            $("#category").select2();
            $("#subcategory").select2();
        });
        
    </script>
@endpush
