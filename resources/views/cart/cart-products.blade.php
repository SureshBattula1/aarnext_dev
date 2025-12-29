@extends('layouts.app')
{{-- <style>
    .form-group .select2-container {
        width: 240px !important;
    }
</style> --}}
@section('content')
    <div class="content-wrapper dashboard-page">
        <div class="card filter-card">
            <div class="card-body">
                <div class="row order-products-filter">
                    <div class="col-md-1">
                        <div class="form-group">
                            <select class="form-control" style="width: 100%;" tabindex="-1" aria-hidden="true"
                                id="branch_count">
                                <option value="12" selected="selected">12</option>
                                <option value="48">48</option>
                                <option value="96">96</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{-- <label for="domain_update">Category:</label> --}}
                            <select class="form-control" name="category" id="category" data-live-search="true">
                                <option selected value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                                @endForeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {{-- <label for="domain_update">SubCategory:</label> --}}
                            <select class="form-control" name="subcategory" id="subcategory"
                                data-live-search="true">
                                <option selected value="">Select SubCategory</option>
                                @foreach ($subcategories as $scategory)
                                    <option value="{{ $scategory->name }}">{{ $scategory->name }}</option>
                                @endForeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="text" class="form-control no-border-right" id="search_item"
                                placeholder="Search...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ url('cart')}}" type="button" class="btn btn-success btn-icon-text">
                            <i class="ti-shopping-cart btn-icon-prepend"></i>
                            View Cart
                          </a>
                    </div>


                </div>
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

                <div id="branch_table" class="product-cards-container row">
                    <!-- Product cards will be dynamically inserted here -->
                </div>

                <table id="hidden_branch_table" class="table d-none">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ItemNo</th>
                            <th>ItemDesc</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>


    </div>
@endsection


@push('scripts')
    <script>
        $(document).ready(function() {
            var buttons = [];
            var BranchListTable = $('#hidden_branch_table').DataTable({
                "dom": '<"html5buttons"B>tp',
                "bServerSide": true,
                "serverSide": true,
                "buttons": buttons,
                "processing": true,
                "bRetrieve": true,
                "paging": true,
                "ordering": false,
                "ajax": {
                    "url": public_path + '/cart-products-json',
                    "type": "GET",
                    "data": function(d) {
                        return $.extend({}, d, {
                            'branch_count': $('#branch_count').val() || '',
                            "search_item": $('#search_item').val() || '',
                            "category": $('#category').val() || '',
                            "subcategory": $('#subcategory').val() || ''
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
                        "name": "id"
                    },
                    {
                        "data": "item_no",
                        "name": "item_no"
                    },
                    {
                        "data": "item_desc",
                        "name": "item_desc"
                    },
                    {
                        "data": "price",
                        "name": "price"
                    }
                ],
                "drawCallback": function(settings) {
                    // Clear the container before appending new cards
                    $('#branch_table').empty();

                    // Append each card to the container
                    var api = this.api();
                    api.rows().every(function(rowIdx, tableLoop, rowLoop) {
                        var data = this.data();
                        var cardHtml = `
                                <div class="col-md-4 grid-margin stretch-card">
							<div class="card item">
								<div class="card-body item-body">
									<div class="d-sm-flex flex-row flex-wrap text-center text-sm-left align-items-center justify-content-center">
										<div class="ms-sm-3 ms-md-0 ms-xl-3 mt-2 mt-sm-0 mt-md-2 mt-xl-0">
											<h6 class="mb-0">${data.item_no}</h6>
											<p class="text-muted mb-1">${data.item_desc}</p>
											<p class="mb-0 text-success fw-bold">${data.price.toFixed(2)}</p>
										</div>
									</div>
                                    <div class="mt-1 d-sm-flex flex-row flex-wrap text-center text-sm-left align-items-center justify-content-center">
										 <input value="${data.id}" type="hidden" id="item-id" name="itemid">
										 <input  class="form-control form-control-sm qty-input" value="1" min="1" type="number" id="item-qty" name="itemqty">
                                        <div class="ms-sm-3 ms-md-0 ms-xl-3 mt-2 mt-sm-0 mt-md-2 mt-xl-0">

                                            <button type="button" class="add-to-cart-btn btn btn-primary btn-rounded btn-sm btn-icon-text">
                                                <i class="ti-plus btn-icon-prepend"></i>Add</button>
										</div>
									</div>
								</div>
							</div>
						</div>`;
                        $('#branch_table').append(cardHtml);
                    });
                },
                "order": [
                    [1, "asc"]
                ]
            });

            $('#search_item').on('keyup', function() {
                BranchListTable.draw();
            });

            $('#category').change(function() {
                BranchListTable.draw();
            });

            $('#subcategory').change(function() {
                BranchListTable.draw();
            });

            $('#branch_count').change(function() {
                BranchListTable.page.len($('#branch_count').val()).draw();
            });
        });
    </script>

<script>
$(document).on('click', '.add-to-cart-btn', function() {
    // Get the closest values for item-id and quantity
    var card = $(this).closest('.card.item');
    var itemId = card.find('#item-id').val();
    var itemQty = card.find('#item-qty').val();

    // Validate the quantity
    if (itemQty <= 0) {
        alert('Please enter a valid quantity.');
        return;
    }

    // Send AJAX request to add the item to the cart
    $.ajax({
        url: public_path + '/add-to-cart',  // Replace with your actual API endpoint
        method: 'POST',
        data: {
            item_id: itemId,
            quantity: itemQty,
            _token: $('meta[name="csrf-token"]').attr('content') // If using Laravel CSRF protection
        },
        success: function(result) {
            if (result.success == 1) {
                // alert(result.message)
                Swal.fire({
                    type: 'success',
                    title: result.message,
                    // showConfirmButton: true,
                    timer: 1000
                });
            } else {
                Swal.fire({
                    type: 'warning',
                    title: result.message,
                    showConfirmButton: true,
                    // timer: 1500
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Something went wrong. Please try again later.',
            });
        }
    });
});

$('#category').change(function() {

    var category = $(this).val();
    if (category) {
        $.ajax({
            url: public_path + '/item-cat-subcats/' + category,
            type: "GET",
            dataType: "json",
            success: function(data) {
                $('#subcategory').empty();
                $('#subcategory').append('<option value="">Select SubCategory</option>');
                $.each(data, function(key, value) {
                    $('#subcategory').append('<option value="' + value
                        .subcategory + '">' +
                        value.subcategory + '</option>');
                });
            }
        });
    } else {
        $('#subcategory').empty();
        $('#subcategory').append('<option value="">Select SubCategory</option>');
    }
});

</script>



<script type="text/javascript">
    $(document).ready(function() {
        $("#category").select2();
        $("#subcategory").select2();
    });
</script>
@endpush
