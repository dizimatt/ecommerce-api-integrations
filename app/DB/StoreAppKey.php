<?php

namespace App\DB;

use Illuminate\Database\Eloquent\Model;

class StoreAppKey extends BaseModel
{
    protected $table = 'shopify_app_keys';

    public $table_mapper    = array();
    public $timestamps      = false;
}