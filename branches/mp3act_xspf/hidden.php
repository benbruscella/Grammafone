<?php
include("includes/mp3act_functions.php");
include_once("includes/sessions.php");

# hack to reconstruct the args, should be a better way..
if(isset($_GET['type'])){
	if($_GET['type'] == 'artists' || $_GET['type'] == 'genre' || $_GET['type'] == 'albums' || $_GET['type'] == 'all')
	{
	$args="type=".$_GET['type']."&num=".$_GET['num']."&items=".$_GET['items'];
	}
	else
	{
	$args="type=".$_GET['type']."&id=".$_GET['id'];
	}
}
?>
<html>
<body>
<object type="application/x-shockwave-flash" width="400" height="170"
<?php echo 'data="'.$GLOBALS[http_url].$GLOBALS[uri_path].'/xspf_player.swf?playlist_url='.$GLOBALS[http_url].$GLOBALS[uri_path].'/xspf_playlist.php?'.$args.'" >'; ?>
<param name="movie" 
<?php echo 'value="'.$GLOBALS[http_url].$GLOBALS[uri_path].'/xspf_player.swf?playlist_url='.$GLOBALS[http_url].$GLOBALS[uri_path].'/xspf_playlist.php?'.$args.'" />'; ?>
</object>
</body>
</html>


