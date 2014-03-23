<?php
/*
 * Created on Jul 16, 2008
 *
 * Static factory class that returns a parsed config object with all of the 
 * application parameters loaded from config.php.
 * 
 */

class ConfigFactory
{
	private static $_config;
	
	public static function getInstance($configFile = 'config.php')
	{
		if (isset(self::$_config)) {
			return self::$_config;
		} else {
			require_once('Zend/Config.php');
			self::$_config = new Zend_Config(require($configFile));
			return self::$_config;					
		}
	} 
	
} 

?>
