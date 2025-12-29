@extends('layouts.app')

@section('content')

<style>
/* --- GLOBAL --- */
body {
    background-color: #f5f6fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* --- CARD LAYOUT --- */
.card { border-radius: 10px; padding: 15px !important; }
.card-title { font-size: 20px; font-weight: 600; margin-bottom: 18px; }

/* --- FORM --- */
.container .row > div { margin-bottom: 12px; }
label { font-weight: 600; font-size: 14px; margin-bottom: 4px; }

.form-control {
    height: 42px !important;
    border-radius: 6px !important;
    font-size: 14px !important;
}

/* --- TABLE WRAPPER --- */
.table-container {
    background-color: #fff;
    padding: 12px;
    border-radius: 6px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    overflow-x: auto;
}

.table thead th {
    background-color: #004d56;
    color: #fff;
    padding: 8px 6px !important;
    white-space: nowrap;
}

.table td, .table th {
    padding: 6px 8px !important;
    font-size: 13px;
}

/* LOADER */
.loader-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(255,255,255,0.75);
    display: flex; align-items: center; justify-content: center;
    flex-direction: column; z-index: 9999;
}

.loader-overlay .spinner {
    width: 45px; height: 45px;
    border-radius: 50%;
    border: 4px solid #ddd;
    border-top: 4px solid #008cba;
    animation: spin 0.9s linear infinite;
}

@keyframes spin { 0% { transform: rotate(0deg);} 100% {transform: rotate(360deg);} }
</style>




<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Stock Transfer Planning</h4>

            <!-- FILTER SECTION -->
            <div class="container">
                <div class="row mb-2">
                    <div class="col-md-3">
                        <label>Start Date</label>
                        <input type="date" class="form-control" id="start_date" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="col-md-3">
                        <label>End Date</label>
                        <input type="date" class="form-control" id="end_date" value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="col-md-4">
                        <label>Search</label>
                        <input type="text" class="form-control" id="search_items" placeholder="Search Item...">
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-4">
                        <label>Item Group</label>
                        <select id="item_group" class="form-control">
                            <option value="">Select Item Group</option>
                            <option value="general">General</option>
                            <option value="hospital">Hospital</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Manufacturer</label>
                        <select id="manufacturer" class="form-control">
                            <option value="">Select Manufacturer</option>
                            <option value="china">China</option>
                            <option value="india">India</option>
                            <option value="portugal">Portugal</option>
                            <option value="europe">Europe</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Select Warehouses</label>
                        <select id="warehouseSelect" class="form-control" multiple></select>
                    </div>
                </div>

                <!-- LOADER -->
                <div id="loaderOverlay" class="loader-overlay" style="display:none;">
                    <div class="spinner"></div>
                    <p>Loading data...</p>
                </div>

                <!-- TABLE -->
                <div class="table-container">
                    <table id="inventoryTable" class="table table-bordered table-striped table-hover text-center">
                        <thead id="tableHeader"></thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection


@push('scripts')
<script>
$(document).ready(function () {

let warehouses = {};
let warehouseColors = {};
let showExtraCols = false;

const colorPalette = ['#3F4F5F','#2F2F2F','#2E4A36','#343A40','#2C3E50','#4B3832'];

loadData();

$("#start_date,#end_date,#item_group,#manufacturer").change(loadData);

let searchDelay;
$("#search_items").keyup(function(){
    clearTimeout(searchDelay);
    searchDelay = setTimeout(loadData, 400);
});

function loadData() {
    $("#loaderOverlay").show();

    $.ajax({
        url: public_path + "/rft-planning-json",
        type: "GET",
        data: {
            start_date: $("#start_date").val(),
            end_date: $("#end_date").val(),
            search: $("#search_items").val(),
            item_group: $("#item_group").val(),
            manufacturer: $("#manufacturer").val(),
        },
        success: function(data) {

            warehouses = {};

            data.forEach(item => {
                item.stores.forEach(store => {
                    if (!warehouses[store.whs_code]) warehouses[store.whs_code] = [];
                    warehouses[store.whs_code].push(calculateItem(item, store));
                });
            });

            let whList = Object.keys(warehouses);
            warehouseColors = {};
            whList.forEach((wh, i) => warehouseColors[wh] = colorPalette[i % colorPalette.length]);

            updateWarehouseSelect(whList);
            updateTable();

            $("#loaderOverlay").hide();
        }
    });
}

function calculateItem(item, store) {
    let estimated_sale = (store.previous_month_day_es * store.zero_stock_days) || 0;
    let total_sales_stock = store.tot_general_qty + store.tot_hospital_qty + estimated_sale;
    let required = Math.max(total_sales_stock - store.store_qty, 0);

    return {
        ...item,
        stock: store.store_qty,
        hospital_stock: store.tot_hospital_qty,
        general_stock: store.tot_general_qty,
        previous_month_day_es: store.previous_month_day_es,
        zero_stock_days: store.zero_stock_days,
        estimated_sale,
        total_sales_stock_qty: total_sales_stock,
        required,
        allocation: 0
    };
}

function updateWarehouseSelect(whList) {
    $("#warehouseSelect").empty();
    whList.forEach(wh => $("#warehouseSelect").append(new Option(wh, wh, true, true)));

    $("#warehouseSelect").select2({ width: "100%" });
}

function updateTable() {

    $("#tableHeader").empty();
    $("#tableBody").empty();

    const selectedWH = $("#warehouseSelect").val();
    if (!selectedWH.length) return;

    // HEADER GENERATION
    let head1 = `<tr><th rowspan="2">Item Code</th><th rowspan="2">Item Name</th>`;
    let head2 = "<tr>";

    selectedWH.forEach(wh => {
        let bg = warehouseColors[wh];
        head1 += `<th colspan="5" style="background:${bg};color:#fff;">${wh}</th>`;
        head2 += `
            <th style="background:${bg};color:#fff;">In Stock</th>
            <th style="background:${bg};color:#fff;">Est Sale</th>
            <th style="background:${bg};color:#fff;">Total Sales</th>
            <th style="background:${bg};color:#fff;">Required</th>
            <th style="background:${bg};color:#fff;">Allocation</th>
        `;
    });

    head1 += `
        <th rowspan="2">Warehouse Stock</th>
        <th rowspan="2">Global Store Stock</th>
        <th rowspan="2">Global Req Stock</th>
        <th rowspan="2">Total Stock</th>
        <th rowspan="2">Req %</th>
        <th rowspan="2">Factor</th>
    </tr>`;

    head2 += "</tr>";

    $("#tableHeader").append(head1 + head2);

    // BODY ROWS
    let baseWH = selectedWH[0];
    warehouses[baseWH].forEach(item => {

        let row = `<tr>
            <td>${item.item_code}</td>
            <td>${item.item_name}</td>
        `;

        let globalStock = 0, globalReq = 0, warehouseStock = 0;

        selectedWH.forEach(wh => {
            let whItem = warehouses[wh].find(x => x.item_code === item.item_code);

            warehouseStock = whItem.warehouse_stock;
            globalStock += whItem.stock;
            globalReq += whItem.required;

            row += `
                <td>${whItem.stock}</td>
                <td>${whItem.estimated_sale}</td>
                <td>${whItem.total_sales_stock_qty}</td>
                <td>${whItem.required}</td>
                <td>${whItem.allocation}</td>
            `;
        });

        let totalStock = warehouseStock + globalStock;
        row += `
            <td>${warehouseStock}</td>
            <td>${globalStock}</td>
            <td>${globalReq}</td>
            <td>${totalStock}</td>
            <td>${ totalStock > 0 ? Math.round(globalReq/totalStock*100) : 0 }%</td>
            <td>${ globalReq > 0 ? (warehouseStock/globalReq).toFixed(2) : 0 }</td>
        </tr>`;

        $("#tableBody").append(row);
    });

    // ---- ENABLE DATATABLE ----
    if ($.fn.DataTable.isDataTable("#inventoryTable")) {
        $("#inventoryTable").DataTable().destroy();
    }

    $("#inventoryTable").DataTable({
        dom: '<"html5buttons"B>tp',
        buttons: [
            { extend: 'copy', className: 'btn btn-secondary' },
            { extend: 'csv', className: 'btn btn-secondary' },
            { extend: 'excel', className: 'btn btn-secondary' },
            { extend: 'print', className: 'btn btn-secondary' }
        ],
        paging: true,
        ordering: true,
        scrollX: true
    });

}

});
</script>
@endpush
