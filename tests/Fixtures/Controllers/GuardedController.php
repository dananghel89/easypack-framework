<?php

namespace Tests\Fixtures\Controllers;

class GuardedController extends \Epack\Controller
{
    public static $allow = true;

    protected function before()
    {
        return static::$allow;
    }

    protected function after()
    {
        echo ' after';
    }

    public function indexAction($name = 'guest')
    {
        echo 'ran for ' . $name;
    }

}
