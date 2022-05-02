<?php

namespace App\Http\Controllers;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class IndexController extends BaseController
{
    public function __construct()
    {
        //
    }
    public function index(Request $request)
    {
        return view('index', ['store' => store()]);
    }
}
