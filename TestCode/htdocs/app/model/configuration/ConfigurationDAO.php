<?php

require_once('model/database/MysqlConnectionFactory.php');
require_once('model/wordpress/Leader.php');

class ConfigurationDAO
{
	public function get($start, $end)
	{
		$db = MysqlConnectionFactory::getInstance();
		$result = $db->fetchAll('SELECT `name`, `hostname`, `port`, `username` FROM configuration where Id >= ? and Id <= ?', array($start, $end));
		$db->closeConnection();
		return $result;	
	}
	
	public function add($configname, $hostname, $port, $username){
		$db = MysqlConnectionFactory::getInstance();
		$data = array(
			'name'      => $configname,
			'hostname' => $hostname,
			'port'      => $port,
			'username' => $username
		);
		 
		$db->insert('configuration', $data);
		$id = $db->lastInsertId();
		return $id;
	}

	public function getAll(){
		$db = MysqlConnectionFactory::getInstance();
		$result = $db->fetchAll('SELECT distinct `name` FROM configuration order by `name`');
		$db->closeConnection();
		return $result;	
	}

	public function delete($id)
	{
		$db = MysqlConnectionFactory::getInstance();
		$result = $db->delete('configuration', "`name` = '".$id."'");
		$db->closeConnection();
		return $result;	
	}
	
	public function getWhere($entry){
		$db = MysqlConnectionFactory::getInstance();
		$result = $db->fetchAll('SELECT DISTINCT `name`, hostname, `port`, COUNT(`username`) AS nameCount FROM configuration GROUP BY `name`, hostname, `port` HAVING nameCount > ?', $entry);
		$db->closeConnection();
		return $result;	
	}
	
	public function getSorted($namesorted, $hostnameSorted, $portSorted, $usernameSorted){
		$db = MysqlConnectionFactory::getInstance();
		$orderbyClause = '';
		if($nameSorted == 'true'){
			$orderbyClause .= '`name`,';
		}
		if($hostnameSorted == 'true'){
			$orderbyClause .= '`hostname`,';
		}
		if($portSorted == 'true'){
			$orderbyClause .= '`port`,';
		}
		if($usernameSorted == 'true'){
			$orderbyClause .= '`username`,';
		}
		if($orderbyClause != ''){
			$orderbyClause = substr($orderbyClause, 0, -1);
			$result = $db->fetchAll('SELECT DISTINCT `name`, hostname, `port` FROM configuration GROUP BY `name`, hostname, `port` order by '. $orderbyClause);
		}
		else{
			$result = $db->fetchAll('SELECT DISTINCT `name`, hostname, `port` FROM configuration GROUP BY `name`, hostname, `port`');
		}
		$db->closeConnection();
		return $result;	
	}
	
}