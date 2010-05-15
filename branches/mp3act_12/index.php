<?php
/*************************************************************************
*  mp3act Digital Music System - A streaming and jukebox solution for your digital music collection
*  http://www.mp3act.net
*  Copyright (C) 2005 Jon Buda (www.jonbuda.com)
*  
*  This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

*  This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
*  
*  You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*************************************************************************/

include_once("includes/mp3act_functions.php"); 
include_once("includes/sessions.php");
require("includes/Sajax.php");
$sajax_remote_uri = 'index.php';
$sajax_request_type = "POST";
sajax_init();
// list of functions to export
sajax_export("getCurrentSong","getUser","musicLookup","playlist_rem","playlist_add","playlistInfo","clearPlaylist","buildBreadcrumb","play","playlist_move","searchMusic","editUser","switchMode","viewPlaylist","getDropDown","savePlaylist","getRandItems","randPlay","resetDatabase","createInviteCode","editSettings","deletePlaylist","adminEditUsers","adminAddUser","submitScrobbler"); 
sajax_handle_client_request(); // serve client instances

if(!isLoggedIn()){
  header("Location: login.php");
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo $GLOBALS['http_url'].$GLOBALS['uri_path']; ?>/feed.php" />
	<link rel="Stylesheet" href="css/mp3act_css.php" type="text/css" />
	<link rel="shortcut icon" type="image/ico" href="favicon.ico" />
	<title><?php echo "$GLOBALS[server_title] ".getSystemSetting("version"); ?> | Welcome <?php echo "$_SESSION[sess_firstname] $_SESSION[sess_lastname]"; ?></title>
	<script type="text/javascript"> 
			var page = 'search';
			var mode = '<?php echo $_SESSION['sess_playmode']; ?>';
			var bc_parenttype = '';
			var bc_parentitem = '';
			var bc_childtype = '';
			var bc_childitem = '';
			var prevpage = '';
			var currentpage = 'search';
			var nowplaying = 0;
			var isplaying = 0;
			var clearbc = 1;
			
	<?php sajax_show_javascript(); ?></script>
	<script type="text/javascript" src="includes/mp3act_js.js"></script>	
	<script type="text/javascript" src="includes/fat.js"></script>
</head>
<body>

<div id="topinfo">
	<div class="right">logged in as <?php echo "$_SESSION[sess_firstname] $_SESSION[sess_lastname]"; ?> [<a href="login.php?logout=1" title="Logout of mp[3]act">logout</a> | <a href="#" onclick="switchPage('prefs'); return false;" title="Set Your User Preferences">my account</a>]</div>
	<strong>mp3act music system v<?php echo getSystemSetting("version"); ?></strong> <?php if(getSystemSetting("mp3bin") != ""){?>[<a href="#" onclick="switchMode('streaming'); return false;" title="Switch to Streaming Mode">stream</a> | <a href="#" onclick="switchMode('jukebox'); return false;" title="Switch to Jukebox Mode">jukebox</a>]<?php } ?>
</div>

<div id="wrap">
	<div id="header">
		<div id="controls">
			
		</div>
		<h1 id="pagetitle"></h1>
		<ul id="nav">
			<li><a href="#" id="search" onclick="switchPage('search'); return false;" title="Search the Music Database">Search</a></li>
			<li><a href="#" id="browse" onclick="switchPage('browse'); return false;"  title="Browse the Music Database" class="c">Browse</a></li>
			<li><a href="#" id="random" onclick="switchPage('random'); return false;" title="Create Random Mixes">Random</a></li>
			<li><a href="#" id="playlists" onclick="switchPage('playlists'); return false;" title="Load Saved Playlists">Playlists</a></li>
			<li><a href="#" id="stats" onclick="switchPage('stats'); return false;" title="View Server Statistics">Stats</a></li>
			<li><a href="#" id="about" onclick="switchPage('about'); return false;" title="About mp[3]act">About</a></li>
			<?php if(accessLevel(8)){ ?><li><a href="#" id="admin" onclick="switchPage('admin'); return false;" title="Admin Panel">Admin</a></li><?php } ?>

		</ul>
		
	</div>
	<div id="loading"><h1>LOADING...</h1></div>
	<div id="left">
		<h2 id="breadcrumb"></h2>
		<div class="box" id="info">
		
		</div>
	</div>
	
	<div id="right">
			<div class="box">
				<div class="head">
					<div class="right"><a href="#" onclick="play('pl',0); return false;" title="Play This Playlist Now">play</a> <a href="#" onclick="savePL('open',0); return false;" title="Save Current Playlist">save</a> <a href="#" onclick="plclear(); return false;"class="red" title="Clear the Current Playlist">clear</a></div>
					<h2 id="pl_title"></h2><span id="pl_info"></span>
				</div>
			<ul id="playlist">
					
			</ul>
			
			<div id="box_extra"> </div>
			</div>
	</div>
	<div class="clear"></div>
</div>
<iframe src="hidden.php" frameborder="0" height="0" width="0" id="hidden" name="hidden"></iframe>
</body>
</html>
