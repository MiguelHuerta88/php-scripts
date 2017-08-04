<?php
include_once("Facade.php");

/**
 * Created by PhpStorm.
 * User: MiguelHuerta
 * Date: 7/28/17
 * Time: 8:29 PM
 */
class BuilderFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Builder';
    }
}