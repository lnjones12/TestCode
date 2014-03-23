<?php
/*
 * Created on Jun 4, 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
require_once('AccountingAbstractController.php');

class IndexController extends AccountingAbstractController
{
		public function init()
	{
		parent::init();
	}
	
	public function indexAction()
	{
		if($this->getRequest()->isPost()){
			$username = $this->getRequest()->getPost('username', null);
			$password = $this->getRequest()->getPost('pass', null);
			$password = hash('sha256', $password);
			$this->view->username = $username;
			$this->view->password = $password;
			require_once('model/auth/AuthService.php');
			
			$user = AuthService::loginUser($username, $password);
			if(!$user){
				header('Location: /web/index/login');
			}
		}
		else{
			require_once('Zend/Auth.php');
			$auth = Zend_Auth::getInstance();
			if ($auth->hasIdentity()) {
				$user = $auth->getIdentity();
			}
			else{
				header('Location: /web/index/login');
			}
		}
		require_once('model/configuration/ConfigurationService.php');
		$this->view->configurations = ConfigurationService::getAll();
		$this->view->user = $user;
	}

	public function loginAction(){
	
	}
	
	public function logoutAction(){
		require_once('model/auth/AuthService.php');
		
		$return = AuthService::logout();
		header('Location: /web/index/login');
	}
}

?>
