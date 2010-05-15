<?php
include("includes/grammafone.php");
include_once("includes/sessions.php");
if(!isLoggedIn()){
  header("Location: login.php?notLoggedIn=1");
}

# hack to reconstruct the args, should be a better way..
if(isset($_GET['type'])){
	if($_GET['type'] == 'artists' || $_GET['type'] == 'genre' || $_GET['type'] == 'albums' || $_GET['type'] == 'all')
	{
	$args="type=".$_GET['type']."&num=".$_GET['num'];
  if (isset($_GET['items']) && !empty($_GET['items']))
    $args.= "&items=".$_GET['items'];
	}
	else
	{
	$args="type=".$_GET['type']."&id=".$_GET['id'];
	}
}
?>
<html>
<body>
<embed src="<?php echo $GLOBALS[http_url].$GLOBALS[uri_path] ?>/jw_mp3_player/mp3player.swf"
       width="500" 
       height="140" 
       allowfullscreen="true" 
       flashvars="&shuffle=false&autostart=true&repeat=list&height=140&width=500&displaywidth=120&file=<?php echo urlencode($GLOBALS[http_url].$GLOBALS[uri_path]."/xspf_playlist.php?".$args); ?>" />
</body>
</html>


