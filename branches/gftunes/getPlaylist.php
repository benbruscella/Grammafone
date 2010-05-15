<?php 
header("Content-type: text/xml"); 
echo "<?xml version='1.0' encoding='UTF-8'?>"?>
<playlist>
<?
if ($_GET["id"]==1 || $_GET["id"]==""){
?>
	<mp3 id="1" title="Sirenthia" artist="Mushroom Giant" time="2:05" album="Kuru" path="library/Sirenthia.mp3" rating="5" playcount="1" art="library/kuru.jpg"/>
<? 
}
?>

<?
if ($_GET["id"]==2 || $_GET["id"]==""){
?>
	<mp3 id="2" title=" Cannibal Queen" artist="Miniature Tigers" time="1:22" album="Fight With Tools" path="library/CannibalQueen.mp3" rating="5" playcount="12" art="library/tigers.jpg" />
<?
}
?>
<?
if ($_GET["id"]==3 || $_GET["id"]==""){
?>
	<mp3 id="3" title="Journeyman" artist="Lovetones" time="1:22" album="Fight With Tools" path="library/Journeyman.mp3" rating="5" playcount="12" art="library/lovetones.jpg" />
<?
}
?>
</playlist>

