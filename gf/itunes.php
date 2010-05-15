<?php 
include "itunes_xml_parser_php5.php";

$songs = iTunesXmlParser("itunes_short.xml");

echo json_encode($songs);

?>