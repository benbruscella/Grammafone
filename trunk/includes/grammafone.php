<?php
  /******************************************
   *  functions
   *  http://www.grammafone.com
   *
   *
   ******************************************/
  require_once('config.php');
  
  $version = "0.1b1";
  $DB = new DB($GLOBALS['db_name'], $GLOBALS['mysql_server'], $GLOBALS['db_user'], $GLOBALS['db_pw']);
  
  /**
   * logger function to help debug ajax calls on the server
   */
  function logger($string) {
      $logger = new Logger('true', "grammafone.log");
      $logger->logTofile($string);
  }
  
  function grammafone_connect() {
  	$db=$DB;
  	return 1;
  }
  
	function isInstalled() {
		$db = new DB($GLOBALS['db_name'], $GLOBALS['mysql_server'], $GLOBALS['db_user'], $GLOBALS['db_pw']);;
		$result = $db->query("SELECT user_id FROM grammafone_users");
		if ($db->numRows($result) > 0)
			return true;
		else
			return false;
	}
  
  function mailSend($to, $subject, $msg) {
      $headers = "MIME-Version: 1.0\n";
      $headers .= "Content-type: text/plain; charset=iso-8859-1\n";
      $headers .= "X-Priority: 3\n";
      $headers .= "X-MSMail-Priority: Normal\n";
      $headers .= "X-Mailer: PHP\n";
      $headers .= "From: \"GrammaFone server\" <noreply@grammafone.com>\n";
      $headers .= "Reply-To: noreply@grammafone.com\n";
      
      if (mail($to, $subject, $msg, $headers)) {
	  return true;
      }
  }
  
  function createInviteCode($email) {
      grammafone_connect();
      $code = '';
      $letters = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
      $seed = array_rand($letters, 10);
      foreach ($seed as $letter) {
	  $code .= $letters[$letter];
      }
      $code .= $email;
      $code = md5(md5($code));
      
      $query = "INSERT INTO grammafone_invites VALUES (NULL,\"$email\",NOW(),\"$code\")";
      if (mysql_query($query)) {
	  $msg = "$email,\n\nYou have been invited to join an GrammaFone Music Server. Click the link below to begin your registration process.\n\n";
	  $msg .= "$GLOBALS[http_url]$GLOBALS[uri_path]/register.php?invite=$code";
	  if (mailSend($email, 'Invitation to Join an GrammaFone Server', $msg)) {
	      return 1;
	  }
	  return 0;
      }
  }
  
  function checkInviteCode($code) {
      grammafone_connect();
      $query = "SELECT * FROM grammafone_invites WHERE invite_code=\"$code\"";
      $result = mysql_query($query);
      if (mysql_num_rows($result) > 0) {
	  $row = mysql_fetch_assoc($result);
	  return $row['email'];
      }
      return 0;
  }
  
  function sendPassword($email) {
      grammafone_connect();
      $query = "SELECT * FROM grammafone_users WHERE email=\"$email\"";
      $result = mysql_query($query);
      if (mysql_num_rows($result) == 0) {
	  return 0;
      } else {
	  $row = mysql_fetch_array($result);
	  $random_password = substr(md5(uniqid(microtime())), 0, 6);
	  $query = "UPDATE grammafone_users SET password=PASSWORD(\"$random_password\") WHERE user_id=$row[user_id]";
	  mysql_query($query);
	  $msg = "$email,\n\nYou have requested a new password for the GrammaFone server you are a member of. Your password has been reset to a new random password. When you login please change your password to a new one of your choice.\n\n";
	  $msg .= "Username: $row[username]\nPassword: $random_password\n\nLogin here: http://$GLOBALS[http_server]$GLOBALS[uri_path]/login.php";
	  $headers = "";
	  $headers .= "MIME-Version: 1.0\n";
	  $headers .= "Content-type: text/plain; charset=iso-8859-1\n";
	  $headers .= "X-Priority: 3\n";
	  $headers .= "X-MSMail-Priority: Normal\n";
	  $headers .= "X-Mailer: PHP\n";
	  $headers .= "From: \"GrammaFone server\" <noreply@grammafone.com>\n";
	  $headers .= "Reply-To: noreply@grammafone.com\n";
	  mail($email, 'Your Password for GrammaFone', $msg, $headers);
	  return 1;
      }
  }
  
  function isLoggedIn() {
      if (isset($_SESSION['sess_logged_in']) && (isset($_SESSION['sess_last_ip']) && $_SESSION['sess_last_ip'] == $_SERVER['REMOTE_ADDR'])) {
	  return 1;
      } elseif (isset($_COOKIE["grammafonet_cookie"])) {
	  grammafone_connect();
	  $query = "SELECT * FROM grammafone_logins WHERE md5=\"$_COOKIE[grammafone_cookie]\"";
	  $result = mysql_query($query);
	  $row = mysql_fetch_array($result);
	  
	  if ((time() - $row['date']) < (60 * 60 * 24 * 30)) {
	      $query = "SELECT * FROM grammafone_users WHERE user_id=$row[user_id]";
	      $result = mysql_query($query);
	      $userinfo = mysql_fetch_assoc($result);
	      
	      if ($userinfo['last_ip'] != $_SERVER['REMOTE_ADDR']) {
		  setcookie("grammafone_cookie", " ", time() - 3600);
		  return 0;
	      }
	      
	      $_SESSION['sess_username'] = $userinfo['username'];
	      $_SESSION['sess_firstname'] = $userinfo['firstname'];
	      $_SESSION['sess_lastname'] = $userinfo['lastname'];
	      $_SESSION['sess_userid'] = $userinfo['user_id'];
	      $_SESSION['sess_accesslevel'] = $userinfo['accesslevel'];
	      $_SESSION['sess_playmode'] = 'player';
	      
	      $_SESSION['sess_stereo'] = $userinfo['default_stereo'];
	      $_SESSION['sess_bitrate'] = $userinfo['default_bitrate'];
	      $_SESSION['sess_usermd5'] = $userinfo['md5'];
	      $_SESSION['sess_theme_id'] = $userinfo['theme_id'];
	      $_SESSION['sess_last_ip'] = $_SERVER['REMOTE_ADDR'];
	      $_SESSION['sess_logged_in'] = 1;
	      return 1;
	  } else {
	      setcookie("grammafone_cookie", " ", time() - 3600);
	      return 0;
	  }
      } else {
	  return 0;
      }
  }
  
  function accessLevel($level) {
      return($_SESSION['sess_accesslevel'] >= $level);
  }
  
  function switchMode($mode) {
      $_SESSION['sess_playmode'] = $mode;
      return $mode;
  }
  
  function getAlt($count) {
      return "class=\"" . (($count % 2 == 0) ? "no" : "") . "alt\"";
  }
  
  function getSystemSetting($setting) {
      grammafone_connect();
      $query = "SELECT $setting FROM grammafone_settings WHERE id=1";
      $result = mysql_query($query);
      $row = mysql_fetch_array($result);
      return $row[$setting];
  }
  
  function setCurrentSong($song_id, $pl_id, $rand = 0) {
      grammafone_connect();
      $query = "DELETE FROM grammafone_currentsong";
      mysql_query($query);
      $query = "INSERT INTO grammafone_currentsong VALUES ($song_id,$pl_id,$rand)";
      mysql_query($query);
  }
  
  function insertScrobbler($song_id, $user_id, $type = 'streaming') {
      grammafone_connect();
      if (hasScrobbler($user_id, $type)) {
	  $sql = "INSERT INTO grammafone_audioscrobbler VALUES (NULL,$user_id,$song_id,\"" . time() . "\")";
	  if (mysql_query($sql)) {
	      submitScrobbler($user_id);
	      return true;
	  }
      }
      return false;
  }
  function hasScrobbler($user_id, $type = 'streaming') {
      grammafone_connect();
      $mode['streaming'] = '(as_type=1 OR as_type=2)';
      $mode['player'] = 'as_type=2';
      $sql = "SELECT as_username FROM grammafone_users WHERE user_id=$user_id AND $mode[$type] AND as_username!='' AND as_password!=''";
      $result = mysql_query($sql);
      if (mysql_num_rows($result) > 0)
	  return true;
      return false;
  }
  
  function getScrobblerStats($user_id) {
      $as = array();
      grammafone_connect();
      $sql = "SELECT as_username,as_lastresult FROM grammafone_users WHERE user_id=$user_id";
      $result = mysql_query($sql);
      $row = mysql_fetch_array($result);
      $as['username'] = $row['as_username'];
      $as['last_result'] = $row['as_lastresult'];
      
      $sql = "SELECT COUNT(as_id) as count FROM grammafone_audioscrobbler WHERE user_id=$user_id";
      $result = mysql_query($sql);
      $row = mysql_fetch_array($result);
      $as['count'] = $row['count'];
      
      return $as;
  }
  
  function submitScrobbler($user_id) {
      echo "Scrobbler support temporarily disabled";
      return 1;
  }
  

















  function letters() {
      $output = "<ul id=\"letters\">";
      $letters = array('#', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
      
      foreach ($letters as $letter) {
	  $output .= "<li><a href=\"#\" onclick=\"updateBox('letter','$letter'); return false;\">" . strtoupper($letter) . "</a></li>\n";
      }
      $output .= "</ul>";
      return $output;
  }
  
  function getDropDown($type, $id) {
      $dropdown = "";
      return $dropdown;
  }
  
  function buildBreadcrumb($page, $parent, $parentitem, $child, $childitem) {
      grammafone_connect();
      $childoutput = '';
      $parentoutput = '';
      if ($page == 'browse' && $child != '') {
	  $output = "<a href=\"#\" onclick=\"updateBox('browse',0); return false;\">Browse</a> &#187; ";
      }
      switch ($child) {
	  case 'album':
	      $query = "SELECT grammafone_albums.album_name,grammafone_artists.artist_name,grammafone_artists.artist_id,grammafone_artists.prefix FROM grammafone_albums,grammafone_artists WHERE grammafone_albums.artist_id=grammafone_artists.artist_id AND grammafone_albums.album_id=$childitem";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      $albums = '';
	      $query = "SELECT album_name,album_id FROM grammafone_albums WHERE artist_id=$row[artist_id] ORDER BY album_name";
	      $result = mysql_query($query);
	      while ($row2 = mysql_fetch_array($result)) {
		  $albums .= "<li><a href=\"#\" onclick=\"updateBox('album',$row2[album_id]); return false;\" title=\"View Details of $row2[album_name]\">$row2[album_name]</a></li>";
	      }
	      $childoutput .= "<span><a href=\"#\" onclick=\"updateBox('artist'," . $row['artist_id'] . "); return false;\">" . $row['prefix'] . " " . $row['artist_name'] . "</a><ul>$albums</ul></span> &#187; " . htmlentities($row['album_name']);
	      break;
	  case 'artist':
	      $query = "SELECT artist_name,prefix FROM grammafone_artists WHERE artist_id=$childitem";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      $albums = '';
	      $query = "SELECT album_name,album_id FROM grammafone_albums WHERE artist_id=$childitem ORDER BY album_name";
	      $result = mysql_query($query);
	      while ($row2 = mysql_fetch_array($result)) {
		  $albums .= "<li><a href=\"#\" onclick=\"updateBox('album',$row2[album_id]); return false;\" title=\"View Details of $row2[album_name]\">$row2[album_name]</a></li>";
	      }
	      $childoutput .= "<span><a href=\"#\" onclick=\"updateBox('artist',$childitem); return false;\">$row[prefix] $row[artist_name]</a><ul>$albums</ul></span>";
	      break;
	  case 'letter':
	      $childoutput .= "<span><a href=\"#\" onclick=\"updateBox('letter','$childitem'); return false;\">" . strtoupper($childitem) . "</a>" . letters() . "</span>";
	      break;
	  case 'genre':
	      $childoutput .= $childitem;
	      break;
	  case 'all':
	      $childoutput .= $childitem;
	      break;
      }
      switch ($parent) {
	  case 'letter':
	      $parentoutput .= "<span><a href=\"#\" onclick=\"updateBox('letter','$parentitem'); return false;\">" . strtoupper($parentitem) . "</a>" . letters() . "</span> &#187; ";
	      break;
	  case 'genre':
	      $query = "SELECT album_name FROM grammafone_albums WHERE album_id=$childitem";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      $parentoutput .= "<a href=\"#\" onclick=\"updateBox('genre','$parentitem'); return false;\">$parentitem</a> &#187; ";
	      break;
	  case 'all':
	      $parentoutput .= "<a href=\"#\" onclick=\"updateBox('all','$parentitem'); return false;\">$parentitem</a> &#187; ";
	      break;
      }
      if (isset($output)) {
	  return $output . $parentoutput . $childoutput;
      } else {
	  return '';
      }
  }
  
  function buildSongList($text, $result, $extra = '') {
      $count = 1;
      $items = array();
      $add = '';
      $text .= "<ul>\n";
      while ($row = mysql_fetch_array($result)) {
	  $alt = getAlt($count);
	  $id = "song$row[song_id]";
	  if ($extra = 'numplays') {
	      $add = "<small>" . $row['numplays'] . "Plays</small> ";
	  }
	  $text .= "<li id=\"$id\" $alt ondblclick=\"pladd('song',$row[song_id]); return false;\" >{$add}<a href=\"#\" onclick=\"pladd('song',$row[song_id]); return false;\" title=\"Add Song to Current Playlist\"><img src=\"img/add.gif\" /></a> <a href=\"#\" onclick=\"play('song',$row[song_id]); return false;\" title=\"Play this Song Now\"><img src=\"img/play.gif\" /></a> $row[track]. $row[name]<p>$row[numplays] Plays<br/><em>$row[length]</em></p></li>\n";
	  $items[] = $id;
	  $count++;
      }
      $text .= "</ul>\n";
      if ($count == 1)
	  return $text . "<p>No songs found</p>";
      $output = array();
      $output[] = $text;
      $output[] = $count - 1;
      $output = array_merge($output, $items);
      return $output;
  }
  
  function buildAlbumList($text, $query, $extra = '') {
      $result = mysql_query($query);
      $count = 1;
      $add = '';
      $text .= "<div id=\"viewer\"><ul>\n";
      while ($row = mysql_fetch_array($result)) {
	  $alt = getAlt($count);
	  $id = "album{$row['album_id']}";
	  if ($extra == 'pubdate') {
	      $add = "<small>" . $row['pubdate'] . "</small> ";
	  }
	  $text .= "<li $alt id=\"$id\">{$add}<a href=\"#\" onclick=\"pladd('album'," . $row['album_id'] . "); return false;\" title=\"Add Album to Current Playlist\"><img src=\"img/add.gif\" /></a> <a href=\"#\" onclick=\"play('album'," . $row['album_id'] . "); return false;\" title=\"Play this Album Now\"><img src=\"img/play.gif\" /></a> <a href=\"#\" onclick=\"updateBox('album'," . $row['album_id'] . "); return false;\" title=\"Click to View Details of " . $row['album_name'] . "\">" . $row['album_name'] . " (" . $row['artist_name'] . ", " . (($row['album_year'] != 0) ? ("<em>" . $row['album_year'] . ")</em>") : (")")) . "</a></li>\n";
	  $items[] = $id;
	  $count++;
      }
      $text .= "</ul></div>\n";
      $output = array();
      $output[] = $text;
      $output[] = $count - 1;
      $output = array_merge($output, $items);
      return $output;
  }
  
  function genreform() {
      grammafone_connect();
      $query = "SELECT * FROM grammafone_genres ORDER BY genre";
      $result = mysql_query($query);
      
      $output = "<select id=\"genre\" name=\"genre\" onchange=\"updateBox('genre',this.options[selectedIndex].value); return false;\">
    <option value=\"\" selected>Choose Genre..";
      
      while ($genre = mysql_fetch_array($result)) {
	  $output .= "  <option value=\"$genre[genre]\">$genre[genre]\n";
      }
      
      $output .= "</select>";
      
      return $output;
  }
  
  function browseSelection() {
      $output = "";
      $output .= "<select id=\"browse\" name=\"browse\" onchange=\"updateBox(this.options[selectedIndex].value, ''); return false;\">";
      $output .= "<option value=\"\" selected>Browse By...";
      $output .= "<option value=all>   Browse By Album Names";
//      $output .= "<option value=artist>Browse By Artist";
//      $output .= "<option value=genre> Browse By Genre";
      $output .= "<option value=recentadd> Browse By Recently Added";
      $output .= "<option value=recentplay> Browse By Recently Played";
      $output .= "<option value=topplay> Browse By Top Played";
      $output .= "</select>";

      $query = "SELECT * FROM grammafone_genres ORDER BY genre";
      $result = mysql_query($query);
      $output .= "<select id=\"genre\" name=\"genre\" onchange=\"updateBox('genre',this.options[selectedIndex].value); return false;\">";
      $output .= "<option value=\"\" selected>Browse Genre...";
      while ($genre = mysql_fetch_array($result)) {
	  $output .= "  <option value=\"$genre[genre]\">$genre[genre]\n";
      }
      $output .= "</select>";
      return $output;
  }
  
  function musicLookup($type, $itemid) {
      grammafone_connect();
      switch ($type) {
	  case 'browse':
	      $query = "SELECT * FROM grammafone_stats";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      $query = "SELECT COUNT(user_id) AS users FROM grammafone_users";
	      $result = mysql_query($query);
	      $row2 = mysql_fetch_array($result);
	      $query = "SELECT COUNT(play_id) AS songs FROM grammafone_playhistory";
	      $result = mysql_query($query);
	      $row3 = mysql_fetch_array($result);
	      
	      $output = "<div class=\"head\">";
	      $output .= "<h2>Browse the Music Collection</h2>";
	      $output .= browseSelection() . "</div>";
	      $output .= "<h3>Local Server Statistics</h3>\n";
	      $output .= "<p><strong>Songs:</strong> $row[num_songs]<br/>\n";
	      $output .= "<strong>Albums:</strong> $row[num_albums]<br/>\n";
	      $output .= "<strong>Artists:</strong> $row[num_artists]<br/>\n";
	      $output .= "<strong>Genres:</strong> $row[num_genres]<br/><br/>\n";
	      $output .= "<strong>Total Time:</strong> $row[total_time]<br/>\n";
	      $output .= "<strong>Total Size:</strong> $row[total_size]<br/><br/>\n";
	      $output .= "<strong>Registered Users:</strong> $row2[users]<br/>\n";
	      $output .= "<strong>Songs Played:</strong> $row3[songs]<br/></p>\n";
	      $output .= "<p>";
//              $output .= "<strong>By Artist Beginning With</strong><br/>" . letters() . "<br/></p>\n";
//              $output .= "<p><strong>By Genre</strong><br/>\n";
//              $output .= genreForm() . "<br/><br/>\n";
//              $output .= "<input type='button' value='Browse All Albums' onclick=\"updateBox('all','All'); return false;\" class='btn2' />\n";
	      $output .= "</p>\n";
	      break;
	  case 'search':
	      $output = "<div class=\"head\">";
	      $output .= "<h2>Search the Music Collection</h2>";
	      $output .= "<form onsubmit='return searchMusic(this)' method='get' action=''>\n";
	      $output .= "
			<input type='text' onfocus='this.select()' name='searchbox' size='25' id='searchbox' value='[enter your search terms]' />
			<select name='search_options' size='1'>
			    <option value='all'>Narrow Search By...</option>
			    <option value='artists'>Artists</option>
			    <option value='albums'>Albums</option>
			    <option value='songs'>Songs</option>
			</select>
			<input type='submit' value='GO' class='btn' /></form>";
	      $output .= "</div>";
	      break;
	  case 'letter':
	      if ($itemid == "#") {
		  $query = "SELECT * FROM grammafone_artists 
		WHERE artist_name 
		LIKE '0%' 
		OR artist_name LIKE '1%' 
		OR artist_name LIKE '2%' 
		OR artist_name LIKE '3%' 
		OR artist_name LIKE '4%' 
		OR artist_name LIKE '5%' 
		OR artist_name LIKE '6%' 
		OR artist_name LIKE '7%' 
		OR artist_name LIKE '8%'
		OR artist_name LIKE '9%'
		ORDER BY artist_name";
	      } else {
		  $query = "SELECT * FROM grammafone_artists
		WHERE artist_name LIKE '$itemid%'
		ORDER BY artist_name";
	      }
	      $result = mysql_query($query);
	      $output = "<div class=\"head\">";
	      $output .= "<h2>Artists Beginning with '" . strtoupper($itemid) . "'</h2></div>";
	      $output .= "<h2 id=\"breadcrumb\"></h2>";
	      $output .= "<p>
				<strong>Artist Listing</strong></p>" . letters() ."
				
				<ul>";
	      $count = 1;
	      while ($row = mysql_fetch_array($result)) {
		  $alt = getAlt($count);
		  $output .= "<li $alt><a href=\"#\" onclick=\"updateBox('artist',$row[artist_id]); return false;\" title=\"View Albums for $row[prefix] $row[artist_name]\">$row[prefix] $row[artist_name]</a></li>\n";
		  $count++;
	      }
	      $output .= "</ul>\n";
	      
	      break;
	      
	  case 'all':
	      $output = "<div class=\"head\">";
	      $output .= "<h2>Browsing All Albums</h2>";
	      $output .= browseSelection() . "</div>";              
//              $output .= "<strong><h3>All Album Listing (Sorted by Album Name):</strong></h3>";
	      $query = "SELECT 
			grammafone_artists.artist_name,
			grammafone_artists.prefix,
			grammafone_albums.* 
		      FROM 
			grammafone_albums,
			grammafone_artists 
		      WHERE 
			grammafone_albums.artist_id=grammafone_artists.artist_id 
		      ORDER BY 
		       album_name";
	      $output = buildAlbumList($output, $query);
	      break;
	      
	  case 'album':
	      $query = "SELECT grammafone_albums.*,grammafone_artists.artist_name,grammafone_artists.prefix,COUNT(grammafone_songs.song_id) as tracks,SEC_TO_TIME(SUM(grammafone_songs.length)) as time FROM grammafone_albums,grammafone_artists,grammafone_songs WHERE grammafone_albums.album_id=$itemid AND grammafone_albums.artist_id=grammafone_artists.artist_id AND grammafone_songs.album_id=$itemid GROUP BY grammafone_songs.album_id";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      
	      $album_art = '';
	      
	      
	      if ($row['album_art'] == "") {
		  $row['album_art'] = art_insert($row['album_id'], $row['artist_name'], $row['album_name']);
		  if ($row['album_art'] != '') {
		      $album_art = "<img onmouseover=\"showAlbumArt('block'); return false;\" onmouseout=\"showAlbumArt('none'); return false;\" src=\"art/$row[album_art]\" />\n";
		  }
	      } elseif ($row['album_art'] != "fail") {
		  $album_art = "<img onmouseover=\"showAlbumArt('block'); return false;\" onmouseout=\"showAlbumArt('none'); return false;\" src=\"art/$row[album_art]\" />\n";
	      }
	      $output = "<div class=\"head\">";
	      $output .= "<div class=\"right\"><a href=\"#\" onclick=\"stream('album'," . $row['album_id'] . "); return false;\" title=\"Stream this Album Now\">stream</a> <a href=\"#\" onclick=\"pladd('album',$row[album_id]); return false;\" title=\"Add Album to Current Playlist\">add</a> " . ((getSystemSetting("downloads") == 1 || (getSystemSetting("downloads") == 2 && accessLevel(5))) ? "<a href=\"#\" onclick=\"newWindow('download',$row[album_id]); return false;\" title=\"Download this Album Now\">download</a>" : "") . "</div>";
	      $output .= "<h2>" . $row['album_name'] . "</h2>" . $row['prefix'] . " " . $row['artist_name'] . "</div>";
	      $output .= "<h2 id=\"breadcrumb\"></h2>";
	      $output .= "<p>$album_art\n";
	      $output .= "  <strong>Tracks:</strong> $row[tracks]<br/>\n";
	      $output .= (($row['album_year'] != 0) ? ("<strong>Year:</strong> " . $row['album_year'] . "<br/>\n") : (""));
	      $output .= "  <strong>Genre:</strong> <a href=\"#\" onclick=\"updateBox('genre','$row[album_genre]'); return false;\" title=\"View Artists from $row[album_genre] Genre\">$row[album_genre]</a><br/>\n";
	      $output .= "  <strong>Play Time:</strong> $row[time]\n";
	      $output .= "  <br/><br/>\n";
	      $output .= "  <strong>Album Tracks</strong></p>\n";
	      $output .= "<img id='bigart' src=\"art/$row[album_art]\" />\n";
	      $query = "SELECT *,SEC_TO_TIME(length) as length FROM grammafone_songs WHERE album_id=$itemid ORDER BY track";
	      $output = buildSongList($output, mysql_query($query));
	      break;
	  case 'genre':
	      $query = "SELECT grammafone_artists.artist_id,grammafone_artists.artist_name,grammafone_artists.prefix FROM grammafone_artists,grammafone_albums WHERE grammafone_albums.album_genre='$itemid' AND grammafone_artists.artist_id=grammafone_albums.artist_id GROUP BY grammafone_artists.artist_id ORDER BY grammafone_artists.artist_name";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      
	      $output = "<div class=\"head\">";
	      $output .= "<h2>Artists for Genre '$itemid'</h2></div>";
	      $output .= "<p>
				<strong>Artist Listing</strong></p>" . letters() . "
				<ul>";
	      
	      $result = mysql_query($query);
	      
	      $count = 1;
	      while ($row = mysql_fetch_array($result)) {
		  $alt = getAlt($count);
		  $output .= "<li $alt><a href=\"#\" onclick=\"updateBox('artist',$row[artist_id]); return false;\" title=\"View Albums for $row[artist_name]\">$row[prefix] $row[artist_name]</a></li>\n";
		  $count++;
	      }
	      $output .= "</ul>\n";
	      break;
	  case 'artist':
	      $query = "SELECT artist_id,artist_name,prefix FROM grammafone_artists WHERE artist_id=$itemid";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      
	      $output = "<div class=\"head\">";
	      $output .= "<h2>$row[prefix] $row[artist_name]</h2></div>";
	      $output .= "<h2 id=\"breadcrumb\"></h2>";              
	      $output .= "<p>\n";
	      $output .= "<strong>Album Listing</strong></p>\n";
	      
	      $query = "SELECT grammafone_albums.* FROM grammafone_albums WHERE grammafone_albums.artist_id=$itemid ORDER BY grammafone_albums.album_name";
	      $output = buildAlbumList($output, $query);
	      break;
	  case 'admin':
	      $output = "<div class=\"head\">";
	      $output .= "<h2>Administration Panel</h2></div>";
	      $output .= "<p>\n";
	      $output .= "<strong>System Settings</strong><br/>\n";
	      $output .= "<a href='#' onclick=\"editSettings(0); return false;\" title='Edit System Systems'>Edit System Settings</a><br/>\n";
	      $output .= "</p>\n";
	      $output .= "<p>\n";
	      $output .= "<strong>Database Functions</strong><br/>\n";
	      $output .= "<a href='#' onclick=\"newWindow('add',0); return false;\" title='Add Music to the Database'>Add New Music to the Database</a><br/>\n";
	      $output .= "<a href='#' onclick=\"clearDB(); return false;\" title='Clear out the Database'>Clear Out the Music Database and Play History</a><br/>\n";
	      $output .= "</p>";
	      $output .= "<p>\n";
	      $output .= "<strong>User Functions</strong><br/>\n";
	      $output .= "<a href='#' onclick=\"adminEditUsers(0,'',''); return false;\" title='Edit User Permissions'>Edit User Accounts</a><br/>\n";
	      $output .= "<a href='#' onclick=\"adminAddUser(0); return false;\" title='Add New User Account'>Add New User Account</a><br/>\n";
	      $output .= "</p>";
	      
	      if (getSystemSetting("invite_mode") == 1) {
		  $output .= "<form onsubmit='return sendInvite(this)' method='get' action=''>\n";
		  $output .= "<p id='invite'>";
		  $output .= "<br/><strong>Send an Invitation for Registration<br/>\n";
		  $output .= "<input type='text' onfocus='this.select()' name='email' id='email' value='Enter Email Address of Recipient' size='32' /><br/>\n";
		  $output .= "<br/><input type='submit' value='send invite' class='btn' /></form>";
		  $output .= "</p>";
	      }
	      
	      break;
	  case 'prefs':
	      $query = "SELECT DATE_FORMAT(grammafone_users.date_created,'%M %D, %Y') as date_created FROM grammafone_users WHERE grammafone_users.user_id=$_SESSION[sess_userid]";
	      $query2 = "SELECT COUNT(play_id) as playcount FROM grammafone_playhistory WHERE user_id=$_SESSION[sess_userid] GROUP BY user_id";
	      $result = mysql_query($query);
	      $result2 = mysql_query($query2);
	      
	      $row = mysql_fetch_array($result);
	      $row2 = mysql_fetch_array($result2);
	      if (mysql_num_rows($result2) == 0) {
		  $row2['playcount'] = 0;
	      }
	      $dayssince = (time() - strtotime($row['date_created'])) / (60 * 60 * 24);
	      $output = "<div class=\"head\">";
	      $output .= "<h2>$_SESSION[sess_firstname] $_SESSION[sess_lastname]'s Account ($_SESSION[sess_username])</h2></div>";
	      $output .= "<p>\n";
	      $output .= "<strong>Date Joined:</strong> $row[date_created]<br/>\n";
	      $output .= "<strong>Songs Played:</strong> $row2[playcount]<br/>\n";
	      $output .= "<strong>Daily Average:</strong> " . round(($row2['playcount'] / $dayssince), 2) . " songs/day<br/><br/>\n";
	      $output .= "<a href='#' onclick=\"editUser('info',0); return false;\" >Edit User Info</a><br/>";
	      $output .= "<a href='#' onclick=\"editUser('settings',0); return false;\" >Edit User Settings</a><br/>";
	      $output .= "<a href='#' onclick=\"editUser('pass',0); return false;\" >Change Password</a><br/>";
	      if (hasScrobbler($_SESSION['sess_userid'])) {
		  $as = getScrobblerStats($_SESSION['sess_userid']);
		  $output .= "<strong>AudioScrobbler Submission Queue:</strong> $as[count] songs " . ($as['count'] > 0 ? "<a href='#' onclick=\"submitScrobbler($_SESSION[sess_userid]); return false;\" title='Force Submission to AudioScrobbler'>[submit]</a>" : "") . "<br/>\n";
		  $output .= "<strong>AudioScrobbler Response:</strong> $as[last_result]<br/>\n";
		  $output .= "<a href='http://www.audioscrobbler.com/user/$as[username]' target='_new' title='View Your AudioSrobbler Statistics Page'>View Your AudioSrobbler Statistics Page</a><br/><br/>\n";
	      }
	      $output .= "</p>";
	      
	      break;
	  case 'random':
	      $output = "<div class=\"head\">";
	      $output .= "<h2>Random Mix Maker</h2></div>";
	      $output .= "<form onsubmit='return randPlay(this)' method='get' action=''>\n<p>";
	      if ($_SESSION['sess_playmode'] == "streaming") {
		  $output .= "<strong>Number of Songs</strong><br/>\n
				<select name='random_count'>
				<option value=10>10 </option>
				<option value=20>20 </option>
				<option value=30>30 </option>
				<option value=40>40 </option>
				<option value=50>50 </option>
	 </select><br/>\n";
	      }
	      $output .= "<strong>Random Type</strong><br/>\n
				<select name='random_type' onchange=\"getRandItems(this.options[selectedIndex].value); return false;\" >
				<option value='' >Choose Type...</option>
				<option value='artists' >Artists</option>
				<option value='genre' >Genre</option>
				<option value='albums' >Albums</option>
				<option value='all' >Everything</option>
	 </select><br/>\n";
	      $output .= "<strong>Random Items</strong>\n<span id='rand_items'></span>
			<br/><br/>";
	      $output .= "<input type='submit' value='play mix' class='btn' />";
	      $output .= "</form></p>\n";
	      break;
	  case 'playlists':
	      $query = "SELECT *,SEC_TO_TIME(time) AS time2 FROM grammafone_saved_playlists WHERE private=0";
	      $result = mysql_query($query);
	      
	      $output = "<div class=\"head\">";
	      $output .= "<h2>Saved Playlists</h2></div>";
	      $output .= "<p><strong>Public Playlists</strong></p>\n";
	      $output .= "<ul>\n";
	      if (mysql_num_rows($result) == 0)
		  $output .= "No Public Playlists";
	      while ($row = mysql_fetch_array($result)) {
		  $output .= "<li><a href=\"#\" onclick=\"deletePlaylist($row[playlist_id]); return false;\" title='Delete this Saved Playlist'><img src=\"img/rem.gif\" /></a> <a href=\"#\" onclick=\"pladd('playlist',$row[playlist_id]); return false;\" title='Load this Saved Playlist'><img src=\"img/add.gif\" /></a> <a onclick=\"updateBox('saved_pl',$row[playlist_id]); \" title='Click to View Playlist' href='#'>$row[playlist_name] - $row[songcount] Songs ($row[time2])</a></li>";
	      }
	      $output .= "</ul>\n";
	      $output .= "<p><strong>Your Private Playlists</strong></p>\n";
	      $query = "SELECT *,SEC_TO_TIME(time) AS time2 FROM grammafone_saved_playlists WHERE private=1 AND user_id=$_SESSION[sess_userid] ORDER BY playlist_id DESC";
	      $result = mysql_query($query);
	      $output .= "<ul>\n";
	      if (mysql_num_rows($result) == 0)
		  $output .= "No Private Playlists";
	      while ($row = mysql_fetch_array($result)) {
		  $output .= "<li><a href=\"#\" onclick=\"pladd('playlist',$row[playlist_id]); return false;\" title='Load this Saved Playlist'><img src=\"img/add.gif\" /></a> <a href=\"#\" onclick=\"deletePlaylist($row[playlist_id]); return false;\" title='DELETE this Saved Playlist'><img src=\"img/rem.gif\" /></a> <a onclick=\"updateBox('saved_pl',$row[playlist_id]); \" title='Click to View Playlist' href='#'>$row[playlist_name] - $row[songcount] Songs ($row[time2])</a></li>";
	      }
	      $output .= "</ul>\n";
	      break;
	  case 'saved_pl':
	      $query = "SELECT *,SEC_TO_TIME(time) AS time2 FROM grammafone_saved_playlists WHERE playlist_id=$itemid";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      $output = "<div class=\"head\">";
	      $output .= "<div class=\"right\"><a href=\"#\" onclick=\"pladd('playlist',$row[playlist_id]); return false;\" title=\"Load Playlist \">load playlist</a></div>";
	      
	      $output .= "<h2>View Saved Playlist</h2></div>";
	      $output .= "<p><strong>Playlist Info</strong><br/>$row[songcount] Songs<br/>$row[time2]</p>\n";
	      $output .= "<p><strong>Playlist Songs</strong></p>\n";
	      $output .= "<ul>\n";
	      $songs = explode(",", $row['playlist_songs']);
	      
	      $count = 0;
	      foreach ($songs as $song) {
		  $query = "SELECT grammafone_songs.*,SEC_TO_TIME(grammafone_songs.length) AS length,grammafone_artists.artist_name FROM grammafone_artists,grammafone_songs WHERE grammafone_songs.song_id=$song AND grammafone_artists.artist_id=grammafone_songs.artist_id";
		  $result = mysql_query($query);
		  $row = mysql_fetch_array($result);
		  $alt = getAlt($count);
		  $output .= "<li $alt>$row[artist_name] - $row[name]<p>$row[numplays] Plays<br/><em>$row[length]</em></p></li>";
		  $count++;
	      }
	      $output .= "</ul>\n";
	      
	      break;
	  case 'about':
	      $output = "<div class=\"head\">";
	      $output .= "<h2>Gramophone.FM Music System - v" . getSystemSetting("version") . "</h2></div>";
	      $output .= "<p>\n";
	      $output .= "<strong>Date: </strong>2008<br/>\n";
	      $output .= "<strong>Website: </strong><a href='http://www.gramophone.fm' target='_blank'>http://www.gramophone.fm</a><br/>\n";
	      $output .= "<strong>Support: </strong><a href='http://forum.gramophone.fm' target='_blank'>http://forum.gramophone.fm</a><br/>\n";
	      
	      $output .= "
<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"new\">
<input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\">
<input  class='noborder' title='Donate to GrammaFone!' type=\"image\" src=\"https://www.paypal.com/en_AU/i/btn/btn_donate_LG.gif\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\">
<img alt=\"\" border=\"0\" src=\"https://www.paypal.com/en_AU/i/scr/pixel.gif\" width=\"1\" height=\"1\">
<input type=\"hidden\" name=\"encrypted\" value=\"-----BEGIN PKCS7-----MIIHVwYJKoZIhvcNAQcEoIIHSDCCB0QCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYAFXr06FSUvnc01UGiMOEPWLHJmJ2VQ9E6SAoZedMmWDHnROmymBea+tdDVQVr5nQkezGh5CyUQeOBcuwKUmes/JqiLScT3baYLQ7OgxqX87qBKZFOkNJA/23dWGhZMM0GxFSeCK70vU1wbtfLFIInwhK5wEttZIB+1xjqWGcxhLjELMAkGBSsOAwIaBQAwgdQGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIgyfE+6WkBpCAgbBjYtgj9ysT25kaRirR+eSsQPPMdXulu02cGfLiArP038e4ofH8trF3ge9L3T9PFzkVR8VgtP5wwTnwMONd91FM6FE9hQq+IEBtfI5Bu48ubyaqnSl2ziwoMaVDJcLSNx9w9oPzh/eji7R++0/EhvzxOXrDYZvGaxkAaFEn7V67jmiZuakYoCZ1Ede9vTauj0T6jx3EOAr1PukQ0PtWfR/3e7ME07RBe3fE4Lz4Fj61raCCA4cwggODMIIC7KADAgECAgEAMA0GCSqGSIb3DQEBBQUAMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTAeFw0wNDAyMTMxMDEzMTVaFw0zNTAyMTMxMDEzMTVaMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAwUdO3fxEzEtcnI7ZKZL412XvZPugoni7i7D7prCe0AtaHTc97CYgm7NsAtJyxNLixmhLV8pyIEaiHXWAh8fPKW+R017+EmXrr9EaquPmsVvTywAAE1PMNOKqo2kl4Gxiz9zZqIajOm1fZGWcGS0f5JQ2kBqNbvbg2/Za+GJ/qwUCAwEAAaOB7jCB6zAdBgNVHQ4EFgQUlp98u8ZvF71ZP1LXChvsENZklGswgbsGA1UdIwSBszCBsIAUlp98u8ZvF71ZP1LXChvsENZklGuhgZSkgZEwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tggEAMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEFBQADgYEAgV86VpqAWuXvX6Oro4qJ1tYVIT5DgWpE692Ag422H7yRIr/9j/iKG4Thia/Oflx4TdL+IFJBAyPK9v6zZNZtBgPBynXb048hsP16l2vi0k5Q2JKiPDsEfBhGI+HnxLXEaUWAcVfCsQFvd2A1sxRr67ip5y2wwBelUecP3AjJ+YcxggGaMIIBlgIBATCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwCQYFKw4DAhoFAKBdMBgGCSqGSIb3DQEJAzELBgkqhkiG9w0BBwEwHAYJKoZIhvcNAQkFMQ8XDTA4MDIxNzExMzIzMFowIwYJKoZIhvcNAQkEMRYEFC0FeVNe86b8gqiBmxEzD9ufQ1rLMA0GCSqGSIb3DQEBAQUABIGAeaPZKokjj7W19S2rHGNAulxTsyZqhyIwsm8wO89zDjpXpHQBZrbr1+T4M3SNk9bpePtAggHG49M+XwBUsQ/xebYvxeIWTCezhIEdt5tCjSQR2kE6jK/QrTfE6MTUzrh+K+AP9tkBafqOHN052LPBTFTzHmQFXhZVAMFF5CvW4Jw=-----END PKCS7-----\">
</form>\n";
	      
	      $output .= "</p>";
	      $output .= "<h3>Thanks to:</h3>\n";
	      $output .= "<p>Jon Buda</p>\n";
	      
	      break;
	  case 'recentadd':
	      $query = "SELECT grammafone_albums.album_name,grammafone_albums.album_id,
			grammafone_artists.artist_name, 
			DATE_FORMAT(grammafone_songs.date_entered,'%m.%d.%Y') as pubdate   
			FROM grammafone_songs,grammafone_albums,grammafone_artists 
			WHERE grammafone_songs.album_id=grammafone_albums.album_id 
			AND grammafone_artists.artist_id=grammafone_songs.artist_id 
			GROUP BY grammafone_songs.album_id ORDER BY grammafone_songs.date_entered DESC LIMIT 40";
	      $output = "<div class=\"head\">";
//              $output .= "<div class=\"right\"><a href=\"#\" onclick=\"switchPage('stats'); return false;\" title=\"Return to Statistics Page\">back</a></div>";
	      $output .= "<h2>Recently Added Albums</h2>";
	      $output .= browseSelection()."</div>";
	      
	      $output = buildAlbumList($output, $query, 'pubdate');
	      break;
	  case 'topplay':
	      $query = "SELECT grammafone_albums.album_name, grammafone_songs.numplays, grammafone_songs.name, 
			grammafone_artists.artist_name,grammafone_songs.song_id 
			FROM grammafone_songs,grammafone_albums,grammafone_artists 
			WHERE grammafone_songs.album_id=grammafone_albums.album_id 
			AND grammafone_artists.artist_id=grammafone_songs.artist_id 
			AND grammafone_songs.numplays > 0 
			ORDER BY grammafone_songs.numplays DESC LIMIT 40";
	      
	      $output = "<div class=\"head\">";
//              $output .= "<div class=\"right\"><a href=\"#\" onclick=\"switchPage('stats'); return false;\" title=\"Return to Statistics Page\">back</a></div>";
	      $output .= "<h2>Top Played Songs</h2>";
	      $output .= browseSelection() . "</div>";
	      $output = buildSongList($output, mysql_query($query), 'numplays');
	      break;
	  case 'recentplay':
	      $query = "SELECT grammafone_songs.name, grammafone_songs.song_id, 
			grammafone_artists.artist_name,
			DATE_FORMAT(grammafone_playhistory.date_played,'%m.%d.%Y') as playdate 
			FROM grammafone_songs,grammafone_artists,grammafone_playhistory 
			WHERE grammafone_songs.song_id=grammafone_playhistory.song_id
			AND grammafone_artists.artist_id=grammafone_songs.artist_id 
			ORDER BY grammafone_playhistory.play_id DESC LIMIT 40";
	      
	      $output = "<div class=\"head\">";
//              $output .= "<div class=\"right\"><a href=\"#\" onclick=\"switchPage('stats'); return false;\" title=\"Return to Statistics Page\">back</a></div>";
	      $output .= "<h2>Recently Played Songs</h2>";
	      $output .= browseSelection() . "</div>";
	      $output = buildSongList($output, mysql_query($query));
	      break;




























      }
      
      return $output;
  }
  function editSettings($update, $invite, $downloads, $amazonid, $upload_path, $sample_mode, $lamebinn) {
      grammafone_connect();
      if ($update) {
	  $query = "UPDATE grammafone_settings SET invite_mode=$invite,sample_mode=$sample_mode,downloads=$downloads,amazonid=\"$amazonid\",upload_path=\"$upload_path\",lamebin=\"$lamebin\" WHERE id=1";
	  mysql_query($query);
	  return 1;
      }
      
      $query = "SELECT * FROM grammafone_settings WHERE id=1";
      $result = mysql_query($query);
      $row = mysql_fetch_array($result);
      
      $output = "<div class=\"head\">";
      $output .= "<h2>Edit GrammaFone System Settings</h2></div>";
      $output .= "<form onsubmit='return editSettings(this)' method='get' action=''>\n";
      $output .= "<p>\n";
      $output .= "<strong>Invitation for Registration</strong><br/><select name='invite'><option value='0' " . ($row['invite_mode'] == '0' ? "selected" : "") . ">Not Required</option><option value='1' " . ($row['invite_mode'] == '1' ? "selected" : "") . ">Required</option></select><br/><br/>\n";
      $output .= "<strong>Sample Mode (play 1/4 of each song)</strong><br/><select name='sample_mode'><option value='0' " . ($row['sample_mode'] == '0' ? "selected" : "") . ">Sample Mode OFF</option><option value='1' " . ($row['sample_mode'] == '1' ? "selected" : "") . ">Sample Mode ON</option></select><br/><br/>\n";
      
      $output .= "<strong>Music Downloads</strong><br/><select name='downloads'><option value='0' " . ($row['downloads'] == '0' ? "selected" : "") . ">Not Allowed</option><option value='1' " . ($row['downloads'] == '1' ? "selected" : "") . ">Allowed for All</option><option value='2' " . ($row['downloads'] == '2' ? "selected" : "") . ">Allowed with Permission</option></select><br/><br/>\n";
      $output .= "<strong>Amazon API Key</strong> <a href='http://www.amazon.com/webservices/' target='_new'>Obtain Key</a><br/><input type='text' size='30' name='amazonid' value='$row[amazonid]' /><br/><br/>\n";
      $output .= "<strong>Upload Path for New Music</strong><br/><input type='text' size='30' name='upload_path' value='$row[upload_path]' /><br/><br/>\n";
      $output .= "<strong>Path to Lame Encoder</strong><br/><input type='text' size='30' name='lamebin' value='$row[lamebin]' /><br/><br/>\n";
      
      $output .= "<input type='submit' value='update settings' class='btn' /> <input type='button' value='cancel' onclick=\"switchPage('admin'); return false;\" class='redbtn' />\n";
      $output .= "</p></form>";
      return $output;
  }
  
  function adminAddUser($firstname = '', $lastname = '', $username = '', $email = '', $level = '', $pass = '') {
      if (!empty($firstname)) {
	  grammafone_connect();
	  $md5 = md5($username);
	  if (ajaxUserGet($username) == 1)
	      return 0;
	  $query = "INSERT INTO grammafone_users VALUES 
								(NULL,\"" . $username . "\",\"" . $firstname . "\",\"" . $lastname . "\",
								PASSWORD(\"" . $pass . "\"),$level,NOW(),1,\"" . $email . "\",\"player\",0,\"s\",\"$md5\",\"\",\"\",1,\"\",\"\",\"\",0)";
	  if (mysql_query($query)) {
	      return 1;
	  }
      } else {
	  $output = "<div class=\"head\">";
	  $output .= "<h2>Add a New User Account</h2></div>";
	  $output .= "<form onsubmit='return adminAddUser(this)' method='get' action=''>\n";
	  $output .= "<p>\n";
	  $output .= "<strong>First Name</strong><br/><input type='text' size='20' name='firstname' id='firstname' tabindex=1 value='' /><br/><br/>\n";
	  $output .= "<strong>Last Name</strong><br/><input type='text' size='20' name='lastname' id='lastname' tabindex=2 value='' /><br/><br/>\n";
	  $output .= "<strong>Desired Username</strong><br/><input type='text' size='20' name='username' id='username' tabindex=3 value='' /><br/><br/>\n";
	  $output .= "<strong>E-Mail Address</strong><br/><input type='text' size='30' name='email' id='email' tabindex=4 value='' /><br/><br/>\n";
	  $output .= "<strong>User Permission Level</strong><br/><select tabindex=5 name='perms'><option value='1'>1 - Normal User</option><option value='5' >5 - Downloading Allowed</option><option value='10'>10 - Administrator</option></select><br/><br/>\n";
	  $output .= "<strong>Password</strong><br/><input type='password' size='15' name='password' id='password' tabindex=6 value='' /><br/>\n";
	  $output .= "<strong>Retype Password</strong><br/><input type='password' size='15' name='password2' id='password2' tabindex=7 value='' /><br/><br/>\n";
	  
	  $output .= "<input type='submit' value='add account' class='btn' /> <input type='button' value='cancel' onclick=\"switchPage('admin'); return false;\" class='redbtn' />\n";
	  $output .= "</p></form>";
	  
	  return $output;
      }
  }
  
  function adminEditUsers($userid = 0, $action = 'list', $active = '', $perms = '') {
      grammafone_connect();
      if ($userid != 0) {
	  if ($action == 'user') {
	      $query = "SELECT * FROM grammafone_users WHERE user_id=$userid";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      $output = "<div class=\"head\">";
	      $output .= "<h2>Edit User - $row[username]</h2></div>";
	      $output .= "<form onsubmit=\"return adminEditUsers($userid,'mod',this)\" method='get' action=''>\n";
	      $output .= "<p>\n";
	      $output .= "<strong>User Status</strong><br/><select name='active'><option value='1' " . ($row['active'] == '1' ? "selected" : "") . ">Active</option><option value='0' " . ($row['active'] == '0' ? "selected" : "") . ">Disabled</option></select><br/><br/>\n";
	      $output .= "<strong>User Permission Level</strong><br/><select name='perms'><option value='1' " . ($row['accesslevel'] == '1' ? "selected" : "") . ">1 - Normal User</option><option value='5' " . ($row['accesslevel'] == '5' ? "selected" : "") . ">5 - Downloading Allowed</option><option value='10' " . ($row['accesslevel'] == '10' ? "selected" : "") . ">10 - Administrator</option></select><br/><br/>\n";
	      $output .= "<input type='submit' value='submit changes' class='btn' /> <input type='button' value='cancel' onclick=\"adminEditUsers(0); return false;\" class='redbtn' />\n";
	      $output .= "</p></form>";
	  } elseif ($action == 'mod') {
	      $query = "UPDATE grammafone_users SET active=$active, accesslevel=$perms WHERE user_id=$userid";
	      $result = mysql_query($query);
	      return 2;
	  } elseif ($action == 'del') {
	      $query = "DELETE FROM grammafone_users WHERE user_id=$userid";
	      $result = mysql_query($query);
	      return 1;
	  }
      } else {
	  $query = "SELECT * FROM grammafone_users WHERE username!=\"Admin\"";
	  $result = mysql_query($query);
	  $output = "<div class=\"head\">";
	  $output .= "<div class=\"right\"><a href=\"#\" onclick=\"switchPage('admin'); return false;\" title=\"Return to Admin Menu\">return to admin</a></div>";
	  
	  $output .= "<h2>Edit GrammaFone Users</h2></div><ul>";
	  $count = 1;
	  while ($row = mysql_fetch_array($result)) {
	      $alt = getAlt($count);
	      $output .= "<li $alt><span class='user'><strong>$row[username]</strong> - ($row[firstname] $row[lastname])</span> <span class='links'><a href='#' title='Edit User Settings' onclick=\"adminEditUsers($row[user_id],'user'); return false;\" >edit user</a> | <a href='#' title='Delete the User' onclick=\"adminEditUsers($row[user_id],'del'); return false;\" >delete user</a></span></li>";
	      $count++;
	  }
	  $output .= "</ul>";
      }
      return $output;
  }
  
  function editUser($type, $input1, $input2, $input3, $input4, $input5, $input6, $input7) {
      grammafone_connect();
      switch ($type) {
	  case 'info':
	      if (!empty($input1)) {
		  $query = "UPDATE grammafone_users SET firstname=\"$input1\",lastname=\"$input2\",email=\"$input3\" WHERE user_id=$_SESSION[sess_userid]";
		  mysql_query($query);
		  return 1;
	      }
	      $query = "SELECT * FROM grammafone_users WHERE user_id=$_SESSION[sess_userid]";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      
	      $output = "<div class=\"head\">";
	      $output .= "<h2>$_SESSION[sess_firstname] $_SESSION[sess_lastname]'s Account Information</h2></div>";
	      $output .= "<form onsubmit='return editUser(\"info\",this)' method='get' action=''>\n";
	      $output .= "<p>\n";
	      $output .= "<strong>First Name</strong><br/><input type='text' size='20' name='firstname' id='firstname' tabindex=1 value='$row[firstname]' /><br/><br/>\n";
	      $output .= "<strong>Last Name</strong><br/><input type='text' size='20' name='lastname' id='lastname' tabindex=2 value='$row[lastname]' /><br/><br/>\n";
	      $output .= "<strong>E-Mail Address</strong><br/><input type='text' size='30' name='email' id='email' tabindex=3 value='$row[email]' /><br/><br/>\n";
	      $output .= "<input type='submit' value='update info' class='btn' /> <input type='button' value='cancel' onclick=\"switchPage('prefs'); return false;\" class='redbtn' />\n";
	      $output .= "</p></form>";
	      
	      break;
	  case 'settings':
	      if (!empty($input1)) {
		  $query = "UPDATE grammafone_users SET default_mode=\"$input1\",default_bitrate=$input2,default_stereo=\"$input3\",theme_id=$input4,as_username=\"$input5\",as_password=\"$input6\",as_type=$input7 WHERE user_id=$_SESSION[sess_userid]";
		  mysql_query($query);
		  return 1;
	      }
	      $query = "SELECT * FROM grammafone_users WHERE user_id=$_SESSION[sess_userid]";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      
	      $output = "<div class=\"head\">";
	      $output .= "<h2>$_SESSION[sess_firstname] $_SESSION[sess_lastname]'s Account Settings</h2></div>";
	      $output .= "<form onsubmit='return editUser(\"settings\",this)' method='get' action=''>\n";
	      $output .= "<p>\n";
	      $output .= "<strong>Theme</strong><br/> 
				<select name='theme_id'>
				<option value=1 " . ($row['theme_id'] == 1 ? "selected" : "") . ">default blue</option>
				<option value=2 " . ($row['theme_id'] == 2 ? "selected" : "") . ">green</option>
				<option value=3 " . ($row['theme_id'] == 3 ? "selected" : "") . ">red</option>
	 </select><br/><br/>\n";
	      $output .= "<strong>Player Downsample</strong><br/>
	<select name='default_bitrate'>
				<option value='0' " . ($row['default_bitrate'] == '0' ? "selected" : "") . ">Don't Downsample </option>
				<option value='128' " . ($row['default_bitrate'] == '128' ? "selected" : "") . ">128 kbps </option>
				<option value='64' " . ($row['default_bitrate'] == '64' ? "selected" : "") . ">64 kbps </option>
				<option value='32' " . ($row['default_bitrate'] == '32' ? "selected" : "") . ">32 kbps </option>
	 </select><br/><br/>\n";
	      
	      $output .= "<strong>Player Stereo Setting</strong><br/>
	<select name='default_stereo'>
				<option value='s' " . ($row['default_stereo'] == 's' ? "selected" : "") . ">Stereo</option>
				<option value='m' " . ($row['default_stereo'] == 'm' ? "selected" : "") . ">Mono</option>
			  </select><br/><br/>\n";
	      $output .= "<strong>AudioScrobbler Username</strong><br/><input type='text' size='20' name='as_username' id='as_username'  value='$row[as_username]' /><br/><br/>\n";
	      $output .= "<strong>AudioScrobbler Password</strong><br/><input type='password' size='20' name='as_password' id='as_password'  value='$row[as_password]' /><br/><br/>\n";
	      $output .= "<strong>AudioScrobbler Usage</strong><br/><select name='as_type'><option value=1 " . ($row['as_type'] == 1 ? "selected" : "") . ">Streaming Only</option><option value=2 " . ($row['as_type'] == 2 ? "selected" : "") . ">Streaming + Player</option></select><br/><br/>\n";
	      
	      
	      $output .= "<input type='submit' value='update settings' class='btn' /> <input type='button' value='cancel' onclick=\"switchPage('prefs'); return false;\" class='redbtn' />\n";
	      $output .= "</p></form>";
	      
	      break;
	  case 'pass':
	      if (!empty($input1)) {
		  $query = "UPDATE grammafone_users SET password=PASSWORD(\"$input2\") WHERE user_id=$_SESSION[sess_userid]";
		  mysql_query($query);
		  return 1;
	      }
	      $query = "SELECT * FROM grammafone_users WHERE user_id=$_SESSION[sess_userid]";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      
	      $output = "<div class=\"head\">";
	      $output .= "<h2>$_SESSION[sess_firstname] $_SESSION[sess_lastname]'s Account Password</h2></div>";
	      $output .= "<form onsubmit='return editUser(\"pass\",this)' method='get' action=''>\n";
	      $output .= "<p>\n";
	      $output .= "<strong>Old Password</strong><br/><input type='password' size='20' name='old_password' id='old_password'   /><br/><br/>\n";
	      $output .= "<strong>New Password</strong><br/><input type='password' size='20' name='new_password' id='new_password'   /><br/><br/>\n";
	      $output .= "<strong>Re-Type New Password</strong><br/><input type='password' size='20' name='new_password2' id='new_password2'  /><br/><br/>\n";
	      $output .= "<input type='submit' value='change password' class='btn' /> <input type='button' value='cancel' onclick=\"switchPage('prefs'); return false;\" class='redbtn' />\n";
	      $output .= "</p></form>";
	      
	      break;
      }
      return $output;
  }
  
  function getRandItems($type) {
      grammafone_connect();
      $options = '';
      switch ($type) {
	  case 'artists':
	      $query = "SELECT * FROM grammafone_artists ORDER BY artist_name";
	      $result = mysql_query($query);
	      while ($row = mysql_fetch_array($result)) {
		  $options .= "<option value=$row[artist_id]>$row[prefix] $row[artist_name]</option>\n";
	      }
	      break;
	  case 'genre':
	      $query = "SELECT genre_id,genre FROM grammafone_genres ORDER BY genre";
	      $result = mysql_query($query);
	      while ($row = mysql_fetch_array($result)) {
		  $options .= "<option value=$row[genre_id]>$row[genre]</option>\n";
	      }
	      break;
	  case 'albums':
	      $query = "SELECT grammafone_artists.artist_name,grammafone_artists.prefix,grammafone_albums.album_id,grammafone_albums.album_name FROM grammafone_albums,grammafone_artists WHERE grammafone_albums.artist_id=grammafone_artists.artist_id ORDER BY artist_name,album_name";
	      $result = mysql_query($query);
	      while ($row = mysql_fetch_array($result)) {
		  $options .= "<option value=$row[album_id]>$row[prefix] $row[artist_name] - $row[album_name]</option>\n";
	      }
	      break;
	  case 'all':
	      return "<br/>All Songs";
	      break;
      }
      
      return "<select name='random_items' multiple size='12' style='width: 90%;'>$options</select>";
  }
  
  function searchMusic($terms, $option) {
      grammafone_connect();
      $query = "SELECT grammafone_songs.song_id, grammafone_albums.album_name,grammafone_songs.track,grammafone_artists.artist_name,grammafone_artists.prefix,grammafone_songs.name,SEC_TO_TIME(grammafone_songs.length) as length 
						FROM grammafone_songs,grammafone_artists,grammafone_albums WHERE grammafone_songs.artist_id=grammafone_artists.artist_id AND grammafone_albums.album_id=grammafone_songs.album_id AND ";
      if ($option == 'all')
	  $query .= "(grammafone_songs.name LIKE '%$terms%' OR grammafone_artists.artist_name LIKE '%$terms%' OR grammafone_albums.album_name LIKE '%$terms%')";
      elseif ($option == 'artists')
	  $query .= "(grammafone_artists.artist_name LIKE '%$terms%')";
      elseif ($option == 'albums')
	  $query .= "(grammafone_albums.album_name LIKE '%$terms%')";
      elseif ($option == 'songs')
	  $query .= "(grammafone_songs.name LIKE '%$terms%')";
      
      $query .= " ORDER BY grammafone_artists.artist_name,grammafone_albums.album_name,grammafone_songs.track";
      
      $result = mysql_query($query);
      $count = mysql_num_rows($result);
      
      $output = "<div class=\"head\">";
      $output .= "<div class=\"right\"><a href=\"#\" onclick=\"switchPage('search'); return false;\" title=\"Begin a New Search\">new search</a></div>";
      $output .= "<h2>Found $count Results for '$terms'</h2></div>";
      $output = buildSongList($output, $result);
      return $output;
  }
  
  function createSongLI($row) {
      $id = "pl_{$row[pl_id]}";
      return "<li id=\"$id\" song=\"{$row[song_id]}\" current_playing=\"no\" ondblclick=\"play('playlist', '$id')\"; onmouseover=\"setBgcolor(this.id,'#FCF7A5'); return false;\" onmouseout=\"setBgcolor(this.id,'#f3f3f3'); return false;\"><a href=\"#\" onclick=\"plrem(this.parentNode.id); return false;\" title=\"Remove Song from Playlist\"><img src=\"img/rem.gif\" /></a> <a href=\"#\" onclick=\"play('playlist', '$id'); return false;\" title=\"Play this song now\"><img src=\"img/play.gif\" /></a> $row[prefix] $row[artist_name] - $row[name]<p>Album: $row[album_name]<br/>Track: $row[track]<br/>$row[time]</p></li>";
  }
  
  // ivy: changed
  function viewPlaylist() {
      grammafone_connect();
      $text = '';
      $items = array();
      $query = "SELECT grammafone_playlist.*, grammafone_artists.artist_name,grammafone_artists.prefix, grammafone_songs.name,grammafone_albums.album_name,grammafone_songs.track,SEC_TO_TIME(grammafone_songs.length) AS time FROM grammafone_playlist,grammafone_artists,grammafone_songs,grammafone_albums WHERE grammafone_playlist.song_id=grammafone_songs.song_id AND grammafone_artists.artist_id=grammafone_songs.artist_id AND grammafone_songs.album_id=grammafone_albums.album_id AND " . playlistCondition() . " ORDER BY grammafone_playlist.pl_id";
      $result = mysql_query($query);
      while ($row = mysql_fetch_array($result)) {
	  // 20080111 Fix #175 changed "pl" to "pl_"
	  $id = "pl_{$row[pl_id]}";
	  $text .= createSongLI($row);
	  $items[] = $id;
      }
      $output[] = $text;
      $output[] = count($items);
      $output = array_merge($output, $items);
      return $output;
  }
  
  function savePlaylist($pl_name, $prvt) {
      grammafone_connect();
      $songs = array();
      $time = 0;
      $query = "SELECT grammafone_playlist.song_id,grammafone_songs.length FROM grammafone_playlist,grammafone_songs WHERE grammafone_songs.song_id=grammafone_playlist.song_id AND " . playlistCondition() . " ORDER BY grammafone_playlist.pl_id";
      $result = mysql_query($query);
      while ($row = mysql_fetch_array($result)) {
	  $songs[] = $row['song_id'];
	  $time += $row['length'];
      }
      $songslist = implode(",", $songs);
      $query = "INSERT INTO grammafone_saved_playlists VALUES (NULL,$_SESSION[sess_userid],$prvt,\"$pl_name\",\"$songslist\",NOW(),$time," . count($songs) . ")";
      mysql_query($query);
      return "<h2>Playlist Saved as '" . $pl_name . "'</h2>";
  }
  
  function clearPlaylist() {
      grammafone_connect();
      $query = "DELETE FROM grammafone_playlist  WHERE user_id=$_SESSION[sess_userid]";
      mysql_query($query);
      return "Playlist is empty";
  }
  function deletePlaylist($id) {
      grammafone_connect();
      $query = "DELETE FROM grammafone_saved_playlists WHERE playlist_id=$id";
      mysql_query($query);
      return 1;
  }
  
  function playlistInfo() {
      grammafone_connect();
      $query = "SELECT COUNT(grammafone_playlist.pl_id) as count, SEC_TO_TIME(SUM(grammafone_songs.length)) as time FROM grammafone_playlist,grammafone_songs WHERE grammafone_playlist.song_id=grammafone_songs.song_id AND " . playlistCondition();
      $result = mysql_query($query);
      $row = mysql_fetch_array($result);
      if ($row['count'] == 0) {
	  return "Playlist is empty";
      }
      return "$row[count] Songs - $row[time]";
  }
  
  // can be replaced by static string
  function playlistCondition() {
      return " " . "grammafone_playlist.user_id=" . $_SESSION['sess_userid'];
  }
  
  // playlist is an encoded string with playlist items
  function playlist_update($playlist) {
      grammafone_connect();
      $query = "SELECT song_id FROM grammafone_playlist WHERE " . playlistCondition() . " ORDER BY pl_id";
      $result = mysql_query($query);
      while ($row = mysql_fetch_array($result)) {
	  $current[] = $row['song_id'];
      }
      $current_nr = count($current);
      $new = explode(",", $playlist);
      
      if ($_SESSION['sess_playmode'] != "streaming") {
	  // renumber currently playing song
	  $query = "SELECT grammafone_currentsong.pl_id FROM grammafone_currentsong";
	  $result = mysql_query($query);
	  if ($row = mysql_fetch_array($result)) {
	      $currently_playing_pl_id = $row['pl_id'];
	  }
      }
      
      if ($current_nr > count($new)) {
	  $query = "DELETE FROM grammafone_playlist WHERE pl_id >=" . count($new) . " AND " . playlistCondition();
	  mysql_query($query);
	  $current_nr = count($new);
      }
      
      for ($i = 0; $i < $current_nr; $i++) {
	  if ($current[$i] != $new[$i]) {
	      $query = "UPDATE grammafone_playlist SET song_id=$new[$i] WHERE pl_id={$i} AND " . playlistCondition();
	      mysql_query($query);
	  }
      }
      
      while ($i < count($new)) {
	  $query = "INSERT INTO grammafone_playlist VALUES($id, $new[$i], $_SESSION[sess_userid]," . ($_SESSION['sess_playmode'] == "streaming" ? 1 : 0) . ")";
	  mysql_query($query);
	  $i++;
      }
      
      if (isset($currently_playing_pl_id)) {
	  $currently_playing_song = $current[$currently_playing_pl_id];
	  for ($i = 0; $i < count($new) && $new[$i] != $currently_playing_song; $i++)
	      if ($i < count($new)) {
		  // found, restore pl_id
		  if ($i != $currently_playing_pl_id) {
		      // moved, so renumber to $i
		      $query = "UPDATE grammafone_currentsong SET grammafone_currentsong.pl_id={$i}";
		      mysql_query($query);
		  }
	      } else {
		  // currently playing song has been removed from list
		  play("streaming", "restart", 0);
	      }
      }
  }
  
  function playlist_add($type, $itemid) {
      grammafone_connect();
      
      // get new id
      $query = "select count(*) as id from grammafone_playlist where " . playlistCondition();
      $result = mysql_query($query);
      $row = mysql_fetch_array($result);
      $id = $row['id'];
      
      switch ($type) {
	  case 'song':
	      $query = "INSERT INTO grammafone_playlist VALUES ($id,$itemid,$_SESSION[sess_userid]," . ($_SESSION['sess_playmode'] == "streaming" ? 1 : 0) . ")";
	      mysql_query($query);
	      $query = "SELECT grammafone_artists.artist_name, grammafone_artists.prefix,grammafone_albums.album_name,SEC_TO_TIME(grammafone_songs.length) AS length,grammafone_songs.name,grammafone_songs.track FROM grammafone_artists,grammafone_songs,grammafone_albums WHERE grammafone_songs.song_id=$itemid AND grammafone_artists.artist_id=grammafone_songs.artist_id AND grammafone_albums.album_id=grammafone_songs.album_id";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      $row['pl_id'] = $id;
	      $row['song_id'] = $itemid;
	      $output[] = createSongLI($row);
	      $output[] = 1;
	      $output[] = "pl_{$id}";
	      return $output;
	      break;
	  case 'album':
	      $items = '';
	      $output = array();
	      $query = "SELECT grammafone_songs.song_id,grammafone_songs.name,grammafone_artists.artist_name,grammafone_artists.prefix,grammafone_albums.album_name,SEC_TO_TIME(grammafone_songs.length) AS length,grammafone_songs.name,grammafone_songs.track FROM grammafone_songs,grammafone_artists,grammafone_albums WHERE grammafone_songs.album_id=$itemid AND grammafone_songs.artist_id=grammafone_artists.artist_id AND grammafone_albums.album_id=grammafone_songs.album_id ORDER BY track";
	      $result = mysql_query($query);
	      while ($row = mysql_fetch_array($result)) {
		  $query = "INSERT INTO grammafone_playlist VALUES($id," . $row['song_id'] . "," . $_SESSION['sess_userid'] . "," . ($_SESSION['sess_playmode'] == "streaming" ? 1 : 0) . ")";
		  mysql_query($query) or $items .= "Query ($query) failed: " . mysql_error();
		  $output[] = "pl_{$id}";
		  $row['pl_id'] = $id;
		  $items .= createSongLI($row);
		  $id++;
	      }
	      $text[] = $items;
	      $num[] = count($output);
	      $text = array_merge($text, $num);
	      $output = array_merge($text, $output);
	      return $output;
	      break;
	  case 'playlist':
	      clearPlaylist();
	      $id = 0;
	      $query = "SELECT * FROM grammafone_saved_playlists WHERE playlist_id=$itemid LIMIT 1";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      $songs = explode(",", $row['playlist_songs']);
	      
	      foreach ($songs as $song) {
		  $query = "INSERT INTO grammafone_playlist VALUES($id,$song,$_SESSION[sess_userid]," . ($_SESSION['sess_playmode'] == "streaming" ? 1 : 0) . ")";
		  mysql_query($query);
		  $id++;
	      }
	      $output[0] = 1;
	      return $output;
	      break;
      }
  }
  
  function randPlay($mode, $type, $num = 0, $items) {
      grammafone_connect();
      $tmp = '';
      $query = '';
      $items2 = explode(" ", $items);
      $items = '';
      
      
      session_cache_limiter('nocache');
      header("Content-Type: audio/mpegurl;");
      header("Content-Disposition: inline; filename=\"playlist.m3u\"");
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Pragma: nocache");
      $tmp .= "#EXTM3U\n";
      switch ($type) {
	  case 'artists':
	      foreach ($items2 as $item) {
		  $items .= " grammafone_songs.artist_id=$item OR";
	      }
	      $items = preg_replace("/OR$/", "", $items);
	      $query = "SELECT grammafone_songs.song_id,grammafone_artists.artist_name,grammafone_songs.name,grammafone_songs.length FROM grammafone_songs,grammafone_artists WHERE grammafone_artists.artist_id=grammafone_songs.artist_id AND (" . $items . ") ORDER BY rand()+0 LIMIT $num";
	      break;
	  case 'genre':
	      foreach ($items2 as $item) {
		  $items .= " grammafone_genres.genre_id=$item OR";
	      }
	      $items = preg_replace("/OR$/", "", $items);
	      $query = "SELECT grammafone_songs.song_id,grammafone_artists.artist_name,grammafone_songs.name,grammafone_songs.length FROM grammafone_songs,grammafone_artists,grammafone_genres,grammafone_albums WHERE grammafone_albums.album_id=grammafone_songs.album_id AND grammafone_albums.album_genre=grammafone_genres.genre AND grammafone_artists.artist_id=grammafone_songs.artist_id AND (" . $items . ") ORDER BY rand()+0 LIMIT $num";
	      break;
	  case 'albums':
	      foreach ($items2 as $item) {
		  $items .= " grammafone_songs.album_id=$item OR";
	      }
	      $items = preg_replace("/OR$/", "", $items);
	      $query = "SELECT grammafone_songs.song_id,grammafone_artists.artist_name,grammafone_songs.name,grammafone_songs.length FROM grammafone_songs,grammafone_artists WHERE grammafone_artists.artist_id=grammafone_songs.artist_id AND (" . $items . ") ORDER BY rand()+0 LIMIT $num";
	      
	      break;
	  case 'all':
	      $query = "SELECT grammafone_songs.song_id,grammafone_artists.artist_name,grammafone_songs.name,grammafone_songs.length FROM grammafone_songs,grammafone_artists WHERE grammafone_artists.artist_id=grammafone_songs.artist_id ORDER BY rand()+0 LIMIT $num";
	      break;
      }
      $result = mysql_query($query);
      
      while ($row = mysql_fetch_array($result)) {
	  $tmp .= "#EXTINF:$row[length],$row[artist_name] - $row[name]\n";
	  $tmp .= "$GLOBALS[http_url]$GLOBALS[uri_path]/playstream.php?i=$row[song_id]&u=$_SESSION[sess_usermd5]&b=$_SESSION[sess_bitrate]&s=$_SESSION[sess_stereo]\n";
      }
      return $tmp;
      exit;
  }
  
  
  /******************************************************************************
   * ? -> %3F
   * = -> %3D
   * & -> %26, as per player docuementation for streaming
   */
  function play($mode, $type, $id) {
      grammafone_connect();
      
      $tmp = '';
      $query = '';
      if ($type == 'song') {
	  $query = "SELECT grammafone_songs.song_id,grammafone_artists.artist_name,grammafone_artists.prefix,grammafone_songs.name,grammafone_songs.length,grammafone_songs.filename,grammafone_albums.album_art FROM grammafone_songs,grammafone_artists,grammafone_albums WHERE grammafone_songs.song_id=$id AND grammafone_artists.artist_id=grammafone_songs.artist_id AND grammafone_songs.album_id = grammafone_albums.album_id AND grammafone_songs.album_id = grammafone_albums.album_id";
      } elseif ($type == 'album') {
	  $query = "SELECT grammafone_songs.song_id,grammafone_artists.artist_name,grammafone_songs.name,grammafone_artists.prefix,grammafone_songs.length FROM grammafone_songs,grammafone_artists WHERE grammafone_artists.artist_id=grammafone_songs.artist_id AND grammafone_songs.album_id=$id ORDER BY grammafone_songs.track";
      } elseif ($type == 'pl') {
	  $query = "SELECT grammafone_songs.song_id,grammafone_artists.artist_name,grammafone_songs.name,grammafone_artists.prefix,grammafone_songs.length FROM grammafone_songs,grammafone_artists,grammafone_playlist WHERE grammafone_artists.artist_id=grammafone_songs.artist_id AND grammafone_songs.song_id=grammafone_playlist.song_id AND grammafone_playlist.user_id=$_SESSION[sess_userid] ORDER BY grammafone_playlist.pl_id";
      } elseif ($type == 'stop') {
	  $query = "DELETE FROM grammafone_currentsong";
	  mysql_query($query);
	  return "";
      }
      
      $result = mysql_query($query);
      
      if ($mode == 'player') {
	  session_cache_limiter('nocache');
	  header("Content-Type: application/xspf+xml;");
	  header("Content-Disposition: inline; filename=\"playlist.xspf\"");
	  header("Expires: 0");
	  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	  header("Pragma: nocache");
	  $tmp .= '<?xml version="1.0" encoding="UTF-8"?><playlist version="1" xmlns="http://xspf.org/ns/0/">' . "\n<trackList>\n";
	  
	  while ($row = mysql_fetch_array($result)) {
	      $length = $row['length'];
	      if (getSystemSetting("sample_mode") == 1) {
		  $length = floor($row['length'] / 4);
	      }
	      $tmp .= "<track>\n";
	      $tmp .= "\t<creator>$row[artist_name]</creator><title>$row[name]</title>\n";
	      $tmp .= "\t<location>$GLOBALS[http_url]$GLOBALS[uri_path]/playstream.php?i=$row[song_id]&u=$_SESSION[sess_usermd5]&b=$_SESSION[sess_bitrate]&s=$_SESSION[sess_stereo]</location>\n";
	      $tmp .= "\t<meta rel=\"type\">sound</meta>\n";
	      $tmp .= "\t<image>$GLOBALS[http_url]$GLOBALS[uri_path]/art/$row[album_art]</image>\n";	      
	      $tmp .= "</track>\n";
	      $tmp .= "   </trackList></playlist>\n";
	  }
	  setCurrentSong($id, 0, 0);
	  return $tmp;
      } else {
	  // We must be wanting to stream
	  session_cache_limiter('nocache');
	  header("Content-Type: audio/mpegurl;");
	  header("Content-Disposition: inline; filename=\"playlist.m3u\"");
	  header("Expires: 0");
	  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	  header("Pragma: nocache");
	  $tmp .= "#EXTM3U\n";
	  while ($row = mysql_fetch_array($result)) {
	      $length = $row['length'];
	      if (getSystemSetting("sample_mode") == 1) {
		  $length = floor($row['length'] / 4);
	      }
	      $tmp .= "#EXTINF:$length,$row[prefix] $row[artist_name] - $row[name]\n";
	      $tmp .= "$GLOBALS[http_url]$GLOBALS[uri_path]/playstream.php?i=$row[song_id]&u=$_SESSION[sess_usermd5]&b=$_SESSION[sess_bitrate]&s=$_SESSION[sess_stereo]\n";
	  }
	  return $tmp;
      }
  }
  
  function download($album) {
      grammafone_connect();
      $query = "SELECT grammafone_songs.filename,
	grammafone_artists.artist_name,
	grammafone_albums.album_name 
	FROM grammafone_songs,grammafone_artists,grammafone_albums 
	WHERE grammafone_songs.album_id=$album 
	AND grammafone_songs.album_id=grammafone_albums.album_id 
	AND grammafone_songs.artist_id=grammafone_artists.artist_id LIMIT 1";
      
      $result = mysql_query($query);
      $row = mysql_fetch_array($result);
      $dir = dirname($row['filename']);
      
      $test = new zip_file("/tmp/album_$album.zip");
      $test->set_options(array('inmemory' => 0, 'storepaths' => 0, 'level' => 0, 'method' => 0, 'prepend' => "$row[artist_name] - $row[album_name]"));
      $test->add_files($dir);
      
      $test->store_files($dir);
      $test->create_archive();
      
      header("Content-type:application/zip");
      
      $header = "Content-disposition: attachment; filename=\"";
      $header .= "album_$album.zip";
      $header .= "\"";
      header($header);
      header("Content-length: " . filesize("/tmp/album_$album.zip"));
      header("Content-transfer-encoding: binary");
      header("Pragma: no-cache");
      header("Expires: 0");
      // how many bytes per chunk
      $chunksize = 1 * (1024 * 1024);
      $buffer = '';
      $handle = fopen("/tmp/album_$album.zip", 'rb');
      if ($handle === false) {
	  return false;
      }
      while (!feof($handle)) {
	  $buffer = fread($handle, $chunksize);
	  print $buffer;
      }
      fclose($handle);
      //readfile("/tmp/album_$album.zip");
      unlink("/tmp/album_$album.zip");
      //$test->download_file();
  }
  
  function verifyIP($user_md5, $ip) {
      grammafone_connect();
      $query = "SELECT user_id FROM grammafone_users WHERE md5=\"$user_md5\" AND last_ip=\"$ip\"";
      $result = mysql_query($query);
      if (mysql_num_rows($result) > 0) {
	  return true;
      }
      return false;
  }
  
  
  function updateNumPlays($num, $r = 0, $user = '', $mode = 'streaming') {
      grammafone_connect();
      $query = "UPDATE grammafone_songs SET numplays=numplays+1";
      if ($r == 1) {
	  $query .= ",random=1";
      }
      $query .= " WHERE song_id=$num";
      mysql_query($query);
      
      if (!empty($user)) {
	  if ($mode == 'player') {
	      $query = "SELECT user_id FROM grammafone_users WHERE md5=\"$user\"";
	      $result = mysql_query($query);
	      $row = mysql_fetch_array($result);
	      $user = $row['user_id'];
	  }
	  $query = "INSERT INTO grammafone_playhistory VALUES (NULL,$user,$num,NOW())";
	  insertScrobbler($num, $user, $mode);
	  mysql_query($query);
      }
  }
  
  // function to replace file_get_contents() using CURL
function file_get_the_contents($url) {
    $ch = curl_init();
    $timeout = 5; // set to zero for no timeout
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $file_contents = curl_exec($ch);
    curl_close($ch);
    return $file_contents;
}  

  function art_insert($album_id, $artist, $album) {
      grammafone_connect();
      $query = '';
      $image = art_query($artist, $album);
      logger("art_insert IMAGE" . $image);
      
      if ($image != "") {
	  $query = "UPDATE grammafone_albums SET album_art=\"$album_id.jpg\" WHERE album_id=$album_id";
	  mysql_query($query);
	  $tmpimg = file_get_the_contents($image);
	  $path = $GLOBALS['abs_path'] . "/art/";
	  
	  $file = "$album_id.jpg";
	  $filename = $path . $file;
	  file_put_contents($filename,file_get_the_contents($image));
	  touch($filename);
	  
	  // Let's make sure the file exists and is writable first.
	  if (is_writable($filename)) {
	      // In our example we're opening $filename in append mode.
	      // The file pointer is at the bottom of the file hence
	      // that's where $somecontent will go when we fwrite() it.
	      if (!$handle = fopen($filename, 'a')) {
		  echo "Cannot open file ($filename)";
		  exit;
	      }
	      
	      // Write $somecontent to our opened file.
	      if (fwrite($handle, $tmpimg) === false) {
		  echo "Cannot write to file ($filename)";
		  exit;
	      }
	      
	      //echo "Success, wrote ($somecontent) to file ($filename)";
	      
	      fclose($handle);
	  } else {
	      echo "The file $filename is not writable";
	  }
	  
	  return $file;
      } else {
	  $query = "UPDATE grammafone_albums SET album_art=\"fail\" WHERE album_id=$album_id";
	  mysql_query($query);
      }
  }
  
  function art_query($artist, $album) {
      $album = preg_replace('!\(.+\)!', '', $album);
      $theq = "$artist, $album";
      $query = urlencode($theq);
      $amazonKey = getSystemSetting("amazonid");
      
      $Operation = "ItemSearch";
      $Version = "2007-07-16";
      $ResponseGroup = "ItemAttributes,Images";
      $request=
	 "http://ecs.amazonaws.com/onca/xml"
       . "?Service=AWSECommerceService"
       . "&AWSAccessKeyId=" . $amazonKey
       . "&Operation=" . $Operation
       . "&Version=" . $Version
       . "&SearchIndex=Music"
       . "&Keywords=" . $query
       . "&ResponseGroup=" . $ResponseGroup;
       logger($request . "\n");
       $response = file_get_the_contents($request);
       $parsed_xml = simplexml_load_string($response);
       if(isset($parsed_xml->OperationRequest->Errors->Error) || isset($parsed_xml->Items->Request->Errors)) {
	   logger("Artwork lookup failed");
	   return null;
       }
       else {
	    return $parsed_xml->Items->Item[0]->MediumImage->URL;
       }
  }
  
  
  function resetDatabase() {
      grammafone_connect();
      $query = array();
      $query[] = "TRUNCATE TABLE grammafone_songs";
      $query[] = "TRUNCATE TABLE grammafone_artists";
      $query[] = "TRUNCATE TABLE grammafone_albums";
      $query[] = "TRUNCATE TABLE grammafone_playlist";
      $query[] = "TRUNCATE TABLE grammafone_saved_playlists";
      $query[] = "TRUNCATE TABLE grammafone_genres";
      $query[] = "TRUNCATE TABLE grammafone_stats";
      $query[] = "TRUNCATE TABLE grammafone_playhistory";
      $query[] = "TRUNCATE TABLE grammafone_currentsong";
      
      foreach ($query as $q) {
	  mysql_query($q);
      }
      
      $path = $GLOBALS['abs_path'] . "/art/";
      
      if (is_dir($path)) {
	  if ($dh = opendir($path)) {
	      while (($file = readdir($dh)) !== false) {
		  if ($file != "." && $file != ".." && $file != ".svn") {
		      unlink($path . $file);
		  }
	      }
	      closedir($dh);
	  }
      }
      return 1;
  }
  
  function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
      echo "Error";
  }
?>
