<?php
/*
 * Created on Jun 13, 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class HashcodeGenerator
{
	const HASH_SALT = 'i_made_a_raft_out_of_monkeys';
	
	public static function next($mixin = 'extra_hash_salt')
	{
		list($usec, $sec) = explode(' ', microtime());
		$seed = (float)$sec + ((float) $usec * 100000);
		srand($seed);
		return md5(self::HASH_SALT . $mixin . rand());
	}	
}

?>
