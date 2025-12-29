@extends('layouts.app')
<style>
    /* Global Styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
    }

    .card {
        margin: 20px auto;
        padding: 5px;
        background-color: #fff;
        border: 1px solid #ddd;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: visible !important;
        height: auto !important;
    }

    .card-header {
        background-color: #333;
        color: #fff;
        padding: 2px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        overflow-x: auto;
        page-break-inside: auto;
    }

    th, td {
        border: 1px solid #ddd;
        padding: 5px;
        text-align: left;
    }

    th {
        background-color: #f0f0f0;
    }

    /* Prevent splitting of rows/tables in PDF */
    table, thead, tbody, tr, td, th {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    /* Invoice Details Styles */
    .first-tbl-td {
        padding: 30px;
        background-color: #ADD8E6;
        width: 100%;
    }

    .first-tbl-font {
        font-size: 18px;
        color: green;
        font-weight: bold;
        margin-right: 5px;
        margin-left: 30px;
    }

    .payments {
        width: 100%;
    }

    .payment-completed {
        color: green;
        font-weight: bold;
        font-size: 20px;
        text-align: center;
        margin-top: 20px;
        display: flex;
        margin-right: 15px;
    }

    .payments-details {
        padding: 30px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        margin-bottom: 20px;
        flex-direction: column;
    }

    .totalAmount {
        font-size: 20px;
        font-weight: bold;
        color: #333;
        text-decoration: underline;
        margin-bottom: 10px;
        display: flex;
        margin-right: 15px;
        padding: 30px;
    }

    .paidAmt {
        padding-left: 10px;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 10px;
    }

    input[type="number"],
    select {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
    }

    .order-summary {
        margin-top: 20px;
        padding: 10px;
        border: 1px solid #ddd;
        background-color: #f9f9f9;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
    }

    .summary-table td {
        padding: 8px 10px;
        font-size: 14px;
    }

    .summary-table tr td:first-child {
        font-weight: bold;
    }

    .summary-table tr:nth-child(even) {
        background-color: #f0f0f0;
    }

    .summaryTbl {
        margin-left: 30px;
        margin-top: 20px;
        text-align: right;
        justify-self: right;
    }

    .invoice-summary {
        display: flex;
        justify-content: space-around;
        align-items: center;
        border: 1px solid #ddd;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 5px;
        margin-top: 20px;
    }

    .invoice-column {
        display: flex;
        flex-direction: column;
        text-align: center;
        margin: 0 10px;
    }

    .label {
        font-size: 16px;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }

    .value {
        font-size: 18px;
        color: #007bff;
    }

    .batchs {
        width: 100px;
        font-size: 8px;
    }

    /* PDF-specific and print fixes */
    @media print {
        .hide-on-pdf {
            display: none !important;
        }

        .card, table {
            width: 100% !important;
            font-size: 12px !important;
            page-break-inside: avoid !important;
        }

        .table td, .table th {
            padding: 6px !important;
        }

        table, thead, tbody, tr, td, th {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .page-break {
            page-break-before: always;
        }
    }

    /* Used in JS when generating PDF */
    .pdf-export {
        font-size: 12px;
    }

    .pdf-export .table td, 
    .pdf-export .table th {
        padding: 6px;
    }
</style>
@section('content')

    <div class="container">
        @php
            $invoiceNo = request()->get('invoice_no');
        @endphp
        <div class="text-end me-3 mt-3">
            <button class="btn btn-primary" onclick="generatePDF()" >Generate PDF</button>
            <button onclick="generateExcel()" class="btn btn-success">Export to Excel</button>
        </div>
        <div class="card">

            <div class="card-header">
                <h3 class="d-block p-2 bg-primary text-white">RFT</h3>
            </div>
            <div id="payment-success-message" class="alert alert-success" style="display:none;"></div>

            <div class="card-body">
                <table class="first-tbl-td"><strong class="first-tbl-font">
                        <thead>
                            <tr>
                                @if (auth()->user()->ware_house == $rft->from_store)
                                    <th> FROM RFT</th>
                                @elseif ( auth()->user()->ware_house == $rft->to_store)
                                <th> TO RFT</th>
                                @endif
                                <th>RFT Number</th>
                                <th>Date</th>
                                {{-- <th>Total Amount</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @if (auth()->user()->ware_house == $rft->from_store)
                                    <td> {{$rft->from_store}}</td>
                                    <input type="hidden" name= "from_store_code" value="{{auth()->user()->ware_house }}">

                                @elseif ( auth()->user()->ware_house == $rft->to_store)
                                <td> 
                                    {{$rft->to_store}}
                                    <input type="hidden" name= "to_store_code" value="{{auth()->user()->ware_house }}">
                                </td>
                                @endif
                                <td>{{ $rft->rft_number ?? 'N/A' }}</td>
                                <td>{{ date('Y-m-d', strtotime($rft->created_at)) }}</td>
                            </tr>
                        </tbody>

                </table>

                @if(session('success'))
                    <script>
                        alert("{{ session('success') }}");
                    </script>
                @endif
                <!-- RFT ITEMS -->
                <div class="card">
                    <div class="container">
                        <h4 class="header-title"></h4>
                        <form id="rftForm" action="{{ route('storeRFTDetails') }}" method="POST">
                            @csrf

                            <div style="width: 100%; overflow: auto; text-align: left;">
                                <table class="table table-striped field" style="table-layout: fixed; width: 100%;">
                                    <thead>
                                        <tr>
                                            <!--<th style="width: 4.5%; padding: 5px;">Select</th>-->
                                            <th style="width: 8%; padding: 10px;">Item Code</th>
                                            <th style="width: 29.5%; padding: 5px;">Item Name</th>
                                            <th style="width: 6%; padding: 5px;">Avl Qty</th>
                                            <th style="width: 6%; padding: 5px;">Req Qty</th>
                                            <th style="width: 11%; padding: 5px;">Sent Qty</th>
                                            <th style="width: 11%; padding: 5px;">Rec Qty</th>
                                            <th style="width: 24%; padding: 5px;">Batches</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($rftItems) && $rftItems->count())
                                            @foreach ($rftItems as $index => $item)
                                            <tr>
                                                <input type="hidden" name="rft_items_id[]"
                                                        value="{{ $item->rti_id }}">
                                                    <input type="hidden" name="item_id[]" value="{{ $item->id }}">
                                                    <td style="width: 8%; padding: 5px;">
                                                        <input type="hidden" name="item_code[]"
                                                            value="{{ $item->item_code }}">
                                                        {{ $item->item_code }}
                                                    </td>
                                                    <input type="hidden" name="rft_number[]"
                                                        value="{{ $item->rft_number }}">
                                                    <input type="hidden" name="rft_id[]" value="{{ $item->id }}">
                                                    <input type="hidden" value= "{{ rand(1, 1000) . time() }}" name="guid" id="guid">
                                                    <td
                                                        style="word-wrap: break-word; white-space: normal; width: 20%; padding: 5px;">
                                                        {{ $item->item_name }}
                                                    </td>
                                                    <td style="width: 8%; padding: 5px;">{{ $item->available_qty }}
                                                    </td>
                                                    <td style="width: 8%; padding: 5px;">{{ $item->requested_qty }}
                                                    </td>
                                                    @if($item->from_status == 0 && $item->to_status == 0 && $item->available_qty > 0 && $rft->from_store == auth()->user()->ware_house)

                                                        <td style="width: 8%; padding: 5px;">
                                                            <input type="number" name="rft_required_qty[]"
                                                                min="0"
                                                                max="{{ $item->available_qty }}" value="{{ $item->available_qty < $item->requested_qty ? $item->available_qty : $item->requested_qty }}"
                                                                onblur="triggerBatchQuantity();"
                                                                oninput="if (this.value > {{ $item->available_qty }}) this.value = {{ $item->available_qty }}"
                                                                style="margin-bottom: 5px; height:40px"
                                                                <?php echo $item->from_store == auth()->user()->ware_house ? '' : 'disabled'; ?>>
                                                        </td>
                                                        <td style="width: 8%; padding: 5px;">
                                                            <input type="number" name="rft_received_qty[]"
                                                                value="{{ $item->rft_received_qty }}" min="0"
                                                                oninput="updateIssuedQuantity(this), validateReceivedQty(this)"
                                                                max="{{ $item->rft_required_qty }}"
                                                                style="margin-bottom: 5px;height:40px" <?php echo $item->to_store == auth()->user()->ware_house ? '' : 'disabled'; ?>
                                                                <?php echo $item->rft_received_qty == 0 ? '' : 'disabled'; ?>>
                                                            <input type="hidden" name="issued_qty[]">
                                                        </td>
                                                        <td style="width: 40%; padding: 5px;">
                                                            <div id="batch-list" class="batchs">
                                                                <div class="batch-info"></div>
                                                            </div>
                                                        </td>
                                                        
                                                    @elseif ($item->from_status == 1 && $item->to_status == 0 && $rft->to_store == auth()->user()->ware_house)

                                                            <td style="width: 8%; padding: 5px;">
                                                                <input type="number" name="rft_required_qty[]"
                                                                    value="{{ $item->rft_required_qty }}"
                                                                    style="margin-bottom: 5px; height:40px" disabled>
                                                            </td>

                                                            <td style="width: 8%; padding: 5px;">
                                                                <input type="number" name="rft_received_qty[]"
                                                                    value="{{ $item->rft_required_qty }}" min="0"
                                                                    oninput="updateIssuedQuantity(this), validateReceivedQty(this)"
                                                                    max="{{ $item->rft_required_qty }}"
                                                                    style="margin-bottom: 5px;height:40px" <?php echo $item->to_store == auth()->user()->ware_house ? '' : 'disabled'; ?>
                                                                    <?php echo $item->rft_received_qty == 0 ? '' : 'disabled'; ?>>
                                                                <input type="hidden" name="issued_qty[]">
                                                            </td>
                                                            <td style="width: 40%; padding: 5px;">
                                                                <div id="batch-list" class="batchs">
                                                                    <div class="batch-info"></div>
                                                                </div>
                                                            </td>
                                                            <input type="hidden" name="to_store_code[]" value="{{$rft->to_store}}">

                                                    @else
                                                       
                                                    <input type="hidden" name="rft_required_qty[]">
                                                    <input type="hidden" name="rft_received_qty[]">
                                                    <td style="width: 8%; padding: 5px;">{{ $item->rft_required_qty }}</td>
                                                    <td style="width: 8%; padding: 5px;">{{ $item->rft_received_qty }}</td>
                                                    <td style="width: 40%; padding: 5px;"> </td>
                                                    @endif
                                                    </tr> 
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8" style="padding: 5px;">No items found for this RFT.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="row hide-on-pdf" style="margin-top: 10px; ">
                                <div class="col-md-6">

                                    <lable>Remarks :</lable>
                                        <textarea name="remarks" style="width: 100%; height: 50px;">{{$rft->remarks}}</textarea>
                                    </div>
                                <div class="col-md-6">
                                    @if($rft->from_status == 0 && $rft->from_store == auth()->user()->ware_house)
                                    <button type="button" id="submitBtn" onclick="formSubmit()" class="btn btn-primary" style="margin-top: 20px; margin-left: auto; display: block;">Submit</button>
                                    @elseif($rft->to_status == 0 && $rft->to_store == auth()->user()->ware_house)
                                    <button type="button" id="submitBtn" onclick="formSubmit()" class="btn btn-primary" style="margin-top: 20px; margin-left: auto; display: block;">Submit</button>
                                    @endif
                                </div>
                            </div>
                        </form>

                    </div>
                </div>

                <span type="hidden" id="rftNumber" data-rft="{{ $rft->rft_number ?? 'N/A' }}"></span>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script src="{{ asset('js/xlsx.full.min.js') }}"></script>

     <script>
            function formSubmit() {
                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.innerText = 'Processing...';
                document.getElementById('rftForm').submit();
            }
    </script>

    <script>
        function updateIssuedQuantity(inputElement) {
            let row = inputElement.closest('tr');
            let requiredQty = parseInt(row.querySelector('input[name="rft_required_qty[]"]').value) || 0;
            let receivedQty = parseInt(inputElement.value) || 0;

            let issuedQty = requiredQty - receivedQty;
            issuedQty = issuedQty >= 0 ? issuedQty : 0;

            let issuedQtyInput = row.querySelector('input[name="issued_qty[]"]');
            issuedQtyInput.value = issuedQty;
        }

        function validateReceivedQty(input) {
            let maxQty = parseInt(input.getAttribute("max"));
            let currentValue = parseInt(input.value);

            if (currentValue > maxQty) {
                input.value = maxQty;
                alert("Received quantity cannot be greater than required quantity!");
            }
        }
    </script>
    <script>
        function triggerBatchQuantity(itemId, quantity, $row) {
            if (!itemId) {
                var $row = $(event.target).closest('tr');
                itemId = $row.find('input[name="item_code[]"]').val();
                quantity = $row.find('input[name="rft_required_qty[]"]').val();
            }
            if (!itemId) {
                console.error('Item ID not found.');
                return;
            }

            $.ajax({
                url: public_path + '/rft-batches/' + itemId + '/' + quantity,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    displayBatches(data, $row);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }
        function displayBatches(batches, $row) {
            var batchList = $row.find('.batchs');
            batchList.html('');

            var validBatches = (batches || []).filter(batch => batch);
            console.log('validBatches', validBatches);
            if (validBatches.length > 0) {
                validBatches.forEach(function(batch, index) {
                    if (batch.batch && batch.qty !== undefined) {
                        var batchInfo = `
                    <div class="batch-info">
                        Batch Num: ${batch.batch}, Qty: ${ Math.round(batch.qty) }, Exp: ${ batch.exp.split(' ')[0]}
                    </div>`;
                        batchList.append(batchInfo);
                    } else {
                        var batchInfo = `
                    <div class="batch-info">${batches}</div>`;
                        batchList.append(batchInfo);
                        console.warn(`Invalid batch properties at index ${index}:`);
                    }
                });
            } else {
                batchList.html('<div class="batch-info">No available batches found for this item.</div>');
            }
        }


        function toTriggerBatches(itemId = null, quantity = null, $row = null, event = null) {
            if (!itemId || !quantity || !$row) {
                if (event) {
                    $row = $(event.target).closest('tr');
                    itemId = itemId || $row.find('input[name="item_code[]"]').val();
                    quantity = quantity || $row.find('input[name="rft_required_qty[]"]').val();
                    var rftId = $row.find('input[name="rft_number[]"]').val();
                } else {
                    console.error('Missing itemId, quantity, or row and no event provided.');
                    return;
                }
            } else {
                var rftId = $row.find('input[name="rft_number[]"]').val();
            }

            if (!itemId || !rftId) {
                console.error('Item ID or RFT ID not found.');
                return;
            }

            $.ajax({
                url: public_path + '/rft-to-batches-details/' + rftId + '/' + itemId + '/' + quantity,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    displayBatches(data, $row);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }

    </script>
    <script>
        $(document).ready(function () {
            // Get store code from backend via Blade safely
            var storeCode = @json(auth()->user()->ware_house);

            $('table.field tbody tr').each(function () {
                var $row = $(this);
                var itemId = $row.find('input[name="item_code[]"]').val();
                var quantity = $row.find('input[name="rft_required_qty[]"]').val();
                var rftNum = $row.find('input[name="rft_number[]"]').val();
                var toStore = $row.find('input[name="to_store_code[]"]').val();

                if (itemId && quantity) {
                    if (storeCode == toStore) {
                        toTriggerBatches(itemId, quantity, $row);
                    } else {
                        triggerBatchQuantity(itemId, quantity, $row);
                    }
                }
            });
        });
    </script>

<script>
    function generatePDF() {
        const elementsToHide = document.querySelectorAll('.hide-on-pdf');
        elementsToHide.forEach(el => el.style.display = 'none');

        const card = document.querySelector('.card');
        const clonedCard = card.cloneNode(true);
        clonedCard.classList.add('pdf-export'); // Add PDF-specific class

        const rftNumber = document.getElementById('rftNumber')?.getAttribute('data-rft') || 'N/A';

        html2pdf().set({
            margin: [10, 10, 10, 10],
            filename: `RFT-Details-${rftNumber}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
        }).from(clonedCard).save().then(() => {
            window.location.reload();
        });
    }
</script>
<script>
    function generateExcel() {
        const card = document.querySelector('.card');
        const rftNumber = document.getElementById('rftNumber')?.getAttribute('data-rft') || 'N/A';

        let data = [];

        const rows = card.querySelectorAll('table tr');
        if (rows.length) {
            rows.forEach(row => {
                let rowData = [];
                row.querySelectorAll('th, td').forEach(cell => {
                    const input = cell.querySelector('input');
                    if (input) {
                        rowData.push(input.value);
                    } else {
                        rowData.push(cell.innerText.trim());
                    }
                });
                data.push(rowData);
            });
        } else {
            data.push(['Card Content']);
            data.push([card.innerText.trim()]);
        }

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, 'RFT Details');

        const today = new Date();
        const dateString = today.getFullYear() + '-' 
            + String(today.getMonth() + 1).padStart(2, '0') + '-' 
            + String(today.getDate()).padStart(2, '0');

        const filename = `RFT-Details-${rftNumber}.xlsx`;
        XLSX.writeFile(wb, filename);
    }
</script>

    
@endpush
