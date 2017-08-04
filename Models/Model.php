<?php
include_once('Builder.php');

/**
 * Model class. This class will hold the main functions that a model will have.
 * 
 * @author Miguel Huerta<miguel.huerta@internetbrands.com>
 */
class Model extends Builder
{
    
    /** 
     * table name
     * 
     * @var string
     */
    protected $table_name = ''; 
    
    /**
     * constructor
     * 
     * 
     */
    public function __construct() 
    {
        //silence is golden
    }
    
    /**
     * get table name
     * 
     * @return String
     */
    public function getTableName()
    {
        return $this->table_name;
    }
    
    /**
     * get fillable array
     * 
     * @return @array
     */
    public function getFillable()
    {
        return $this->fillable;
    }
    
    /**
     * public function setFillable
     * 
     * @param array
     * @return void
     */
    public function setFillable($fillable)
    {
        $this->fillable = $fillable;
    }

}
