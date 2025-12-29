<!DOCTYPE html>
<html>
<head>
    <title>Stock Comparison</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 50px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
        }

        .matched {
            background-color: #d4edda;
            color: #155724;
        }

        .unmatched {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <h1 style="text-align:center;">Warehouse Stock Comparison (Web vs SAP)</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Warehouse Code</th>
                <th>Web Quantity</th>
                <th>SAP Quantity</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                <tr class="{{ strtolower($item['status']) }}">
                    <td>{{ $item['id'] }}</td>
                    <td>{{ $item['warehouse_code'] }}</td>
                    <td>{{ number_format($item['web_quantity']) }}</td>
                    <td>{{ rtrim(rtrim($item['sap_quantity'], '0'), '.') }}</td>
                    <td>{{ $item['status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
