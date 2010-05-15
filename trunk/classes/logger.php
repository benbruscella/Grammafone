<?php

// Enable/Disable logging to LOGFILE
define('LOG_TO_FILE', true);
// File to log messages to if enabled
define('LOGFILE', 'grammafone.log');

Class Logger {
	
	function __construct($name) {
		// does the Logging file exist?
		if(LOG_TO_FILE == true && !file_exists(LOGFILE)) {
			$handle = fopen(LOGFILE, "w+");
			fclose($handle);
		}
		
		// Check everything is OK with logging
		if(LOG_TO_FILE == true && !is_writable(LOGFILE)) {
			die('Logging enabled but cannot write to ' . LOGFILE);
		}	
	}
	
	
	// Setup a logging function
	function logToFile($msg) {
		if (LOG_TO_FILE == true) {
			// open file
			$fd = fopen(LOGFILE, "a");
			// append date/time to message
			$str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg; 
			// write string
			fwrite($fd,  $str . "\n");
			// close file
			fclose($fd);
		}
	}
}
?>
