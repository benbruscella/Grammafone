<?php
include_once("grammafone.php");
include_once("audioScrobblerClass.php");
grammafone_connect();
set_time_limit(0);

function getSongInfo($song_id){
  $sql = "SELECT grammafone_songs.length, 
      grammafone_songs.name, 
      grammafone_artists.artist_name, 
      grammafone_albums.album_name 
      FROM grammafone_songs,grammafone_artists,grammafone_albums 
      WHERE grammafone_songs.song_id=$song_id 
      AND grammafone_songs.album_id=grammafone_albums.album_id 
      AND grammafone_artists.artist_id=grammafone_songs.artist_id";
      if($result = mysql_query($sql)){
           $row = mysql_fetch_array($result);
           return $row;
      }
}


function updateScrobblerResult($user_id, $result){
  $result = date("(m.d.y g:i:sa) ").$result;
  $sql = "UPDATE grammafone_users SET as_lastresult=\"$result\" WHERE user_id=$user_id";
  if(mysql_query($sql)){
    return TRUE;
  }
}

if(isset($_SERVER['argv'][1])){
  
  
  $sql = "SELECT as_username,as_password FROM grammafone_users WHERE as_password!=\"\" AND as_username!=\"\" AND user_id=".$_SERVER['argv'][1];
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  $as = new scrobbler($row['as_username'], $row['as_password']);
  
  if(mysql_num_rows($result) > 0 ){
   
      $wait = 60;
      $success = FALSE;
      while(!$success && $wait<=7200){
        if($as->handshake()) {
            //echo "Handshake Success\n";
            updateScrobblerResult($_SERVER['argv'][1], "Handshake Successful");
            $success=TRUE;
           
        }else{
          //echo "Handshake Failed (waiting $wait seconds): ".$as->getErrorMsg()."\n";
          updateScrobblerResult($_SERVER['argv'][1], "Handshake Failed: ".$as->getErrorMsg());
          sleep($wait);
          $wait = $wait*2;
        }
      }

      $sql = "SELECT * FROM grammafone_audioscrobbler WHERE user_id=".$_SERVER['argv'][1];
         
           
      $wait = 60;
      $success = FALSE;
     while(!$success && $wait<=7200){
       
      $result = mysql_query($sql);
      $songs = array();
      if(mysql_num_rows($result) > 0){
       $now = time();
        while($row = mysql_fetch_array($result)){
            $song=getSongInfo($row['song_id']);
            $songs[]= $row['as_id'];
             if($song['length']>30){
              $as->queueTrack($song['artist_name'], $song['album_name'], $song['name'], $row['as_timestamp'], $song['length']);
            }
        }
       
       if($as->submitTracks()) {
           //echo "Submit Success\n";
           $success=TRUE;
           $songs_sql = implode(" OR as_id=",$songs);
           $sql = "DELETE FROM grammafone_audioscrobbler WHERE as_id=$songs_sql";
           mysql_query($sql);
           updateScrobblerResult($_SERVER['argv'][1], "Successfully submitted ".count($songs)." songs to AudioScrobbler");

       }else{
         //echo "Submit Failed (waiting $wait seconds): ".$as->getErrorMsg()."\n";
         updateScrobblerResult($_SERVER['argv'][1], "Submit Failed: ".$as->getErrorMsg());
         sleep($wait);
         $wait = $wait*2;
       }
     }
     
    }
  }
}


?>