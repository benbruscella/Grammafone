<?php

	function databaseConnect() {
		if(@mysql_connect($GLOBALS['mysql_server'],$GLOBALS['db_user'],$GLOBALS['db_password'])){
			if(@mysql_select_db($GLOBALS['db_name'])){
				return 1;
			}
			return 0;
		}
		return 0;
	}

	function databaseIsInstalled() {
		return (mysql_query ("SELECT * FROM `grammafone_users` LIMIT 0,1"));
	}


	function databaseReset(){
	
		$query = array();
		$query[] = "TRUNCATE TABLE grammafone_status";
		$query[] = "TRUNCATE TABLE grammafone_songs";
		$query[] = "TRUNCATE TABLE grammafone_artists";
		$query[] = "TRUNCATE TABLE grammafone_albums";
		$query[] = "TRUNCATE TABLE grammafone_playlist";
		$query[] = "TRUNCATE TABLE grammafone_stats";
		$query[] = "TRUNCATE TABLE grammafone_currentsong";
		
		foreach($query as $q){
			mysql_query($q);
		}
		
		return true;
	}

// Connecting to database
if (databaseConnect() == 0) {
	die('Cannot connect to database ' . $GLOBALS['db_name'] . '. Check the configuration!');
}

	
