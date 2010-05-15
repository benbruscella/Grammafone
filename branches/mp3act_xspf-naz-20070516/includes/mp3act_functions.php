<?php
/******************************************
*	mp3act functions
*	http://www.mp3act.net
*
*
******************************************/
include('mp3act_config.php');

function mp3act_connect() {
	if(@mysql_connect($GLOBALS['mysql_server'],$GLOBALS['db_user'],$GLOBALS['db_pw'])){
		if(@mysql_select_db($GLOBALS['db_name'])){
			return 1;
		}
		return 0;
	}
	return 0;
}

function mp3act_sendmail($to,$subject,$msg){
  $headers = "MIME-Version: 1.0\n";
  $headers .= "Content-type: text/plain; charset=iso-8859-1\n";
  $headers .= "X-Priority: 3\n";
  $headers .= "X-MSMail-Priority: Normal\n";
  $headers .= "X-Mailer: PHP\n";
  $headers .= "From: \"mp3act server\" <noreply@mp3act.net>\n";
  $headers .= "Reply-To: noreply@mp3act.net\n";
  
  if(mail($to,$subject,$msg,$headers)){
    return TRUE;
  }
}

function createInviteCode($email){
		mp3act_connect();
		$code = '';
		$letters = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		$seed = array_rand($letters,10);
		foreach($seed as $letter){
			$code .= $letters[$letter];
		}
		$code .= $email;
		$code = md5(md5($code));

		$query = "INSERT INTO mp3act_invites VALUES (NULL,\"$email\",NOW(),\"$code\")";
		if(mysql_query($query)){
      
			$msg = "$email,\n\nYou have been invited to join an mp3act Music Server. Click the link below to begin your registration process.\n\n";
			$msg .= "$GLOBALS[http_url]$GLOBALS[uri_path]/register.php?invite=$code";
			if(mp3act_sendmail($email,'Invitation to Join an mp3act Server',$msg)){
			  return 1;
			}
			return 0;
		}
}

function checkInviteCode($code){
	mp3act_connect();
	$query = "SELECT * FROM mp3act_invites WHERE invite_code=\"$code\"";
	$result = mysql_query($query);
	if(mysql_num_rows($result) > 0 ){
		$row = mysql_fetch_assoc($result);
		return $row['email'];
	}
	return 0;
}

function sendPassword($email){
		mp3act_connect();
  	$query = "SELECT * FROM mp3act_users WHERE email=\"$email\"";
  	$result = mysql_query($query);
  	if(mysql_num_rows($result) == 0){
  		return 0;
  	}else{
  		$row = mysql_fetch_array($result);
  		$random_password = substr(md5(uniqid(microtime())), 0, 6);
  		$query = "UPDATE mp3act_users SET password=PASSWORD(\"$random_password\") WHERE user_id=$row[user_id]";
  		mysql_query($query);
  		$msg = "$email,\n\nYou have requested a new password for the mp3act server you are a member of. Your password has been reset to a new random password. When you login please change your password to a new one of your choice.\n\n";
			$msg .= "Username: $row[username]\nPassword: $random_password\n\nLogin here: $GLOBALS[http_url]$GLOBALS[uri_path]/login.php";
	    
			if(mp3act_sendmail($email,'Your Password for mp3act',$msg)){
  		  return 1;
  		}
  	}
  	
}

function isLoggedIn(){
		
   if(isset($_SESSION['sess_logged_in']) && (isset($_SESSION['sess_last_ip']) && $_SESSION['sess_last_ip'] == $_SERVER['REMOTE_ADDR'])){
    return 1;
  }
  elseif(isset($_COOKIE["mp3act_cookie"])){
  	mp3act_connect();
  	$query = "SELECT * FROM mp3act_logins WHERE md5=\"$_COOKIE[mp3act_cookie]\"";
  	$result = mysql_query($query);
  	$row = mysql_fetch_array($result);
  	
  	if( (time()-$row['date']) < (60*60*24*30) ){
  		$query = "SELECT * FROM mp3act_users WHERE user_id=$row[user_id]";
  		$result = mysql_query($query);
  		$userinfo = mysql_fetch_assoc($result);
  		
  			if($userinfo['last_ip'] != $_SERVER['REMOTE_ADDR']){
  				setcookie("mp3act_cookie"," ",time()-3600);
					return 0;
  			}
  			
  			$_SESSION['sess_username'] = $userinfo['username'];
        $_SESSION['sess_firstname'] = $userinfo['firstname'];
        $_SESSION['sess_lastname'] = $userinfo['lastname'];
        $_SESSION['sess_userid'] = $userinfo['user_id'];
				$_SESSION['sess_accesslevel'] = $userinfo['accesslevel'];
				$_SESSION['sess_playmode'] = $userinfo['default_mode'];
				if(getSystemSetting("mp3bin") == "")
					$_SESSION['sess_playmode'] = 'streaming';
        
        $_SESSION['sess_stereo'] = $userinfo['default_stereo'];
        $_SESSION['sess_bitrate'] = $userinfo['default_bitrate'];
				$_SESSION['sess_usermd5'] = $userinfo['md5'];
				$_SESSION['sess_theme_id'] = $userinfo['theme_id'];
				$_SESSION['sess_last_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['sess_logged_in'] = 1;
        return 1;
  	}
  	else{
  		setcookie("mp3act_cookie"," ",time()-3600);
			return 0;
  	}
  	
  }
  else{
    return 0;
  }
}

function accessLevel($level){
	return ($_SESSION['sess_accesslevel'] >= $level);
}

function switchMode($mode){
	$_SESSION['sess_playmode'] = $mode;
	return $mode;
}

function getSystemSetting($setting){
	mp3act_connect();
	$query = "SELECT $setting FROM mp3act_settings WHERE id=1";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	return $row[$setting];
}

function setCurrentSong($song_id,$pl_id,$rand=0){
	mp3act_connect();
	$query = "DELETE FROM mp3act_currentsong";
	mysql_query($query);
	$query = "INSERT INTO mp3act_currentsong VALUES ($song_id,$pl_id,$rand)";
	mysql_query($query);
}

function getCurrentSong($curArtist, $curSong){
	$data = array();

	mp3act_connect();
	$query = "SELECT mp3act_currentsong.random,mp3act_currentsong.pl_id,mp3act_artists.artist_name,mp3act_artists.prefix, mp3act_songs.name FROM mp3act_artists,mp3act_songs,mp3act_currentsong WHERE mp3act_currentsong.song_id=mp3act_songs.song_id AND mp3act_songs.artist_id=mp3act_artists.artist_id";
	$result = mysql_query($query);
	if(mysql_num_rows($result) == 0){
		$data[] = "<strong>No Songs Playing</strong><span id='artist'></span><span id='song'></span>";
		$data[] = 0;
		$data[] = 0;
		return $data;
	}
	$row = mysql_fetch_array($result);
	if($row['artist_name'] == $curArtist && $row['name'] == $curSong){
		$data[] = 1;
		$data[] = $row['pl_id'];
		$data[] = 1;
		return $data;
	}
	
	$data[] = "<strong>Currently Playing".($row['random'] ? " (Random Mix)" : "")."</strong><br/><span id='artist'>$row[prefix] $row[artist_name]</span><br/><span id='song'>$row[name]</span>\n";
	$data[] = $row['pl_id'];
	$data[] = 1;
	return $data;
}


function insertScrobbler($song_id,$user_id,$type='jukebox'){
  mp3act_connect();
  if(hasScrobbler($user_id,$type)){
    $sql = "INSERT INTO mp3act_audioscrobbler VALUES (NULL,$user_id,$song_id,\"".time()."\")";
    if(mysql_query($sql)){
      submitScrobbler($user_id);
      return TRUE;
    }
  }
  return FALSE;
}
function hasScrobbler($user_id,$type='jukebox'){
  mp3act_connect();
  $mode['jukebox'] = '(as_type=1 OR as_type=2)';
  $mode['streaming'] = 'as_type=2';
  $sql = "SELECT as_username FROM mp3act_users WHERE user_id=$user_id AND $mode[$type] AND as_username!='' AND as_password!=''";
  $result = mysql_query($sql);
  if(mysql_num_rows($result)>0)
    return TRUE;
  return FALSE;
}

function getScrobblerStats($user_id){
  $as = array();
  mp3act_connect();
  $sql = "SELECT as_username,as_lastresult FROM mp3act_users WHERE user_id=$user_id";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  $as['username'] = $row['as_username'];
  $as['last_result'] = $row['as_lastresult'];
  
  $sql = "SELECT COUNT(as_id) as count FROM mp3act_audioscrobbler WHERE user_id=$user_id";
  $result = mysql_query($sql);
    $row = mysql_fetch_array($result);
    $as['count'] = $row['count'];
        
  return $as;
}

function submitScrobbler($user_id){
  exec(getSystemSetting("phpbin")." includes/audioScrobbler.php $user_id > /dev/null 2>&1 &"); 
  return 1;
}

function genreform(){
  mp3act_connect();
  $query = "SELECT * FROM mp3act_genres ORDER BY genre";
  $result = mysql_query($query);
  
  $output = "<select id=\"genre\" name=\"genre\" onchange=\"updateBox('genre',this.options[selectedIndex].value); return false;\">
    <option value=\"\" selected>Choose Genre..";
  
  while($genre = mysql_fetch_array($result)){
    $output .= "  <option value=\"$genre[genre]\">$genre[genre]\n";
  }
  $output .= "</select>";
 
  return $output;
}

function getUser($username){
  mp3act_connect();
  $sql = "SELECT user_id FROM mp3act_users WHERE username=\"$username\"";
   $result = mysql_query($sql);

  if(mysql_num_rows($result)>0)
    return 1;
  return 0;
}

function letters(){
	$output = "<ul id=\"letters\">";
	$letters = array('#','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	
	foreach($letters as $letter){
		$output .= "<li><a href=\"#\" onclick=\"updateBox('letter','$letter'); return false;\">".strtoupper($letter)."</a></li>\n";
	}
	$output .= "</ul>";
	return $output;
}

function getDropDown($type, $id){
	$dropdown = "";
	return $dropdown;
}

function buildBreadcrumb($page,$parent,$parentitem,$child,$childitem){
	mp3act_connect();
	$childoutput='';
	$parentoutput ='';
	if($page == 'browse' && $child != ''){
		$output = "<a href=\"#\" onclick=\"updateBox('browse',0); return false;\">Browse</a> &#187; ";
	}
	switch($child){
		case 'album':
			$query = "SELECT mp3act_albums.album_name,mp3act_artists.artist_name,mp3act_artists.artist_id,mp3act_artists.prefix FROM mp3act_albums,mp3act_artists WHERE mp3act_albums.artist_id=mp3act_artists.artist_id AND mp3act_albums.album_id=$childitem";
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
			$albums = '';
			$query = "SELECT album_name,album_id FROM mp3act_albums WHERE artist_id=$row[artist_id] ORDER BY album_name";
			$result = mysql_query($query);
			while($row2 = mysql_fetch_array($result)){
				$albums .= "<li><a href=\"#\" onclick=\"updateBox('album',$row2[album_id]); return false;\" title=\"View Details of $row2[album_name]\">$row2[album_name]</a></li>";
			}
			$childoutput .= "<span><a href=\"#\" onclick=\"updateBox('artist'," . $row['artist_id'] . "); return false;\">" . $row['prefix'] . " " . $row['artist_name'] . "</a><ul>$albums</ul></span> &#187; " . htmlentities($row['album_name']);
		break;
		case 'artist':
			$query = "SELECT artist_name,prefix FROM mp3act_artists WHERE artist_id=$childitem";
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
			$albums = '';
			$query = "SELECT album_name,album_id FROM mp3act_albums WHERE artist_id=$childitem ORDER BY album_name";
			$result = mysql_query($query);
			while($row2 = mysql_fetch_array($result)){
				$albums .= "<li><a href=\"#\" onclick=\"updateBox('album',$row2[album_id]); return false;\" title=\"View Details of $row2[album_name]\">$row2[album_name]</a></li>";
			}
			$childoutput .= "<span><a href=\"#\" onclick=\"updateBox('artist',$childitem); return false;\">$row[prefix] $row[artist_name]</a><ul>$albums</ul></span>";
		break;
		case 'letter':
			$childoutput .= "<span><a href=\"#\" onclick=\"updateBox('letter','$childitem'); return false;\">".strtoupper($childitem)."</a>".letters()."</span>";
		break;
		case 'genre':
			$childoutput .=  $childitem;
		break;
		case 'all':
			$childoutput .=  $childitem;
		break;
	
	}
	switch($parent){
	
		case 'letter':
			$parentoutput .= "<span><a href=\"#\" onclick=\"updateBox('letter','$parentitem'); return false;\">".strtoupper($parentitem)."</a>".letters()."</span> &#187; ";
		break;
		case 'genre':
			$query = "SELECT album_name FROM mp3act_albums WHERE album_id=$childitem";
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
			$parentoutput .= "<a href=\"#\" onclick=\"updateBox('genre','$parentitem'); return false;\">$parentitem</a> &#187; ";
		break;
		case 'all':
			$parentoutput .=  "<a href=\"#\" onclick=\"updateBox('all','$parentitem'); return false;\">$parentitem</a> &#187; ";
		break;
	
	}
	if (isset($output)) {
	  return $output.$parentoutput.$childoutput;
	} else {
	  return '';
	}	
}

function musicLookup($type,$itemid){
	mp3act_connect();
	switch($type){
	case 'browse':
    $output = "<div class=\"head\">";
			$output .= "<h2>Browse the Music Database</h2></div>";
			$output .= "<p>";
			$output .= "<strong>By Artist Beginning With</strong><br/>".letters()."<br/></p>\n";
			$output .= "<p><strong>By Genre</strong><br/>\n";
			$output .= genreForm()."<br/><br/>\n";
			$output .= "<input type='button' value='Browse All Albums' onclick=\"updateBox('all','All'); return false;\" class='btn2' />\n";
    	$output .= "</p>\n";

	break;
	case 'search':
    $output = "<div class=\"head\">";
			$output .= "<h2>Search the Music Database</h2></div>";
			$output .= "<form onsubmit='return searchMusic(this)' method='get' action=''>\n";
			$output .= "<p>
				<strong>Keywords</strong><br/>
				<input type='text' onfocus='this.select()' name='searchbox' size='35' id='searchbox' value='[enter your search terms]' />
    		<br/><br/>
    		<strong>Narrow Your Search</strong>
    		<br/>
    		<select name='search_options' size='1'>
    			<option value='all'>All Fields</option>
    			<option value='artists'>Artists</option>
    			<option value='albums'>Albums</option>
    			<option value='songs'>Songs</option>
    		</select><br/><br/>
    		<input type='submit' value='submit search' class='btn' /></form>";
				$output .= "</p>\n";

	break;
	case 'letter':
	if($itemid == "#"){
      $query = "SELECT * FROM mp3act_artists 
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
    }else{
    	$query = "SELECT * FROM mp3act_artists
                WHERE artist_name LIKE '$itemid%'
                ORDER BY artist_name";
    }
    $result = mysql_query($query);
    $output = "<div class=\"head\">";
			$output .= "<h2>Artists Beginning with '".strtoupper($itemid)."'</h2></div>";
			$output .= "<p>
				<strong>Artist Listing</strong></p>
				<ul>";
					$count =1;
    while($row = mysql_fetch_array($result)){
    ($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
			$output .= "<li $alt><a href=\"#\" onclick=\"updateBox('artist',$row[artist_id]); return false;\" title=\"View Albums for $row[prefix] $row[artist_name]\">$row[prefix] $row[artist_name]</a></li>\n";
			$count++;
		}
				$output .= "</ul>\n";

	break;
	
	case 'all':
			$output = "<div class=\"head\">";
			//$output .= "<div class=\"right\"><a href='#' onclick=\"updateBox('all',$itemid-30); return false;\">Prev Page</a> <a href='#' onclick=\"updateBox('all',$itemid+30); return false;\">Next Page</a> </div>";
			$output .= "<h2>All Albums</h2></div> ";
			$output .= "<p>
				<strong>Album Listing</strong></p>
				<ul>";
		$start = $itemid;
		$query = "SELECT mp3act_artists.artist_name,mp3act_artists.prefix,mp3act_albums.* FROM mp3act_albums,mp3act_artists WHERE mp3act_albums.artist_id=mp3act_artists.artist_id ORDER BY artist_name,album_name"; /* LIMIT $start,30"; */
		$result = mysql_query($query);
			$count = 1;
		while($row = mysql_fetch_array($result)){
			($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
			$output .= "<li $alt><a href=\"#\" onclick=\"pladd('album'," . $row['album_id'] . "); return false;\" title=\"Add Album to Current Playlist\"><img src=\"img/add.gif\" /></a> <a href=\"#\" onclick=\"play('album'," . $row['album_id'] . "); return false;\" title=\"Play this Album Now\"><img src=\"img/play.gif\" /></a> <a href=\"#\" onclick=\"updateBox('album'," . $row['album_id'] . "); return false;\" title=\"View Details of " . $row['album_name'] . "\">" . $row['prefix'] . " " . $row['artist_name'] . " - " . $row['album_name'] . " " . (($row['album_year'] != 0) ? ("<em>(" . $row['album_year'] . ")</em>") : ("")) . "</a></li>\n";
			
			$count++;
		}
		$output .= "</ul>\n";
	break;
	
	case 'album':
		$query = "SELECT mp3act_albums.*,mp3act_artists.artist_name,mp3act_artists.prefix,COUNT(mp3act_songs.song_id) as tracks,SEC_TO_TIME(SUM(mp3act_songs.length)) as time FROM mp3act_albums,mp3act_artists,mp3act_songs WHERE mp3act_albums.album_id=$itemid AND mp3act_albums.artist_id=mp3act_artists.artist_id AND mp3act_songs.album_id=$itemid GROUP BY mp3act_songs.album_id";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
			
			$album_art='';
			
			
			if($row['album_art'] == ""){
				$row['album_art'] = art_insert($row['album_id'],$row['artist_name'],$row['album_name']);
				if($row['album_art'] != ''){
					$album_art = "<img onmouseover=\"showAlbumArt('block'); return false;\" onmouseout=\"showAlbumArt('none'); return false;\" src=\"art/$row[album_art]\" />\n";
				}
			}elseif($row['album_art'] != "fail"){
				$album_art = "<img onmouseover=\"showAlbumArt('block'); return false;\" onmouseout=\"showAlbumArt('none'); return false;\" src=\"art/$row[album_art]\" />\n";
			}
			$output = "<div class=\"head\">";
			$output .= "<div class=\"right\"><a href=\"#\" onclick=\"play('album',".$row['album_id']."); return false;\" title=\"Play this Album Now\">play</a> <a href=\"#\" onclick=\"pladd('album',$row[album_id]); return false;\" title=\"Add Album to Current Playlist\">add</a> ".((getSystemSetting("downloads")==1 || (getSystemSetting("downloads")==2 && accessLevel(5))) ? "<a href=\"#\" onclick=\"newWindow('download',$row[album_id]); return false;\" title=\"Download this Album Now\">download</a>" : "")."</div>";
			$output .= "<h2>".$row['album_name']."</h2>".$row['prefix']." ".$row['artist_name']."</div>";
			$output .= "<p>$album_art\n";
			$output .= "	<strong>Tracks:</strong> $row[tracks]<br/>\n";
			$output .= (($row['album_year'] != 0) ? ("<strong>Year:</strong> " . $row['album_year'] . "<br/>\n") : (""));
			$output .= "	<strong>Genre:</strong> <a href=\"#\" onclick=\"updateBox('genre','$row[album_genre]'); return false;\" title=\"View Artists from $row[album_genre] Genre\">$row[album_genre]</a><br/>\n";
			$output .= "	<strong>Play Time:</strong> $row[time]\n";
			$output .= "	<br/><br/>\n";
			$output .= "	<strong>Album Tracks</strong></p>\n";
			$output .= "<ul>\n";
			$output .= "<img id='bigart' src=\"art/$row[album_art]\" />\n";
		$query = "SELECT *,SEC_TO_TIME(length) as length FROM mp3act_songs WHERE album_id=$itemid ORDER BY track";
		$result = mysql_query($query);
		$count=1;
		while($row = mysql_fetch_array($result)){
			($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
			$output .= "<li $alt ondblclick=\"pladd('song',$row[song_id]); return false;\" ><a href=\"#\" onclick=\"pladd('song',$row[song_id]); return false;\" title=\"Add Song to Current Playlist\"><img src=\"img/add.gif\" /></a> <a href=\"#\" onclick=\"play('song',$row[song_id]); return false;\" title=\"Play this Song Now\"><img src=\"img/play.gif\" /></a> $row[track]. $row[name]<p>$row[numplays] Plays<br/><em>$row[length]</em></p></li>\n";
			$count++;
		}
		
		$output .= "</ul>\n";
	break;
	case 'genre':
		$query = "SELECT mp3act_artists.artist_id,mp3act_artists.artist_name,mp3act_artists.prefix FROM mp3act_artists,mp3act_albums WHERE mp3act_albums.album_genre='$itemid' AND mp3act_artists.artist_id=mp3act_albums.artist_id GROUP BY mp3act_artists.artist_id ORDER BY mp3act_artists.artist_name";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		
			$output = "<div class=\"head\">";
			$output .= "<h2>Artists for Genre '$itemid'</h2></div>";
			$output .= "<p>
				<strong>Artist Listing</strong></p>
				<ul>";
			
		$result = mysql_query($query);
		
		$count=1;
		while($row = mysql_fetch_array($result)){
			($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
			$output .= "<li $alt><a href=\"#\" onclick=\"updateBox('artist',$row[artist_id]); return false;\" title=\"View Albums for $row[artist_name]\">$row[prefix] $row[artist_name]</a></li>\n";
			$count++;
		}
		$output .= "</ul>\n";
	break;
	case 'artist':
		$query = "SELECT artist_id,artist_name,prefix FROM mp3act_artists WHERE artist_id=$itemid";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		
			$output = "<div class=\"head\">";
			$output .= "<h2>$row[prefix] $row[artist_name]</h2></div>";
			$output .= "<p>\n";
			$output .= "<strong>Album Listing</strong></p>\n";
			$output .= "<ul>\n";
			
		$query = "SELECT mp3act_albums.* FROM mp3act_albums WHERE mp3act_albums.artist_id=$itemid ORDER BY mp3act_albums.album_name";
		$result = mysql_query($query);
		$count=1;
		while($row = mysql_fetch_array($result)){
			($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
			$output .= "<li $alt><a href=\"#\" onclick=\"pladd('album'," . $row['album_id'] . "); return false;\" title=\"Add Album to Current Playlist\"><img src=\"img/add.gif\" /></a> <a href=\"#\" onclick=\"play('album'," . $row['album_id'] . "); return false;\" title=\"Play this Album Now\"><img src=\"img/play.gif\" /></a> <a href=\"#\" onclick=\"updateBox('album'," . $row['album_id'] . "); return false;\" title=\"View Details of " . $row['album_name'] . "\">" . $row['album_name'] . " " . (($row['album_year'] != 0) ? ("<em>(" . $row['album_year'] . ")</em>") : (""))."</a></li>\n";
			$count++;
		}
		$output .= "</ul>\n";
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
			
			if(getSystemSetting("invite_mode") == 1){
			
				$output .= "<form onsubmit='return sendInvite(this)' method='get' action=''>\n";
				$output .= "<p id='invite'>";
				$output .= "<br/><strong>Send an Invitation for Registration<br/>\n";
				$output .= "<input type='text' onfocus='this.select()' name='email' id='email' value='Enter Email Address of Recipient' size='32' /><br/>\n";
				$output .= "<br/><input type='submit' value='send invite' class='btn' /></form>";
				$output .= "</p>";
			}
			
	break;
	case 'prefs':
			$query = "SELECT DATE_FORMAT(mp3act_users.date_created,'%M %D, %Y') as date_created FROM mp3act_users WHERE mp3act_users.user_id=$_SESSION[sess_userid]";
			$query2 = "SELECT COUNT(play_id) as playcount FROM mp3act_playhistory WHERE user_id=$_SESSION[sess_userid] GROUP BY user_id";
			$result = mysql_query($query);
			$result2 = mysql_query($query2);

			$row = mysql_fetch_array($result);
			$row2 = mysql_fetch_array($result2);
			if(mysql_num_rows($result2) == 0){
				$row2['playcount'] = 0;
			}
			$dayssince = (time()-strtotime($row['date_created'])) / (60 * 60 * 24);
			$output = "<div class=\"head\">";
			$output .= "<h2>$_SESSION[sess_firstname] $_SESSION[sess_lastname]'s Account ($_SESSION[sess_username])</h2></div>";
			$output .= "<p>\n";
			$output .= "<strong>Date Joined:</strong> $row[date_created]<br/>\n";
			$output .= "<strong>Songs Played:</strong> $row2[playcount]<br/>\n";
			$output .= "<strong>Daily Average:</strong> ".round(($row2['playcount'] / $dayssince),2)." songs/day<br/><br/>\n";
			$output .= "<a href='#' onclick=\"editUser('info',0); return false;\" >Edit User Info</a><br/>";
			$output .= "<a href='#' onclick=\"editUser('settings',0); return false;\" >Edit User Settings</a><br/>";
			$output .= "<a href='#' onclick=\"editUser('pass',0); return false;\" >Change Password</a><br/><br/>";
			if(hasScrobbler($_SESSION['sess_userid'])){
			  $as = getScrobblerStats($_SESSION['sess_userid']);
			  $output .= "<strong>AudioScrobbler Submission Queue:</strong> $as[count] songs ".($as['count']>0 ? "<a href='#' onclick=\"submitScrobbler($_SESSION[sess_userid]); return false;\" title='Force Submission to AudioScrobbler'>[submit]</a>" : "")."<br/>\n";
			  $output .= "<strong>AudioScrobbler Response:</strong> $as[last_result]<br/>\n";
			  $output .= "<a href='http://www.audioscrobbler.com/user/$as[username]' target='_new' title='View Your AudioSrobbler Statistics Page'>View Your AudioSrobbler Statistics Page</a><br/><br/>\n";
			}
			$output .= "</p>";
	
	break;
	case 'random':
			$output = "<div class=\"head\">";
			$output .= "<h2>Random Mix Maker</h2></div>";
			$output .= "<form onsubmit='return randPlay(this)' method='get' action=''>\n<p>";
			if($_SESSION['sess_playmode'] == "streaming"){
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
			$query = "SELECT *,SEC_TO_TIME(time) AS time2 FROM mp3act_saved_playlists WHERE private=0";
			$result = mysql_query($query);
			
			$output = "<div class=\"head\">";
			$output .= "<h2>Saved Playlists</h2></div>";
			$output .= "<p><strong>Public Playlists</strong></p>\n";
			$output .= "<ul>\n";
			if(mysql_num_rows($result) == 0)
				$output .= "No Public Playlists";
			while ($row = mysql_fetch_array($result)){
				$output .= "<li><a href=\"#\" onclick=\"pladd('playlist',$row[playlist_id]); return false;\" title='Load this Saved Playlist'><img src=\"img/add.gif\" /></a> ".(accessLevel(10) ? "<a href=\"#\" onclick=\"deletePlaylist($row[playlist_id]); return false;\" title='DELETE this Saved Playlist'><img src=\"img/rem.gif\" /></a>": "")." <a onclick=\"updateBox('saved_pl',$row[playlist_id]); \" title='Click to View Playlist' href='#'>$row[playlist_name] - $row[songcount] Songs ($row[time2])</a></li>";
			}
			$output .= "</ul>\n";
			$output .= "<p><strong>Your Private Playlists</strong></p>\n";
			$query = "SELECT *,SEC_TO_TIME(time) AS time2 FROM mp3act_saved_playlists WHERE private=1 AND user_id=$_SESSION[sess_userid] ORDER BY playlist_id DESC";
			$result = mysql_query($query);
			$output .= "<ul>\n";
			if(mysql_num_rows($result) == 0)
				$output .= "No Private Playlists";
			while ($row = mysql_fetch_array($result)){
				$output .= "<li><a href=\"#\" onclick=\"pladd('playlist',$row[playlist_id]); return false;\" title='Load this Saved Playlist'><img src=\"img/add.gif\" /></a> <a href=\"#\" onclick=\"deletePlaylist($row[playlist_id]); return false;\" title='DELETE this Saved Playlist'><img src=\"img/rem.gif\" /></a> <a onclick=\"updateBox('saved_pl',$row[playlist_id]); \" title='Click to View Playlist' href='#'>$row[playlist_name] - $row[songcount] Songs ($row[time2])</a></li>";
			}
			$output .= "</ul>\n";
	break;
	case 'saved_pl':
			$query = "SELECT *,SEC_TO_TIME(time) AS time2 FROM mp3act_saved_playlists WHERE playlist_id=$itemid";
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
			$output = "<div class=\"head\">";
			$output .= "<div class=\"right\"><a href=\"#\" onclick=\"pladd('playlist',$row[playlist_id]); return false;\" title=\"Load Playlist\">load playlist</a></div>";

			$output .= "<h2>View Saved Playlist</h2></div>";
			$output .= "<p><strong>Playlist Info</strong><br/>$row[songcount] Songs<br/>$row[time2]</p>\n";
			$output .= "<p><strong>Playlist Songs</strong></p>\n";
			$output .= "<ul>\n";
			$songs = explode(",",$row['playlist_songs']);
		
		$count = 0;
		foreach($songs as $song){
			$query = "SELECT mp3act_songs.*,SEC_TO_TIME(mp3act_songs.length) AS length,mp3act_artists.artist_name FROM mp3act_artists,mp3act_songs WHERE mp3act_songs.song_id=$song AND mp3act_artists.artist_id=mp3act_songs.artist_id";
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
	($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
			$output .= "<li $alt>$row[artist_name] - $row[name]<p>$row[numplays] Plays<br/><em>$row[length]</em></p></li>";
			$count++;
		}
			$output .= "</ul>\n";
			
	break;
	case 'about':
			$output = "<div class=\"head\">";
			$output .= "<h2>mp3act Music System - v".getSystemSetting("version")."</h2></div>";
			$output .= "<p>\n";
			$output .= "<strong>Date: </strong>July 31, 2005<br/>\n";
			$output .= "<strong>Author: </strong><a href='http://www.jonbuda.com' target='_blank'>Jon Buda</a> | <a href='http://www.visiblebits.com' target='_blank'>A VisibleBits Production</a><br/>\n";
			$output .= "<strong>Website: </strong><a href='http://www.mp3act.net' target='_blank'>http://www.mp3act.net</a><br/>\n";
			$output .= "<strong>Support: </strong><a href='http://www.mp3act.net/support/' target='_blank'>http://www.mp3act.net/support/</a><br/>\n";

			$output .= "<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target='_blank'>
<input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\">
<input class='noborder' title='Donate to mp3act!' type=\"image\" src=\"img/paypal_donate.gif\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\">
<input type=\"hidden\" name=\"encrypted\" value=\"-----BEGIN PKCS7-----MIIHFgYJKoZIhvcNAQcEoIIHBzCCBwMCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYA4veI6qAxD/af5tw+U4bCCL6Dq/VKfbP7vqm2pH+IMxxiKfpDL4lq0rwKY53oZPbg7piEkawKT3/KUuCfx+HxgySt8baF2ebbK3AyKOmvFd2/eDyNTxRiS/tF0pNmW0DzE2JCoQW2HJajxXM5Z+UyJN0Z9v5FhPETMb8feDYo41jELMAkGBSsOAwIaBQAwgZMGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIjWvBHPqz4jiAcI2IZ5qVE6XWPHK7Y7bjlbSFiYqwwEDPiBqQlrSZE/qVfm5Q8kNsdtWXycfr6zeEd9AtHRdPV4l0Vao/IUJDj3pwGKtHjGcPXJW2kA4FzgAH4e+8zbQTTPbg/hNyh93xt8VJJZd7JQsc93UKwPzs5AigggOHMIIDgzCCAuygAwIBAgIBADANBgkqhkiG9w0BAQUFADCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20wHhcNMDQwMjEzMTAxMzE1WhcNMzUwMjEzMTAxMzE1WjCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20wgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGBAMFHTt38RMxLXJyO2SmS+Ndl72T7oKJ4u4uw+6awntALWh03PewmIJuzbALScsTS4sZoS1fKciBGoh11gIfHzylvkdNe/hJl66/RGqrj5rFb08sAABNTzDTiqqNpJeBsYs/c2aiGozptX2RlnBktH+SUNpAajW724Nv2Wvhif6sFAgMBAAGjge4wgeswHQYDVR0OBBYEFJaffLvGbxe9WT9S1wob7BDWZJRrMIG7BgNVHSMEgbMwgbCAFJaffLvGbxe9WT9S1wob7BDWZJRroYGUpIGRMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbYIBADAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4GBAIFfOlaagFrl71+jq6OKidbWFSE+Q4FqROvdgIONth+8kSK//Y/4ihuE4Ymvzn5ceE3S/iBSQQMjyvb+s2TWbQYDwcp129OPIbD9epdr4tJOUNiSojw7BHwYRiPh58S1xGlFgHFXwrEBb3dgNbMUa+u4qectsMAXpVHnD9wIyfmHMYIBmjCCAZYCAQEwgZQwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tAgEAMAkGBSsOAwIaBQCgXTAYBgkqhkiG9w0BCQMxCwYJKoZIhvcNAQcBMBwGCSqGSIb3DQEJBTEPFw0wNTA0MTMxOTM3MDZaMCMGCSqGSIb3DQEJBDEWBBSEfLq1T8OGroO3jwycdLCxmwl7WTANBgkqhkiG9w0BAQEFAASBgLsYmppV3QgSoiPud2C7ZCh7NRBX/bPC4jgYT6Qf42vdh4mjAIptVJZn66HM8UQsKI9feP8x7+7g1S3/u+AoHVk5FQgaiRbGni2EKUO2il8YvjlwWLeRxJLuBPoTYeyMgGNFCTu/8TUSus0kpb8tpcFZWg1TGrhuX90XIbPjmisS-----END PKCS7-----\">
</form>\n";

			$output .= "</p>";
			$output .= "<h3>Thanks to Contributors and Testers</h3>\n";
			$output .= "<p>Ben Callam<br/>Joe Doss<br/>All of 708 Park St.</p>\n";
				
	break;
	case 'stats':
		$query = "SELECT * FROM mp3act_stats";
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
			$query = "SELECT COUNT(user_id) AS users FROM mp3act_users";
			$result = mysql_query($query);
			$row2 = mysql_fetch_array($result);
			$query = "SELECT COUNT(play_id) AS songs FROM mp3act_playhistory";
			$result = mysql_query($query);
			$row3 = mysql_fetch_array($result);
			
			$output = "<div class=\"head\">";
			$output .= "<h2>Server Statistics</h2></div>";
			$output .= "<p>\n";
			$output .= "<a href='#' onclick=\"updateBox('recentadd',0); return false;\" >Recently Added Albums</a><br/>";
			$output .= "<a href='#' onclick=\"updateBox('recentplay',0); return false;\" >Recently Played Songs</a><br/>";
			$output .= "<a href='#' onclick=\"updateBox('topplay',0); return false;\" >Top Played Songs</a><br/>";


			$output .= "</p>\n";
				$output .= "<h3>Local Server Statistics</h3>\n";
				$output .= "<p><strong>Songs:</strong> $row[num_songs]<br/>\n";
			$output .= "<strong>Albums:</strong> $row[num_albums]<br/>\n";
			$output .= "<strong>Artists:</strong> $row[num_artists]<br/>\n";
			$output .= "<strong>Genres:</strong> $row[num_genres]<br/><br/>\n";
			$output .= "<strong>Total Time:</strong> $row[total_time]<br/>\n";
			$output .= "<strong>Total Size:</strong> $row[total_size]<br/><br/>\n";
			$output .= "<strong>Registered Users:</strong> $row2[users]<br/>\n";
			$output .= "<strong>Songs Played:</strong> $row3[songs]<br/></p>\n";

	break;
	case 'recentadd':			
			$query = "SELECT mp3act_albums.album_name,mp3act_albums.album_id,
			mp3act_artists.artist_name, 
			DATE_FORMAT(mp3act_songs.date_entered,'%m.%d.%Y') as pubdate   
			FROM mp3act_songs,mp3act_albums,mp3act_artists 
			WHERE mp3act_songs.album_id=mp3act_albums.album_id 
			AND mp3act_artists.artist_id=mp3act_songs.artist_id 
			GROUP BY mp3act_songs.album_id ORDER BY mp3act_songs.date_entered DESC LIMIT 40";
			$result = mysql_query($query);
			
			$output = "<div class=\"head\">";
			$output .= "<div class=\"right\"><a href=\"#\" onclick=\"switchPage('stats'); return false;\" title=\"Return to Statistics Page\">back</a></div>";
			$output .= "<h2>Recently Added Albums</h2></div><ul>";
			$count=1;
		while($row = mysql_fetch_array($result)){
			($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
			$output .= "<li $alt><small>$row[pubdate]</small> <a href=\"#\" onclick=\"pladd('album',$row[album_id]); return false;\" title=\"Add Album to Current Playlist\"><img src=\"img/add.gif\" /></a> <a href=\"#\" onclick=\"play('album',$row[album_id]); return false;\" title=\"Play this Album Now\"><img src=\"img/play.gif\" /></a> <a href=\"#\" onclick=\"updateBox('album',$row[album_id]); return false;\" title=\"View Details of $row[album_name]\"><em>$row[artist_name]</em> - $row[album_name]</a></li>";		
			$count++;
		}
		$output .= "</ul>";
			

	break;
	case 'topplay':			
			$query = "SELECT mp3act_albums.album_name, mp3act_songs.numplays, mp3act_songs.name, 
			mp3act_artists.artist_name,mp3act_songs.song_id 
			FROM mp3act_songs,mp3act_albums,mp3act_artists 
			WHERE mp3act_songs.album_id=mp3act_albums.album_id 
			AND mp3act_artists.artist_id=mp3act_songs.artist_id 
			AND mp3act_songs.numplays > 0 
			ORDER BY mp3act_songs.numplays DESC LIMIT 40";
			$result = mysql_query($query);
			
			$output = "<div class=\"head\">";
			$output .= "<div class=\"right\"><a href=\"#\" onclick=\"switchPage('stats'); return false;\" title=\"Return to Statistics Page\">back</a></div>";
			$output .= "<h2>Top Played Songs</h2></div><ul>";
			$count=1;
		while($row = mysql_fetch_array($result)){
			($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
			$output .= "<li $alt><small>$row[numplays] Plays</small> <a href=\"#\" onclick=\"pladd('song',$row[song_id]); return false;\" title=\"Add Song to Current Playlist\"><img src=\"img/add.gif\" /></a> <a href=\"#\" onclick=\"play('song',$row[song_id]); return false;\" title=\"Play this Song Now\"><img src=\"img/play.gif\" /></a> <em>$row[artist_name]</em> - $row[name]</li>";		
			$count++;
		}
		$output .= "</ul>";
	break;
	case 'recentplay':			
			$query = "SELECT mp3act_songs.name, mp3act_songs.song_id, 
			mp3act_artists.artist_name,
			DATE_FORMAT(mp3act_playhistory.date_played,'%m.%d.%Y') as playdate 
			FROM mp3act_songs,mp3act_artists,mp3act_playhistory 
			WHERE mp3act_songs.song_id=mp3act_playhistory.song_id
			AND mp3act_artists.artist_id=mp3act_songs.artist_id 
			ORDER BY mp3act_playhistory.play_id DESC LIMIT 40";
			$result = mysql_query($query);
			
			$output = "<div class=\"head\">";
			$output .= "<div class=\"right\"><a href=\"#\" onclick=\"switchPage('stats'); return false;\" title=\"Return to Statistics Page\">back</a></div>";
			$output .= "<h2>Recently Played Songs</h2></div><ul>";
			$count=1;
		while($row = mysql_fetch_array($result)){
			($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
			$output .= "<li $alt><small>$row[playdate]</small> <a href=\"#\" onclick=\"pladd('song',$row[song_id]); return false;\" title=\"Add Song to Current Playlist\"><img src=\"img/add.gif\" /></a> <a href=\"#\" onclick=\"play('song',$row[song_id]); return false;\" title=\"Play this Song Now\"><img src=\"img/play.gif\" /></a> <em>$row[artist_name]</em> - $row[name]</li>";		
			$count++;
		}
		$output .= "</ul>";
			

	break;
	}
	
	return $output;
		
}
function editSettings($update,$invite,$downloads,$amazonid,$upload_path,$sample_mode,$mp3bin,$lamebin,$phpbin){
		mp3act_connect();
		if($update){
			$query = "UPDATE mp3act_settings SET invite_mode=$invite,sample_mode=$sample_mode,downloads=$downloads,amazonid=\"$amazonid\",upload_path=\"$upload_path\",mp3bin=\"$mp3bin\",lamebin=\"$lamebin\",phpbin=\"$phpbin\" WHERE id=1";
				mysql_query($query);
				return 1;
		}
		
		$query = "SELECT * FROM mp3act_settings WHERE id=1";
				$result = mysql_query($query);
				$row = mysql_fetch_array($result);
				
			$output = "<div class=\"head\">";
			$output .= "<h2>Edit mp3act System Settings</h2></div>";
			$output .= "<form onsubmit='return editSettings(this)' method='get' action=''>\n";
			$output .= "<p>\n";
			$output .= "<strong>Invitation for Registration</strong><br/><select name='invite'><option value='0' ".($row['invite_mode'] == '0' ? "selected" : "").">Not Required</option><option value='1' ".($row['invite_mode'] == '1' ? "selected" : "").">Required</option></select><br/><br/>\n";
    	$output .= "<strong>Sample Mode (play 1/4 of each song)</strong><br/><select name='sample_mode'><option value='0' ".($row['sample_mode'] == '0' ? "selected" : "").">Sample Mode OFF</option><option value='1' ".($row['sample_mode'] == '1' ? "selected" : "").">Sample Mode ON</option></select><br/><br/>\n";

    	$output .= "<strong>Music Downloads</strong><br/><select name='downloads'><option value='0' ".($row['downloads'] == '0' ? "selected" : "").">Not Allowed</option><option value='1' ".($row['downloads'] == '1' ? "selected" : "").">Allowed for All</option><option value='2' ".($row['downloads'] == '2' ? "selected" : "").">Allowed with Permission</option></select><br/><br/>\n";
    	$output .= "<strong>Amazon API Key</strong> <a href='http://www.amazon.com/webservices/' target='_new'>Obtain Key</a><br/><input type='text' size='30' name='amazonid' value='$row[amazonid]' /><br/><br/>\n";
    	 $output .= "<strong>Upload Path for New Music</strong><br/><input type='text' size='30' name='upload_path' value='$row[upload_path]' /><br/><br/>\n";
    	 $output .= "<strong>Path to MP3 Player</strong><br/><input type='text' size='30' name='mp3bin' value='$row[mp3bin]' /><br/><br/>\n";
    	 $output .= "<strong>Path to Lame Encoder</strong><br/><input type='text' size='30' name='lamebin' value='$row[lamebin]' /><br/><br/>\n";
    	 $output .= "<strong>Path to PHP-CLI Binary</strong><br/><input type='text' size='30' name='phpbin' value='$row[phpbin]' /><br/><br/>\n";

    	$output .= "<input type='submit' value='update settings' class='btn' /> <input type='button' value='cancel' onclick=\"switchPage('admin'); return false;\" class='redbtn' />\n";
			$output .= "</p></form>";
			return $output;
}

function adminAddUser($firstname='',$lastname='',$username='',$email='',$level='',$pass=''){
  if(!empty($firstname)){
    mp3act_connect();
    $md5 = md5($username);
    if(getUser($username)==1)
      return 0;
    $query = "INSERT INTO mp3act_users VALUES 
    							(NULL,\"".$username."\",\"".$firstname."\",\"".$lastname."\",
    							PASSWORD(\"".$pass."\"),$level,NOW(),1,\"".$email."\",\"streaming\",0,\"s\",\"$md5\",\"\",\"\",1,\"\",\"\",\"\",0)";
    if(mysql_query($query)){
      return 1;
    }
    
  }else{
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

function adminEditUsers($userid=0,$action='list',$active='',$perms=''){
	mp3act_connect();
	if($userid!=0){
		if($action == 'user'){
			$query = "SELECT * FROM mp3act_users WHERE user_id=$userid";
		  $result = mysql_query($query);
		  $row = mysql_fetch_array($result);
		  $output = "<div class=\"head\">";
			$output .= "<h2>Edit User - $row[username]</h2></div>";
			$output .= "<form onsubmit=\"return adminEditUsers($userid,'mod',this)\" method='get' action=''>\n";
			$output .= "<p>\n";
			$output .= "<strong>User Status</strong><br/><select name='active'><option value='1' ".($row['active'] == '1' ? "selected" : "").">Active</option><option value='0' ".($row['active'] == '0' ? "selected" : "").">Disabled</option></select><br/><br/>\n";
    	$output .= "<strong>User Permission Level</strong><br/><select name='perms'><option value='1' ".($row['accesslevel'] == '1' ? "selected" : "").">1 - Normal User</option><option value='5' ".($row['accesslevel'] == '5' ? "selected" : "").">5 - Downloading Allowed</option><option value='10' ".($row['accesslevel'] == '10' ? "selected" : "").">10 - Administrator</option></select><br/><br/>\n";
    	$output .= "<input type='submit' value='submit changes' class='btn' /> <input type='button' value='cancel' onclick=\"adminEditUsers(0); return false;\" class='redbtn' />\n";
			$output .= "</p></form>";
		}
		elseif($action=='mod'){
			$query = "UPDATE mp3act_users SET active=$active, accesslevel=$perms WHERE user_id=$userid";
			$result = mysql_query($query);
			return 2;
		}
		elseif($action=='del'){
			$query = "DELETE FROM mp3act_users WHERE user_id=$userid";
		$result = mysql_query($query);
		return 1;
		}
	}
	else{
		$query = "SELECT * FROM mp3act_users WHERE username!=\"Admin\"";
		$result = mysql_query($query);
		$output = "<div class=\"head\">";
					$output .= "<div class=\"right\"><a href=\"#\" onclick=\"switchPage('admin'); return false;\" title=\"Return to Admin Menu\">return to admin</a></div>";

			$output .= "<h2>Edit mp3act Users</h2></div><ul>";
			$count=1;
		while($row = mysql_fetch_array($result)){
			($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
			$output .= "<li $alt><span class='user'><strong>$row[username]</strong> - ($row[firstname] $row[lastname])</span> <span class='links'><a href='#' title='Edit User Settings' onclick=\"adminEditUsers($row[user_id],'user'); return false;\" >edit user</a> | <a href='#' title='Delete the User' onclick=\"adminEditUsers($row[user_id],'del'); return false;\" >delete user</a></span></li>";		
			$count++;
		}
		$output .= "</ul>";
	}
	return $output;
}

function editUser($type,$input1,$input2,$input3,$input4,$input5,$input6,$input7){
	mp3act_connect();
	switch($type){
	case 'info':
			if(!empty($input1)){
				$query = "UPDATE mp3act_users SET firstname=\"$input1\",lastname=\"$input2\",email=\"$input3\" WHERE user_id=$_SESSION[sess_userid]";
				mysql_query($query);
				return 1;
			}
			$query = "SELECT * FROM mp3act_users WHERE user_id=$_SESSION[sess_userid]";
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
			if(!empty($input1)){
				$query = "UPDATE mp3act_users SET default_mode=\"$input1\",default_bitrate=$input2,default_stereo=\"$input3\",theme_id=$input4,as_username=\"$input5\",as_password=\"$input6\",as_type=$input7 WHERE user_id=$_SESSION[sess_userid]";
				mysql_query($query);
				return 1;
			}
			$query = "SELECT * FROM mp3act_users WHERE user_id=$_SESSION[sess_userid]";
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
			
			$output = "<div class=\"head\">";
			$output .= "<h2>$_SESSION[sess_firstname] $_SESSION[sess_lastname]'s Account Settings</h2></div>";
			$output .= "<form onsubmit='return editUser(\"settings\",this)' method='get' action=''>\n";
			$output .= "<p>\n";
			$output .= "<strong>Theme</strong><br/> 
				<select name='theme_id'>
				<option value=1 ".($row['theme_id'] == 1 ? "selected" : "").">default blue</option>
				<option value=2 ".($row['theme_id'] == 2 ? "selected" : "").">green</option>
				<option value=3 ".($row['theme_id'] == 3 ? "selected" : "").">red</option>
         </select><br/><br/>\n";
			$output .= "<strong>Default Playmode</strong><br/> 
				<select name='default_playmode'>
				<option value='streaming' ".($row['default_mode'] == 'streaming' ? "selected" : "").">Streaming Mode </option>
				<option value='jukebox' ".($row['default_mode'] == 'jukebox' ? "selected" : "").">Jukebox Mode </option>
         </select><br/><br/>\n";
    	$output .= "<strong>Streaming Downsample</strong><br/>
    	<select name='default_bitrate'>
				<option value='0' ".($row['default_bitrate'] == '0' ? "selected" : "").">Don't Downsample </option>
				<option value='128' ".($row['default_bitrate'] == '128' ? "selected" : "").">128 kbps </option>
				<option value='64' ".($row['default_bitrate'] == '64' ? "selected" : "").">64 kbps </option>
				<option value='32' ".($row['default_bitrate'] == '32' ? "selected" : "").">32 kbps </option>
         </select><br/><br/>\n";
         
    	$output .= "<strong>Streaming Stereo Setting</strong><br/>
    	<select name='default_stereo'>
				<option value='s' ".($row['default_stereo'] == 's' ? "selected" : "").">Stereo</option>
				<option value='m' ".($row['default_stereo'] == 'm' ? "selected" : "").">Mono</option>
			  </select><br/><br/>\n";
			  $output .= "<strong>AudioScrobbler Username</strong><br/><input type='text' size='20' name='as_username' id='as_username'  value='$row[as_username]' /><br/><br/>\n";
			  $output .= "<strong>AudioScrobbler Password</strong><br/><input type='password' size='20' name='as_password' id='as_password'  value='$row[as_password]' /><br/><br/>\n";
			  $output .= "<strong>AudioScrobbler Usage</strong><br/><select name='as_type'><option value=1 ".($row['as_type'] == 1 ? "selected" : "").">Jukebox Only</option><option value=2 ".($row['as_type'] == 2 ? "selected" : "").">Jukebox + Streaming</option></select><br/><br/>\n";
			  
    	$output .= "<input type='submit' value='update settings' class='btn' /> <input type='button' value='cancel' onclick=\"switchPage('prefs'); return false;\" class='redbtn' />\n";
			$output .= "</p></form>";
	
	break;
	case 'pass':
			if(!empty($input1)){
				$query = "UPDATE mp3act_users SET password=PASSWORD(\"$input2\") WHERE user_id=$_SESSION[sess_userid]";
				mysql_query($query);
				return 1;
			}
			$query = "SELECT * FROM mp3act_users WHERE user_id=$_SESSION[sess_userid]";
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

function getRandItems($type){
	mp3act_connect();
	$options = '';
	switch($type){
		case 'artists':
			$query = "SELECT * FROM mp3act_artists ORDER BY artist_name";
			$result = mysql_query($query);
			while($row = mysql_fetch_array($result)){
				$options .= "<option value=$row[artist_id]>$row[prefix] $row[artist_name]</option>\n";
			}
		break;
		case 'genre':
			$query = "SELECT genre_id,genre FROM mp3act_genres ORDER BY genre";
			$result = mysql_query($query);
			while($row = mysql_fetch_array($result)){
				$options .= "<option value=$row[genre_id]>$row[genre]</option>\n";
			}
		break;
		case 'albums':
			$query = "SELECT mp3act_artists.artist_name,mp3act_artists.prefix,mp3act_albums.album_id,mp3act_albums.album_name FROM mp3act_albums,mp3act_artists WHERE mp3act_albums.artist_id=mp3act_artists.artist_id ORDER BY artist_name,album_name";
			$result = mysql_query($query);
			while($row = mysql_fetch_array($result)){
				$options .= "<option value=$row[album_id]>$row[prefix] $row[artist_name] - $row[album_name]</option>\n";
			}
		break;
		case 'all':
			return "<br/>All Songs";
		break;
	}
	
	return "<select name='random_items' multiple size='12' style='width: 90%;'>$options</select>";

}

function searchMusic($terms,$option){
	mp3act_connect();
	$query="SELECT mp3act_songs.song_id, mp3act_albums.album_name,mp3act_songs.track,mp3act_artists.artist_name,mp3act_artists.prefix,mp3act_songs.name,SEC_TO_TIME(mp3act_songs.length) as length 
						FROM mp3act_songs,mp3act_artists,mp3act_albums WHERE mp3act_songs.artist_id=mp3act_artists.artist_id AND mp3act_albums.album_id=mp3act_songs.album_id AND ";
	if($option == 'all')
		$query .= "(mp3act_songs.name LIKE '%$terms%' OR mp3act_artists.artist_name LIKE '%$terms%' OR mp3act_albums.album_name LIKE '%$terms%')";
	elseif($option == 'artists')
		$query .= "(mp3act_artists.artist_name LIKE '%$terms%')";
	elseif($option == 'albums')
		$query .= "(mp3act_albums.album_name LIKE '%$terms%')";
	elseif($option == 'songs')
		$query .= "(mp3act_songs.name LIKE '%$terms%')";

	$query .= " ORDER BY mp3act_artists.artist_name,mp3act_albums.album_name,mp3act_songs.track";
	
	$result = mysql_query($query);
	$count = mysql_num_rows($result);

			$output = "<div class=\"head\">";
			$output .= "<div class=\"right\"><a href=\"#\" onclick=\"switchPage('search'); return false;\" title=\"Begin a New Search\">new search</a></div>";
			$output .= "<h2>Found $count Results for '$terms'</h2></div>";	
			$output .= "<ul>\n";
		if($count>0){
			$count=1;
			while($row = mysql_fetch_array($result)){
				($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
				$output .= "<li $alt><a href=\"#\" onclick=\"pladd('song',$row[song_id]); return false;\" title=\"Add Song to Current Playlist\"><img src=\"img/add.gif\" /></a> <a href=\"#\" onclick=\"play('song',$row[song_id]); return false;\" title=\"Play this Song Now\"><img src=\"img/play.gif\" /></a> $row[prefix] $row[artist_name] - $row[name]<p>Album: $row[album_name]<br/>Track: $row[track]<br/><em>$row[length]</em></p></li>\n";
				$count++;
			}
		}
		$output .= "</ul>\n";
		return $output;
}

function viewPlaylist(){
	mp3act_connect();
	$output = '';
	$query = "SELECT mp3act_playlist.*, mp3act_artists.artist_name,mp3act_artists.prefix, mp3act_songs.name,mp3act_albums.album_name,mp3act_songs.track,SEC_TO_TIME(mp3act_songs.length) AS time FROM mp3act_playlist,mp3act_artists,mp3act_songs,mp3act_albums WHERE mp3act_playlist.song_id=mp3act_songs.song_id AND mp3act_artists.artist_id=mp3act_songs.artist_id AND mp3act_songs.album_id=mp3act_albums.album_id AND " . (($_SESSION['sess_playmode'] == "streaming") ? ("mp3act_playlist.user_id=" . $_SESSION['sess_userid'] . " AND private=1") : ("private=0")) . " ORDER BY mp3act_playlist.pl_id";
	
	$result=mysql_query($query);
	
	while($row = mysql_fetch_array($result)){
			$output .= "<li id=\"pl$row[pl_id]\" onmouseover=\"setBgcolor('pl".$row['pl_id']."','#FCF7A5'); return false;\" onmouseout=\"setBgcolor('pl".$row['pl_id']."','#f3f3f3'); return false;\"><a href=\"#\" onclick=\"movePLItem('up',this.parentNode); return false;\" title=\"Move Song Up in Playlist\"><img src=\"img/up.gif\" /></a> <a href=\"#\" onclick=\"movePLItem('down',this.parentNode); return false;\" title=\"Move Song Down in Playlist\"><img src=\"img/down.gif\" /></a> <a href=\"#\" onclick=\"plrem(this.parentNode.id); return false;\" title=\"Remove Song from Playlist\"><img src=\"img/rem.gif\" /></a> $row[prefix] $row[artist_name] - $row[name]<p>Album: $row[album_name]<br/>Track: $row[track]<br/>$row[time]</p></li>";
		}
	if (isset($output)) {
		return $output;
	} else {
	  return '';
	}
}
function savePlaylist($pl_name, $prvt){
	mp3act_connect();
	$songs = array();
	$time=0;
	$query = "SELECT mp3act_playlist.song_id,mp3act_songs.length FROM mp3act_playlist,mp3act_songs WHERE mp3act_songs.song_id=mp3act_playlist.song_id AND " . (($_SESSION['sess_playmode'] == "streaming") ? ("mp3act_playlist.user_id=" . $_SESSION['sess_userid'] . " AND private=1") : ("private=0")) . " ORDER BY mp3act_playlist.pl_id";
	$result = mysql_query($query);
	while($row = mysql_fetch_array($result)){
		$songs[] = $row['song_id'];
		$time += $row['length'];
	}
	$songslist = implode(",",$songs);
	$query = "INSERT INTO mp3act_saved_playlists VALUES (NULL,$_SESSION[sess_userid],$prvt,\"$pl_name\",\"$songslist\",NOW(),$time,".count($songs).")";
	mysql_query($query);
	return "<h2>Playlist Saved as '".$pl_name."'</h2>";
}

function clearPlaylist(){
	mp3act_connect();
	$query = "DELETE FROM mp3act_playlist";
	if($_SESSION['sess_playmode'] == 'streaming'){
		$query .= " WHERE user_id=$_SESSION[sess_userid] AND private=1";
	}
	else{
		$query .= " WHERE private=0";
	}
	mysql_query($query);
	return "Playlist is empty";
}
function deletePlaylist($id){
	mp3act_connect();
	$query = "DELETE FROM mp3act_saved_playlists WHERE playlist_id=$id";
	mysql_query($query);
	return 1;
}

function playlistInfo(){
	mp3act_connect();
	$query = "SELECT COUNT(mp3act_playlist.pl_id) as count, SEC_TO_TIME(SUM(mp3act_songs.length)) as time FROM mp3act_playlist,mp3act_songs WHERE mp3act_playlist.song_id=mp3act_songs.song_id AND " . (($_SESSION['sess_playmode'] == "streaming") ? ("mp3act_playlist.user_id=" . $_SESSION['sess_userid'] . " AND private=1") : ("private=0"));
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	if($row['count'] == 0){
		return "Playlist is empty";
	}
	return "$row[count] Songs - $row[time]";
}

function playlist_rem($itemid){
	mp3act_connect();
	$id = substr($itemid, 2);
	$query = "DELETE FROM mp3act_playlist WHERE pl_id=$id";
	mysql_query($query);
	return $itemid;
}

function playlist_move($item1,$item2){
	mp3act_connect();
	$item1 = substr($item1, 2);
	$item2 = substr($item2, 2);
	$row = array();
	$query = "SELECT pl_id,song_id FROM mp3act_playlist WHERE pl_id=$item1 OR pl_id=$item2";
	$result = mysql_query($query);
	while($row[] = mysql_fetch_array($result)){
		
	}
	$query = "UPDATE mp3act_playlist SET song_id=" . $row[0]['song_id'] . " WHERE pl_id=" . $row[1]['pl_id'];
		mysql_query($query);
		$query = "UPDATE mp3act_playlist SET song_id=" . $row[1]['song_id'] . " WHERE pl_id=" . $row[0]['pl_id'];

	mysql_query($query);
}

function playlist_add($type,$itemid){
	mp3act_connect();
	switch($type){
	case 'song':
	  
		$query = "INSERT INTO mp3act_playlist VALUES (NULL,$itemid,$_SESSION[sess_userid],".($_SESSION['sess_playmode'] == "streaming" ? 1 : 0).")";
		
		mysql_query($query);
		$id = mysql_insert_id();
		$query = "SELECT mp3act_artists.artist_name, mp3act_artists.prefix,mp3act_albums.album_name,SEC_TO_TIME(mp3act_songs.length) AS length,mp3act_songs.name,mp3act_songs.track FROM mp3act_artists,mp3act_songs,mp3act_albums WHERE mp3act_songs.song_id=$itemid AND mp3act_artists.artist_id=mp3act_songs.artist_id AND mp3act_albums.album_id=mp3act_songs.album_id";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		
		$output[] = "<li id=\"pl$id\" onmouseover=\"setBgcolor('pl".$id."','#FCF7A5'); return false;\" onmouseout=\"setBgcolor('pl".$id."','#f3f3f3'); return false;\"><a href=\"#\" onclick=\"movePLItem('up',this.parentNode); return false;\" title=\"Move Song Up in Playlist\"><img src=\"img/up.gif\" /></a> <a href=\"#\" onclick=\"movePLItem('down',this.parentNode); return false;\" title=\"Move Song Down in Playlist\"><img src=\"img/down.gif\" /></a> <a href=\"#\" onclick=\"plrem(this.parentNode.id); return false;\" title=\"Remove Song from Playlist\"><img src=\"img/rem.gif\" /></a> $row[prefix] $row[artist_name] - $row[name]<p>Album: $row[album_name]<br/>Track: $row[track]<br/>$row[length]</p></li>";
		$output[] = 1;
		$output[] = 'pl'.$id;
		return $output;
	break;
	case 'album':
		$items='';
		$output = array();
		$query = "SELECT mp3act_songs.song_id,mp3act_songs.name,mp3act_artists.artist_name,mp3act_artists.prefix,mp3act_albums.album_name,SEC_TO_TIME(mp3act_songs.length) AS length,mp3act_songs.name,mp3act_songs.track FROM mp3act_songs,mp3act_artists,mp3act_albums WHERE mp3act_songs.album_id=$itemid AND mp3act_songs.artist_id=mp3act_artists.artist_id AND mp3act_albums.album_id=mp3act_songs.album_id ORDER BY track";
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result)){
		  $query = "INSERT INTO mp3act_playlist VALUES(NULL," . $row['song_id'] . "," . $_SESSION['sess_userid'] . "," . ($_SESSION['sess_playmode'] == "streaming" ? 1 : 0) . ")";
			mysql_query($query);
			$id = mysql_insert_id();
			$output[] = 'pl'.$id;
			$items .= "<li id=\"pl$id\" onmouseover=\"setBgcolor('pl".$id."','#FCF7A5'); return false;\" onmouseout=\"setBgcolor('pl".$id."','#f3f3f3'); return false;\"><a href=\"#\" onclick=\"movePLItem('up',this.parentNode); return false;\" title=\"Move Song Up in Playlist\"><img src=\"img/up.gif\" /></a> <a href=\"#\" onclick=\"movePLItem('down',this.parentNode); return false;\" title=\"Move Song Down in Playlist\"><img src=\"img/down.gif\" /></a> <a href=\"#\" onclick=\"plrem(this.parentNode.id); return false;\" title=\"Remove Song from Playlist\"><img src=\"img/rem.gif\" /></a> $row[prefix] $row[artist_name] - $row[name]<p>Album: $row[album_name]<br/>Track: $row[track]<br/>$row[length]</p></li>";
		}
		$text[] = $items;
		$num[] = count($output);
		$text = array_merge($text,$num);
		$output = array_merge($text,$output);
		return $output;

	break;
	case 'playlist':
		clearPlaylist();
		$query = "SELECT * FROM mp3act_saved_playlists WHERE playlist_id=$itemid LIMIT 1";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		$songs = explode(",",$row['playlist_songs']);
		
		foreach($songs as $song){
			$query = "INSERT INTO mp3act_playlist VALUES(NULL,$song,$_SESSION[sess_userid],".($_SESSION['sess_playmode'] == "streaming" ? 1 : 0).")";
			mysql_query($query);
		}
		$output[0] = 1;
		return $output;

	break;
	}
}
function randPlay($mode,$type,$num=0,$items){
		mp3act_connect();
		$tmp = '';
		$query = '';
		$items2 = explode(" ",$items);
		$items = '';
		
	if($mode == 'streaming'){
	
		session_cache_limiter('nocache');
        header("Content-Type: application/xspf+xml;");
  	header("Content-Disposition: inline; filename=\"playlist.xspf\"");
  	header("Expires: 0");
  	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  	header("Pragma: nocache"); 
		$tmp .= '<?xml version="1.0" encoding="UTF-8"?><playlist version="1" xmlns="http://xspf.org/ns/0/">'."\n<trackList>\n";
		switch($type){
			case 'artists':
				foreach($items2 as $item){
					$items .= " mp3act_songs.artist_id=$item OR";
				}
				$items = preg_replace("/OR$/","",$items);
				$query = "SELECT mp3act_songs.song_id,mp3act_artists.artist_name,mp3act_songs.name,mp3act_songs.length FROM mp3act_songs,mp3act_artists WHERE mp3act_artists.artist_id=mp3act_songs.artist_id AND (".$items.") ORDER BY rand()+0 LIMIT $num"; 
			break;
			case 'genre':
				foreach($items2 as $item){
					$items .= " mp3act_genres.genre_id=$item OR";
				}
				$items = preg_replace("/OR$/","",$items);
				$query = "SELECT mp3act_songs.song_id,mp3act_artists.artist_name,mp3act_songs.name,mp3act_songs.length,mp3act_albums.album_art FROM mp3act_songs,mp3act_artists,mp3act_genres,mp3act_albums WHERE mp3act_albums.album_id=mp3act_songs.album_id AND mp3act_albums.album_genre=mp3act_genres.genre AND mp3act_artists.artist_id=mp3act_songs.artist_id AND mp3act_albums.album_id = mp3act_songs.album_id AND (".$items.") ORDER BY rand()+0 LIMIT $num"; 
			break;
			case 'albums':
			foreach($items2 as $item){
					$items .= " mp3act_songs.album_id=$item OR";
				}
				$items = preg_replace("/OR$/","",$items);
				$query = "SELECT mp3act_songs.song_id,mp3act_artists.artist_name,mp3act_songs.name,mp3act_songs.length,mp3act_albums.album_art FROM mp3act_songs,mp3act_artists,mp3act_albums WHERE mp3act_artists.artist_id=mp3act_songs.artist_id AND mp3act_albums.album_id = mp3act_songs.album_id AND (".$items.") ORDER BY rand()+0 LIMIT $num"; 
			
			break;
			case 'all':
				$query = "SELECT mp3act_songs.song_id,mp3act_artists.artist_name,mp3act_songs.name,mp3act_songs.length, mp3act_albums.album_art FROM mp3act_songs,mp3act_artists, mp3act_albums WHERE mp3act_artists.artist_id=mp3act_songs.artist_id AND mp3act_albums.album_id = mp3act_songs.album_id ORDER BY rand()+0 LIMIT $num"; 
			break;
		}
		$result = mysql_query($query);
			
			while($row = mysql_fetch_array($result)){
				$tmp .= "<track>\n";
				$tmp .= "\t<creator>$row[artist_name]</creator><title>$row[name]</title>\n";
				$tmp .= "\t<location>$GLOBALS[http_url]$GLOBALS[uri_path]/playstream.php?i=$row[song_id]&u=$_SESSION[sess_usermd5]&b=$_SESSION[sess_bitrate]&s=$_SESSION[sess_stereo]</location>\n";
        $tmp .= "\t<meta rel=\"type\">mp3</meta>\n";
        $tmp .= "\t<image>$GLOBALS[http_url]$GLOBALS[uri_path]/art/$row[album_art]</image>\n";
				$tmp .= "</track>\n";
			}
		$tmp.="</trackList>\n</playlist>\n";
		return $tmp;   
		exit;
	}
	// JUKEBOX MODE
	else{
		switch($type){
			case 'artists':
				if(!file_exists("/tmp/mp3act")){
	  				touch("/tmp/mp3act");
	  				$string="";
						foreach($items2 as $item){
	  					$string.= "$item ";
						}
   			 	 exec(getSystemSetting("phpbin")." includes/play.php 6 $_SESSION[sess_userid] $string > /dev/null 2>&1 &"); 
					}
				break;
				case 'albums':
				if(!file_exists("/tmp/mp3act")){
	  				touch("/tmp/mp3act");
	  				$string="";
						foreach($items2 as $item){
	  					$string.= "$item ";
						}
   			 	 exec(getSystemSetting("phpbin")." includes/play.php 5 $_SESSION[sess_userid] $string > /dev/null 2>&1 &"); 
					}
				break;
				case 'genre':
				if(!file_exists("/tmp/mp3act")){
	  				touch("/tmp/mp3act");
	  				$string="";
						foreach($items2 as $item){
	  					$string.= "$item ";
						}
   			 	 exec(getSystemSetting("phpbin")." includes/play.php 4 $_SESSION[sess_userid] $string > /dev/null 2>&1 &"); 
					}
				break;
			case 'all':
				if(!file_exists("/tmp/mp3act")){
	  				touch("/tmp/mp3act");
	  				
   			 	 exec(getSystemSetting("phpbin")." includes/play.php 0 $_SESSION[sess_userid] > /dev/null 2>&1 &"); 
					}
				break;
		} //End switch
	}
	
	
}

function play($mode,$type,$id){
		mp3act_connect();
		$tmp = '';
		$query = '';
	if($mode == 'streaming'){
	
		session_cache_limiter('nocache');
        header("Content-Type: application/xspf+xml;");
  	header("Content-Disposition: inline; filename=\"playlist.xspf\"");
  	header("Expires: 0");
  	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  	header("Pragma: nocache"); 
		$tmp .= '<?xml version="1.0" encoding="UTF-8"?><playlist version="1" xmlns="http://xspf.org/ns/0/">'."\n<trackList>\n";
		
		if($type=='song'){		
			$query = "SELECT mp3act_songs.song_id,mp3act_artists.artist_name,mp3act_artists.prefix,mp3act_songs.name,mp3act_songs.length,mp3act_albums.album_art FROM mp3act_songs,mp3act_artists,mp3act_albums WHERE mp3act_songs.song_id=$id AND mp3act_albums.album_id = mp3act_songs.album_id AND mp3act_artists.artist_id=mp3act_songs.artist_id"; 
		}
		elseif($type=='album'){
			$query = "SELECT mp3act_songs.song_id,mp3act_artists.artist_name,mp3act_songs.name,mp3act_artists.prefix,mp3act_songs.length,mp3act_albums.album_art FROM mp3act_songs,mp3act_artists,mp3act_albums WHERE mp3act_artists.artist_id=mp3act_songs.artist_id AND mp3act_albums.album_id = mp3act_songs.album_id AND mp3act_songs.album_id=$id ORDER BY mp3act_songs.track"; 
		}
		 elseif($type=='pl'){
			$query = "SELECT mp3act_songs.song_id,mp3act_artists.artist_name,mp3act_songs.name,mp3act_artists.prefix,mp3act_songs.length,mp3act_albums.album_art FROM mp3act_songs,mp3act_artists,mp3act_playlist,mp3act_albums WHERE mp3act_artists.artist_id=mp3act_songs.artist_id AND mp3act_songs.song_id=mp3act_playlist.song_id AND mp3act_albums.album_id = mp3act_songs.album_id AND mp3act_playlist.user_id=$_SESSION[sess_userid] AND mp3act_playlist.private=1 ORDER BY mp3act_playlist.pl_id"; 
		}	
			
			$result = mysql_query($query);
			while($row = mysql_fetch_array($result)){
				$length = $row['length'];
				if(getSystemSetting("sample_mode") == 1){
					$length = floor($row['length']/4);
				}
				$tmp .= "<track>\n";
				$tmp .= "\t<creator>$row[artist_name]</creator><title>$row[name]</title>\n";
				$tmp .= "\t<location>$GLOBALS[http_url]$GLOBALS[uri_path]/playstream.php?i=$row[song_id]&u=$_SESSION[sess_usermd5]&b=$_SESSION[sess_bitrate]&s=$_SESSION[sess_stereo]</location>\n";
        $tmp .= "\t<meta rel=\"type\">mp3</meta>\n";
        $tmp .= "\t<image>$GLOBALS[http_url]$GLOBALS[uri_path]/art/$row[album_art]</image>\n";
				$tmp .= "</track>\n";
			}
			$tmp.="   </trackList></playlist>\n";
			return $tmp;   
			exit;
	}
	// JUKEBOX MODE
	else{
			switch($type){
				case 'stop':
					//exec("killall -c ".basename(getSystemSetting("phpbin"))." > /dev/null 2>&1 &");
					 //exec("killall -c ".basename(getSystemSetting("mp3bin"))." > /dev/null 2>&1 &");
					 killCmd("play.php");
					 killCmd(basename(getSystemSetting("mp3bin")));
					 //submitScrobbler($_SESSION['sess_userid']); 
					 
					if(file_exists("/tmp/mp3act")){
						unlink("/tmp/mp3act");
					}
					$query = "UPDATE mp3act_songs SET random=0";
					mysql_query($query);
					$query = "DELETE FROM mp3act_currentsong";
					mysql_query($query);
				break;
				case 'prev':
						// PREV is not working...
					 /*exec("killall ".getSystemSetting("phpbin")." > /dev/null 2>&1 &");
					 exec("killall ".getSystemSetting("mp3bin")." > /dev/null 2>&1 &");
					 $query = "DELETE FROM mp3act_currentsong";
						mysql_query($query);
					 exec(getSystemSetting("phpbin")." includes/play.php 3 $id > /dev/null 2>&1 &"); 
					 */
				break;
				case 'next':
					 //exec("killall -c ".basename(getSystemSetting("mp3bin"))." > /dev/null 2>&1 &");
					 killCmd(basename(getSystemSetting("mp3bin")));
				break;
				case 'song':
					if(!file_exists("/tmp/mp3act")){
	  				touch("/tmp/mp3act");
   			 	 exec(getSystemSetting("phpbin")." includes/play.php 1 $_SESSION[sess_userid] $id > /dev/null 2>&1 &"); 
					}
				break;
				case 'album':
					if(!file_exists("/tmp/mp3act")){
	  				touch("/tmp/mp3act");
   			 	 exec(getSystemSetting("phpbin")." includes/play.php 2 $_SESSION[sess_userid] $id > /dev/null 2>&1 &"); 
					}
				break;
				case 'pl':
					if(!file_exists("/tmp/mp3act")){
	  				touch("/tmp/mp3act");
   			 	 exec(getSystemSetting("phpbin")." includes/play.php 3 $id > /dev/null 2>&1 &"); 
					}
				break;
			}// END JUKEBOX MODE
	}
}
function killCmd($cmd){
  $pids =  shell_exec("ps A | grep '$cmd' | awk -- '{print $1}'");
  $pids = explode("\n",$pids);
  exec("kill $pids[0] $pids[1] > /dev/null 2>&1 &");
}

function playLocal($file){
	$mp3player = getSystemSetting("mp3bin");
	$command = "$mp3player ".escapeshellarg(stripslashes($file));
	$pid = shell_exec("nohup $command > /dev/null 2>&1");
	//setPid($pid);
  //$tmp=exec("$mp3player ".escapeshellarg(stripslashes($file))." ");
}

function setPid($pid){
  if(file_exists("/tmp/mp3act") && is_writable("/tmp/mp3act")){
    $handle = fopen("/tmp/mp3act", "w");
    fwrite($handle, $pid);
    fclose($handle);
  }
}

function download($album){
	mp3act_connect();
	$query = "SELECT mp3act_songs.filename,
	mp3act_artists.artist_name,
	mp3act_albums.album_name 
	FROM mp3act_songs,mp3act_artists,mp3act_albums 
	WHERE mp3act_songs.album_id=$album 
	AND mp3act_songs.album_id=mp3act_albums.album_id 
	AND mp3act_songs.artist_id=mp3act_artists.artist_id LIMIT 1";
	
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	$dir = dirname($row['filename']);
	
	$test = new zip_file("/tmp/album_$album.zip");
	$test->set_options(array('inmemory'=>0,'storepaths'=>0,'level'=>0,'method'=>0,'prepend'=>"$row[artist_name] - $row[album_name]"));
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
	$chunksize = 1*(1024*1024); // how many bytes per chunk
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

function verifyIP($user_md5,$ip){
  mp3act_connect();
  $query = "SELECT user_id FROM mp3act_users WHERE md5=\"$user_md5\" AND last_ip=\"$ip\"";
  $result = mysql_query($query);
  if(mysql_num_rows($result) > 0){
  	return true;
  }
  return false;
}


function updateNumPlays($num,$r=0,$user='',$mode='jukebox'){
	mp3act_connect();
	$query = "UPDATE mp3act_songs SET numplays=numplays+1";
	if($r==1){
	 $query .= ",random=1";
	}
	$query .= " WHERE song_id=$num";
  mysql_query($query);
  
  if(!empty($user)){
  	if($mode == 'streaming'){
			$query = "SELECT user_id FROM mp3act_users WHERE md5=\"$user\"";	
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);
			$user = $row['user_id'];
			
  	}
  	$query = "INSERT INTO mp3act_playhistory VALUES (NULL,$user,$num,NOW())";
  	insertScrobbler($num,$user,$mode);
  	mysql_query($query);
  }
}

function streamPlay($id, $rate=0, $stereo="s",$user='',$ip=''){
  mp3act_connect();
  // check to see if IP is in the verified IP DB for that user
  if(verifyIP($user,$ip)){
  
  $query = "SELECT mp3act_artists.artist_name, 
  mp3act_songs.name,
  mp3act_songs.bitrate, 
  mp3act_songs.length as length, 
  mp3act_songs.filename as filename, 
  mp3act_songs.size as size 
  FROM mp3act_songs,mp3act_artists 
  WHERE mp3act_songs.song_id=$id 
  AND mp3act_artists.artist_id=mp3act_songs.artist_id";
  
  $result=mysql_query($query);
  $row = mysql_fetch_array($result);
	updateNumPlays($id,0,$user,'streaming');
	clearstatcache(); // flush buffer
  
	$file['name'] = basename($row['filename']);
	$mp3out = '';
	if(getSystemSetting("lamebin") != "" && $rate != 0){
			$row['size'] = (($row['length'] + 1) * $rate * 1000)/8;
			$mp3out = getSystemSetting("lamebin")." -b $rate -s $stereo --silent --nores --mp3input -h \"".stripslashes($row['filename'])."\" -";
	}else{
		$mp3out = stripslashes($row['filename']);
	
	}
	$size=$row['size'];
	$mode = getSystemSetting("sample_mode");
	if($mode == 1){
		$size = floor($row['size']/4);
	}
	header("Content-Type: audio/mpeg");
	header("Content-Length: $size");
  header("Content-Disposition: filename=$row[artist_name] - $row[name]");
	
	// Run the command, and read back the results at the bitrate size + 1K.
	$blocksize=($row['bitrate']*1024)+1024;
	$totaldata=0;
	if($rate!=0 && $mode==1){
		$temp = @popen($mp3out, "r");
		while (($data = @fread($temp, $blocksize )) && ($totaldata <= $size ) )
			{echo $data; $totaldata+=$blocksize; }
		pclose($temp);
	}
	elseif($rate!=0 ){
		$temp = @popen($mp3out, "r");
		while ($data = @fread($temp, $blocksize) )
			{echo $data; }
		pclose($temp);
	}
	elseif($mode==1 ){
		$temp = @fopen($mp3out, "r");
		while (!feof($temp)  && ($totaldata <= $size ) )
			{$data = @fread($temp, $blocksize); echo $data; $totaldata+=$blocksize; }
		fclose($temp);
	}
	else{
		$temp = @fopen($mp3out, "r");
		while (!feof($temp) )
			{$data = @fread($temp, $blocksize); echo $data; }
		fclose($temp);
	}
	} // end IF for verify IP
	exit;
}

function art_insert($album_id, $artist, $album){
		mp3act_connect();
		$query ='';
		$image = art_query($artist,$album);
		if($image != ""){
			$query = "UPDATE mp3act_albums SET album_art=\"$album_id.jpg\" WHERE album_id=$album_id";
			mysql_query($query);

			$tmpimg = _http_get($image);
			$path = $GLOBALS['abs_path']."/art/";
			
			$file = "$album_id.jpg";
			$filename = $path.$file;
			touch($filename);
			
			// Let's make sure the art directory is writable first.
			if (is_writable($filename)) {

				 if (!$handle = fopen($filename, 'a')) {
							 echo "Cannot open file ($filename)";
							 exit;
				 }
			
				 // Write $somecontent to our opened file.
				 if (fwrite($handle, $tmpimg) === FALSE) {
						 echo "Cannot write to file ($filename)";
						 exit;
				 }
								
				 fclose($handle);
			
			} else {
				 echo "The file $filename is not writable";
			}
			
			return $file;
		}
		else{
			$query = "UPDATE mp3act_albums SET album_art=\"fail\" WHERE album_id=$album_id";
			mysql_query($query);
		}

}

function art_query ($artist, $album) {
  $amazon_api_id = getSystemSetting("amazonid");
  if(!empty($amazon_api_id)){
  	$album = preg_replace( '!\(.+\)!', '', $album );
		$theq = "$artist, $album";
		$query = urlencode($theq);
		
		$file = "http://xml.amazon.com/onca/xml3?t=blah&dev-t=";
		$file .= $amazon_api_id;
		$file .= "&mode=music&type=lite&page=1&f=xml&KeywordSearch=";
		$file .= $query;
		$fp = fopen($file, "r");
		$contentStart = fread($fp, 200000);

		$content = ereg_replace("<?xml.*\">.*<ProductInfo.*\">","",$contentStart);

		$items = explode("</Details>",$content);
		$maxlinks2 = "1";

		for ($i = 0; $i < $maxlinks2; $i++) {
						
				//$artSmall = ereg_replace(".*<ImageUrlSmall>","",$items[$i]);
				//$artSmall = ereg_replace("</ImageUrlSmall>.*","",$artSmall);
				$artMedium = ereg_replace(".*<ImageUrlMedium>","",$items[$i]);
				$artMedium = ereg_replace("</ImageUrlMedium>.*","",$artMedium);
				//$artLarge = ereg_replace(".*<ImageUrlLarge>","",$items[$i]);
				//$artLarge = ereg_replace("</ImageUrlLarge>.*","",$artLarge);
				
				if (strstr($artMedium, "amazon.com") == true) {
				
						//$small_size = getimagesize($artSmall);
								//if($small_size[0] > 2) { $small_okay = "yes"; } else { $small_okay = "no"; }
					 /* $medium_size = getimagesize($artMedium);
								if($medium_size[0] > 2) { $medium_okay = "yes"; } else { $medium_okay = "no"; }
						$large_size = getimagesize($artLarge);
								if($large_size[0] > 2) { $large_okay = "yes"; } else { $large_okay = "no"; }
							*/
						//return $artSmall;  
						return $artMedium; 
				
				} else {
						return '';
				}
				
		}
  }
  else{
  	return '';
  }

  }

// Grab an image over the web and save it locally
function _http_get($url)
{

   $url_stuff = parse_url($url);
   $port = isset($url_stuff['port']) ? $url_stuff['port'] : 80;

   $fp = fsockopen($url_stuff['host'], $port);
   $buffer = '';
   $query  = 'GET ' . $url_stuff['path'] . " HTTP/1.0\n";
   $query .= 'Host: ' . $url_stuff['host'];
   $query .= "\n\n";

   fwrite($fp, $query);

   while ($tmp = fread($fp, 1024))
   {
       $buffer .= $tmp;
   }

   preg_match('/Content-Length: ([0-9]+)/', $buffer, $parts);
   return substr($buffer, - $parts[1]);


}

function resetDatabase(){
	mp3act_connect();
	$query = array();
	$query[] = "TRUNCATE TABLE mp3act_songs";
	$query[] = "TRUNCATE TABLE mp3act_artists";
	$query[] = "TRUNCATE TABLE mp3act_albums";
	$query[] = "TRUNCATE TABLE mp3act_playlist";
	$query[] = "TRUNCATE TABLE mp3act_saved_playlists";
	$query[] = "TRUNCATE TABLE mp3act_genres";
	$query[] = "TRUNCATE TABLE mp3act_stats";
	$query[] = "TRUNCATE TABLE mp3act_playhistory";
	$query[] = "TRUNCATE TABLE mp3act_currentsong";
	
	foreach($query as $q){
		mysql_query($q);
	}
	
	$path = $GLOBALS['abs_path']."/art/";

	if (is_dir($path)) {
   if ($dh = opendir($path)) {
       while (($file = readdir($dh)) !== false) {
       		if($file !="." && $file != ".." && $file !=".svn"){
            unlink($path . $file);
          }
       }
       closedir($dh);
   }
	}
	return 1;
}
    
?>
