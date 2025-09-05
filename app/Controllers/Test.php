<?php

namespace App\Controllers;

class Test 
{
    public $test;

    public function __construct($test = 58)
    {
        $this->test = $test;
    }

    public function hello()
    {
        echo 'Hello';
    }
}