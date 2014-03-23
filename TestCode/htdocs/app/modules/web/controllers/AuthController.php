<?php

require_once('CompetitorWirelessAbstractController.php');

class AuthController extends CompetitorWirelessAbstractController
{
	public function init()
	{
		parent::init();
	}

	public function forgotpasswordAction()
	{
	}
	
	public function changepasswordAction()
	{
		$token = $this->_getParam('token');
		$this->view->token = $token;
	}

	public function loginAction()
	{
//		$email = $this->_getParam('email');
//		$passcode = $this->_getParam('passcode');
//		$referer = $this->_getParam('referer');
//		require_once('model/auth/AuthService.php');
//		$user = AuthService::loginUser($email, $passcode);
//		
//		if ($user != null) {
//			$this->_logger->info('Logged in user: ' . $user->id . ' ' . $user->email);
//		} else {
//			$this->_logger->debug('Failed login: ' . $email . ' / ' . $passcode);
//		}
//		
//		if ($referer == null) { $referer = '/'; }
//		$this->_redirect($referer);
	}
	
	public function logoutAction()
	{
		require_once('Zend/Auth.php');
		
		if (isset($this->_user) && ($this->_user != null)) {
			$this->_logger->info('Logging off user: ' . $this->_user->id . ' ' . $this->_user->email);
		}
		
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		unset($this->_user);
		$this->_redirect('/');
	}
	
}

?>
