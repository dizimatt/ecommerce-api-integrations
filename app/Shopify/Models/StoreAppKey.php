<?php

namespace App\Shopify\Models;

use App\DB\BaseModel;

class StoreAppKey extends BaseModel
{
    protected $table = 'shopify_app_keys';

    public $table_mapper    = array();
    public $timestamps      = false;
}
