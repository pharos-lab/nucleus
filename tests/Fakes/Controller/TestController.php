<?php

namespace Tests\Fakes\Controller;

use Nucleus\Controller\BaseController;
use Nucleus\Http\Request;

class TestController extends BaseController
{
    public function routeWithoutMiddleware(Request $request)
    {
        return 'route without middleware ok!';
    }
}
