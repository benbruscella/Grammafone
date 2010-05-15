<?php
/***********************
* GrammaFone upgrade file
*
***********************/

include_once("base.inc.php");

grammafone_connect();
$errors = FALSE;

$sql = "UPDATE Gramophone.FM SET version='1.2' WHERE id=1";
if(!mysql_query($sql)){
  $errors = TRUE;
}
$sql = "CREATE TABLE IF NOT EXISTS grammafone_audioscrobbler (
  as_id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  song_id int(11) NOT NULL default '0',
  as_timestamp varchar(100) NOT NULL default '',
  PRIMARY KEY  (as_id)
) TYPE=MyISAM";
if(!mysql_query($sql)){
  $errors = TRUE;
}

$sql = "ALTER TABLE grammafone_users ADD as_username varchar(20) NOT NULL default ''";
if(!mysql_query($sql)){
  $errors = TRUE;
}

$sql = "ALTER TABLE grammafone_users ADD as_password varchar(30) NOT NULL default ''";
if(!mysql_query($sql)){
  $errors = TRUE;
}
$sql = "ALTER TABLE grammafone_users ADD as_lastresult varchar(255) NOT NULL default ''";
if(!mysql_query($sql)){
  $errors = TRUE;
}
$sql = "ALTER TABLE grammafone_users ADD as_type tinyint(4) NOT NULL default '0'";
if(!mysql_query($sql)){
  $errors = TRUE;
}

if(!$errors){
  echo "<strong>Gramophone.FM successfully upgraded!</strong>";
	echo "<p><a href=\"$GLOBALS[http_url]$GLOBALS[uri_path]/\">Login to your new Gramophone.FM server</a></p>";
}

?>
