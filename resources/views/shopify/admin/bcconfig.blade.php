<!DOCTYPE html>
<html lang="en">

<head>
    <title>New Shopify Admin Index</title>
</head>

<body>
<h1>Shopify Dashboard</h1>
<div>
    <h1>BigCommerce Configuration</h1>
    <table>
        <tr>
            <th style="text-align: left" }>domain</th><th style="text-align: left">api_url</th><th style="text-align: left">api_token</th>
        </tr>
        <tr>
            <td>{{$bc_store['domain']}}</td>
            <td>{{$bc_store['api_url']}}</td>
            <td>{{$bc_store['api_token']}}</td>
        </tr>
    </table>
    </div>

    </body>

    </html>
