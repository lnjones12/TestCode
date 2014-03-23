<?php
/*
 * Created on Jun 13, 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
class EmailService
{
	public static function sendEmail($recipients, $from, $subject, $msg, $userName, $userPassword, $lognote)
	{
		require_once('model/logging/LogFactory.php');
		$logger = LogFactory::getInstance();
	    $config = &$GLOBALS['config'];

		if ($config->environment == 'PROD') {

		require_once('Mail.php');
		require_once('Mail/mime.php');

		$emailLogger = LogFactory::getInstance('email');
    		
		$htmlBody = '<html><head><title> Competitor Wireless </title></head><body>';
		$htmlBody .= $msg;
		$htmlBody .= '</body></html>';
		
  		$headers["From"]    = $from;
  		$headers["To"]      = $recipients;
  		$headers["Subject"] = $subject;	
  		
		$params["host"] = $config->smtpAccounts->host;
		$params["port"] = $config->smtpAccounts->port;
		$params["auth"] = $config->smtpAccounts->auth;
		$params["username"] = $userName;
		$params["password"] = $userPassword; 
		
		$message = new Mail_mime(); 
		$message->setHTMLBody($htmlBody);
		$messageBody = $message->get();
		
		$headers = $message->headers($headers);
			      		
			$smtp =& Mail::factory("smtp", $params);
			
			if(!$smtp->send($recipients, $headers, $messageBody)){
				$logger->err('Failed sending email from: ' . $from . ' to ' . $recipients . ' ' . $subject . ' NOTE: ' . $lognote);
				return false;
			}
			if (!preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/',$recipients)){
					$emailLogger->debug("\n  NOT an Valid EMAIL - EmailService.php line 50 : " . $recipients);
				
				// this is the debug log for all the email we have been sent out
				$emailLogger->debug("\n  Header-From: " . $from
							 . "\n  Header-TO: " . $recipients
							 . "\n  Header-Subject: " . $subject);
			}
						
		} else {
			// If not in production, just log.
			$logger->debug('Simulating Email: ' . "\n To: " . $recipients . "\n From: " . $from
							. "\n Subject: " . $subject . "\n Message: " . $msg
							. "\n Account User Name: " . $userName
							. "\n Account Password: " . $userPassword);
		}
		
		return true;
	}
}

?>
