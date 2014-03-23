<?php
/*
 * Created on Jul 14, 2008
 * Front Controller for Zend Framework
 * All PHP and dynamic content is routed through this setup controller.
 */

	// TODO: PHP Documentor

	// Set up PHP base directory.
	ini_set('include_path', ini_get('include_path') 
				. PATH_SEPARATOR . dirname(__FILE__) . '/lib' 
				. PATH_SEPARATOR . dirname(__FILE__) . '/app');
	
	// Hopefully sets cookies to HTTPONLY
	require_once('Zend/Session.php');
	Zend_Session::setOptions(array('cookie_httponly' => 'On'));
	
	
	// Create Front Controller instance and configure
	require_once('Zend/Loader.php');
	Zend_Loader::loadClass('Zend_Controller_Front');
	$front = Zend_Controller_Front::getInstance();
	
	// Load configuration
	require_once('model/config/ConfigFactory.php');
	$config = ConfigFactory::getInstance();
	$GLOBALS['config'] = &$config;
	
	// Set up 'modules', the MAJOR overarching concerns of the application.
	foreach ($config->modules as $path => $identifier) {
		$front->addControllerDirectory($path, $identifier);
	}

	// Set the default module/controller/action to handle default/index page
	$front->setDefaultModule('web');
	$front->setDefaultControllerName('index');
	$front->setDefaultAction('index');

	// Miscellaneous configuration
	date_default_timezone_set($config->timeZone);	
	error_reporting($config->errors);
	
	// Fire up the Layout Engine and Controller Dispatcher
	require_once('Zend/Layout.php');
	$options = array('layout' => 'default', 'layoutPath' => 'app/layouts', 'contentKey' => 'CONTENT');
	Zend_Layout::startMvc($options);
	
	$front->throwExceptions($config->exceptions);
	$front->returnResponse(true);
	$response = $front->dispatch();

	if ($response->isException()) {
		// This is an exception condition
		// TODO: Figure out how you want to render this page, LOG it, etc.
		$exceptions = $response->getException();
		$response->sendHeaders();
		
		foreach ($exceptions as $e) {
			echo "Exception: " . $e->getMessage() . " <br/>\n";
		}
		
		$response->outputBody();
		
	} else {
		// This is a success condition
		$response->sendHeaders();
		$response->outputBody();
	}
?>
