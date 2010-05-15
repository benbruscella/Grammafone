<?php
/******************************************
*	mp3act configuration file
*	http://www.mp3act.net
*
*
******************************************/
$GLOBALS['server_title'] = "mp3act music system";

$GLOBALS['http_server'] = $_SERVER['HTTP_HOST'];
$GLOBALS['abs_path'] = dirname($_SERVER['SCRIPT_FILENAME']);  //Path for mp3act on your filesystem
$GLOBALS['uri_path'] = (dirname($_SERVER['SCRIPT_NAME']) != '/' ? dirname($_SERVER['SCRIPT_NAME']) : ""); 

// MySQL Information
$GLOBALS['mysql_server'] = "localhost";
$GLOBALS['db_name'] = "mp3act";
$GLOBALS['db_user'] = "mp3act";
$GLOBALS['db_pw'] = "mp3act";

?>
