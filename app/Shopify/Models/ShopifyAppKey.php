<?php

namespace App\Shopify\Models;

//use App\DB\BaseModel;
use Illuminate\Database\Eloquent\Model;

class ShopifyAppKey extends Model
{
    protected $table = 'shopify_app_keys';

    public $table_mapper    = array();
    public $timestamps      = false;
}
