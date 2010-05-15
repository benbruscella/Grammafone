<?php
include_once("grammafone.php");
include_once("audioScrobblerClass.php");
mp3act_connect();
set_time_limit(0);

function getSongInfo($song_id){
  $sql = "SELECT mp3act_songs.length, 
      mp3act_songs.name, 
      mp3act_artists.artist_name, 
      mp3act_albums.album_name 
      FROM mp3act_songs,mp3act_artists,mp3act_albums 
      WHERE mp3act_songs.song_id=$song_id 
      AND mp3act_songs.album_id=mp3act_albums.album_id 
      AND mp3act_artists.artist_id=mp3act_songs.artist_id";
      if($result = mysql_query($sql)){
           $row = mysql_fetch_array($result);
           return $row;
      }
}


function updateScrobblerResult($user_id, $result){
  $result = date("(m.d.y g:i:sa) ").$result;
  $sql = "UPDATE mp3act_users SET as_lastresult=\"$result\" WHERE user_id=$user_id";
  if(mysql_query($sql)){
    return TRUE;
  }
}

if(isset($_SERVER['argv'][1])){
  
  
  $sql = "SELECT as_username,as_password FROM mp3act_users WHERE as_password!=\"\" AND as_username!=\"\" AND user_id=".$_SERVER['argv'][1];
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

      $sql = "SELECT * FROM mp3act_audioscrobbler WHERE user_id=".$_SERVER['argv'][1];
         
           
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
           $sql = "DELETE FROM mp3act_audioscrobbler WHERE as_id=$songs_sql";
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
