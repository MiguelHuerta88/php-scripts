<?php

/**
 * Interface class for my Builder class
 * 
 * @author Miguel Huerta<miguel.huerta@internetbrands.com>
 */
interface BuilderInterface
{
    /**
     * Get row function
     * 
     * @param type $execute
     */
    public function getRow($execute = true);
    
    /**
     * Get all rows function
     * 
     * @param type $execute
     */
    public function get($execute = true);
    
    /**
     * Select function
     * 
     * @param type $column
     */
    public function select($column = "*");
    
    /**
     * Find function.
     * 
     * @param type $id
     */
    public function find($id);
    
    
    /**
     * WHere clause function
     * 
     * @param $column
     * @param $operator
     * @param $value
     * 
     * @return
     */
    public function where(
            $column = '',
            $operator = '=',
            $value = null
    );
    
    /**
      * Where NULL function.
      *
      * @param $column string
      *
      * return
      */
    public function whereNull($column);
    
    /**
      * common function to try to remove duplicate logic in every where function
      *
      * @return
      *
      */
    public function setWhere($type = " AND ");
    
    /**
      * where not null
      *
      * @param $column
      *
      * @return
      */
    public function whereNotNull($column);
    
    /**
      * orWhere function
      *
      * @param $column
      * @param $operator
      * @param $value
      *
      * @return
      */
    public function orWhere(
        $column,
        $operator = '=',
        $value
    );
    
    /**
      * function to begin and end nested where. Must be called before begining and after to close the statment
      *
      * @return
      */
    public function nestedWhere();
    
    /**
     * limit function.
     * 
     * @param $limit int
     * 
     * @return
     */
    public function limit($limit = 1);
    
    /**
      * Count function.
      * @param $execute. to run query or just return object
      *
      * @return
      */
    public function count($execute = true);
    
    /**
      * toString function, to outputthe current sql string
      *
      * @return String
      */
    public function toRawSql();
    
    /**
      * update function.
      *
      * @param array $fields
      *
      * @return array
      */
    public function update($fields);
    
    /**
      * Insert function.
      *
      * @param array $fields
      *
      * @return array
      */
    public function insert($fields);
    
    /**
      * Delete function
      *
      * @param int $id
      *
      * @returnn array
      */
    public function delete($id);
    
    /**
     * inner join
     *
     * @param joinTable
     * @param $fieldOne
     * @param $operator
     * @param $fieldTwo
     */
    public function join(
        $joinTable,
        $fieldOne,
        $operator = '=',
        $fieldTwo
    );
    
    /**
      * Order by function
      *
      * @param column
      * @param sortType
      *
      * @return
      */
    public function orderBy($column = 'id', $sortType = 'asc');
    
    /**
     * raw. function to accept raq sql and run it
     *
     * @param sql string
     *
     *
     *
     */
    public function raw($sql);
}

