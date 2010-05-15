<?php
	session_save_path('/tmp');
	session_name('grammafone');
	ini_set( "session.gc_maxlifetime", "10800" );
	session_start();
	$var = array('i'=>10, 'j'=>20);
?>
