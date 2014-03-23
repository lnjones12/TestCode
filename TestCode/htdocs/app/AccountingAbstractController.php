<?php

/* Abstract controller to be subclassed by all application controllers.
 * Sets up all the core plumbing that is used in every single controller
 * (e.g. logging, etc.) 
 */

abstract class AccountingAbstractController extends Zend_Controller_Action
{
	protected $_logger;
	protected $_module;
	protected $_controller;
	protected $_action;
	protected $_user;

	public function init()
	{
		require_once('model/logging/LogFactory.php');
		
		// Just conveniences for access
		$this->_module = $this->_getParam('module');
		$this->_controller = $this->_getParam('controller');
		$this->_action = $this->_getParam('action');
		$this->_logger = LogFactory::getInstance();
		
		// Shove logger into global scope to save time when I need it for debugging.
		$GLOBALS['logger'] = $this->_logger;
		
		// If user is logged in with this session, load identity
		require_once('Zend/Auth.php');
		require_once('model/auth/users/AppUser.php');
		$auth = Zend_Auth::getInstance();
		
		if ($auth->hasIdentity()) {
			$this->_user = $auth->getIdentity();
			$this->view->authenticatedUser = $this->_user;
		} else {
			$this->_user = null;
		}		
		
		// Put a nice json version of the user in the view if logged in
		
		if (isset($this->_user) && ($this->_user != null)) {
			require_once('Zend/Json.php');
			$this->view->authenticatedUserJson = Zend_Json::encode($this->_user);
		}
		
		// If this is an AJAX module controller, we need to turn off the layout manager.
		if ($this->_module == 'ajax') {
			$this->_helper->layout->disableLayout();
		}	
		if ($this->_module == 'admin') {
			$this->_helper->layout->disableLayout();
		}	
		
	}	
}

?>
