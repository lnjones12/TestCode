<?php
/*
 * Created on Jun 4, 2009
 */

require_once('Zend/Auth/Adapter/Interface.php');

class MySQLAuthAdapter implements Zend_Auth_Adapter_Interface 
{
	private $_email = '';
	private $_passcode = '';
	
	public function __construct($email, $passcode) 
	{
		$this->_email = $email;
		$this->_passcode = $passcode;
	}
	
	public function authenticate()
	{
		require_once('model/database/MysqlConnectionFactory.php');
		$db = MysqlConnectionFactory::getInstance();
		$result = $db->fetchAll('SELECT * FROM login WHERE Username = ? AND Password = ?', array($this->_email, $this->_passcode));
		
		// Row retrieved, get the whole user record
		require_once('model/auth/AuthService.php');
		$user = AuthService::getUser($result[0]['Id']);
		unset($stmt);

		if ($user == null ){
			return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, null, array('Unknown failure'));
		} else {
			return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $user, array('Authenticated.'));
		}
	}	
}

?>
