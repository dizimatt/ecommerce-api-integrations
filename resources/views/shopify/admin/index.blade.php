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
                    <!--
                    <form action="/shopify/admin/products" method="get">
                        <input type="hidden" name="shop" value="{{$shop}}" />
                        <button type="submit">
                            Products
                        </button>
                    </form>
                    -->
                    <form action="https://{{$shop}}/apps/proxy/admin/config" method="get">
                        <button type="submit">
                            Config
                        </button>
                    </form>
                    <form action="https://{{$shop}}/apps/proxy/admin/bcconfig" method="get">
                        <button type="submit">
                            Big Commerce Config
                        </button>
                    </form>
                </span>
            </td></tr>
        <tr></tr>
    </table>

</div>

</body>

</html>
