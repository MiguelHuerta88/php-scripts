<?php

/**
 * Connection factory class
 * 
 * @author Miguel Huerta<miguel.huerta@internetbrands.com>
 */
class ConnectionFactory
{
    /**
     * path to the pdo_config.xml file. Should be adjusted based on system.
     * 
     * @var string
     */
    public static $filepath = "/Applications/MAMP/htdocs/orm_update/php-scripts/pdo_config.xml";
    
    /**
     * function to connect to our PDO.
     * 
     * @return PDO instance
     */
    public static function connect()
    {   
        // check the filepath. If empty output error and end
        if(empty(self::$filepath))
        {
            var_dump("The path to the pdo config is empty. We are ending this now!");
            exit();
        }
        
        // try to load the xml
        $xml = simplexml_load_file(self::$filepath);

        // pull the data and store it
        $driver = $xml->driver;
        $host = $xml->host;
        $user = empty($xml->user) ? '' : $xml->user;
        $pass = empty($xml->pass) ? '' : $xml->pass;
        $db = $xml->db;

        // build the string for pdo
        $dsn = $driver. ":host=" . $host . ";dbname=" . $db;
        // try to set up PDO
        try{
            return new PDO($dsn, $user, $pass);
        } catch (Exception $ex) {
              var_dump("Problem. Trying to set up PDO. Quitting");
              exit();
        }
    }
}

