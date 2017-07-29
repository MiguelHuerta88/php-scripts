<?php

/**
 * Created by PhpStorm.
 * User: MiguelHuerta
 * Date: 7/28/17
 * Time: 8:27 PM
 */
abstract class Facade
{
    public static function __callStatic($method, $args)
    {
        $instance = new static();

        if(method_exists($instance, $method)) {
            return call_user_func_array([$instance, $method], $args);
        }
        var_dump("Method:" . $method . " could not be found");
        exit();
    }
}