<?php

namespace App\Indeed\Models;

use Illuminate\Database\Eloquent\Model;

class IndeedAPI extends Model
{
    private $_nonceLife;
    private $_nonceSalt;

    protected $table = "indeed_api";
}
