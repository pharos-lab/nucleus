<?php

namespace App\Controllers;

use Nucleus\Controller\BaseController;
use Nucleus\Http\Request;

class TestController extends BaseController
{
    public function index(Request $request)
    {
        return $this->view('home', ['name' => 'mth']);
    }

    public function api(Request $request)
    {
        return $this->json(['message' => 'Hello API']);
    }

    public function param(Request $request, $id, $post, Test $test, $test2 = 45)
    {
        return $this->view('home', ['name' => 'mth', 'test' => $test]);
    }
}
