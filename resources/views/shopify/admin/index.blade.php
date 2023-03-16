<!DOCTYPE html>
<html lang="en">

<head>
    <title>New Shopify Admin Index</title>
</head>

<body>
<h1>Shopify Admin Index</h1>
<div>
    <table className="services-table">
        <tr><th colSpan="3">Product Pages</th></tr>
        <tr>
            <td>
                <span>
                    <form action="/shopify/admin/products" method="get" target="_blank">
                        <input type="hidden" name="shop" value="{{$hostname}}" />
                        <button type="submit">
                            Products
                        </button>
                    </form>
                </span>
            </td></tr>
        <tr></tr>
    </table>

</div>

</body>

</html>
