<?php
	include_once("base.inc.php");
	$song = $_GET["id"];
	//$song = "file://localhost/Z:/audio/A Perfect Circle/Mer De Noms/05 Orestes.mp3";
	//$song = "file://localhost/Z:/audio/Ryan%20Adams%20&#38;%20The%20Cardinals/Cold%20Roses%20-%20Cd%201/01%20Magnolia%20Mountain.mp3";
	//$song = "file://localhost/Z:/audio/Ryan Adams & The Cardinals/Cold Roses - Cd 1/01 Magnolia Mountain.mp3";
	
	$song = str_replace('%20',' ',$song); 
    $song = str_replace('&#38;','&',$song); 
	
	fb("ID: " . $id);
    fb("Song Starting: " . $song);
	
	header("Content-Type: audio/mpeg");
	header("Content-Length: filesize($song)");
	session_write_close();
	$temp = @fopen($song, "r");
	while ($data = @fread($temp, 1024) ) {
		echo $data; 
	}
	fb("Song: " . $song);
	pclose($temp);
?>