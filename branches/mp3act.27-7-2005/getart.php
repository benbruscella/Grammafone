<?php
include_once("includes/mp3act_functions.php"); 
//phpinfo();
//resetDatabase();
createLoginCode('fdafd@fdas.com');
/*
mp3act_connect();
    set_time_limit(1000);

$query = "SELECT mp3act_albums.*,mp3act_artists.artist_name FROM mp3act_albums,mp3act_artists WHERE mp3act_albums.album_art=\"\" AND mp3act_albums.artist_id=mp3act_artists.artist_id";
$result = mysql_query($query);


while($row = mysql_fetch_array($result)){
	$art = art_insert($row[album_id],$row[artist_name],$row[album_name]);
	echo $art;
}*/
?>

