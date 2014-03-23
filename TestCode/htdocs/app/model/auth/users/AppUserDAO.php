<?php

require_once('model/database/MysqlConnectionFactory.php');
require_once('model/auth/users/AppUser.php');



class AppUserDAO
{
	
	public function get($id)
	{
		$db = MysqlConnectionFactory::getInstance();
		$result = $db->fetchAll('SELECT * FROM login where Id = ?', $id);
		$db->closeConnection();
		$user = new AppUser();
		$user->id = $result[0]['Id'];
		$user->username = $result[0]['Username'];
		$user->passcode = $result[0]['Password'];
		return $user;		
	}
	
	
}

?>
