<?php
/******************************************
*	configuration file
*
******************************************/
$GLOBALS['server_title'] = "Gramophone.FM Music System";
$GLOBALS['http_protocol'] = 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '');
$GLOBALS['http_server'] = $_SERVER['HTTP_HOST'];
$GLOBALS['http_url'] = $GLOBALS['http_protocol']."://".$GLOBALS['http_server'];
$GLOBALS['abs_path'] = dirname($_SERVER['SCRIPT_FILENAME']);  //Path for grammafone on your filesystem
$GLOBALS['uri_path'] = (dirname($_SERVER['SCRIPT_NAME']) != DIRECTORY_SEPARATOR ? dirname($_SERVER['SCRIPT_NAME']) : ""); 
$GLOBALS['upload_path'] = $GLOBALS['abs_path'] . "/music/";

// MySQL Information
$GLOBALS['mysql_server'] = "localhost";
$GLOBALS['db_name'] = "grammafone";
$GLOBALS['db_user'] = "";
$GLOBALS['db_pw'] = "";

?>
