<?php
	include_once("base.inc.php");

	$song = "Z:\\audio\\@new\\Peeping Tom\\Music Swop Shop\\04 White Lightning.mp3";
	if ($_GET["id"]==1) {
		$song = "Z:\\audio\\Lorenzo Jovanotti\\Safari (limited edition)\\01 Fango.mp3";
	}
	if ($_GET["id"]==2) {
		$song = "Z:\\audio\\Ryan Adams & The Cardinals\\Cardinology\\03 Fix It.mp3";
	}
	if ($_GET["id"]==3) {
		$song = "Z:\\audio\\Flobots\\Fight With Tools\\11 We Are Winning.mp3";
	}
	if ($_GET["id"]==4) {
		$song = "Z:\\audio\\Peeping Tom\\Music Swop Shop\\04 White Lightning.mp3";
	}
	fb("Song Starting: " . $song);
	
	header("Content-Type: audio/mpeg");
	header("Content-Length: filesize($song)");
	session_write_close();
	$temp = @fopen($song, "r");
	while ($data = @fread($temp, 1024) ) {
		echo $data; 
	}
	pclose($temp);
?>