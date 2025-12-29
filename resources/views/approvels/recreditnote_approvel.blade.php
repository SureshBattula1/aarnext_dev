@extends('layouts.app')

<style>
/* Layout */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px;
}

/* Card */
.card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #ffffff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    padding: 24px;
}

.card-title { 
    font-size: 1.4rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 20px;
    border-bottom: 2px solid #f3f4f6;
    padding-bottom: 8px;
}

/* Button */
.btn {
    border-radius: 8px !important;
    padding: 10px 20px;
    font-weight: 500;
}

.btn-success {
    background: #16a34a;
    border: none;
}
.btn-danger {
    background: #dc2626;
    border: none;
}
.btn-warning {
    background: #f59e0b;
    border: none;
    color: #fff;
}

/* Modal */
.modal-content {
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}
.modal-header {
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}
.modal-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #111827;
}
.modal-body label {
    font-weight: 500;
    color: #374151;
}
.modal-footer {
    border-top: 1px solid #e5e7eb;
}

/* Inputs */
.form-control, .select2-selection--single {
    border-radius: 8px !important;
    border: 1px solid #d1d5db;
    height: 44px !important;
    font-size: 0.95rem;
}
.form-control:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

/* Table */
.table {
    border-collapse: separate;
    border-spacing: 0 8px;
    width: 100%;
}
.table thead th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
    padding: 12px;
    border: none;
}
.table tbody tr {
    background: #fff;
    border: 1px solid #e5e7eb;
}
.table tbody td {
    padding: 12px;
    border: none;
}
.table tbody tr td:first-child {
    border-left: 4px solid #3b82f6;
}

/* Alerts */
.alert {
    border-radius: 8px;
    font-size: 0.95rem;
    padding: 12px 16px;
}
.alert-success {
    background: #ecfdf5;
    color: #065f46;
}
.alert-danger {
    background: #fef2f2;
    color: #991b1b;
}
.form-control{
    max-width: 400px !important; 
}
/* Only for Customer dropdown */
#customer_id + .select2-container{
    min-width: 300px !important; /* Your required width */
}

#customer_id + .select2-container .select2-selection--single {
    height: 38px; /* match bootstrap form-control height */
    display: flex;
    align-items: center;
    padding-left: 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}

/* Fix arrow alignment */
#customer_id + .select2-container .select2-selection__arrow {
    height: 100%;
    right: 10px;
}
</style>

@section('content')
@php
  $userRole = Auth::user()->role;
@endphp

@if ($userRole != 5)
<div class="container">
    <div class="card">
        <h4 class="card-title">RECREDIT CREDIT APPROVAL LIST</h4>

        <div class="row justify-content-center">
            <div class="col-lg-6 requestBtn">
                <div class="text-end mb-4">
                    <button type="button" class="btn btn-warning approval-btn shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#creditNoteModal">
                        <i class="bi bi-cash-coin me-2"></i> Credit Note Approval
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="creditNoteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form id="approvalForm" method="POST" class="w-100">
                    @csrf
                    <div class="modal-content approval-modal shadow-lg">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-person-check me-2"></i> Credit Note Approval
                            </h5>
                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-4">
                                <label for="customer_id" class="form-label fw-semibold">
                                    <i class="bi bi-person me-1 text-warning"></i> Customer
                                </label>
                                <select id="customer_id" name="customer_id"
                                    class="form-control shadow-sm custom-select2"
                                    data-placeholder="Search and select a customer...">
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="invoice_id" class="form-label fw-semibold">
                                    <i class="bi bi-receipt me-1 text-warning"></i> Invoice No
                                </label>
                                <select id="invoice_id" name="invoice_id"
                                    class="form-control shadow-sm custom-select2"
                                    data-placeholder="Search and select an invoice...">
                                </select>
                                <span id="invoice-error" class="text-danger" style="display:none;">Please select an invoice.</span>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="button" id="submitApprovalBtn" class="btn btn-success rounded-pill px-4">
                                <i class="bi bi-check-circle me-1"></i> Approve Request
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="container">
        
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @elseif(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
        
    @if ($userRole === 1)
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>Customer Code</th>
                        <th>Requested Date</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($creditNotes as $note)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $note->sales_invoice_no }}</td>
                        <td>{{ $note->customer_id }}</td>
                        <td>{{ \Carbon\Carbon::parse($note->credit_date_req)->format('d-m-Y') }}</td>
                        <td>
                            <textarea type="text" class="form-control remark-input" placeholder="Enter remarks" id="remark-{{ $note->id }}"></textarea>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm" onclick="updateCreditStatus({{ $note->id }}, 'accept')">Accept</button>
                            <button class="btn btn-danger btn-sm" onclick="updateCreditStatus({{ $note->id }}, 'reject')">Reject</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No pending credit requests.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
    @endif
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    
$(document).ready(function () {
    // Customer Dropdown
    
    $('#customer_id').select2({
        placeholder: $('#customer_id').data('placeholder'),
        allowClear: true,
        minimumInputLength: 3,
        dropdownParent: $('#creditNoteModal'),
        ajax: {
            url: public_path + '/credit-approve-customers',
            dataType: 'json',
            delay: 250,
            data: params => ({ term: params.term }),
            processResults: data => ({
                results: data 
            })
        },
        width: '500px'
    });

    // When customer is selected, auto call invoices API
    $('#customer_id').on('select2:select', function (e) {
        let customerCode = e.params.data.customer_code;

        $.ajax({
            url: public_path + '/credit_customer_invoices/' + customerCode,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                console.log("Invoices for customer:", response);

                // Populate normal invoice dropdown
                let invoiceSelect = $('#invoice_id');
                invoiceSelect.empty(); // clear old options
                invoiceSelect.append('<option value="">Select Invoice</option>');

                response.forEach(invoice => {
                    invoiceSelect.append(
                        $('<option>', {
                            value: invoice.text,
                            text: invoice.text
                        })
                    );
                });
            },
            error: function (xhr) {
                console.error("Error fetching invoices:", xhr.responseText);
            }
        });
    });

    $('#submitApprovalBtn').on('click', function (e) {
        e.preventDefault();
        let invoiceValue = $('#invoice_id').val();
        
        if (!invoiceValue) {
            $('#invoice-error').show();
            return; 
        } else {
            $('#invoice-error').hide();
        }

        let formData = $('#approvalForm').serialize();
        $.ajax({
            url: public_path + '/recredit-note-request',
            method: "POST",
            data: formData,
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    $('#creditNoteModal').modal('hide');
                    location.reload();
                    // $('#credit_note_table').DataTable().ajax.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function (xhr) {
                alert("Error: " + (xhr.responseJSON?.message || "Server error"));
            }
        });
    });
});

// Accept/Reject from table
function updateCreditStatus(id, action) {
        alert('Are you want to approve this invoice for recredit?');
        let remark = $('#remark-' + id).val().trim();
        $.ajax({
            url: public_path + '/approve-recredit-request/' + id + '/' + action,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: { remarks: remark },
            success: function (data) {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Server error.');
            }
        });
    }
</script>
@endpush
