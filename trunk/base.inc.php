<?php
if (file_exists('includes/config.php'))
    require_once ('includes/config.php');

else {
    echo "<b>Error: Cannot locate config.php</b>";
    echo '<pre>';
    include('docs/install.txt');
    echo '</pre>';
    die();
}
require_once('FirePHPCore/fb.php');
require_once("classes/db.class.php");
require_once("classes/addMusic.class.php");
require_once("includes/sessions.php"); 
require_once("includes/grammafone.php"); 
require_once("includes/ajax.php"); 
require_once("lib/Sajax.php");
require_once('lib/getid3/getid3.php');
require_once("lib/archive.php");
?>
