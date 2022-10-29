@php use Monet\Stock\Models\Product; @endphp
    <!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Stock</title>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap">
        <link rel="stylesheet" href="{{ asset('monet/stock/css/stock.css') }}" type="text/css">
    </head>
    <body>
        <div class="header">
            <h2>
                The Queens Head &copy;
            </h2>

            <h1>
                {{ $category->name }} Stock Management
            </h1>

            <p>
                This document is confidential and are intended solely for the use of The Queens Head staff. If you have
                received this document in error, please immediately disregard the document and contact 01799 521446.
            </p>
        </div>

        <div>
            <table>
                <thead>
                    <tr>
                        <th>
                            ID
                        </th>

                        <th>
                            Product
                        </th>

                        <th>
                            Amount
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>
                                {{ $product->id }}
                            </td>

                            <td>
                                {{ $product->name }} ({{ $product->unit->value }})
                            </td>

                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </body>
</html>
