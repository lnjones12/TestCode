<?php
/*
 * Created on Jun 19, 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once('CompetitorWirelessAbstractController.php');

class Ajax_IndexController extends CompetitorWirelessAbstractController
{
	public function init()
	{
		parent::init();
	}
	
	public function feedbackAction()
	{
		$config = &$GLOBALS['config'];
		$name = $this->_getParam('trackername');
		$email = $this->_getParam('contactEmail');
		$event_name = $this->_getParam('eventName');
		$reason = $this->_getParam('contactReason');
		$message = $this->_getParam('contactMessage');
		$toEmail = $config->smtpAccounts->general;
		
		$emailBody = file_get_contents($config->documentRoot . '/app/htmlstubs/feedbackEmail.htm');
		$find = array('{SEND_DATE}', '{SENDER_EMAIL}', '{MESSAGE_SUBJECT}', '{EVENT_NAME}', '{MESSAGE_BODY}', '{AUTHENTICATED_USER_INFO}');
		
		if ($this->_user != null) { 
			$authInfo = $this->_user->id . '<br/>' . $this->_user->firstName . ' '
						. $this->_user->lastName . '<br/>' . $this->_user->email . '<br/>';
		} else {
			$authInfo = $name;
		}
		
		$replace = array(date('m/d/Y'), $email, $reason, $event_name, $message, $authInfo);
		$emailBody = str_replace($find, $replace, $emailBody); 

		$config = &$GLOBALS['config'];
		require_once('model/util/EmailService.php');
		EmailService::sendEmail($toEmail->email, $email, 
								'Competitor Wireless Feedback - ' . $reason,
								$emailBody, $toEmail->userName, 
								$toEmail->password, 'Competitor Wireless feedback');

		$this->_helper->json->sendJSON(array(
			'status' => 'success'
		));
	}
}

?>
