<?php
include_once("mp3act_functions.php");
mp3act_connect();
// Play a song
if($_SERVER['argv'][1] == 1){
  $query = "SELECT song_id,filename FROM mp3act_songs WHERE song_id=".$_SERVER['argv'][3]." ORDER BY track";
	$result=mysql_query($query);
	
	while($row = mysql_fetch_array($result)){	   
	   updateNumPlays($row['song_id'],0,$_SERVER['argv'][2]);
	   setCurrentSong($row['song_id'],0);
     playLocal($row['filename']);
	}
	
	$query = "DELETE FROM mp3act_currentsong";
	mysql_query($query);
	if(file_exists("/tmp/mp3act")){ unlink("/tmp/mp3act"); }
	exec(getSystemSetting("phpbin")." includes/play.php 3 $id > /dev/null 2>&1 &"); 

}
// play an album
elseif($_SERVER['argv'][1] == 2){
	$query = "SELECT song_id,filename FROM mp3act_songs WHERE album_id=".$_SERVER['argv'][3]." ORDER BY track";
	$result=mysql_query($query);
	while($row = mysql_fetch_array($result)){
	  updateNumPlays($row['song_id'],0,$_SERVER['argv'][2]);
    setCurrentSong($row['song_id'],0);
    playLocal($row['filename']);
	}
	
	$query = "DELETE FROM mp3act_currentsong";
	mysql_query($query);
	if(file_exists("/tmp/mp3act")){ unlink("/tmp/mp3act"); }
	exec(getSystemSetting("phpbin")." includes/play.php 3 $id > /dev/null 2>&1 &");
	
}
// play the playlist
elseif($_SERVER['argv'][1] == 3){
			$pl_id = $_SERVER['argv'][2];
			$where = '';
		  $query = "SELECT mp3act_songs.filename,mp3act_playlist.* FROM mp3act_playlist,mp3act_songs WHERE mp3act_songs.song_id=mp3act_playlist.song_id AND mp3act_playlist.private=0";
		
	    $query2 = " ORDER BY mp3act_playlist.pl_id LIMIT 1";
	   
	
	  while(1){
	  	if($pl_id!='' && $pl_id!=0){
	  		$where = " AND mp3act_playlist.pl_id>$pl_id";
	  	}
	  	$newquery = $query.$where.$query2;
	  	
	  	$result=mysql_query($newquery);
			if(mysql_num_rows($result) == 0){
				$query = "DELETE FROM mp3act_currentsong";
				 mysql_query($query);
				if(file_exists("/tmp/mp3act")){ unlink("/tmp/mp3act"); }
				break;
			}
			else{ 
					$row = mysql_fetch_array($result);
					$pl_id = $row['pl_id'];
					updateNumPlays($row['song_id'],0,$row['user_id']);
					setCurrentSong($row['song_id'],$row['pl_id']);
					playLocal($row['filename']);
			}
    }
	
	$query = "DELETE FROM mp3act_currentsong";
	mysql_query($query);
	if(file_exists("/tmp/mp3act")){ unlink("/tmp/mp3act"); }
	
	
}
// play random genres
elseif($_SERVER['argv'][1] == 4){
	$query1 = "SELECT mp3act_songs.song_id,mp3act_songs.filename FROM mp3act_albums,mp3act_songs,mp3act_genres WHERE mp3act_albums.album_id=mp3act_songs.album_id AND mp3act_albums.album_genre=mp3act_genres.genre AND (";
	for($i=3; $i<count($_SERVER['argv']); $i++){
	  $query1 .= " mp3act_genres.genre_id=".$_SERVER['argv'][$i]." OR";
	}
	$query1 = preg_replace("/OR$/","",$query1);
	$query1 .= ") AND mp3act_songs.random!=1 ORDER BY RAND()+0 LIMIT 1";
	
	while(1){
	   $result=mysql_query($query1);	   
	   
	    
		if(mysql_num_rows($result) == 0){
		  $query = "DELETE FROM mp3act_currentsong";
	     mysql_query($query);
			 $query = "UPDATE mp3act_songs SET random=0";
	     mysql_query($query);
	    if(file_exists("/tmp/mp3act")){ unlink("/tmp/mp3act"); }
			break;
		}
		else{
		$row = mysql_fetch_array($result);
	   $query = "DELETE FROM mp3act_currentsong";
	   mysql_query($query);
		 
	   updateNumPlays($row['song_id'],1,$_SERVER['argv'][2]);
	   setCurrentSong($row['song_id'],0,1);
     
     playLocal($row['filename']);
    }
	}
	$query = "DELETE FROM mp3act_currentsong";
	mysql_query($query);
	if(file_exists("/tmp/mp3act")){ unlink("/tmp/mp3act"); }
	
	
}
// Play random albums
elseif($_SERVER['argv'][1] == 5){
	$query1 = "SELECT song_id,filename FROM mp3act_songs WHERE (";
	for($i=2; $i<count($_SERVER['argv']); $i++){
	  $query1 .= " album_id=".$_SERVER['argv'][$i]." OR";
	}
	$query1 = preg_replace("/OR$/","",$query1);
	$query1 .= ") AND random!=1 ORDER BY RAND()+0 LIMIT 1";
	echo $query1;
	
	while(1){
	   $result=mysql_query($query1);	   
	   
	    
		if(mysql_num_rows($result) == 0){
		  $query = "DELETE FROM mp3act_currentsong";
	     mysql_query($query);
			 $query = "UPDATE mp3act_songs SET random=0";
	     mysql_query($query);
	    if(file_exists("/tmp/mp3act")){ unlink("/tmp/mp3act"); }
			break;
		}
		else{
		$row = mysql_fetch_array($result);
	   $query = "DELETE FROM mp3act_currentsong";
	   mysql_query($query);
		 
	   updateNumPlays($row['song_id'],1,$_SERVER['argv'][2]);
     	setCurrentSong($row['song_id'],0,1);
  		playLocal($row['filename']);
    }
	}
	$query = "DELETE FROM mp3act_currentsong";
	mysql_query($query);
	if(file_exists("/tmp/mp3act")){ unlink("/tmp/mp3act"); }
	
	
}
// Play random artists
elseif($_SERVER['argv'][1] == 6){
	$query1 = "SELECT song_id, filename FROM mp3act_songs WHERE (";
	for($i=3; $i<count($_SERVER['argv']); $i++){
	  $query1 .= " artist_id=\"".urldecode($_SERVER['argv'][$i])."\" OR";
	}
	$query1 = preg_replace("/OR$/","",$query1);
	$query1 .= ") AND random!=1 ORDER BY RAND()+0 LIMIT 1";
	
	while(1){
	   $result=mysql_query($query1);	   
	    
		if(mysql_num_rows($result) == 0){
		  $query = "DELETE FROM mp3act_currentsong";
	     mysql_query($query);
			$query = "UPDATE mp3act_songs SET random=0";
	     mysql_query($query);
	    if(file_exists("/tmp/mp3act")){ unlink("/tmp/mp3act"); }
			break;
		}
		else{
		$row = mysql_fetch_array($result);
	   $query = "DELETE FROM mp3act_currentsong";
	   mysql_query($query);
		 
	   updateNumPlays($row['song_id'],1,$_SERVER['argv'][2]);
     	setCurrentSong($row['song_id'],0,1);
  		playLocal($row['filename']);
    }
	}
	$query = "DELETE FROM mp3act_currentsong";
	mysql_query($query);
	if(file_exists("/tmp/mp3act")){ unlink("/tmp/mp3act"); }
	
	
}
// play everything random
else{
  
 while(1){
 
   $query="SELECT song_id,filename FROM mp3act_songs WHERE random!=1 ORDER BY rand()+0 LIMIT 1";
   $result=mysql_query($query);
   $row = mysql_fetch_array($result);
	 $query = "DELETE FROM mp3act_currentsong";
	 mysql_query($query);

   updateNumPlays($row['song_id'],1,$_SERVER['argv'][2]);
	 setCurrentSong($row['song_id'],0,1);
   playLocal($row['filename']);
 }
    $query = "DELETE FROM mp3act_currentsong";
	  mysql_query($query);
	  if(file_exists("/tmp/mp3act")){ unlink("/tmp/mp3act"); }
}
?>