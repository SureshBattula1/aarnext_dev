@extends('layouts.app')

@section('content')


<style>
    /* --- GLOBAL --- */
body {
    background-color: #f5f6fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
}

/* --- CARD LAYOUT --- */
.card {
    border-radius: 10px;
    padding: 15px !important;
}

.card-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 18px;
}

/* --- FORM SPACING IMPROVED --- */
.container .row > div {
    margin-bottom: 12px;
}

label {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 4px;
}

/* --- INPUTS --- */
.form-control, .select2-container--default .select2-selection--single {
    height: 42px !important;
    border-radius: 6px !important;
    font-size: 14px !important;
}

/* --- MULTI SELECT2 --- */
.select2-container .select2-selection--multiple {
    min-height: 42px !important;
    max-height: 100px !important;
    overflow-y: auto !important;
    padding: 4px !important;
    border-radius: 6px !important;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice {
    padding: 3px 6px !important;
    font-size: 13px !important;
}

/* --- SECTION TITLE --- */
.section-title {
    font-weight: 600;
    margin: 10px 0 14px 0;
    font-size: 18px;
}

/* --- TABLE WRAPPER --- */
.table-container {
    background-color: #fff;
    padding: 12px;
    border-radius: 6px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    overflow-x: auto;
}

/* --- TABLE --- */
.table thead th {
    background-color: #004d56;
    color: #fff;
    font-size: 13px;
    padding: 8px 6px !important;
    white-space: nowrap;
}

.table td, .table th {
    vertical-align: middle !important;
    padding: 6px 8px !important;
    font-size: 13px;
}

.table-hover tbody tr:hover {
    background-color: #e6eef3 !important;
}

/* Reduce item name wrapping issue */
.table td:nth-child(2) {
    max-width: 160px;
    white-space: normal;
    word-wrap: break-word;
}


/* --- LOADER --- */
.loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.75);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loader-overlay .spinner {
    border: 4px solid #ddd;
    border-top: 4px solid #008cba;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    animation: spin 0.9s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}


</style>

<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Stock Transfer Planning</h4>
            <div class="container">
                <div class="row mb-2">
                    <div class="col-md-3">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ \Carbon\Carbon::now()->subMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label>Search</label>
                        <input type="text" class="form-control" id="search_items" placeholder="Search by Item name or Code..." autocomplete="off">
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-4">
                        <label>Item Group</label>
                        <select name="item_group" id="item_group" class="form-control">
                            <option value="">Select Item Group</option>
                            <option value="cash">General</option>
                            <option value="hospital">Hospital</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Manufacturer</label>
                        <select name="manufacturer" id="manufacturer" class="form-control">
                            <option value="">Select Manufacturer</option>
                            <option value="Portugal">Portugal</option>
                            <option value="Asia">Asia</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <h6>Select Warehouses</h6>
                        <select name="warehouseSelect[]" id="warehouseSelect" class="warehouse-selector" multiple></select>
                    </div>
                </div>
                <div id="loaderOverlay" class="loader-overlay" style="display:none;">
                    <div class="spinner"></div>
                    <p class="mt-2">Loading data, please wait...</p>
                </div>
                <!-- Combined Table -->
                <div class="stock-detail-table">
                    <div class="row align-items-center mb-3">
                        <div class="col text-center">
                            <div class="section"></div>
                        </div>
                        <div class="col text-center">
                            <h5 class="section-title">Combined Inventory</h5>
                        </div>
                        <div class="col text-end">
                            <button id="toggleColsBtn" class="btn btn-primary ms-2">Show More 👁️</button>
                            <button id="exportCsvBtn" class="btn btn-success ms-2">
                                Export CSV
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table id="stockTable" class="table table-bordered table-striped table-hover text-center">
                            <thead id="tableHeader"></thead>
                            <tbody id="tableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- <script>
    $(document).ready(function () {

    let warehouses = {};
    let warehouseColors = {};
    let showExtraCols = false;

    

    const fixedDarkColors = ['#3F4F5F', '#2F2F2F', '#2E4A36', '#343A40', '#2C3E50', '#4B3832'];

    loadData();

    $('#start_date, #end_date, #item_group, #manufacturer').on('change', loadData);

    let searchTimeout;
    $('#search_items').on('keyup', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(loadData, 500);
    });

    function loadData() {
        $('#loaderOverlay').show();

        const filters = {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            search: $('#search_items').val(),
            item_group: $('#item_group').val(),
            manufacturer: $('#manufacturer').val(),
        };

        $.ajax({
            url: public_path + '/rft-planning-json',
            method: 'GET',
            data: filters,
            dataType: 'json',
            success: function (backendData) {
                warehouses = {};

                backendData.forEach(item => {
                    const itemCode = item.item_code;
                    const itemName = item.item_name;
                    const warehouseStock = item.warehouse_stock;

                    item.stores.forEach(store => {
                        const whsCode = store.whs_code;
                        if (!warehouses[whsCode]) warehouses[whsCode] = [];

                        const calculatedItem = calculateItem({
                            item_code: itemCode,
                            item_name: itemName,
                            warehouse_stock: warehouseStock,
                            stock: store.store_qty,
                            previous_month_day_es: store.previous_month_day_es,
                            zero_stock_days: store.zero_stock_days,
                            hospital_stock: store.tot_hospital_qty,
                            general_stock: store.tot_general_qty,
                        });

                        warehouses[whsCode].push(calculatedItem);
                    });
                });

                const warehouseNames = Object.keys(warehouses);
                warehouseColors = {};
                warehouseNames.forEach((wh, index) => {
                    warehouseColors[wh] = fixedDarkColors[index % fixedDarkColors.length];
                });

                updateWarehouseSelect(warehouseNames);
                updateTable();
                $('#loaderOverlay').hide();

            },
            error: function (xhr, status, error) {
                console.error("❌ Error fetching data:", error);
                $('#loaderOverlay').hide();

            }
        });
    }

    function calculateItem(item) {
        const stock = item.stock || 0;
        const estimated_sale = (item.previous_month_day_es * item.zero_stock_days) || 0;
        const total_sales_stock_qty = (item.general_stock || 0) + (item.hospital_stock || 0) + estimated_sale;
        const required = Math.max(total_sales_stock_qty - stock, 0);
        const allocation = 0;
        const req_percent = stock > 0 ? Math.round((required / stock) * 100) : 0;

        return {
            ...item,
            estimated_sale,
            total_sales_stock_qty,
            required,
            allocation,
            req_percent
        };
    }

    function updateWarehouseSelect(warehouseNames) {

        const warehouseSelect = $('#warehouseSelect');
        warehouseSelect.empty();

        warehouseNames.forEach(wh => {
            warehouseSelect.append(new Option(wh, wh, true, true));
        });

        warehouseSelect.select2({
            placeholder: "Select Warehouses",
            allowClear: true,
            width: '100%',
            dropdownCssClass: "scrollable-dropdown"
        });

        warehouseSelect.trigger('change');

    }

    function updateTable() {
        
        const tableHeader = $('#tableHeader').empty();
        const tableBody = $('#tableBody').empty();

        const selectedOptions = $('#warehouseSelect').val();
        if (!selectedOptions || selectedOptions.length === 0) {
            tableHeader.html('<tr><th colspan="100%">Please select at least one warehouse</th></tr>');
            return;
        }

        const headerRow = $('<tr>');
        headerRow.append('<th rowspan="2">Item Code</th><th rowspan="2">Item Name</th>');
        selectedOptions.forEach(wh => {
            const color = warehouseColors[wh];
            headerRow.append(`<th colspan="${showExtraCols ? 9 : 5}" style="background:${color};color:#fff;">${wh}</th>`);
        });
        headerRow.append(`
            <th rowspan="2">Warehouse Stock</th>
            <th rowspan="2">Global Store Stock</th>
            <th rowspan="2">Global Req Stock</th>
            <th rowspan="2">Total Stock</th>
            <th rowspan="2">Req %</th>
            <th rowspan="2">Factor</th>
        `);
        tableHeader.append(headerRow);

        const subHeader = $('<tr>');
        selectedOptions.forEach(wh => {
            const color = warehouseColors[wh];
            const cols = showExtraCols
                ? ['Hospital', 'General', 'In Stock', 'Prev ES', 'Zero Days', 'Est Sale', 'Total Sales', 'Required', 'Allocation']
                : ['In Stock', 'Est Sale', 'Total Sales', 'Required', 'Allocation'];
            cols.forEach(c => subHeader.append(`<th style="background:${color};color:#fff;">${c}</th>`));
        });
        tableHeader.append(subHeader);

        const firstWarehouse = selectedOptions[0];
        const items = warehouses[firstWarehouse] || [];

        items.forEach(item => {
            const row = $('<tr>');
            row.append(`<td>${item.item_code}</td><td>${item.item_name}</td>`);

            let warehouseStock = 0, globleStoreStock = 0, totalRequired = 0;
            selectedOptions.forEach(wh => {
                const whItem = warehouses[wh].find(i => i.item_code === item.item_code);
                if (whItem) {
                    warehouseStock = whItem.warehouse_stock;
                    globleStoreStock += whItem.stock;
                    totalRequired += whItem.required;
                }
            });

            const totalStock = warehouseStock + globleStoreStock;
            const totalReqPercent = totalStock > 0 ? Math.round((totalRequired / totalStock) * 100) : 0;
            const factor = totalRequired > 0
                ? Number((warehouseStock / totalRequired).toFixed(2))
                : 0;


            selectedOptions.forEach(wh => {
                const whItem = warehouses[wh].find(i => i.item_code === item.item_code);

                if (whItem) {

                    
                    let storeStock = whItem.stock; // In Stock field
                    let alloc = 0;
                    console.log(typeof storeStock , ' STORE STOCK '); // number (expected)
                    // console.log(typeof alloc , 'ALLOC'); 
                    if (factor < 1) {
                        // CASE 1: factor < 1 → multiply stock
                        alloc = storeStock * factor;
                        
                    } 
                    else if (factor > 1) {
                        alloc = storeStock + Number(factor);
                        // CASE 2: factor > 1 → add stock
                        // alloc = storeStock + factor;
                    console.log(typeof factor , 'ALLOC'); 

                    } 
                    else {
                        // CASE 3: factor == 1 → same as stock
                        alloc = storeStock;
                    }

                    whItem.allocation = Math.round(alloc);
                }
            });

            selectedOptions.forEach(wh => {
                const whItem = warehouses[wh].find(i => i.item_code === item.item_code);
                const color = warehouseColors[wh] + '11';
                if (whItem) {
                    const cells = showExtraCols
                        ? [whItem.hospital_stock, whItem.general_stock, whItem.stock, whItem.previous_month_day_es,
                        whItem.zero_stock_days, whItem.estimated_sale, whItem.total_sales_stock_qty,
                        whItem.required, whItem.allocation]
                        : [whItem.stock, whItem.estimated_sale, whItem.total_sales_stock_qty,
                        whItem.required, whItem.allocation];
                    cells.forEach(val => row.append(`<td style="background:${color}">${val}</td>`));
                } else {
                    row.append(`<td colspan="${showExtraCols ? 9 : 5}" style="background:${color};">-</td>`);
                }
            });

            row.append(`
                <td>${warehouseStock}</td>
                <td>${globleStoreStock}</td>
                <td>${totalRequired}</td>
                <td>${totalStock}</td>
                <td>${totalReqPercent}%</td>
                <td>${factor}</td>
            `);

            tableBody.append(row);
        });
    }

    $('#toggleColsBtn').on('click', () => {
        $('#loaderOverlay').show();

        showExtraCols = !showExtraCols;
        setTimeout(() => {
            updateTable();
            $('#toggleColsBtn').text(showExtraCols ? 'Show Less 👁️‍🗨️' : 'Show More 👁️');
            $('#loaderOverlay').hide();
        }, 50); 
    });

    $('#warehouseSelect').on('change', updateTable);
    });

</script> --}}

<script>
    $(document).ready(function () {
    
        let warehouses = {};
        let warehouseColors = {};
        let showExtraCols = false;
    
        const fixedDarkColors = ['#3F4F5F', '#2F2F2F', '#2E4A36', '#343A40', '#2C3E50', '#4B3832'];
    
        loadData();
    
        $('#start_date, #end_date, #item_group, #manufacturer').on('change', loadData);
    
        let searchTimeout;
        $('#search_items').on('keyup', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(loadData, 500);
        });
    
        function loadData() {
            $('#loaderOverlay').show();
    
            const filters = {
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                search: $('#search_items').val(),
                item_group: $('#item_group').val(),
                manufacturer: $('#manufacturer').val(),
            };
    
            $.ajax({
                url: public_path + '/rft-planning-json',
                method: 'GET',
                data: filters,
                dataType: 'json',
                success: function (backendData) {
                    warehouses = {};
    
                    backendData.forEach(item => {
                        const itemCode = item.item_code;
                        const itemName = item.item_name;
                        const warehouseStock = item.warehouse_stock;
    
                        item.stores.forEach(store => {
                            const whsCode = store.whs_code;
                            if (!warehouses[whsCode]) warehouses[whsCode] = [];
    
                            const calculatedItem = calculateItem({
                                item_code: itemCode,
                                item_name: itemName,
                                warehouse_stock: warehouseStock,
                                stock: store.store_qty,
                                previous_month_day_es: store.previous_month_day_es,
                                zero_stock_days: store.zero_stock_days,
                                hospital_stock: store.tot_hospital_qty,
                                general_stock: store.tot_general_qty,
                            });
    
                            warehouses[whsCode].push(calculatedItem);
                        });
                    });
    
                    const warehouseNames = Object.keys(warehouses);
                    warehouseColors = {};
                    warehouseNames.forEach((wh, index) => {
                        warehouseColors[wh] = fixedDarkColors[index % fixedDarkColors.length];
                    });
    
                    updateWarehouseSelect(warehouseNames);
                    updateTable();
                    $('#loaderOverlay').hide();
                },
                error: function () {
                    $('#loaderOverlay').hide();
                }
            });
        }
    
        function calculateItem(item) {
            const stock = item.stock || 0;
            const estimated_sale = (item.previous_month_day_es * item.zero_stock_days) || 0;
            const total_sales_stock_qty = (item.general_stock || 0) + (item.hospital_stock || 0) + estimated_sale;
            const required = Math.max(total_sales_stock_qty - stock, 0);
            const allocation = 0;
            const req_percent = stock > 0 ? Math.round((required / stock) * 100) : 0;
    
            return {
                ...item,
                estimated_sale,
                total_sales_stock_qty,
                required,
                allocation,
                req_percent
            };
        }
    
        function updateWarehouseSelect(warehouseNames) {
            const warehouseSelect = $('#warehouseSelect');
            warehouseSelect.empty();
    
            warehouseNames.forEach(wh => {
                warehouseSelect.append(new Option(wh, wh, true, true));
            });
    
            warehouseSelect.select2({
                placeholder: "Select Warehouses",
                allowClear: true,
                width: '100%'
            });
    
            warehouseSelect.trigger('change');
        }
    
        function updateTable() {
    
            const tableHeader = $('#tableHeader').empty();
            const tableBody = $('#tableBody').empty();
    
            const selectedOptions = $('#warehouseSelect').val();
            if (!selectedOptions || selectedOptions.length === 0) {
                tableHeader.html('<tr><th colspan="100%">Please select at least one warehouse</th></tr>');
                return;
            }
    
            /* ---------------- HEADER ---------------- */
            const headerRow = $('<tr>');
            headerRow.append('<th rowspan="2">Item Code</th><th rowspan="2">Item Name</th>');
    
            selectedOptions.forEach(wh => {
                headerRow.append(
                    `<th colspan="${showExtraCols ? 9 : 5}" style="background:${warehouseColors[wh]};color:#fff;">${wh}</th>`
                );
            });
    
            headerRow.append(`
                <th rowspan="2">Warehouse Stock</th>
                <th rowspan="2">Global Store Stock</th>
                <th rowspan="2">Global Req Stock</th>
                <th rowspan="2">Total Stock</th>
                <th rowspan="2">Req %</th>
                <th rowspan="2">Factor</th>
            `);
    
            tableHeader.append(headerRow);
    
            const subHeader = $('<tr>');
            selectedOptions.forEach(wh => {
                const cols = showExtraCols
                    ? ['Hospital','General','In Stock','Prev ES','Zero Days','Est Sale','Total Sales','Required','Allocation']
                    : ['In Stock','Est Sale','Total Sales','Required','Allocation'];
    
                cols.forEach(c => {
                    subHeader.append(`<th style="background:${warehouseColors[wh]};color:#fff;">${c}</th>`);
                });
            });
    
            tableHeader.append(subHeader);
    
            /* ---------------- BODY ---------------- */
            const firstWarehouse = selectedOptions[0];
            const items = warehouses[firstWarehouse] || [];
    
            items.forEach(item => {
    
                const row = $('<tr>');
                row.append(`<td>${item.item_code}</td>`);
                row.append(`<td>${item.item_name}</td>`);
    
                let warehouseStock = 0, globalStoreStock = 0, totalRequired = 0;
    
                selectedOptions.forEach(wh => {
                    const whItem = warehouses[wh].find(i => i.item_code === item.item_code);
                    if (whItem) {
                        warehouseStock = whItem.warehouse_stock;
                        globalStoreStock += whItem.stock;
                        totalRequired += whItem.required;
                    }
                });
    
                const totalStock = warehouseStock + globalStoreStock;
                const totalReqPercent = totalStock > 0 ? Math.round((totalRequired / totalStock) * 100) : 0;
                // const factor = totalRequired > 0 ? Number((warehouseStock / totalRequired).toFixed(2)) : 0;
                const factor = totalRequired > 0 ? Number((totalStock / totalRequired).toFixed(2)) : 0;
    
                /* Warehouse Columns */
                selectedOptions.forEach(wh => {
                    const whItem = warehouses[wh].find(i => i.item_code === item.item_code);
                    const bg = warehouseColors[wh] + '11';
    
                    if (whItem) {
    
                        let alloc;
                        if (factor < 1) alloc = whItem.required * factor;
                        else if (factor > 1) alloc = whItem.required + factor;
                        else alloc = whItem.required;
    
                        whItem.allocation = Math.round(alloc);
    
                        const cells = showExtraCols
                            ? [
                                whItem.hospital_stock,
                                whItem.general_stock,
                                whItem.stock,
                                whItem.previous_month_day_es,
                                whItem.zero_stock_days,
                                whItem.estimated_sale,
                                whItem.total_sales_stock_qty,
                                whItem.required,
                                whItem.allocation
                              ]
                            : [
                                whItem.stock,
                                whItem.estimated_sale,
                                whItem.total_sales_stock_qty,
                                whItem.required,
                                whItem.allocation
                              ];
    
                        cells.forEach(val => row.append(`<td style="background:${bg}">${val}</td>`));
    
                    } else {
                        const emptyCells = showExtraCols ? 9 : 5;
                        for (let i = 0; i < emptyCells; i++) {
                            row.append(`<td style="background:${bg}">-</td>`);
                        }
                    }
                });
    
                row.append(`
                    <td>${warehouseStock}</td>
                    <td>${globalStoreStock}</td>
                    <td>${totalRequired}</td>
                    <td>${totalStock}</td>
                    <td>${totalReqPercent}%</td>
                    <td>${factor}</td>
                `);
    
                tableBody.append(row);
            });
        }
    
        // $('#toggleColsBtn').on('click', () => {
        //     showExtraCols = !showExtraCols;
        //     updateTable();
        //     $('#toggleColsBtn').text(showExtraCols ? 'Show Less 👁️‍🗨️' : 'Show More 👁️');
        // });

        $('#toggleColsBtn').on('click', () => {

            $('#loaderOverlay').show();

            showExtraCols = !showExtraCols;
            setTimeout(() => {
                updateTable();
                $('#toggleColsBtn').text(
                    showExtraCols ? 'Show Less 👁️‍🗨️' : 'Show More 👁️'
                );
                $('#loaderOverlay').hide();
            }, 50); 
        });

    
        $('#warehouseSelect').on('change', updateTable);
    });
    </script>
    
<script>
    $('#exportCsvBtn').on('click', function () {
        exportStockTableCSV('stock-transfer-planning.csv');
    });
    
    function exportStockTableCSV(filename) {
    
        const table = document.getElementById("stockTable");
        const theadRows = table.querySelectorAll("thead tr");
        const tbodyRows = table.querySelectorAll("tbody tr");
    
        let csv = [];
        let headers = [];
    
        const firstRow = theadRows[0].querySelectorAll("th");
        const secondRow = theadRows[1].querySelectorAll("th");
    
        let subHeaderIndex = 0;
    
        /* ---------------------------------
           BUILD FLATTENED HEADERS (CORRECT)
        ----------------------------------*/
        firstRow.forEach(th => {
            const text = th.innerText.trim();
            const colspan = th.colSpan || 1;
            const rowspan = th.rowSpan || 1;
    
            // Case 1: Fixed columns (rowspan=2)
            if (rowspan === 2 && colspan === 1) {
                headers.push(`"${text}"`);
            }
    
            // Case 2: Warehouse grouped columns
            else if (colspan > 1) {
                for (let i = 0; i < colspan; i++) {
                    const subText = secondRow[subHeaderIndex]?.innerText.trim() || '';
                    headers.push(`"${text} - ${subText}"`);
                    subHeaderIndex++;
                }
            }
        });
    
        csv.push(headers.join(","));
    
        /* ---------------------------------
           TABLE BODY
        ----------------------------------*/
        tbodyRows.forEach(row => {
            let rowData = [];
            row.querySelectorAll("td").forEach(td => {
                let text = td.innerText.replace(/\s+/g, ' ').trim();
                text = text.replace(/"/g, '""');
                rowData.push(`"${text}"`);
            });
            csv.push(rowData.join(","));
        });
    
        /* ---------------------------------
           DOWNLOAD
        ----------------------------------*/
        const csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
        const downloadLink = document.createElement("a");
    
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";
    
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
    </script>
    
@endpush
