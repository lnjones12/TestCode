<?php
/*
 * Created on Jun 20, 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
require_once('model/auth/users/AppUser.php');
require_once('Zend/Auth/Adapter/Interface.php');

class AuthRefreshAdapter implements Zend_Auth_Adapter_Interface
{
	/*
	 * This class is used when a logged-in user updates their info.  We need to
	 * use this trick to get the new auth info loaded into the authenticated
	 * login without using password
	 */
	 
	private $user;
	
	function __construct($user) 
	{ 
		$this->user = $user; 
	}
	
	function authenticate() 
	{
		return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $this->user, array('Authenticated.'));
	}
}
?>
