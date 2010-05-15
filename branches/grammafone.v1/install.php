<?php
include("includes/grammafone.php");

function installed(){
  $query = "SELECT user_id FROM mp3act_users";
  $result = @mysql_query($query);
	if(@mysql_num_rows($result) > 0){
		echo "<strong class='error'>It appears that you have already installed GrammFone on this computer.</strong><br/><br/>";
		echo "<a href=\"$GLOBALS[http_url]$GLOBALS[uri_path]/\">Login to your GrammaFone server</a><br/>";
    return TRUE;
	}
	return FALSE;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		
	<title><?php echo $GLOBALS['server_title']; ?> | Install Page</title>
</head>
<style>
body{
	padding: 22px;
	margin:0;
	color: #333;
	background: #F0F0F0;
	text-align: center;
	font: 18px Times;
	
}

h2{
	font-size: 100%;
	margin:0;
	padding: 0 0 5px 0;

}
ul{
	list-style-type: none;
}
a{
	color: blue;
}

a:hover{
	/*color: #fff;
	background: #F21518;
	text-decoration: none;*/
	color: #F21518;
}
img{
	border: 0;
}
input,select{
	border: 1px solid #ccc;
	background: #f3f3f3;
	color: #333;
	font-size: 100%;
	padding: 2px 3px;
	
}
select{
	padding: 2px 0 2px 3px;
}
input:focus{
	border: 1px solid #999;
	background: #fff;
	color: #000;
}

input.btn{
	background: #244A79;
	color: #fff;
	padding: 2px;
	border-color: #0E2F58;
	font: normal 14px sans-serif;
}

input.btn:hover{
	background: #0E2F58;
}


.clear{
  clear: both;
}
.error{
	color: #E63838;
	font-weight: bold;
}
p#error{
	color: #f20000;
	font-weight: bold;
}
p.pad{
	padding: 0px 18px;
}
.right{
	float: right;
}
#wrap{
	background: #fff;
	border: 1px solid #ccc;
	text-align: left;
	padding: 0px;
	margin:0;
	
}

#header{
	position: relative;
	background: #0E2F58;
	height: 50px;
	color: #fff;
	padding: 15px 0 0px 15px;
}

#header h1{
	color: #9ABEE5;
	padding: 0;
	margin:0;
	font-size: 150%;
}
#topinfo{
	
	font-size: 90%;
	color: #666;
	text-align: left;
	padding: 0 0 4px 0;
	
}
</style>
<body>
<?php
	
	$step = 1;
	if(isset($_GET['step']))
		$step = $_GET['step'];
?>
<div id="topinfo">
	<div class="right">Installation Page</div>
	<strong>GrammaFone</strong>
</div>
<div id="wrap">
	<div id="header">
		<h1>GrammaFone Quick Install - Step <?php echo $step; ?></h1>
	</div>
	<p class='pad'>
	<?php
	switch($step){
		case 1:
			if(mp3act_connect()){
				if(!installed()){
				  echo "<strong>Welcome to the mp3act installation page</strong><br/><br/>";
				  echo "This is a very simple and easy installation.  You'll be enjoying your music in no time at all. I swear.<br/><br/>";
				  echo "<a href='install.php?step=2'>Proceed to Step 2 &raquo;</a>";
				}
				
			}
			else{
				echo "<strong class='error'>Unable to establish MySQL connection to database '".$GLOBALS[db_name]."'</strong><br/>";
				echo "Please make sure you have created the database and that the database settings in 'mp3act.conf' are correct.";
			}
		break;
// Test to see if DB and user are set in conf file
// Test DB connection
// If good install Tables

// Give instructions for setting permissions and external programs
// mpg123, lame, Amazon API, php bin
		case 2:
		mp3act_connect();
		if(!installed()){
$querys['albums'] = "CREATE TABLE mp3act_albums (
  album_id int(11) NOT NULL auto_increment,
  album_name varchar(255) NOT NULL default '',
  artist_id int(255) NOT NULL default '0',
  album_genre varchar(50) default NULL,
  album_year smallint(6) NOT NULL default '0',
  album_art text NOT NULL,
  PRIMARY KEY  (album_id)
) TYPE=MyISAM";

$querys['artists'] = "CREATE TABLE mp3act_artists (
  artist_id int(11) NOT NULL auto_increment,
  artist_name varchar(255) default NULL,
  prefix varchar(7) NOT NULL default '',
  PRIMARY KEY  (artist_id)
) TYPE=MyISAM";

$querys['current'] = "CREATE TABLE mp3act_currentsong (
  song_id int(11) NOT NULL default '0',
  pl_id int(11) NOT NULL default '0',
  random tinyint(3) NOT NULL default '0'
) TYPE=MyISAM";


$querys['genres'] = "CREATE TABLE mp3act_genres (
  genre_id int(11) NOT NULL auto_increment,
  genre varchar(25) NOT NULL default '',
  PRIMARY KEY  (genre_id)
) TYPE=MyISAM";

$querys['play_history'] = "CREATE TABLE mp3act_playhistory (
  play_id int(11) NOT NULL auto_increment,
  user_id int(6) default NULL,
  song_id int(11) default NULL,
  date_played datetime default NULL,
  PRIMARY KEY  (play_id)
) TYPE=MyISAM";

$querys['playlist'] = "
CREATE TABLE mp3act_playlist (
  pl_id int(11) NOT NULL auto_increment,
  song_id int(11) default NULL,
  user_id int(11) NOT NULL default '0',
  private tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (pl_id)
) TYPE=MyISAM";

$querys['playlists'] = "CREATE TABLE mp3act_saved_playlists (
  playlist_id int(11) NOT NULL auto_increment,
  user_id int(11) default NULL,
  private tinyint(3) default NULL,
  playlist_name varchar(255) default NULL,
  playlist_songs text,
  date_created datetime default NULL,
  time int(11) default NULL,
  songcount smallint(8) default NULL,
  PRIMARY KEY  (playlist_id)
) TYPE=MyISAM";


$querys['songs'] = "CREATE TABLE mp3act_songs (
  song_id int(11) NOT NULL auto_increment,
  artist_id int(11) NOT NULL default '0',
  album_id int(11) NOT NULL default '0',
  date_entered datetime default NULL,
  name varchar(255) default NULL,
  track smallint(6) NOT NULL default '0',
  length int(11) NOT NULL default '0',
  size int(11) NOT NULL default '0',
  bitrate smallint(6) NOT NULL default '0',
  type varchar(4) default NULL,
  numplays int(11) NOT NULL default '0',
  filename text,
  random tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (song_id)
) TYPE=MyISAM";


$querys['stats'] = "CREATE TABLE mp3act_stats (
  num_artists smallint(5) unsigned NOT NULL default '0',
  num_albums smallint(5) unsigned NOT NULL default '0',
  num_songs mediumint(8) unsigned NOT NULL default '0',
  num_genres tinyint(3) unsigned NOT NULL default '0',
  total_time varchar(12) NOT NULL default '0',
  total_size varchar(10) NOT NULL default '0'
) TYPE=MyISAM";

$querys['logins'] ="CREATE TABLE mp3act_logins (
  login_id int(11) NOT NULL auto_increment,
  user_id int(11) default NULL,
  date int(11) default NULL,
  md5 varchar(100) NOT NULL default '',
  PRIMARY KEY  (login_id)
) TYPE=MyISAM";

$querys['invites'] = "CREATE TABLE mp3act_invites (
  invite_id int(11) NOT NULL auto_increment,
  email varchar(100) NOT NULL default '',
  date_created datetime NOT NULL default '0000-00-00 00:00:00',
  invite_code varchar(255) NOT NULL default '',
  PRIMARY KEY  (invite_id)
) TYPE=MyISAM";

$querys['themes'] ="CREATE TABLE mp3act_themes (
  theme_id smallint(6) NOT NULL auto_increment,
  theme_name varchar(25) default NULL,
  color1 varchar(11) default NULL,
  color2 varchar(11) default NULL,
  color3 varchar(11) default NULL,
  color4 varchar(11) default NULL,
  color5 varchar(11) default NULL,
  theme_user_id smallint(6) default NULL,
  PRIMARY KEY  (theme_id)
) TYPE=MyISAM";
$querys['theme1'] = "INSERT INTO `mp3act_themes` VALUES (NULL, 'default blue', '#0E2F58', '#244A79', '#416899', '#9ABEE5', '#F48603', 0)";
$querys['theme2'] = "INSERT INTO `mp3act_themes` VALUES (NULL, 'green', '#194904', '#2E6D12', '#60A041', '#89C86E', '#3873A1', 0)";
$querys['theme3'] = "INSERT INTO `mp3act_themes` VALUES (NULL, 'red', '#6D0C11', '#912328', '#B44146', '#CEB78B', '#7A643A', 0)";
        
$querys['users'] = "CREATE TABLE mp3act_users (
  user_id int(11) NOT NULL auto_increment,
  username varchar(100) NOT NULL default '',
  firstname varchar(100) NOT NULL default '',
  lastname varchar(100) NOT NULL default '',
  password varchar(255) NOT NULL default '',
  accesslevel tinyint(4) NOT NULL default '0',
  date_created datetime NOT NULL default '0000-00-00 00:00:00',
  active tinyint(4) NOT NULL default '0',
  email varchar(255) NOT NULL default '',
  default_mode varchar(50) NOT NULL default '',
  default_bitrate int(11) NOT NULL default '0',
  default_stereo varchar(50) NOT NULL default '',
  md5 varchar(255) NOT NULL default '',
  last_ip varchar(50) NOT NULL default '',
  last_login datetime default NULL,
  theme_id smallint(6) NOT NULL default '1',
  as_username varchar(20) NOT NULL default '',
  as_password varchar(30) NOT NULL default '',
  as_lastresult varchar(255) NOT NULL default '',
  as_type tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (user_id)
) TYPE=MyISAM";

$querys['audioscrobbler'] = "CREATE TABLE IF NOT EXISTS mp3act_audioscrobbler (
  as_id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  song_id int(11) NOT NULL default '0',
  as_timestamp varchar(100) NOT NULL default '',
  PRIMARY KEY  (as_id)
) TYPE=MyISAM";

$querys['settings'] = "CREATE TABLE mp3act_settings (
  id int(3) NOT NULL auto_increment,
  version varchar(15) NOT NULL default '',
  invite_mode tinyint(4) NOT NULL default '0',
  upload_path varchar(255) NOT NULL default '',
  amazonid varchar(255) NOT NULL default '',
  downloads tinyint(4) NOT NULL default '0',
  sample_mode tinyint(2) NOT NULL default '0',
  mp3bin varchar(100) NOT NULL default '',
  lamebin varchar(100) NOT NULL default '',
  phpbin varchar(100) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM";

$querys['settingsinfo'] = "INSERT INTO `mp3act_settings` VALUES (NULL,'0.1',0, '', '', 0,0,'','','')";

echo "<strong>Creating mp3act Database Tables....</strong><br/><br/>";
//  CREATE TABLES
$error = 0;
foreach($querys as $key=>$query){
	if(mysql_query($query)){
		
	}
	else{
		$error = 1;
	}
	
}
if(!$error){
		echo "<strong>mp3act Databases Installed Successfully</strong<br/><br/>";
	}
					echo "<a href='install.php?step=3'>Proceed to Step 3 &raquo;</a>";
    }
		break;
		case 3:
		  mp3act_connect();
			if(!installed()){
			?>
			<strong class='error'>Take a Moment to Configure Your mp3act Installation.</strong><br/> You don't have to set these now. They are accessible from the Admin menu. However some of the options are neccessary for some features to work.<br/>
				<form method='post' action="install.php?step=4">
			<p class='pad'>
			<strong>Invitation for Registration</strong><br/>(Users are required to be invited to register)<br/><select name='invite'><option value='0' >Not Required</option><option value='1'>Required</option></select><br/><br/>
    	<strong>Sample Mode</strong><br/>(play 1/4 of each song)<br/><select name='sample_mode'><option value='0'>Sample Mode OFF</option><option value='1' >Sample Mode ON</option></select><br/><br/>
    	<strong>Music Downloads</strong><br/>(Rules for Users Downloading Music)<br/><select name='downloads'><option value='0' >Not Allowed</option><option value='1' >Allowed for All</option><option value='2' >Allowed with Permission</option></select><br/><br/>
    	<strong>Amazon API Key</strong><br/>(needed for downloading Album Art) <a href='http://www.amazon.com/webservices/' target='_new'>Obtain Key</a><br/><input type='text' size='30' name='amazonid' /><br/><br/>
    	 <strong>Path to MP3 Player</strong><br/>(ex. /usr/bin/mpg123)<br/><input type='text' size='30' name='mp3bin'  /><br/><br/>
    	 <strong>Path to Lame Encoder</strong><br/>(ex. /usr/bin/lame)<br/><input type='text' size='30' name='lamebin'  /><br/><br/>
    	<strong>Path to PHP-CLI Binary</strong><br/>(ex. /usr/bin/php)<br/><input type='text' size='30' name='phpbin'  /><br/><br/>
			<input type='submit' value='save settings and continue &raquo;' class='btn' />
  
			</p>
				
				</form>
			<?php
		  }
		break;
		case 4:
			mp3act_connect();
			if(!installed()){
			  $query = "UPDATE mp3act_settings SET invite_mode=$_POST[invite],sample_mode=$_POST[sample_mode],downloads=$_POST[downloads],amazonid=\"$_POST[amazonid]\",mp3bin=\"$_POST[mp3bin]\",lamebin=\"$_POST[lamebin]\",phpbin=\"$_POST[phpbin]\" WHERE id=1";
  			mysql_query($query);
  			echo "<strong>Settings Saved....</strong><br/><br/>";
  			echo "<strong>Installation Successful!</strong><br/><br/>";
  			if(!ini_get('allow_url_fopen')){
  				echo "<strong class='error'>WARNING: </strong>Need to Set allow_url_fopen to 'On' in your php.ini file.<br/><br/>";
  			}
  			if(!is_writable($GLOBALS['abs_path']."/art/")){
  				echo "<strong class='error'>WARNING: </strong>The /art/ directory is currently not writable. Please change the permissions on this directory if you wish to use Album Art.<br/><br/>";
				
  			}
  			echo "<a href=\"$GLOBALS[http_url]$GLOBALS[uri_path]/\">Login to your new mp3act server</a><br/>";
  			$random_password = substr(md5(uniqid(microtime())), 0, 6);
  			$query = "INSERT INTO `mp3act_users` VALUES (NULL, 'admin', 'Admin', 'User', PASSWORD(\"$random_password\"), 10, NOW(), 1, '', 'streaming', 0, 's', '21232f297a57a5a743894a0e4a801fc3', '', '0000-00-00 00:00:00', 1,'','','',0)";
  			mysql_query($query);
  			echo "<br/><strong>Username:</strong> Admin<br/><strong>Password:</strong> $random_password (Please change this password as soon as you login.)<br/><br/>";

  			echo "To add music to the database, choose the 'Admin' tab and click on 'Add Music to Database'";
  		}
		break;
} // END SWITCH
?>
</p>
</div>

</body>


</html>
