<?php
class ConfigurationService
{

	public static function get($start, $end)
	{
		require_once('model/configuration/ConfigurationDAO.php');
		$dao = new ConfigurationDAO();
		return $dao->get($start, $end);
	}
	
	public static function add($configname, $hostname, $port, $username)
	{
		require_once('model/configuration/ConfigurationDAO.php');
		$dao = new ConfigurationDAO();
		return $dao->add($configname, $hostname, $port, $username);
	}
	
	public static function getAll()
	{
		require_once('model/configuration/ConfigurationDAO.php');
		$dao = new ConfigurationDAO();
		return $dao->getAll();
	}
	
	public static function delete($id)
	{
		require_once('model/configuration/ConfigurationDAO.php');
		$dao = new ConfigurationDAO();
		return $dao->delete($id);
	}
	
	public static function getWhere($entry)
	{
		require_once('model/configuration/ConfigurationDAO.php');
		$dao = new ConfigurationDAO();
		return $dao->getWhere($entry);
	}
	
	public static function getSorted($namesorted, $hostnameSorted, $portSorted, $usernameSorted)
	{
		require_once('model/configuration/ConfigurationDAO.php');
		$dao = new ConfigurationDAO();
		return $dao->getSorted($namesorted, $hostnameSorted, $portSorted, $usernameSorted);
	}
	
}