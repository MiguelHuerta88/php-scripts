<?php

include_once("BuilderFacade.php");
include_once("ConnectionFactory.php");

/**
 * Class BaseBuilder
 */
abstract class BaseBuilder extends BuilderFacade
{
    /**
     * attribute array used for update or save calls
     *
     * @var array $attributes
     */
    protected $attributes = array();

    /**
     * connection variable
     *
     * @var connection
     */
    protected $connection;

    /**
     * fillable. Fields that are only allowed to be updated
     */
    public $fillable = array();

    /**
     * sql variable
     *
     * @param string $sl
     */
    protected $sql;

    /**
     * Primary key column. If this is different change it in the child.
     *
     * @var string
     */
    public $primaryColumn = 'id';


    /**
     * Variable to keep track if where has already been set.
     *
     * @var boolean
     */
    protected $whereUsed = false;

    /**
     * variable to keep track of nested where clause
     *
     * @var boolean
     */
    protected $nestedWhere = false;

    /**
     * variable used to check if we are at the start of nested where
     *
     * @var isStartNestedWhere
     *
     */
    protected $isStartNestedWhere = false;

    /**
     * isCount variable to tell us if we are retrieving count
     *
     * @var isCount
     */
    protected $isCount = false;

    /**
     * Order by used
     *
     * @var boolean
     */
    protected $sortUsed = false;

    /**
     * Is select already set
     */
    protected $isSelectUsed = false;

    /**
     * is limit used
     *
     * @var boolean
     */
    protected $isLimitUsed = false;

    /**
     * Prepared statement values
     *
     * @var array
     */
    protected $pdoValues = array();

    /**
     * Magic method
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // check if the function exists
        if(method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }

        // error
        print_r(
            [
                'error_found' => 'Found inside __call',
                'message' => "Method: " . $name . " could not be found",
                'called_in' => get_called_class(),
            ]
        );
        exit();
    }

    /**
     * get function to query DB
     * @param $execute. to run query or just return object
     *
     * @return
     */
    protected function getRow($execute = true)
    {
        $records = $this->limit(1)->get();

        if(is_array($records) && count($records) > 0) {
            // return first element as object
            return array_shift($records);
        }
        return null;
    }

    /**
     * get function to query DB
     * @param $execute. to run query or just return object
     *
     * @return
     */
    protected function get($execute = true)
    {
        // check that we have started the sql
        if(!$this->sql) {
            $this->select();
        } elseif(!$this->isSelectUsed) {
            $this->select();
        }

        if(!$execute) {
            return $this;
        }

        // call the query function
        return $this->query();
    }

    /**
     * select function. to start the sql. accepts comma separated list eg. id, created
     *
     * @param string $select
     *
     * @return void
     */
    protected function select($column = "*")
    {
        // if we have something begin to build the sql
        if($column) {
            $this->sql = "SELECT " . $column . " FROM " . $this->table_name . $this->sql;
        }
        $this->isSelectUsed = true;

        return $this;
    }

    /**
     * find function
     *
     * @param int|array id
     *
     * @return
     */
    protected function find($id)
    {
        // if we are passed an array
        if(is_array($id)) {
            $this->sql .= " WHERE id IN(";
            $first = true;
            foreach($id as $item) {
                if($first) {
                    $first = false;
                    $this->sql .= "?";
                } else {
                    $this->sql .= ",?";
                }
                array_push($this->pdoValues, $item);
            }
            $this->sql .= ")";

            // change whereUsed
            $this->whereUsed = true;

            return $this->get();
        }

        // if an int was passed
        if(is_int($id)) {
            // append to sql.
            $this->sql .= " WHERE id = ?";
            array_push($this->pdoValues, $id);

            // change whereUsed
            $this->whereUsed = true;

            return $this->getRow();
        }
    }

    /**
     * WHere clause function
     *
     * @param $column
     * @param $operator
     * @param $value
     *
     * @return
     */
    protected function where(
        $column = '',
        $operator = '=',
        $value = null
    ) {
        $this->setWhere();

        // begin to build where clause
        $this->sql .= $column . " ". $operator ." " . "?";
        array_push($this->pdoValues, $value);
        return $this;
    }

    /**
     * Where NULL function.
     *
     * @param $column string
     *
     * return
     */
    protected function whereNull($column)
    {
        $this->setWhere();
        $this->sql .= $column . " IS NULL ";

        return $this;
    }

    /**
     * common function to try to remove duplicate logic in every where function
     *
     * @return
     *
     */
    public function setWhere($type = " AND ")
    {
        if($this->whereUsed && !$this->isStartNestedWhere) {
            $this->sql .= $type;
        } elseif($this->isStartNestedWhere){
            $this->isStartNestedWhere = false;
        } else {
            $this->sql .= " WHERE ";
            $this->whereUsed = true;
        }
    }

    /**
     * where not null
     *
     * @param $column
     *
     * @return
     */
    protected function whereNotNull($column)
    {
        // call function to check how to set where up
        $this->setWhere();

        $this->sql .= $column . " IS NOT NULL ";
        return $this;
    }

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
    ) {
        $this->setWhere(" OR ");

        // begin to build where clause
        $this->sql .= $column . " ". $operator . " " . "?";
        array_push($this->pdoValues, $value);
        return $this;
    }

    /**
     * function to begin and end nested where. Must be called before begining and after to close the statment
     *
     * @return
     */
    public function nestedWhere()
    {
        // there is really nothing special here. Just append ( or ) to the sql
        $this->isStartNestedWhere = !$this->isStartNestedWhere && $this->nestedWhere ? true : false;

        // before we do that we need to check if the where clause is started
        if(!$this->isStartNestedWhere) {
            if($this->whereUsed) {
                $this->sql .= " AND ";
            } else {
                $this->sql .= " WHERE ";
                $this->whereUsed = true;
            }
            $this->isStartNestedWhere = true;
        }
        if($this->nestedWhere) {
            // nested where is already started. So we close it since they called the function again
            $this->sql .= " ) ";
            $this->nestedWhere = false;
        } else {
            $this->sql .= " ( ";
            $this->nestedWhere = true;
        }

        return $this;
    }

    /**
     * limit function.
     *
     * @param $limit int
     *
     * @return
     */
    public function limit($limit = 1)
    {
        if(is_int($limit) && !$this->isLimitUsed) {
            $this->sql .= " LIMIT " . $limit;

            // set the variable to true
            $this->isLimitUsed = true;
        }



        return $this;
    }

    /**
     * Count function.
     * @param $execute. to run query or just return object
     *
     * @return
     */
    public function count($execute = true)
    {
        // check if count is not already being used in the sql
        if(!$this->isCount) {
            // set it to true
            $this->isCount = true;

            // prepend the count to sql
            $this->sql = "SELECT COUNT(*) AS count FROM " . $this->table_name . $this->sql;

            // set the isSelect. since we will use the getRow function.
            $this->isSelectUsed = true;
        }

        if(!$execute) {
            return $this;
        }

        // call the getRow which should return a single object
        $row = $this->getRow();

        return $row->count;
    }

    /**
     * query protected function to run the query command and return the object
     *
     * @return
     */
    protected function query()
    {
        // end the sql statement
        $this->sql .= ";";

        // prepare and execute/ returns array
        list($stmt, $count) = $this->prepare();


        // return either a collection or empty array
        return $count > 0 ? $this->collection($stmt) : array();
    }

    /**
     * Build the collection to return.
     *
     * @param @pdo statement
     *
     * @return array collection
     */
    protected function collection($stmt)
    {
        // collection array
        $collection = array();

        // get the class that we should build our collection for
        $class = get_class($this);

        return $stmt->fetchAll(PDO::FETCH_CLASS, "$class");
    }

    /**
     * raw. function to accept raq sql and run it
     *
     * @param sql string
     *
     *
     *
     */
    public function raw($sql)
    {
        // overwrite anything in the sql at this point
        $this->sql = $sql;

        // clear our any pdoValues
        $this->pdoValues = array();

        // prepare it
        $response = $this->prepare();

        $stmt = $response['stmt'];
        $count = $response['count'];

        return $count > 0 ? $stmt->fetchAll() : array();
    }

    /**
     * update function.
     *
     * @param array $fields
     *
     * @return array
     */
    protected function update($fields)
    {
        // before we do anything we check that they have a fillable array set.
        if(empty($this->fillable))
        {
            // in order to do a mass assignment we need a fillable array set.
            $reply = "You are trying to update but have not set a fillable array in " . __CLASS__;
            return [false, $reply];
        }

        // now we must remove any fields in array that are not in fillable
        $fieldsToUse = $this->checkAgainstFillable($fields);
        if(empty($fieldsToUse)) {
            // return back false. We have nothing to insert here
            $reply = "You passed in invalid fields to be updated. Inside " . __CLASS__;
            return [false, $reply];
        }

        // pull the id of the record to update
        $id = isset($fields['id']) ? $fields['id'] : null;

        if(!$id) {
            $reply = 'You are trying to update a record but the DEV forgot to pass in the id for the record that is being updated.';
            return [false, $reply];
        }
        $inserted = $this->buildSqlAndUpdate($fieldsToUse, $id);

        if(!$inserted) {
            $reply = "We could not update your data into table";
            return [false, $reply];
        }
        return [true, 'We Successfully updated the data.'];

    }

    /**
     * Insert function.
     *
     * @param array $fields
     *
     * @return array
     */
    protected function insert($fields)
    {
        // before we do anything we check that they have a fillable array set.
        if(empty($this->fillable))
        {
            // in order to do a mass assignment we need a fillable array set.
            $reply = "You are trying to insert but have not set a fillable array in " . __CLASS__;
            return [false, $reply];
        }

        // now we must remove any fields in array that are not in fillable
        $fieldsToUse = $this->checkAgainstFillable($fields);
        if(empty($fieldsToUse)) {
            // return back false. We have nothing to insert here
            $reply = "You passed in invalid fields to be inserted. Inside " . __CLASS__;
            return [false, $reply];
        }

        $inserted = $this->buildSqlAndInsert($fieldsToUse);

        if(!$inserted) {
            $reply = "We could not insert your data into table";
            return [false, $reply];
        }
        return [true, 'We Successfully inserted your data.'];
    }

    /**
     * function to build sql string and update into DB
     *
     * @param $fieldsToUse
     *
     * @return array
     */
    protected function buildSqlAndUpdate($fieldsToUse, $id)
    {
        $started = false;

        // begin to build sql to insert
        $this->sql = "UPDATE " . $this->table_name . " SET ";
        $strFields = "";
        // loop through fieldsTouse to begin to build complete sql
        foreach($fieldsToUse as $index => $field) {
            if($started) {
                $strFields .= ", " .$index . "= ? ";
            } else {
                $started = true;
                $strFields .= $index. "= ?  ";
            }
            array_push($this->pdoValues, $field);
        }
        //var_dump($this->pdoValues);
        $this->sql .= $strFields . " WHERE " . $this->primaryColumn . " = ?;";
        array_push($this->pdoValues, $id);


        //prepare the statement
        list($stmt, $count) = $this->prepare();

        // return true or false depending if statment did anything
        return $count > 0 ? true : false;
    }


    /**
     * function to build sql string and insert into DB
     *
     * @param $fieldsToUse
     *
     * @return array
     */
    protected function buildSqlAndInsert($fieldsToUse)
    {
        $started = false;

        // begin to build sql to insert
        $this->sql = "INSERT INTO " . $this->table_name;
        $strFields = "";
        $insertFields = "";
        // loop through fieldsTouse to begin to build complete sql
        foreach($fieldsToUse as $index => $field) {
            if($started) {
                $insertFields .= "," . $index;
                $strFields .= ", ?";
            } else {
                $started = true;
                $insertFields .= $index;
                $strFields .= "? ";
            }
            array_push($this->pdoValues, $field);
        }

        // complete the finalized query
        $this->sql .= " (" . $insertFields . ") VALUES (" . $strFields .");";

        //var_dump($this->sql);
        //var_dump($this->pdoValues);
        //prepare the statement
        list($stmt, $count) = $this->prepare();

        // return true or false depending if statment did anything
        return $count > 0 ? true : false;
    }

    /**
     * Delete function
     *
     * @param int $id
     *
     * @returnn array
     */
    protected function delete($id)
    {
        // begin to build our query;
        $this->sql = "DELETE FROM " . $this->table_name . " WHERE " . $this->primaryColumn . " = ? ;";

        // push onto pdoValues array
        array_push($this->pdoValues, $id);

        list($stmt, $count) = $this->prepare();

        if($count) {
            // we successfully deleted the record
            $reply = "We successfully deleted the record.";
            return [true, $reply];
        } else {
            // failed send back info
            $reply = "We could not remove your selected record.";
            return [false, $reply];
        }

    }

    /**
     * Prepare function.
     * @return array
     */
    protected function prepare()
    {
        // if connection is null. connect
        if(!$this->connection)
        {
            $this->connection = ConnectionFactory::connect();
        }

        // create stmt and prepare it and return back array
        $stmt = $this->connection->prepare($this->sql);

        $stmt->execute($this->pdoValues);

        // reset all variables
        $this->resetVariables();

        return [$stmt, $stmt->rowCount()];
    }

    /**
     * reset all of our variables that we used for each query
     *
     * @return void
     */
    protected function resetVariables()
    {
        // reset sql
        $this->sql = null;

        // reset where used
        $this->whereUsed = false;

        // reset nested where
        $this->nestedWhere = false;

        // reset is start nested where
        $this->isStartNestedWhere = false;

        // reset isCount
        $this->isCount = false;

        // reset sort used
        $this->sortUsed = false;

        // reset is select used
        $this->isSelectUsed = false;

        // reset pdoValues
        $this->pdoValues = array();

    }

    /**
     * Function to check our fillable array and only use the fields that we have set
     *
     * @param array $fields
     *
     * @return array
     */
    protected function checkAgainstFillable($fields)
    {
        $use = array();

        foreach($this->fillable as $index => $field) {
            if(array_key_exists($field, $fields)){
                $use[$field] = $fields[$field];
            }
        }
        return $use;
    }


    /** our join functions **/

    /**
     * inner join
     *
     * @param joinTable
     * @param $fieldOne
     * @param $operator
     * @param $fieldTwo
     */
    protected function join(
        $joinTable,
        $fieldOne,
        $operator = '=',
        $fieldTwo
    ){
        $this->sql .= " JOIN " . $joinTable . " ON " . $fieldOne . $operator . $fieldTwo;
        return $this;
    }

    /**
     * Order by function
     *
     * @param column
     * @param sortType
     *
     * @return
     */
    public function orderBy($column = 'id', $sortType = 'asc')
    {
        if(!$this->sortUsed) {
            $this->sortUsed = true;

            // append to the sql
            $this->sql .= " ORDER BY " . $column . " " . $sortType;
        } else {
            $this->sql .= ", " . $column . " " . $sortType;
        }
        return $this;
    }

    /**
     * Save function.
     *
     * @return array
     */
    public function save()
    {
        // save can be called on either new models or when we retrieve by id.
        if(isset($this->attributes[$this->primaryColumn]) && $this->find((int)$this->attributes[$this->primaryColumn]))
        {
            // update
            // @note figure out why this give us back cannot update data but the DB
            // has updated data
            return $this->update($this->attributes);
        }
        // else we didn't find a matching model which means user wants to insert
        return $this->insert($this->attributes);
    }
}