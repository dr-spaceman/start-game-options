<?php

/**
 * Database wrapper for a MySQL with PHP tutorial
 * 
 * @copyright Eran Galperin
 * @license MIT License
 * @see http://www.binpress.com/tutorial/using-php-with-mysql-the-right-way/17
 */
class Db {
	// The database connection
	protected static $connection;
	
	/**
	 * Connect to the database
	 * 
	 * @return bool false on failure / mysqli MySQLi object instance on success
	 */
	public function connect() {
		
		// Try and connect to the database
		if(!isset(self::$connection)) {
			// Load configuration as an array. Use the actual location of your configuration file
			// Put the configuration file outside of the document root
			$config = parse_ini_file('/home/ninsiteuser/config-db.ini'); 
			self::$connection = new mysqli('localhost.squarehaven.com', $config['username'], $config['password'], $config['dbname']);
		}
	
		// If connection was not successful, handle the error
		if(self::$connection === false) {
			// Handle error - notify administrator, log to a file, show an error screen, etc.
			die("Failed to connect to database: " . self::$connection->connect_error());
			return false;
		}
		return self::$connection;
	}
	
	/**
	 * Query the database
	 *
	 * @param $query The query string
	 * @return mixed The result of the mysqli::query() function
	 */
	public function query($query) {
		// Check query
		if(!$query || $query == "") {
			return false;
		}
		
		// Connect to the database
		$connection = $this -> connect();
		
		// Query the database
		$result = $connection -> query($query);
		
		return $result;
	}
	
	/**
	 * Fetch rows from the database (SELECT query)
	 *
	 * @param $query The query string
	 * @return bool False on failure / array Database rows on success
	 */
	public function select($query, $return_object = false, $return_only_first_row = false) {
		$rows = array();
		$result = $this -> query($query);
		if($result === false) {
			return false;
		}
		if($return_object == true) {
			$rows = $result -> fetch_object();
		} else {
			while ($row = (array) $result -> fetch_assoc()) {
				$rows[] = $row;
			}
			if($return_only_first_row == true) {
				$rows = $rows[0];
			}
		}
		return $rows;
	}

	public function selectFirst($query) {
		return $this -> select($query, false, true);
	}
	
	/**
	 * Fetch the last error from the database
	 * 
	 * @return string Database error message
	 */
	public function error() {
		$connection = $this -> connect();
		return $connection -> error;
	}
	
	/**
	 * Escape value for use in a database query
	 *
	 * @param string $value The value to be quoted and escaped
	 * @return string The quoted and escaped string
	 */
	public function res($value) {
		$connection = $this -> connect();
		return $connection -> real_escape_string($value);
	}
}