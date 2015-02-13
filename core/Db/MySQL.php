<?php
/**
 * Core Framework
 *
 * @author Velik Georgiev Chelebiev
 * @version 0.1.0
 * @copyright Copyright (c) 2015 Velik Georgiev Chelebiev
 * @license MIT License
 */

namespace core\Db;

use core\Utils\Arrays as Arrays;
use core\Filter\Filter;

class MySQL {
    /**
     * Data to establesh connection.
     * Data such as username, host, password, dbname, driver
     * @var Array 
     */
    private $connectioDataKeys = ["driver", "user", "password", "host", "dbname"];
    
    /**
     * PDO connection. Establish a new connectio with the database.
     * @var PDO
     */
    private $pdo = null;
    
    public function __construct(array $connectionData) {
        /**
         * Validate if all data is inserted
         */
        if(!Arrays::keysExists($this->connectioDataKeys, $connectionData)) {
            throw new DbException("You must specify all the connection data");
        }
        
        $data = (object)$connectionData;
        
        /**
         * Establesh PDO connection
         */
        $this->pdo = new \PDO($data->driver . ":dbname=" . $data->dbname . ";host=" . $data->host, $data->user, $data->password);
    }

    public function fetchAll($sql, $cache = false) {
        if($cache) {
            $filename = "./temp/cache/" . base64_encode($sql) . ".dbcache.php";

            if(file_exists($filename)) {
                return unserialize(file_get_contents($filename));
            } else {
                $result = $this->fetch($sql, true);
                file_put_contents($filename, serialize($result));

                return $result;
            }
        } else {
            return $this->fetch($sql, true);
        }
    }

    public function clearCache($cache) {

    }
    
    public function fetchOne($sql) {
        return $this->fetch($sql);
    }
    
    /**
     * Inserts a new record in the database.
     *
     * @param string $into The name of the table
     * @param array $data The values the should be inserted. (The array keys are the
     *                      name of the rows/attributes and the array values are the 
     *                      values for the given attribute)
     * @return 
     */
    public function insert($into, array $data) {
        if($this->pdo == null) {
            throw new DbException("No connection estableshed.");
        }
        
        // Get attribute/row names
        $attr = array_keys($data);

        // Set the query param names
        $params = implode(", ", Arrays::addPrefix($attr, ":"));

        /**
         * Build the SQL query
         * INSERT INTO()
         */
        $insertSQL = "INSERT INTO " . $into . "(" . implode(", ", $attr) . ") ";
        $insertSQL .= "VALUES(" . $params . ")";
        $statement = $this->pdo->prepare($insertSQL);
        
        /**
         * Binding the params
         */
        foreach ($data as $key => $value) {
            $statement->bindParam(":" . $key, $data[$key]);
        }

        return $statement->execute();
    }
    
    private function fetch($sql, $all = false) {
        $result = $this->execute($sql);
        
        if($result == null) {
            return null;
        }
        
        if($all == false) {
            return $result->fetch(\PDO::FETCH_ASSOC);
        } else {
            return $result->fetchAll(\PDO::FETCH_ASSOC);
        }
    }
    
    public function execute($sql) {
        if($this->pdo == null) {
            throw new DbException("No connection estableshed.");
        }

        $statement = $this->pdo->prepare($sql);
        
        if(!$statement->execute()) {
            return null;
        }
        
        return $statement;
    }
}

?>
