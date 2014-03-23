<?php
class WordpressService
{

	public static function getCitizensLeaderboard($numInLeaderboard)
	{
		require_once('model/wordpress/WordpressDAO.php');
		$dao = new WordpressDAO();
		return $dao->getCitizensLeaderboard($numInLeaderboard);
	}
	
	public static function getEliteLeaderboard($sex)
	{
		require_once('model/wordpress/WordpressDAO.php');
		$dao = new WordpressDAO();
		return $dao->getEliteLeaderboard($sex);
	}
	
	public static function getLeaderDetails($ru_ID, $elite)
	{
		require_once('model/wordpress/WordpressDAO.php');
		$dao = new WordpressDAO();
		return $dao->getLeaderDetails($ru_ID, $elite);
	}
}