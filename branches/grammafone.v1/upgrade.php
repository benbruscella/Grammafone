<?php
/***********************
* mp3act upgrade file
*
***********************/

include("includes/grammafone.php");

mp3act_connect();
$errors = FALSE;

$sql = "UPDATE mp3act_settings SET version='1.2' WHERE id=1";
if(!mysql_query($sql)){
  $errors = TRUE;
}
$sql = "CREATE TABLE IF NOT EXISTS mp3act_audioscrobbler (
  as_id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  song_id int(11) NOT NULL default '0',
  as_timestamp varchar(100) NOT NULL default '',
  PRIMARY KEY  (as_id)
) TYPE=MyISAM";
if(!mysql_query($sql)){
  $errors = TRUE;
}

$sql = "ALTER TABLE mp3act_users ADD as_username varchar(20) NOT NULL default ''";
if(!mysql_query($sql)){
  $errors = TRUE;
}

$sql = "ALTER TABLE mp3act_users ADD as_password varchar(30) NOT NULL default ''";
if(!mysql_query($sql)){
  $errors = TRUE;
}
$sql = "ALTER TABLE mp3act_users ADD as_lastresult varchar(255) NOT NULL default ''";
if(!mysql_query($sql)){
  $errors = TRUE;
}
$sql = "ALTER TABLE mp3act_users ADD as_type tinyint(4) NOT NULL default '0'";
if(!mysql_query($sql)){
  $errors = TRUE;
}

if(!$errors){
  echo "<strong>mp3act successfully upgraded!</strong>";
	echo "<p><a href=\"$GLOBALS[http_url]$GLOBALS[uri_path]/\">Login to your new mp3act server</a></p>";
}

?>
