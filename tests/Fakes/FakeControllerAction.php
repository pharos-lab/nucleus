<?php

namespace Tests\Fakes;

use Nucleus\Http\Request;

class FakeControllerAction
{
    public function __invoke(Request $request)
    {
        return 'original';
    }
}