<?php
/***********************
* mp3act upgrade file
*
***********************/

include("includes/mp3act_functions.php");

mp3act_connect();

$sql = "UPDATE mp3act_settings SET version='1.1' WHERE id=1";
if(mysql_query($sql)){
  echo "<strong>mp3act successfully upgraded!</strong>";
  	echo "<p><a href=\"http://$GLOBALS[http_server]$GLOBALS[uri_path]/\">Login to your new mp3act server</a></p>";
}

?>