/* mp3act RSS Feed Creator */
<?php
include_once("includes/grammafone.php"); 

mp3act_connect();

header("Content-Type: text/xml");

$query = "SELECT mp3act_albums.album_name,
mp3act_albums.album_art,
mp3act_artists.artist_name,mp3act_artists.prefix,
DATE_FORMAT(mp3act_songs.date_entered,'%a, %d %b %Y %T') as pubdate   
FROM mp3act_songs,mp3act_albums,mp3act_artists 
WHERE mp3act_songs.album_id=mp3act_albums.album_id 
AND mp3act_artists.artist_id=mp3act_songs.artist_id 
GROUP BY mp3act_songs.album_id ORDER BY mp3act_songs.date_entered DESC LIMIT 10";

$result = mysql_query($query);
echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?>";
?>

<rss version="2.0">

<channel>
	<title>mp3act - Recently Added Albums</title>
	<pubDate>Fri, 03 Jun 2005 10:23:45 +0000</pubDate>
	<link><?php echo "$GLOBALS[http_url]$GLOBALS[uri_path]/"; ?></link>
	<description>A list of the 10 most recently added music albums to this mp3act server.</description>
	<generator><?php echo "$GLOBALS[http_url]$GLOBALS[uri_path]/feed.php"; ?></generator>
	<language>en</language>
<?php
while($row = @mysql_fetch_array($result)){ ?>

<item>
<title><?php echo "$row[prefix] ".htmlentities($row['artist_name'])." - ".htmlentities($row['album_name']); ?></title>
<description>	<![CDATA[<?php echo "<p ><strong>Artist:</strong> $row[prefix]".htmlentities($row['artist_name'])."<br/><strong>Album:</strong> ".htmlentities($row['album_name']); 
		if($row['album_art'] && $row['album_art'] != 'fail' ) { echo "<br/><img src=\"$GLOBALS[http_url]$GLOBALS[uri_path]/art/".$row['album_art']."\" />"; }
	?>
	</p>]]></description>
<pubDate><?php echo $row['pubdate']; ?> +0000</pubDate>
<content:encoded>
	<![CDATA[<?php echo "<p style=\"margin:0; border-bottom: 8px solid #aaa; border-top: 8px solid #aaa; padding: 8px; background: #ddd; \"><strong>Artist:</strong> $row[prefix] ".htmlentities($row['artist_name'])."<br/><strong>Album:</strong> ".htmlentities($row['album_name']); 
		if($row['album_art'] && $row['album_art'] != 'fail' ) { echo "<br/><img style=\"background: #fff; border:1px solid #999; margin:0; padding:3px; \" src=\"$GLOBALS[http_url]$GLOBALS[uri_path]/art/".$row['album_art']."\" />"; }
	?>
	</p>]]>
</content:encoded>
<link>http://mp3act.net</link>
</item>
<?php
}
?>
</channel>

</rss>
