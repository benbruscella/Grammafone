<?php
session_save_path('/tmp');
session_name('mp3act');
ini_set( "session.gc_maxlifetime", "10800" );
session_start();

?>