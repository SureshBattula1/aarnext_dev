@extends('layouts.app')
<style>
    select.form-control {
        font-size: 9px;
        font-weight: bold;
    }
    .select2-search--dropdown {
        display: block;
        padding: 4px;
        width: 250px;
        font-size: 10px;
        font-weight: bold;
        color: #000;
    }
    .select2-container--default .select2-results>.select2-results__options {
        max-height: 208px;
        overflow-y: auto;
        width: 384px;
    }
    .table td, .jsgrid .jsgrid-table td{
        padding: 4px !important;
    }

</style>

@section('content')
    <div class="container">
        <h4 class="generalheader" style="font-size: 16px; font-weight: bold; margin: 10px;">GENERAL EXPENSES</h4>
        <form id="expenses_payment_form" action="{{ route('expenses-store') }}" method="POST" autocomplete="off">
            @csrf
            <div class="form-container">
                <div class="card" style="padding: 10px; overflow: scroll;">
                    <div class="table-container">
                        <table id="expenses_table" class="table">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center ">Account Name</th>
                                    <th class="text-center ">Doc Remarks</th>
                                    <th class="text-center ">Amount</th>
                                    <th class="text-center ">Inter Company</th>
                                    <th class="text-center ">Store/House</th>
                                    <th class="text-center ">Employee</th>
                                    <th class="text-center ">Other</th>
                                    <th class="text-center ">Vehicle</th>
                                    <th class="text-center ">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select class="form-control account_name select2" name="account_number[]">
                                            <option value="">Select Account Name </option>
                                            @foreach ($expenses as $expense)
                                                <option value="{{ $expense->account_number }}" 
                                                    data-account-number="{{ $expense->account_number }}">
                                                    {{ $expense->account_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td ><textarea type="text" class="form-control" style="width: 200px;" name="doc_remarks[]"></textarea></td>
                                    <td style="padding: 0px;"><input type="number" class="form-control amount" style="width: 100px;" name="amount[]" step="0.01"></td>
                                    <td>
                                        <select class="form-control select2" name="inter_company[]">
                                            <option value="">Select Inter Company</option>
                                            @foreach ($expenses_inter_cmpy as $inter)
                                                <option value="{{ $inter->distribution }}">{{ $inter->distribution_rule_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control select2 " name="store_house[]">
                                            <option value="">Select Store/House</option>
                                            @foreach ($exp_store_or_house as $store)
                                                <option value="{{ $store->distribution }}">{{ $store->distribution_rule_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                    <select class="form-control select2 employee" name="employee[]">
                                        <option value="">Select Employee</option>
                                        @foreach ($expenses_employee as $employee)
                                            <option value="{{ $employee->distribution }}">{{ $employee->distribution_rule_name }}</option>
                                        @endforeach
                                    </select>
                                    </td>
                                    <td>
                                        <select class="form-control select2" name="other[]">
                                            <option value="">Select Other</option>
                                            @foreach ($expenses_other as $other)
                                                <option value="{{ $other->distribution }}">{{ $other->distribution_rule_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control select2" name="vehicle[]">
                                            <option value="">Select Vehicle</option>
                                            @foreach ($expenses_vehicle as $vehicle)
                                                <option value="{{ $vehicle->distribution }}">{{ $vehicle->distribution_rule_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <button type="button" class="bor_none remove-row-btn">
                                            <img src="{{ asset('images/close-icon.png') }}" alt="Remove Row" 
                                                title="Remove Row" style="width:22px; height:22px;">
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="row" style="margin-top: 10px;">
                            <div class="col-md-3"></div>
                            <div class="col-md-1"> <strong>Total: </strong></div>
                            <div class="calculated-amount col-md-3">
                               
                                <input type="text" name="total_calculated_amount" readonly id="total_calculated_amount_hidden" 
                                    style="width: 200px; text-align: right;">
                            </div>
                            <div class="col-md-5"></div>
                        </div>
                        <div class="row" style="">
                            <div class="col-md-8"></div>
                            <div class="footerbutton col-md-2">
                                <button type="submit" class="btn btn-primary">Save Amounts</button>
                            </div>
                            <div class="col-md-2"></div>
                        </div>
                    </div>
                </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
       
        //$(document).ready(function () {
        //    $('.select2').select2({
        //        placeholder: "Select an option", 
        //        allowClear: true, 
        //    });
        //})
    </script>
    <script>
        $(document).ready(function () {
            function recalculateTotal() {
                let total = 0;
                $('.amount').each(function () {
                    total += parseFloat($(this).val()) || 0;
                });
                $('#total_calculated_amount_hidden').val(total.toFixed(2));
            }

            $('#expenses_table').on('change', '.account_name', function () {
                const lastRow = $('#expenses_table tbody tr:last');
                const isEmpty = lastRow.find('select').toArray().every(input => $(input).val() === '');

                if (!isEmpty) {
                    const newRow = lastRow.clone();
                    newRow.find('input, select').val(''); 
                    newRow.find('textarea').val('');
                
                    $('#expenses_table tbody').append(newRow);

                }
            });
            
        
            // Remove Row
            $('#expenses_table').on('click', '.remove-row-btn', function () {
                $(this).closest('tr').remove();
                recalculateTotal();
            });

            $('#expenses_table').on('input', '.amount', function () {
                recalculateTotal();
            });

            $('#expenses_payment_form').submit(function (e) {
                e.preventDefault(); 
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        alert('Form submitted successfully!');
                        location.reload(); 
                    },
                    error: function (xhr) {
                        alert('An error occurred: ' + xhr.responseText);
                    },
                });
            });
        });
    </script>

@endpush
