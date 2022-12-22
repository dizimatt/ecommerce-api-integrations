<?php

namespace App\Shopify\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyProduct extends Model
{
    protected $table = 'shopify_products';
    protected $fillable = ['id'];

}
