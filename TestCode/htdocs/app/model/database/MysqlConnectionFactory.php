<?php
 
require_once 'Zend/Db/Adapter/Pdo/Mysql.php';

 class MysqlConnectionFactory
 {
 	private static $_mysqli;
 	
 	public static function getInstance() 
 	{
 		if (isset(self::$_mysqli)) {
 			return self::$_mysqli;
 		} else {
 			if (isset($GLOBALS['config'])) {
 				$config = &$GLOBALS['config'];
 
 				self::$_mysqli = new Zend_Db_Adapter_Pdo_Mysql(array(
					'host'     => $config->database->mysql->server,
					'username' => $config->database->mysql->UID,
					'password' =>  $config->database->mysql->PWD,
					'dbname'   =>  $config->database->mysql->Database
				));
 											
 				if (!self::$_mysqli) {
  					throw new MysqlException('Failed to connect to database.');
 				}	
 				
 				//self::$_mysqli->autocommit(false);
 				//self::$_mysqli->select_db($config->database->mysql->schema);
 				return self::$_mysqli;
 				
 			} else {
 				throw new MysqlException('Database configuration unavailable.');
 			}
 		}
 	} 
 	
 	public function __destruct() 
 	{
 		self::$_mysqli->closeConnection();
 	}
 	
} 

// Shell exception for MySQL exceptions all around

class MysqlException extends Exception {}

?>
