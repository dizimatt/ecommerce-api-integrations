<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</head>
<body>
<H1>welcome to {{ $store->shop_name }} - ({{ $store->main_store_type }} Partner Store)</H1>
<div class="row">
    <div class="col-6">
        <div class="row mx-2">
            <div class="col-12 border text-center">
                Products
            </div>
            <div class="col-12">
                <?php
                foreach($products['data'] as $product){
                   echo $product['name'],JSON_PRETTY_PRINT . "<br />";
                }
                ?>
                <a href="/bigcommerce/api/all_products?shop={{ $store->id }}">list all products</a>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row mx-2">
            <div class="col-12 border text-center">
                Orders
            </div>
            <div class="col-12">
                <a href="/bigcommerce/api/all_products?shop={{ $store->id }}">list all orders</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
