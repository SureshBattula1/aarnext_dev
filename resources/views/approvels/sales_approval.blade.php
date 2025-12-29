@extends('layouts.app')

<style>
/* Main Button Styling */
.approval-btn {
    font-weight: bold;
    padding: 12px 28px;
    border-radius: 50px;
    font-size: 1.1rem;
    transition: all 0.3s ease-in-out;
}
.approval-btn:hover {
    background-color: #f7b600;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(247, 182, 0, 0.3);
}

/* Modal Styling */
.approval-modal {
    border-radius: 16px;
    overflow: hidden;
    animation: fadeInScale 0.3s ease-in-out;
}
.approval-modal .modal-header {
    border-bottom: none;
    padding: 16px 24px;
}
.approval-modal .modal-body {
    padding: 20px 30px;
}
.approval-modal .modal-footer {
    border-top: none;
    padding: 16px 24px;
}

/* Card Styling */
.card {
    border-radius: 16px;
    box-shadow: 0 0 10px rgba(0, 0, 0 , 0.1);
    padding: 20px;
    margin: 20px;
    background-color: #fff;
    min-height: 600px !important;
}
.card-header {
    background-color: #333;
    color: #fff;
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}
.requestBtn {
    color: #333;
    padding: 10px 20px;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    margin: 50px;
}

/* Select2 Styling */
.select2-container--default .select2-selection--single {
    height: 48px;
    border-radius: 12px;
    border: 1px solid #ddd;
    padding: 8px 12px;
    transition: all 0.2s ease-in-out;
}
.select2-container--default .select2-selection--single:hover,
.select2-container--default .select2-selection--single:focus {
    border-color: #f7b600;
    box-shadow: 0 0 0 0.2rem rgba(247, 182, 0, 0.25);
}
.select2-selection__rendered {
    font-size: 1rem;
    line-height: 30px;
    color: #333;
}
.select2-container .select2-dropdown {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}
.select2-search--dropdown .select2-search__field {
    border-radius: 8px;
    padding: 8px 10px;
    border: 1px solid #ddd;
}
.select2-results__option--highlighted {
    background-color: #f7b600 !important;
    color: #fff !important;
}
.card-title{
    font-size: 1rem;
    margin: 25px;
}
/* Animation */
@keyframes fadeInScale {
    0% { opacity: 0; transform: scale(0.95); }
    100% { opacity: 1; transform: scale(1); }
}
</style>

@section('content')
<div class="container">

    @php
        $userRole = Auth::user()->role;
    @endphp
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h4 class="card-title">Sales Order Approval List</h4>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>#</th>
                <th>Invoice No</th>
                <th>Customer ID</th>
                <th>Requested Date</th>
                <th>Remarks</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="sales-list-body">
            <tr>
                <td colspan="6" class="text-center">Loading...</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () { 
        loadSalesRequests();
    });
    
    function loadSalesRequests() {
        $.ajax({
            url: '{{ url("sale_order_request_json") }}', // ✅ fixed
            method: 'GET',
            success: function (response) {
                if (response.status === 200) {
                    let rows = '';
                    if (response.salesDetails.length > 0) {
                        $.each(response.salesDetails, function (index, order) {
                            rows += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${order.sales_invoice_no}</td>
                                    <td>${order.customer_id}</td>
                                    <td>${order.user_code ?? '-'}</td>
                                    <td>
                                        <textarea class="form-control remark-input" 
                                            placeholder="Enter remarks" 
                                            id="remark-${order.id}"></textarea>
                                    </td>
                                    <td>
                                        <button class="btn btn-success btn-sm approval-btn" 
                                            onclick="updateSalesStatus(${order.id}, 'accept')">Accept</button>
                                        <button class="btn btn-danger btn-sm approval-btn" 
                                            onclick="updateSalesStatus(${order.id}, 'reject')">Reject</button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        rows = `<tr><td colspan="6" class="text-center">No pending sales approvals.</td></tr>`;
                    }
                    $('#sales-list-body').html(rows);
                } else {
                    $('#sales-list-body').html(`<tr><td colspan="6" class="text-center">Error loading data.</td></tr>`);
                }
            },
            error: function () {
                $('#sales-list-body').html(`<tr><td colspan="6" class="text-center">Server error.</td></tr>`);
            }
        });
    }
    
    function updateSalesStatus(id, action) {
        let remark = $('#remark-' + id).val().trim();
        $.ajax({
            url: '{{ url("approve-sales-request") }}/' + id + '/' + action, // ✅ fixed
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: { remarks: remark },
            success: function (data) {
                if (data.success) {
                    alert(data.message);
                    loadSalesRequests(); // reload table only
                } else {
                    alert('Error: ' + data.message);
                }
            },
            error: function () {
                alert('Server error.');
            }
        });
    }    
</script>
@endpush
