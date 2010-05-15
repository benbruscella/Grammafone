<?php
include("base.inc.php");

$step = 1;
if(isset($_GET['step'])){
	$step = $_GET['step'];
}

function installed(){
  $query = "SELECT user_id FROM grammafone_users";
  $result = @mysql_query($query);
	if(@mysql_num_rows($result) > 0){
		echo "<strong class='error'>It appears that you have already installed GrammaFone on this computer.</strong><br/><br/>";
		echo "<a href=\"$GLOBALS[http_url]$GLOBALS[uri_path]/\">Login to your GrammaFone server</a><br/>";
	    return TRUE;
	}
	return FALSE;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo $GLOBALS['server_title']; ?> | Install Page</title>
    <link rel="Stylesheet" href="css/install.css" type="text/css" />
</head>
<body>
<div id="topinfo">
	<div class="right">Installation Page</div>
	<strong>Grammafone Music System</strong>
</div>
<div id="wrap">
	<div id="header">
		<h1>GrammaFone Quick Install - Step <?php echo $step; ?></h1>
	</div>
	<div class='pad'>
	<?php
	switch($step){
		case 1:
			if(grammafone_connect()){
				if(!installed()){
				  echo "<p><strong>Welcome to the GrammaFone installation page</strong></p>";
				  echo "<p>This is a very simple and easy installation.  You'll be enjoying your music in no time at all. I swear.</p>";
				  echo "<p><a href='install.php?step=2'>Proceed to Step 2 &raquo;</a></p>";
				}

			}
			else{
				echo "<p><strong class='error'>Unable to establish MySQL connection to database '".$GLOBALS[db_name]."'</strong></p>";
				echo "<p>Please make sure you have created the database and that the database settings in your configuration file are correct.</p>";
			}
		break;
		// Test to see if DB and user are set in conf file
		// Test DB connection
		// If good install Tables

		// Give instructions for setting permissions and external programs
		// mpg123, lame, Amazon API, php bin
		case 2:
			grammafone_connect();
			if(!installed()){
				$querys['albums'] = "CREATE TABLE grammafone_albums (
				  album_id int(11) NOT NULL auto_increment,
				  album_name varchar(255) NOT NULL default '',
				  artist_id int(255) NOT NULL default '0',
				  album_genre varchar(50) default NULL,
				  album_year smallint(6) NOT NULL default '0',
				  album_art text NOT NULL,
				  PRIMARY KEY  (album_id)
				) TYPE=MyISAM";

				$querys['artists'] = "CREATE TABLE grammafone_artists (
				  artist_id int(11) NOT NULL auto_increment,
				  artist_name varchar(255) default NULL,
				  prefix varchar(7) NOT NULL default '',
				  PRIMARY KEY  (artist_id)
				) TYPE=MyISAM";

				$querys['current'] = "CREATE TABLE grammafone_currentsong (
				  song_id int(11) NOT NULL default '0',
				  pl_id int(11) NOT NULL default '0',
				  random tinyint(3) NOT NULL default '0'
				) TYPE=MyISAM";


				$querys['genres'] = "CREATE TABLE grammafone_genres (
				  genre_id int(11) NOT NULL auto_increment,
				  genre varchar(25) NOT NULL default '',
				  PRIMARY KEY  (genre_id)
				) TYPE=MyISAM";

				$querys['play_history'] = "CREATE TABLE grammafone_playhistory (
				  play_id int(11) NOT NULL auto_increment,
				  user_id int(6) default NULL,
				  song_id int(11) default NULL,
				  date_played datetime default NULL,
				  PRIMARY KEY  (play_id)
				) TYPE=MyISAM";

				$querys['playlist'] = "
				CREATE TABLE grammafone_playlist (
				  pl_id int(11) NOT NULL,
				  song_id int(11) default NULL,
				  user_id int(11) NOT NULL default '0',
				  private tinyint(4) NOT NULL default '0',
				  INDEX(user_id, private, pl_id)
				) TYPE=MyISAM";

				$querys['playlists'] = "CREATE TABLE grammafone_saved_playlists (
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


				$querys['songs'] = "CREATE TABLE grammafone_songs (
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


				$querys['stats'] = "CREATE TABLE grammafone_stats (
				  num_artists smallint(5) unsigned NOT NULL default '0',
				  num_albums smallint(5) unsigned NOT NULL default '0',
				  num_songs mediumint(8) unsigned NOT NULL default '0',
				  num_genres tinyint(3) unsigned NOT NULL default '0',
				  total_time varchar(12) NOT NULL default '0',
				  total_size varchar(10) NOT NULL default '0'
				) TYPE=MyISAM";

				$querys['logins'] ="CREATE TABLE grammafone_logins (
				  login_id int(11) NOT NULL auto_increment,
				  user_id int(11) default NULL,
				  date int(11) default NULL,
				  md5 varchar(100) NOT NULL default '',
				  PRIMARY KEY  (login_id)
				) TYPE=MyISAM";

				$querys['invites'] = "CREATE TABLE grammafone_invites (
				  invite_id int(11) NOT NULL auto_increment,
				  email varchar(100) NOT NULL default '',
				  date_created datetime NOT NULL default '0000-00-00 00:00:00',
				  invite_code varchar(255) NOT NULL default '',
				  PRIMARY KEY  (invite_id)
				) TYPE=MyISAM";

				$querys['themes'] ="CREATE TABLE grammafone_themes (
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
				$querys['theme1'] = "INSERT INTO `grammafone_themes` VALUES (NULL, 'default blue', '#0E2F58', '#244A79', '#416899', '#9ABEE5', '#F48603', 0)";
				$querys['theme2'] = "INSERT INTO `grammafone_themes` VALUES (NULL, 'green', '#194904', '#2E6D12', '#60A041', '#89C86E', '#3873A1', 0)";
				$querys['theme3'] = "INSERT INTO `grammafone_themes` VALUES (NULL, 'red', '#6D0C11', '#912328', '#B44146', '#CEB78B', '#7A643A', 0)";

				$querys['users'] = "CREATE TABLE grammafone_users (
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

				$querys['audioscrobbler'] = "CREATE TABLE IF NOT EXISTS grammafone_audioscrobbler (
				  as_id int(11) NOT NULL auto_increment,
				  user_id int(11) NOT NULL default '0',
				  song_id int(11) NOT NULL default '0',
				  as_timestamp varchar(100) NOT NULL default '',
				  PRIMARY KEY  (as_id)
				) TYPE=MyISAM";

				$querys['settings'] = "CREATE TABLE grammafone_settings (
				  id int(3) NOT NULL auto_increment,
				  version varchar(15) NOT NULL default '',
				  invite_mode tinyint(4) NOT NULL default '0',
				  upload_path varchar(255) NOT NULL default '',
				  amazonid varchar(255) NOT NULL default '',
				  downloads tinyint(4) NOT NULL default '0',
				  sample_mode tinyint(2) NOT NULL default '0',
				  lamebin varchar(100) NOT NULL default '',
				  PRIMARY KEY  (id)
				) TYPE=MyISAM";

				$querys['settingsinfo'] = "INSERT INTO `grammafone_settings` VALUES (NULL,'svn',0, '', '', 0,'', '')";

				echo "<p><strong>Creating GrammaFone Database Tables...</strong></p>";
				//  CREATE TABLES
				$error = 0;
				foreach($querys as $key=>$query){
					if(mysql_query($query)){

					}else{
						$error = 1;
					}
				}
				if(!$error){
					echo "<p><strong>GrammaFone Databases Installed Successfully</strong></p>";
				}else{
					die("<p>Error during setup</p>");
				}
				echo "<p><a href='install.php?step=3'>Proceed to Step 3 &raquo;</a></p>";
			}	/* End of a huge case :-( */
		break;
		case 3:
			grammafone_connect();
			if(!installed()){
			?>
				<p><strong class='error'>Take a Moment to Configure Your GrammaFone Installation.</strong></p>
				<p>You don't have to set these now. They are accessible from the Admin menu. However some of the options are neccessary for some features to work.</p>
				<form method='post' action="install.php?step=4">
					<p class='pad'>
						<strong>Invitation for Registration</strong><br/>(Users are required to be invited to register)<br/><select name='invite'><option value='0' >Not Required</option><option value='1'>Required</option></select><br/><br/>
						<strong>Sample Mode</strong><br/>(play 1/4 of each song)<br/><select name='sample_mode'><option value='0'>Sample Mode OFF</option><option value='1' >Sample Mode ON</option></select><br/><br/>
						<strong>Music Downloads</strong><br/>(Rules for Users Downloading Music)<br/><select name='downloads'><option value='0' >Not Allowed</option><option value='1' >Allowed for All</option><option value='2' >Allowed with Permission</option></select><br/><br/>
						<strong>Amazon API Key</strong><br/>(needed for downloading Album Art) <a href='http://www.amazon.com/webservices/' target='_new'>Obtain Key</a><br/><input type='text' size='30' name='amazonid' /><br/><br/>
						<strong>Path to Lame Encoder</strong><br/>(ex. /usr/bin/lame)<br/><input type='text' size='30' name='lamebin'  /><br/><br/>
						<input type='submit' value='save settings and continue &raquo;' class='btn' />
					</p>
				</form>
			<?php
			}
		break;
		case 4:
			grammafone_connect();
			if(!installed()){
				$amazon_api_id = trim($_POST['amazonid']);
				$query = "UPDATE grammafone_settings SET invite_mode=$_POST[invite],sample_mode=$_POST[sample_mode],downloads=$_POST[downloads],amazonid=\"$amazon_api_id\",lamebin=\"$_POST[lamebin]\" WHERE id=1";
				mysql_query($query);
				echo "<p><strong>Settings Saved....</strong></p>";
				echo "<p><strong>Installation Successful!</strong></p>";
  			if(!ini_get('allow_url_fopen')){
  				echo "<p><strong class='error'>WARNING: </strong>Need to Set allow_url_fopen to 'On' in your php.ini file.</p>";
  			}
  			if(!is_writable($GLOBALS['abs_path']."/art/")){
  				echo "<p><strong class='error'>WARNING: </strong>The /art/ directory is currently not writable. Please change the permissions on this directory if you wish to use Album Art.</p>";

  			}
  			echo "<p><a href=\"$GLOBALS[http_url]$GLOBALS[uri_path]/\">Login to your new GrammaFone server</a></p>";
  			$random_password = substr(md5(uniqid(microtime())), 0, 6);
  			$query = "INSERT INTO `grammafone_users` VALUES (NULL, 'admin', 'Admin', 'User', PASSWORD(\"$random_password\"), 10, NOW(), 1, '', 'player', 0, 's', '21232f297a57a5a743894a0e4a801fc3', '', '0000-00-00 00:00:00', 1,'','','',0)";
  			mysql_query($query);
  			echo "<p><strong>Username:</strong> Admin<br/><strong>Password:</strong> $random_password (Please change this password as soon as you login.)</p>";
  			echo "<p>To add music to the database, choose the 'Admin' tab and click on 'Add Music to Database'</p>";
  		}
		break;
	} // END SWITCH
?>
	</div>
</div>
</body>
</html>