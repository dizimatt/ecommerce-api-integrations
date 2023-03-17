<!DOCTYPE html>
<html lang="en">

<head>
    <title>New Shopify Admin Index</title>
</head>

<body>
<h1>Shopify Dashboard</h1>
<div>
    <h1>Shopify: Configuration</h1>
    <table>
        <tr>
            <th style="text-align: left" }>domain</th><th style="text-align: left">hostname</th><th style="text-align: left">access_token</th>
        </tr>
        <tr>
            <td>{{$shopify_store['domain']}}</td>
            <td>{{$shopify_store['hostname']}}</td>
            <td>{{$shopify_store['access_token']}}</td>
        </tr>
    </table>
    </div>

    </body>

    </html>
