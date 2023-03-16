<!DOCTYPE html>
<html lang="en">

<head>
    <title>New Shopify Admin Index</title>
</head>

<body>
<h1>Shopify Dashboard</h1>
<div>
    <h1>Shopify: Indexed Products</h1>
    <table>
        <tr>
            <th style="text-align: left" }>Shop Name</th><th style="text-align: left">id</th><th style="text-align: left">title</th>
        </tr>
        @foreach($shopify_products as $product)
            <tr>
                <td>{{store()->shop_name}}</td>
                <td>{{$product['id']}}</td>
                <td>{{$product['title']}}</td>
            </tr>
        @endforeach
    </table>
</div>

</body>

</html>
