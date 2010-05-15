<?php 

	echo $_GET["q"] . "<br />";
	
	require_once('./includes/base.inc.php'); 
	
	if (databaseIsInstalled() == true) {
		echo '<h1> index.php </h1>';
	}
	else {
		echo 'GrammaFone installation not found for database "'.$GLOBALS['db_name'].'" on "'.$GLOBALS['db_server'].'"<br/><br/>';
		echo 'Verify your configuration, then click <a href="install.php">here</a> to run the install script...';
	}


