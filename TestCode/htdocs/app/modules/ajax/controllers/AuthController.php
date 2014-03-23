<?php
/*
 * Created on Jun 8, 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
require_once('CompetitorWirelessAbstractController.php');

class Ajax_AuthController extends CompetitorWirelessAbstractController
{
	public function init()
	{
		parent::init();
	}
	
	public function changepasswordAction()
	{
		$email = $this->_getParam('email');
		$pass1 = $this->_getParam('pass1');
		$token = $this->_getParam('token');
		
		// Validate email
		
		require_once('model/auth/AuthService.php');
		$user = AuthService::getUserByEmail($email);
		
		if (!$user) {
			$this->_helper->json->sendJSON(array(
				'status' => 'failure',
				'message' => 'There is no user with the email ' . $email
			));
		}		
		
		// Validate password token
		
		if (!AuthService::validatePasswordToken($token, $email)) {
			$this->_helper->json->sendJSON(array(
				'status' => 'failure',
				'message' => 'Email address / token combination invalid.'
			));
		}
		
		// Update password
		
		require_once('model/database/OdbcConnectionFactory.php');
		$dbh = OdbcConnectionFactory::getInstance();
		
		if (!AuthService::changeUserPassword($user->id, $pass1)) {
			odbc_rollback($dbh);
			
			$this->_helper->json->sendJSON(array(
				'status' => 'failure',
				'message' => 'Unable to update user record.'
			));
		}
		
		odbc_commit($dbh);
		
		$this->_logger->info('Changed password for user ' . $user->id . ' ' . $user->email);
		
		$this->_helper->json->sendJSON(array(
			'status' => 'success',
			'message' => 'Password has been changed.'
		));
	}
	
	public function changepwrequestAction()
	{
		$config = $GLOBALS['config'];
		$email = $this->_getParam('email');

		require_once('model/auth/AuthService.php');
		
		if (!AuthService::getUserByEmail($email)) {
			$this->_helper->json->sendJSON(array(
				'status' => 'failure',
				'message' => 'There is no user with the email address ' . $email
			));
		}

		require_once('model/util/HashcodeGenerator.php');
		$token = substr(HashcodeGenerator::next(), 0, $config->tracking->hashcodeLength);
		
		require_once('model/database/OdbcConnectionFactory.php');
		$dbh = OdbcConnectionFactory::getInstance();
		
		require_once('model/auth/PasswordToken.php');
		$pToken = new PasswordToken();
		$pToken->token = $token;
		$pToken->email = $email;
		
		if (AuthService::addPasswordToken($pToken)) {
			// TODO: yet again, fix the email thing
			odbc_commit($dbh);
			require_once('model/util/EmailService.php');
			$config = &$GLOBALS['config'];
			$subject = 'Competitor Wireless Password Change Request';
			$msg = 'To change your password, click the following link: '
					. '<a href="' . $config->websiteUrl . '/web/auth/changepassword/token/'
					. $token . '">Change Password</a>';

			$fromEmail = $config->smtpAccounts->account;
			EmailService::sendEmail($email, 
								$fromEmail->email, 
								$subject, 
								$msg, 
								$fromEmail->userName, 
								$fromEmail->password, 
								'Password Change Request');			
			
			
			$this->_helper->json->sendJSON(array(
				'status' => 'success'
			));
		} else {
			$this->_helper->json->sendJSON(array(
				'status' => 'failure',
				'messsage' => 'Failed creating password token record.'
			));
		}
	}
	
	public function registeruserAction()
	{
		require_once('model/auth/users/AppUser.php');
		$user = new AppUser();
		$user->email = $this->_getParam('email1');
		$user->firstName = $this->_getParam('firstName');
		$user->lastName = $this->_getParam('lastName');
		$user->postalCode = $this->_getParam('postalCode');
		$user->SMSCarrierId = $this->_getParam('carrierId');
		$user->SMSPhone = $this->_getParam('phone1') . $this->_getParam('phone2')
							. $this->_getParam('phone3');
		$user->passcode = $this->_getParam('passcode1');
		$user->gender = $this->_getParam('gender');
		$user->acceptedTerms = ($this->_getParam('acceptedTerms') == 'true') ? 1 : 0;
		
		// TODO: last minute form checking
		require_once('model/database/OdbcConnectionFactory.php');
		require_once('model/auth/AuthService.php');
		
		$dbh = OdbcConnectionFactory::getInstance();
		
		if ($user = AuthService::addUser($user)) {
			odbc_commit($dbh);
			$this->_logger->info('Registered new user: ' . var_export($user, true));
			$this->_helper->json->sendJSON(array(
				'status' => 'success',
				'message' => 'User record added.',
				'user' => $user
			));
		} else {
			odbc_rollback($dbh);
			$this->_helper->json->sendJSON(array(
				'status' => 'failure',
				'message' => 'Failed adding new user record.'
			));
		}
	}
	
	public function updateuserAction()
	{
		// Check to make sure the user is not trying to update a user record that
		// is not them.
		
		$userId = $this->_getParam('userInfoUserId');
		
		if ($this->_user->id != $userId) {
			$this->_helper->json->sendJSON(array(
				'status' => 'failure',
				'message' => 'Permission denied.'
			));
		}
		
		// Populate user object and update
		
		require_once('model/auth/users/AppUser.php');
		$user = new AppUser();
		$user->id = $userId;
		$user->SMSPhone = $this->_getParam('userInfoPhone1') . $this->_getParam('userInfoPhone2') 
						. $this->_getParam('userInfoPhone3');
		$user->email = $this->_getParam('userInfoEmail');
		$user->lastName = $this->_getParam('userInfoLastName');
		$user->firstName = $this->_getParam('userInfoFirstName');
		$user->SMSCarrierId = $this->_getParam('userInfoCarrierId');
		$user->postalCode = $this->_getParam('userInfoPostalCode');
		$user->gender = $this->_getParam('userInfoGender');
		$user->acceptedTerms = ($this->_getParam('userInfoAcceptedTerms') == 'yes') ? 1 : 0;
		
		require_once('model/auth/AuthService.php');
		require_once('model/database/OdbcConnectionFactory.php');
		$dbh = OdbcConnectionFactory::getInstance();
		
		if (!AuthService::updateUser($user)) {
			$this->_logger->err('Failed updating user record for ' . $user->id . ' ' . $user->email);
			odbc_rollback($dbh);
			$this->_helper->json->sendJSON(array(
				'status' => 'failure',
				'message' => 'Failed updating user record.'
			));
		} else {
			$this->_logger->info('Updated user record for user ' . $user->id . ' ' . $user->email);
		}

		// Passed all updates, commit transaction
		odbc_commit($dbh);
		
		// We've just updated our account, we need to update the authentication credentials.
		// We skip the username/password thing here...
		require_once('Zend/Auth.php');
		require_once('model/auth/AuthRefreshAdapter.php');
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();		
		$auth->authenticate(new AuthRefreshAdapter($user));		
		$this->_logger->info('Updated user info for: ' . $user->id . ' ' . $user->email);
		
		$this->_helper->json->sendJSON(array(
			'status' => 'success',
			'message' => 'User record updated.',
			'user' => $user
		));		
	}
	
	public function loginAction()
	{
		$email = $this->_getParam('email');
		$passcode = $this->_getParam('passcode');
		require_once('model/auth/AuthService.php');
		require_once('Zend/Json.php');
		require_once('model/auth/users/AppUser.php');
		$user = AuthService::loginUser($email, $passcode);
		
		if ($user != null) {
			$this->_logger->info('Logged in user: ' . $user->id . ' ' . $user->email);
			$_SESSION['zend_auth']['firstName'] = $user->firstName;
			$_SESSION['zend_auth']['lastName'] = $user->lastName;
			$_SESSION['zend_auth']['postalCode'] = $user->postalCode;
			$_SESSION['zend_auth']['gender'] = $user->gender;
			$_SESSION['zend_auth']['email'] = $user->email;
			$_SESSION['zend_auth']['id'] = $user->id;
			$this->_helper->json->sendJSON(array(
					'status' => 'success',
					'authenticatedUser' => $user,
					'authenticatedUserJson' => Zend_Json::encode($user)
			));
			
		} else {
			$this->_helper->json->sendJSON(array(
					'status' => 'failure',
					'message' => 'Incorrect email and password.'
			));
		}
	}
	
	public function emailexistsAction()
	{
		$email = $this->_getParam('email');
		require_once('model/auth/AuthService.php');
		$user = AuthService::getUserByEmail($email);
		
		if ($user == null) {
			$this->_helper->json->sendJSON(array(
				'status' => 'success',
				'duplicate' => false
			));
		} else {
			$this->_helper->json->sendJSON(array(
				'status' => 'success',
				'duplicate' => true
			));
		}
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
		
		$this->_helper->json->sendJSON(array(
			'status' => 'success',
			'message' => 'User logged off.'
		));
	}
}

?>
