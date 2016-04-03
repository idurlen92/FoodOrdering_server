<?php

class DatabaseHandler {

    /**@var PDO */
    private static $pdo_object = null;
	/**@var string */
	public static $lastError = null;

	/**@var DatabaseHandler*/
	private static $instance = null;



    private function __construct($filePath){
	    if(self::$pdo_object === null)
            self::connect($filePath);
    }


	private function __clone(){}


    public function connect($filePath){
        $host = 'localhost';
        $dbName = 'nikolam1_food_ordering';
        $username = 'root';
        $password = '';

        try{
            self::$pdo_object = new PDO("mysql:host={$host};dbname={$dbName};", $username, $password, array(
	                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
	            ));
        }
        catch(PDOException $e){
            echo "PDOException: " . $e->getMessage();
        }
    }


    public function  disconnect(){
        self::$pdo_object = null;
	    self::$lastError = null;
	    self::$instance = null;
    }


    /**
     * Singleton: gets the instance of DatabaseHandler class.
     * @return DatabaseHandler
     */
    public static function getInstance($filePath = 'config.ini'){
        if(self::$instance === null)
	        self::$instance = new self($filePath);
        return self::$instance;
    }



	/**
	 * Returns last error message from catched exception.
	 * @return string | null
	 */
	public static function getLastError(){
		$error = self::$lastError;
		self::$lastError = null;
		return $error;
	}


	public function beginTransaction(){
		return self::$pdo_object->beginTransaction();
	}


	public function rollBackTransaction(){
		return self::$pdo_object->rollBack();
	}

	public function commitTransaction(){
		return self::$pdo_object->commit();
	}


    /**
     * Executing queries with binding parameters and expecting data returned.
     * * Note: if no arguments expected pass empty array!
     * @param $query
     * @param $arguments
     * @return null | array
     */
    public function executeSelect($query, $arguments){
        $data = null;
        try{
            $pdoStatement = self::$pdo_object->prepare($query);
            $pdoStatement->execute($arguments);
            $data = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
            $pdoStatement = null;
        }
        catch(PDOException $e){
            //echo "PDO: " . $e->getMessage();
	        self::$lastError = $e->getMessage();
        }
        return $data;
    }


    /**
     * Executing non-select SQL statements.
     * Note: if no arguments expected pass empty array!
     * @param $statement
     * @param $arguments
     * @return bool
     */
    public function execNonSelect($statement, $arguments){
        $success = true;
        try{
	        //self::$pdo_object->
            $pdoStatement = self::$pdo_object->prepare($statement);
            $success = $pdoStatement->execute($arguments);
            if($success == false){
	            //var_dump($pdoStatement->errorInfo());
	            $error = $pdoStatement->errorInfo();
	            self::$lastError = $error[2];
            }
            $pdoStatement = null;
        }
        catch(PDOException $e){
            $success = false;
	        self::$lastError = $e->getMessage();
            //echo "PDO: " . $e->getMessage();
        }
        return $success;
    }



	public function getLastInsertId(){
		return self::$pdo_object->lastInsertId();
	}



}