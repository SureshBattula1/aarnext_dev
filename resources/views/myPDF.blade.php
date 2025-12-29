<!DOCTYPE html>

<html>

<head>

    <title>Laravel 10 Generate PDF Example - ItSolutionStuff.com</title>

    <style>
  /*      @page {
            margin: 50px 25px;
            
        }*/
        @page { margin: 50px; }

/*     #header { position: fixed; left: 0px; top: -180px; right: 0px; height: 150px; background-color: orange; text-align: center; }
     .footer { position: fixed; left: 0px; bottom: -30px; right: 0px; height: 50px; 
        background-color: lightblue;

         }*/

        .content {
            margin-bottom: 100px; /* Ensure space for footer */
        }

        .footer {
            position: fixed;
            bottom: 30px; /* Adjust as necessary */
            left: 0px;
            right: 0px;
            height: 50px;
            background-color: red;
            text-align: center;
           
        }

        .header {
            position: fixed;
            top: -180px;
            left: 0px;
            right: 0px;
            height: 150px;
            background-color: orange;
            text-align: center;
            line-height: 35px; /* Vertically center the text */
        }

    



        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        h4 {
            margin: 0;
        }

        .w-full {
            width: 100%;
        }

        .w-half {
            width: 50%;
        }

        .margin-top {
            margin-top: 1.25rem;
        }

        /* .footer {
            font-size: 0.875rem;
            padding: 1rem;
            background-color: rgb(241 245 249);
        } */

        table {
            width: 100%;
            border-spacing: 0;
        }

        table.products {
            font-size: 0.875rem;
        }

        table.products tr {
            background-color: rgb(96 165 250);
        }

        table.products th {
            color: #ffffff;
            padding: 0.5rem;
        }

        table tr.items {
            background-color: rgb(241 245 249);
        }

        table tr.items td {
            padding: 0.5rem;
        }

        .total {
            text-align: right;
            margin-top: 1rem;
            font-size: 0.875rem;
        }

/*        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;/ Adjust height as needed / text-align: center;
            font-size: 12px;
            line-height: 30px;/ Vertically center the text / border-top: 1px solid #ccc;/ Optional: A top border for the footer /
        }*/

      .item-container {
                page-break-inside: avoid; 

            }      


   /*     .content {
            margin: 50px 0;
            /* Account for header and footer */
        }*/
    </style>

</head>

<body>
     <div class="header">
        <!-- Optional header content -->
        <h2>Header Section</h2>
    </div>

    <div class="content">
        <table class="w-full">
            <tr>
                <td class="w-half">
                    <img src="{{ asset('laraveldaily.png') }}" alt="laravel daily" width="200" />
                </td>
                <td class="w-half">
                    <h2>Invoice ID: 834847473</h2>
                </td>
            </tr>
        </table>

        <div class="margin-top">
            <table class="products">
                <tr>
                    <th>Qty</th>
                    <th>Description</th>
                    <th>Price</th>
                </tr>
                @foreach ($data as $item)
                    <tr class="items">
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ $item['description'] }}</td>
                        <td>{{ $item['price'] }}</td>
                    </tr>
                @endforeach
            </table>
        </div>

        <div class="total">
            Total: $129.00 USD
        </div>
    </div>

    <!-- Footer section that will appear on every page -->
    <div class="footer">
        Copyright &copy; <?php echo date("Y");?> 
    </div>







</body>

</html>
