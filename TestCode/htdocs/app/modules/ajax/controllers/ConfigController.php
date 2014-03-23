<?php

require_once('AccountingAbstractController.php');

class Ajax_ConfigController extends AccountingAbstractController
{
	
	public function init()
	{
		parent::init();
	}
	
	public function getAction(){
		
		$start = $this->_getParam('start');
		$end = $this->_getParam('end');
		
		require_once('model/configuration/ConfigurationService.php');
		$result = ConfigurationService::get($start, $end);
		if ($result) {
			$this->_helper->json->sendJSON(array(
				'configurations' => $result
			));
		} else {
			$this->_helper->json->sendJSON(array(
				'status' => 'failure',
				'message' => 'Unable to get configurations.'
			));
		}
	}
	
	public function addAction(){
		
		$configname = $this->_getParam('configname');
		$hostname = $this->_getParam('hostname');
		$port = $this->_getParam('port');
		require_once('Zend/Auth.php');
		$auth = Zend_Auth::getInstance();
		$user = $auth->getIdentity();
		
		
		require_once('model/configuration/ConfigurationService.php');
		$result = ConfigurationService::add($configname, $hostname, $port, $user->username);
		if ($result) {
			$this->_helper->json->sendJSON(array(
				'status' => 'success',
				'insertid' => $result
			));
		} else {
			$this->_helper->json->sendJSON(array(
				'status' => 'failure',
				'message' => 'Unable to add configuration.'
			));
		}
	}
	
	public function deleteAction(){
		
		$id = $this->_getParam('id');
		
		
		require_once('model/configuration/ConfigurationService.php');
		$result = ConfigurationService::delete($id);
		if ($result) {
			$this->_helper->json->sendJSON(array(
				'status' => 'success',
			));
		} else {
			$this->_helper->json->sendJSON(array(
				'status' => 'failure',
				'message' => 'Unable to delete configuration.'
			));
		}
	}
	
	public function getwhereAction(){
		
		$entry = $this->_getParam('entry');
		
		
		require_once('model/configuration/ConfigurationService.php');
		$result = ConfigurationService::getWhere($entry);
		if ($result) {
			$this->_helper->json->sendJSON(array(
				'status' => 'success',
				'configurations' => $result
			));
		} else {
			$this->_helper->json->sendJSON(array(
				'status' => 'failure',
				'message' => 'Unable to get configurations.'
			));
		}
	}
	
	public function getsortedAction(){
		$namesorted = $this->_getParam('namesorted');
		$hostnameSorted = $this->_getParam('hostnamesorted');
		$portSorted = $this->_getParam('portSorted');
		$usernameSorted = $this->_getParam('usernameSorted');
		
		require_once('model/configuration/ConfigurationService.php');
		$result = ConfigurationService::getSorted($namesorted, $hostnameSorted, $portSorted, $usernameSorted);
		if ($result) {
			$this->_helper->json->sendJSON(array(
				'status' => 'success',
				'configurations' => $result
			));
		} else {
			$this->_helper->json->sendJSON(array(
				'status' => 'failure',
				'message' => 'Unable to get configurations.'
			));
		}
	}

}