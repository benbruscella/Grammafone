<?php
// hidden iframe to process streaming

include("includes/grammafone.php");
include_once("includes/sessions.php");


// Play the Music
if(isset($_GET['type'])){
	if($_GET['type'] == 'artists' || $_GET['type'] == 'genre' || $_GET['type'] == 'albums' || $_GET['type'] == 'all'){
		echo randPlay($_SESSION['sess_playmode'],$_GET['type'],$_GET['num'],$_GET['items']);
	}
	else{
		echo play($_SESSION['sess_playmode'],$_GET['type'],$_GET['id']);
	}
}
?>


