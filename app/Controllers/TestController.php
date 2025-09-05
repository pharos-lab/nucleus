<?php

namespace App\Controllers;

use Nucleus\View\View;

class TestController
{
    public function index($request)
    {
        return View::make('home', ['name' => 'mth']);
    }

    public function about()
    {
        return "Page About via TestController";
    }
}
