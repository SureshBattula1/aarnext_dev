@extends('layouts.app')

@section('content')
<style>
    .card-header {
        text-align: center;
        font-weight: bold;
        margin: 15px;
    }
    .card {
        margin: 20px;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0);
    },


    .invList {
        width: 100%;
        overflow-x: auto;
    }

    .table-container {
        overflow-x: auto;
        max-width: 100%;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #ccc;
        padding: 10px;
        text-align: center;
    }

    @media screen and (max-width: 600px) {
        table, thead, tbody, tr, th, td {
            display: block;
            width: 100%;
        }

        tr {
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }

        th {
            display: none;
        }

        td {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        td::before {
            content: attr(data-label);
            font-weight: bold;
        }

        input[type="checkbox"] {
            transform: scale(1.2);
        }

        .totalAmt {
            margin-bottom: 10px;
        }
    }

    .total-row {
        background-color: #f9f9f9;
        font-weight: bold;
    }

    .totalAmt input {
        background-color: #e9ecef;
        cursor: not-allowed;
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Invoice Payments List</div>

                <div class="totalAmt" style="margin-bottom: 10px;">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="totalAmount">Total Collection:</label>
                            <input type="text" name ="tot_collection" id="totalCollection" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="totalAmount">Total TPA:</label>
                            <input type="text" name="tot_tpa" id="totalTPA" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="totalAmount">Total Cash:</label>
                            <input type="text" name="tot_cash" id="totalCash" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                <div class="invList">
                    <table id="invoicePaymentsTable">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Customer Code</th>
                                <th>Invoice No.</th>
                                <th>Total Amount</th>
                                <th>Credit Note Amount</th>
                                <th>Balance To Pay</th>
                                <th>TPA Amount</th>
                                <th>Cash Amount</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceListBody">
                            <!-- Dynamic data will be inserted here by AJAX -->
                        </tbody>
                    </table>
                </div>

                <div class="form-group" style="margin:15px; text-align:end;">
                    <button id="saveData" class="btn btn-primary">Submit</button>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')

<script>
$(document).ready(function() {

        $.ajax({
            url: "{{ url('/get-invoice-payments') }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var invoiceList = response.data;
                var html = '';

                invoiceList.forEach(function(invoice) {
                    var balanceAmount = invoice.invoice_total - (invoice.credit_total || 0);
                    html += '<tr>';
                    html += '<td><input type="checkbox" name="select_invoice" value="'+ invoice.id +'" checked class="invoice-checkbox" onclick="handleCheckboxChange(this)"></td>';
                    html += '<td><input type="text" name="customer_id" value="'+ invoice.customer_id +'" class="form-control" readonly></td>';
                    html += '<td><input type="text" name="doc_num" value="'+ invoice.doc_num +'" class="form-control" readonly></td>';
                    html += '<td><input type="text" name="invoice_total" value="'+ invoice.invoice_total +'" class="form-control" readonly></td>';
                    html += '<td><input type="text" name="credit_amt" value="'+ (invoice.credit_total || 0) +'" class="form-control" readonly></td>';
                    html += '<td><input type="text" name="balance_amt" value="'+ balanceAmount +'" class="form-control" readonly></td>';
                    html += '<td><input type="number" name="tpa_amt" value="0" class="form-control" data-balance="'+ balanceAmount +'"></td>';
                    html += '<td><input type="number" name="cash_amt" value="'+ balanceAmount +'" class="form-control" readonly></td>';
                    html += '</tr>';
                });
                $('#invoiceListBody').html(html);
                updateTotalRow();
            }
        });

    $(document).on('input', 'input[name="tpa_amt"]', function() {
        var row = $(this).closest('tr');
        var balanceAmount = parseFloat(row.find('input[name="balance_amt"]').val()) || 0;
        var tpaAmount = parseFloat($(this).val()) || 0;
        if(balanceAmount < tpaAmount){
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Maximum Amount allowed is " + balanceAmount,
                });
            row.find('input[name="tpa_amt"]').val(balanceAmount);
        }
        var remainingCash = parseFloat(balanceAmount - tpaAmount).toFixed(2);


        row.find('input[name="cash_amt"]').val(remainingCash > 0 ? remainingCash : 0);

        updateTotalRow();
    });

    $(document).on('input', 'input[name="cash_amt"]', function() {
        var row = $(this).closest('tr');
        var balanceAmount = parseFloat(row.find('input[name="balance_amt"]').val()) || 0;
        var tpaAmount = parseFloat(row.find('input[name="tpa_amt"]').val()) || 0;

        var cashAmount = parseFloat($(this).val()) || 0;

        var remainingTPA = parseFloat(balanceAmount - cashAmount).toFixed(2);
        if (remainingTPA >= 0) {
            row.find('input[name="tpa_amt"]').val(remainingTPA);
        }

        updateTotalRow();
    });

    $(document).on('change', '.invoice-checkbox', function() {
        updateTotalRow();
    });

    function updateTotalRow() {
        var totalCollection = 0;
        var totalTPA = 0;
        var totalCash = 0;

        $('#invoiceListBody tr').each(function() {
            var checkbox = $(this).find('.invoice-checkbox');
            if (checkbox.is(':checked')) {
                totalCollection += parseFloat($(this).find('input[name="invoice_total"]').val()) || 0;
                totalTPA += parseFloat($(this).find('input[name="tpa_amt"]').val()) || 0;
                totalCash += parseFloat($(this).find('input[name="cash_amt"]').val()) || 0;
            }
        });

        $('#totalCollection').val(totalCollection);
        $('#totalTPA').val(totalTPA);
        $('#totalCash').val(totalCash);

        if ($('.invoice-checkbox:checked').length > 0) {
            $('.totalAmt input').prop('disabled', false); // Enable total row
        } else {
            $('.totalAmt input').prop('disabled', true); // Disable total row
        }
    }

    $('#saveData').click(function() {
        var updatedData = [];
        var totCollection = $('input[name="tot_collection"]').val();
        var totTpa = $('input[name="tot_tpa"]').val();
        var totCash = $('input[name="tot_cash"]').val();
        $('#invoiceListBody tr').each(function() {
            var $row = $(this);
            if($row.find('input[name="customer_id"]').prop('disabled') || $row.find('input[name="doc_num"]').prop('disabled'))
            {
                return; // Skip this row
            }

            var rowData = {
                id: $(this).find('input[name="select_invoice"]').val(),
                customer_id: $(this).find('input[name="customer_id"]').val(),
                doc_num: $(this).find('input[name="doc_num"]').val(),
                invoice_total: $(this).find('input[name="invoice_total"]').val(),
                credit_amt: $(this).find('input[name="credit_amt"]').val(),
                balance_amt: $(this).find('input[name="balance_amt"]').val(),
                tpa_amt: $(this).find('input[name="tpa_amt"]').val(),
                cash_amt: $(this).find('input[name="cash_amt"]').val(),
            };
            updatedData.push(rowData);
        });

        $.ajax({
            url: "{{ url('/store-bulk-inv-payments') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                tot_collection : totCollection,
                tot_tpa: totTpa,
                tot_cash: totCash,
                data: updatedData
            },
            success: function(response) {
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Bulk Payments data has been saved successfully.",
                    confirmButtonText: "OK"
                });
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Something went wrong." + error,
                    confirmButtonText: "OK"
                });
            }
        });
    });
});

function handleCheckboxChange(checkbox) {
    let row = $(checkbox).closest("tr");
    console.log('Row:', row);

    let customer_id = row.find('input[name="customer_id"]').val();
    let doc_num = row.find('input[name="doc_num"]').val();
    let invoice_total = row.find('input[name="invoice_total"]').val();
    let credit_amt = row.find('input[name="credit_amt"]').val();
    let balance_amt = row.find('input[name="balance_amt"]').val();
    let tpa_amt = row.find('input[name="tpa_amt"]').val();
    let cash_amt = row.find('input[name="cash_amt"]').val();
    row.find('input').each(function() {
        if ($(this).attr('type') !== 'checkbox') {
            $(this).prop('disabled', !checkbox.checked);
        }
    });

}


</script>
@endpush
