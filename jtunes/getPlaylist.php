<?php 
header("Content-type: text/xml"); 
echo "<?xml version='1.0' encoding='UTF-8'?>";
echo "<playlist>";
if ($_GET["id"]==1 || $_GET["id"]==""){
echo "	<mp3 id=\"1\" title=\"Fango\" artist=\"Jovanotti\" time=\"4:34\" album=\"Safari\" rating=\"5\" playcount=\"12\" art=\"library/safari.jpg\" />";
echo "	<mp3 id=\"2\" title=\"Fix It\" artist=\"Ryan Adams\" time=\"3:00\" album=\"Cardinology\" rating=\"5\" playcount=\"12\" art=\"library/cardiology_cover.jpg\" />";
}
if ($_GET["id"]==2 || $_GET["id"]==""){
echo "	<mp3 id=\"3\" title=\"We Are Winning\" artist=\"Flobots\" time=\"3:27\" album=\"Fight With Tools\" rating=\"5\" playcount=\"12\" art=\"library/flobots.jpg\" />";
echo "	<mp3 id=\"4\" title=\"White Lightning\" artist=\"Peeping Tom\" time=\"3:27\" album=\"Music Swap Shop\" rating=\"5\" playcount=\"12\" art=\"library/flobots.jpg\" />";
}
echo "</playlist>";
?>
