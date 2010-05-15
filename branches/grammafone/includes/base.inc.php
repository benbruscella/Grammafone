<?php
	// Configurations
	require_once('lib/config.inc.php');

	error_reporting (E_ALL);

	if (version_compare(phpversion(), '5.2.0', '<') == true) { 
		die ('PHP 5.2.0 and above only. Sorry'); 
	}
	// Constants:
	define ('DIRSEP', DIRECTORY_SEPARATOR);

	// Get site path
	$site_path = realpath(dirname(__FILE__) . DIRSEP . '..' . DIRSEP) . DIRSEP;
	define ('site_path', $site_path);	

	// For auto loading of classes
	function __autoload($class_name) {
		$filename = strtolower($class_name) . '.php';
		$file = site_path . 'classes' . DIRSEP . $filename;
	
		if (file_exists($file) == false) {
			return false;
		}
		echo $file . "<br/>";
		include ($file);
	}	
	$registry = new Registry;
	$logger = new Logger('true', "/tmp/grammafone.log"); 

	// Database Class
	require_once('lib/database.php');	
	
	$logger->logTofile("New Request - URL is: " . URL);

