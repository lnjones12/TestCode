<?php

require_once('model/database/OdbcConnectionFactory.php');
require_once('model/wordpress/Leader.php');

class WordpressDAO
{
	public function getCitizensLeaderboard($numInLeaderboard)
	{
		try {
			$timingDbh = OdbcConnectionFactory::getConnection('DRIVER={SQL Server};SERVER=ec2-204-236-241-134.compute-1.amazonaws.com;DATABASE=CGIGrandPrix',
							'CGIGrandPrix', 'C0mp3t!t0r');
		} catch (OdbcException $e) {
			return false;
		}

		$query = 'SELECT vw_CitizensAwards.[ru_ID],vw_CitizensAwards.[ru_firstname],vw_CitizensAwards.[ru_lastname],
			vw_CitizensAwards.[ru_sex],[a_div],[a_totalpoints], ru_state, ru_city FROM [CGIGrandPrix].[dbo].[vw_CitizensAwards] 
			join vw_CitizensByTotalPoints on vw_CitizensAwards.ru_ID = vw_CitizensByTotalPoints.ru_ID order by a_div, a_totalpoints desc';
		$stmt = odbc_prepare($timingDbh, $query);

		if (!$stmt) {
			throw new OdbcException(odbc_error($timingDbh) . ' ' . odbc_errormsg($timingDbh) . ' ' . __METHOD__);
		}

		if (!odbc_execute($stmt, array())) {
			throw new OdbcException(odbc_error($timingDbh) . ' ' . odbc_errormsg($timingDbh) . ' ' . __METHOD__);
		}

		$leaders = array();
		$count = 1;
		$division = '';
		// now i have to split this out into top for each division
		while ($row = odbc_fetch_array($stmt)) {

			$division = $row['a_div'];
			
			$leader = new Leader();
			$leader->score = $row['a_totalpoints'];
			$leader->ID = $row['ru_ID'];
			$leader->firstname = $row['ru_firstname'];
			$leader->lastname = $row['ru_lastname'];
			$leader->sex = $row['ru_sex'];
			$leader->division = $division;
			$leader->city = $row['ru_city'];
			$leader->state = $row['ru_state'];
			
			$leaders[$division][] = $leader;
		}

		unset($stmt);
		odbc_close($timingDbh);
		unset($timingDbh);
		return $leaders;
	}
	
	public function getEliteLeaderboard($sex)
	{
		try {
			$timingDbh = OdbcConnectionFactory::getConnection('DRIVER={SQL Server};SERVER=ec2-204-236-241-134.compute-1.amazonaws.com;DATABASE=CGIGrandPrix',
							'CGIGrandPrix', 'C0mp3t!t0r');
		} catch (OdbcException $e) {
			return false;
		}

		$query = "SELECT distinct [es_score],[ru_ID],[ru_firstname],[ru_lastname],[ru_country]
  			FROM [CGIGrandPrix].[dbo].[vw_ElitesByTotalPoints] where ru_sex = '".$sex."' order by es_score desc";
		
		$stmt = odbc_prepare($timingDbh, $query);

		if (!$stmt) {
			throw new OdbcException(odbc_error($timingDbh) . ' ' . odbc_errormsg($timingDbh) . ' ' . __METHOD__);
		}

		if (!odbc_execute($stmt, array())) {
			throw new OdbcException(odbc_error($timingDbh) . ' ' . odbc_errormsg($timingDbh) . ' ' . __METHOD__);
		}

		$leaders = array();

		while ($row = odbc_fetch_array($stmt)) {
			$leader = new Leader();
			$leader->score = $row['es_score'];
			$leader->ID = $row['ru_ID'];
			$leader->firstname = $row['ru_firstname'];
			$leader->lastname = $row['ru_lastname'];
			$leader->sex = $row['ru_sex'];
			$leader->country = $row['ru_country'];
			
			$leaders[] = $leader;
		}

		unset($stmt);
		odbc_close($timingDbh);
		unset($timingDbh);
		return $leaders;
	}
	
	public function getLeaderDetails($ru_ID, $elite){
		
		try {
			$timingDbh = OdbcConnectionFactory::getConnection('DRIVER={SQL Server};SERVER=ec2-204-236-241-134.compute-1.amazonaws.com;DATABASE=CGIGrandPrix',
							'CGIGrandPrix', 'C0mp3t!t0r');
		} catch (OdbcException $e) {
			return false;
		}
		$query = "select ru_ID, ru_firstname, ru_lastname, ev_eventName, ev_eventDate, ev_eventWeight, rf_placePoints, rf_timePoints, 
			cs_score, es_score, rf_ageDiv, ru_sex, rf_displayFinishTime, rf_ageGrade, rf_openTimePoints, rf_openPlacePoints from vw_PointHolders where ru_ID = ?";
		
		$stmt = odbc_prepare($timingDbh, $query);

		if (!$stmt) {
			throw new OdbcException(odbc_error($timingDbh) . ' ' . odbc_errormsg($timingDbh) . ' ' . __METHOD__);
		}

		if (!odbc_execute($stmt, array($ru_ID))) {
			throw new OdbcException(odbc_error($timingDbh) . ' ' . odbc_errormsg($timingDbh) . ' ' . __METHOD__);
		}

		$leaders = array();

		while ($row = odbc_fetch_array($stmt)) {
			$leader = new Leader();
			
			$leader->ID = $row['ru_ID'];
			$leader->firstname = $row['ru_firstname'];
			$leader->lastname = $row['ru_lastname'];
			$leader->event_name = $row['ev_eventName'];
			$leader->event_date = $row['ev_eventDate'];
			$leader->event_weight = $row['ev_eventWeight'];
			if($elite){
				$leader->place_points = $row['rf_openPlacePoints'];
				$leader->time_points = $row['rf_openTimePoints'];
				$leader->score = $row['es_score'];
			}
			else{
				$leader->place_points = $row['rf_placePoints'];
				$leader->time_points = $row['rf_timePoints'];
				$leader->score = $row['cs_score'];
			}
			$leader->division = $row['rf_ageDiv'];
			$leader->sex = $row['ru_sex'];
			$leader->finish_time = $row['rf_displayFinishTime'];
			$leader->age_grade = $row['rf_ageGrade'];
			
			$leaders[] = $leader;
		}

		unset($stmt);
		odbc_close($timingDbh);
		unset($timingDbh);
		return $leaders;
		
	}
	
}