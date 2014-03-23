<?php

require_once('model/auth/users/AppUserDAO.php');

class AuthService
{
	public static function getUser($id)
	{
		$dao = new AppUserDAO();
		return $dao->get($id);
	}

	public static function addUser($user)
	{
		$dao = new AppUserDAO();
		return $dao->add($user);
	}
	
	public static function loginUser($email, $passcode)
	{
		require_once('Zend/Auth.php');
		require_once('model/auth/MySQLAuthAdapter.php');
		
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		$result = $auth->authenticate(new MySQLAuthAdapter($email, $passcode));

		if ($auth->hasIdentity()) {
			$user = $auth->getIdentity();
			return $user;
		} else {
			return false;
		}
	}
	
	public static function logout(){
		require_once('Zend/Auth.php');
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		return false;
	}
}

?>
