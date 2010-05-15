<?php
/*************************************************************************
 *  Grammafone Digital Music System - For your digital music collection
 *  http://www.grammafone.com
 *  Copyright (C) 2008 Ben Bruscella, and the GrammaFone team
 *************************************************************************/
include("base.inc.php");

$sajax_remote_uri = 'index.php';
$sajax_request_type = "POST";
//$sajax_debug_mode = 1;
sajax_init();
/* list of functions to export for SAJAX */
sajax_export(
    "ajaxSongGetCurrent",
    "ajaxUserGet",
    "musicLookup",
    "ajaxPlaylistRemove",
    "playlist_add",
    "playlist_update",
    "playlistInfo",
    "clearPlaylist",
    "buildBreadcrumb",
    "play",
    "searchMusic",
    "editUser",
    "switchMode",
    "viewPlaylist",
    "getDropDown",
    "savePlaylist",
    "getRandItems",
    "randPlay",
    "resetDatabase",
    "createInviteCode",
    "editSettings",
    "deletePlaylist",
    "adminEditUsers",
    "adminAddUser",
    "submitScrobbler"); 
sajax_handle_client_request(); /* serve client instances */
if(!isInstalled()) {
    header("Location: install.php");
}
else {
    if(!isLoggedIn()){
	header("Location: login.php");
    }
}

set_error_handler('errorHandler');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo "http://".$GLOBALS['http_server'].$GLOBALS['uri_path']; ?>/feed.php" />
<link rel="Stylesheet" href="css/grammafone.css" type="text/css" />
<link rel="stylesheet" href="css/smoothness/jquery-ui.custom.css"  type="text/css"/>
<link rel="shortcut icon" type="image/ico" href="favicon.ico" />
<title><?php echo "$GLOBALS[server_title] ".$version; ?> | Welcome <?php echo "$_SESSION[sess_firstname] $_SESSION[sess_lastname]"; ?></title>
<script type="text/javascript"> 
    var page = 'browse';
    var mode = 'player';
    var bc_parenttype = '';
    var bc_parentitem = '';
    var bc_childtype = '';
    var bc_childitem = '';
    var prevpage = '';
    var currentpage = 'browse';
    var nowplaying = 0;
    var isplaying = 0;
    var clearbc = 1;
    var playlisting;
    var just_added = 0;
    <?php sajax_show_javascript(); ?></script>
    <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.custom.min.js"></script>
    <script type="text/javascript" src="lib/player/swfobject.js"></script>
    <script type="text/javascript" src="js/grammafone.js"></script>	
    <script type="text/javascript" src="js/player.js"></script>	
	<script>
	    $(document).ready(function(){
	    	playerShow();
			init();		
	        $("#loading").ajaxStart(function() {
	            $(this).show();
	        });
	        $("#loading").ajaxStop(function() {
	            $(this).addClass('ohmy').hide('slow');
	        });
		});
		
	</script>
</head>
<body>

<div id="topinfo">
    <div class="right">logged in as <?php echo "$_SESSION[sess_firstname] $_SESSION[sess_lastname]"; ?> [<a href="login.php?logout=1" title="Logout of Gramophone.FM">logout</a> | <a href="#" onclick="switchPage('prefs'); return false;" title="Set Your User Preferences">my account</a>]
    </div>
    <strong>Gramophone.FM Music System</strong><?php echo " (v" . $version . ")"; ?>
</div>

<div id="wrap">
    <div id="header">
	<div id="controls">
	    <div class="current" id="current"><span id="artist"></span><span id="song"></span></div><br/>
	    <div class="buttons">
	    	<a href="#" onclick="play('playlist','prev'); return false;" title="Previous Song"><img src="img/rew_big.gif" /></a>
	    	<a href="#" onclick="play('playlist','next'); return false;" title="Next Song"><img src="img/ff_big.gif" /></a>
	    	<span id="jwplayer" style=" "><a href="http://www.macromedia.com/go/getflashplayer">Get Flash</a> to see this player.</span>
	    </div>
	</div>
	
	<h1 id="pagetitle"></h1>
	<ul id="nav">
	    <li><a href="#" id="browse" onclick="switchPage('browse'); return false;"  title="Browse the Music Database" class="c">Music</a></li>
	    <li><a href="#" id="search" onclick="switchPage('search'); return false;" title="Search the Music Database">Search</a></li>
	    <li><a href="#" id="playlists" onclick="switchPage('playlists'); return false;" title="Load Saved Playlists">Playlists</a></li>
	    <li><a href="#" id="about" onclick="switchPage('about'); return false;" title="About Gramophone.FM">About</a></li>
	    <?php if(accessLevel(8)){ ?>
		    <li><a href="#" id="admin" onclick="switchPage('admin'); return false;" title="Admin Panel">Admin</a></li>
	    <?php } ?>
	</ul>
    </div>
   
    <div id="loading"><h1>LOADING...</h1></div>
    
    <div id="left">
	<div class="box" id="info"></div>
    </div>
    <div id="right">
	<div class="box">
	    <div class="head" id="playerwidth">
		<div class="right">
		    <a href="#" onclick="play('playlist',-1); return false;" title="Play This Playlist Now">play</a>
		    <a href="#" onclick="stream('pl',0); return false;" title="Stream This Playlist in M3U format Now">stream</a>
		    <a href="#" onclick="savePL('open',0); return false;" title="Save Current Playlist">save</a> 
		    <a href="#" onclick="plclear(); return false;"class="red" title="Clear the Current Playlist">clear</a>
		</div>
		<h2 id="pl_title"></h2><span id="pl_info"></span>
	    </div>
	    <ul id="playlist"></ul>
	    <div id="box_extra"></div>
	</div>
    </div>
    <div class="clear"></div>
</div>

<iframe src="hidden.php" frameborder="0" height="0" width="0" id="hidden" name="hidden">
</iframe>

</body>
</html>

