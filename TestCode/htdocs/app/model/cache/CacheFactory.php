<?php
/*
 * Created on Mar 28, 2008
 * Singleton cache factory
 */

class CacheFactory
{
	private static $_cache;
	
	private function __destruct()
	{
		self::$_cache = null;
	}
	
	public static function getInstance()
	{
		if (isset(self::$_cache)) {
			// return existing instance
			return self::$_cache;
			
		} else {
			require_once('Zend/Cache.php');
			
			// create one	
 			if (isset($GLOBALS['config'])) {
 				$config = &$GLOBALS['config'];
				$fType = $config->cache->frontName;
				$bType = $config->cache->backName;
				$fOpts = array();
				$fOpts['lifetime'] = $config->cache->frontOpts->lifetime;
				$fOpts['automatic_serialization'] = $config->cache->frontOpts->automatic_serialization;
				$fOpts['caching'] = $config->cache->frontOpts->caching;
				$bOpts = array();
				$bOpts['cache_dir'] = $config->cache->backOpts->cache_dir;
				$bOpts['hashed_directory_level'] = $config->cache->backOpts->hashed_directory_level;
				
				self::$_cache = Zend_Cache::factory($fType, $bType, $fOpts, $bOpts);
				return self::$_cache;
				
 			} else {
 				throw new Exception('Caching configuration unavailable.');
 			}			
		}
	} 
} 

?>
