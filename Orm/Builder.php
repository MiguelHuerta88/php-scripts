<?php

// include the ConnectionFactory
include_once("Orm/BaseBuilder.php");

/**
 * Builder class. That will house all of our CRUD functions for our DB
 * 
 * @author Miguel Huerta<miguel.huerta@internetbrands.com>
 */
class Builder extends BaseBuilder
{
    /* from here on these are functions that every model will have */

    /**
     * toString function, to outputthe current sql string
     *
     * @return String
     */
    public function toString()
    {
        return $this->prepareToString();
    }

    /**
     * prepare sql for toString
     *
     * @return String
     */
    protected function prepareToString()
    {
        // explode
        $exploded = explode("?", $this->sql);

        // final sql
        $sql = '';

        foreach($exploded as $index => $section)
        {
            $sql .= $section . $this->pdoValues[$index];
        }

        return $sql;
    }

    /**
     * magic __set function.
     *
     * @param $name
     * $param $value
     *
     * @return void
     */
    protected function __set($name, $value)
    {
        // set the attribute for the value
        $this->attributes[$name] = $value;

        // set the value so we can easily retrieve it using $object->column
        $this->$name = $value;
    }
}

