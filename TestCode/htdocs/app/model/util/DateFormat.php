<?php

class DateFormat
{
	public static function mysqlDateTimeToString($mysqlDateTime) {
		/*
		 * Convert MySQL DateTime field in 'YYYY-MM-DD HH:MM:SS.mmm format to
		 * screen-printable format.
		 */
		
		$d = date_parse($mysqlDateTime);
		$str = str_pad($d['month'], 2, '0', STR_PAD_LEFT)
			. '/' . str_pad($d['day'], 2, '0', STR_PAD_LEFT)
			. '/' . str_pad($d['year'], 4, '0', STR_PAD_LEFT);
		return $str;
	}
	public static function mysqlDateTimeToStringStartTime($mysqlDateTime) {
		/*
		 * Convert MySQL DateTime field in 'YYYY-MM-DD HH:MM:SS.mmm format to
		 * screen-printable format.
		 */
		
		$d = date_parse($mysqlDateTime);
		$ampm = 'AM';
		$hour = $d['hour'];
		if($d['hour'] > 12){
			$ampm = 'PM';
			$hour = $d['hour'] - 12;
		}
		$str = str_pad($d['month'], 2, '0', STR_PAD_LEFT)
			. '/' . str_pad($d['day'], 2, '0', STR_PAD_LEFT)
			. '/' . str_pad($d['year'], 4, '0', STR_PAD_LEFT)
			. ' at ' . str_pad($hour, 2, '0', STR_PAD_LEFT)
			. ':' . str_pad($d['minute'], 2, '0', STR_PAD_LEFT)
			. ' '. $ampm;
		return $str;
	}
}

?>