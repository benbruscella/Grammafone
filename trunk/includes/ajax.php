<?php
require_once('config.php');

/**
* ajaxSongGetCurrent: Returns the Current Playing Song.
 *
 * AJAX function to return the current playing song html
 *
 * @param string $curArtist The Current Artist
 * @param string $curSong The Current Song
 * @return string the full greeting string.
 * @author Grammafone Dev
 * @todo there is a bug in this code which causes the refresh to fail.
 */
function ajaxSongGetCurrent($curArtist, $curSong){
    grammafone_connect();
    $data = array();
    $query = "SELECT 
                grammafone_currentsong.random, 
                grammafone_currentsong.pl_id,
                grammafone_artists.artist_name, 
                grammafone_artists.prefix, 
                grammafone_songs.name 
              FROM 
                grammafone_artists, 
                grammafone_songs, 
                grammafone_currentsong 
              WHERE 
                grammafone_currentsong.song_id = grammafone_songs.song_id 
              AND 
                grammafone_songs.artist_id=grammafone_artists.artist_id";
    $result = mysql_query($query);
    if(mysql_num_rows($result) == 0) {
        $data[] = "<strong>No Songs Selected For Playback</strong>
                   <span id='artist'></span><span id='song'></span>";
        $data[] = 0;
        $data[] = 0;
        return $data;
    }
    else {
        $row = mysql_fetch_array($result);
        if($row['artist_name'] == $curArtist && $row['name'] == $curSong) {
            $data[] = 1;
            $data[] = $row['pl_id'];
            $data[] = 1;
            return $data;
        }
        else {
            $data[] = "<strong>Now Playing: </strong>
                       <span id='artist'>$row[prefix] $row[artist_name]</span> - <span id='song'>$row[name]</span>\n";
            $data[] = $row['pl_id'];
            $data[] = 1;
            return $data;
        }
    }
}

/**
 * ajaxUserGet: Returns the User flag.
 *
 * @param
 * @return
 * @author
 * @todo
 */
function ajaxUserGet($username){
    grammafone_connect();
    $sql = "SELECT 
              user_id 
            FROM 
              grammafone_users 
            WHERE 
              username=\"$username\"";
    $result = mysql_query($sql);
    
    if(mysql_num_rows($result)>0) {
        return 1;
    }
    else {
        return 0;
    }
}                 

/**
 * ajaxPlaylistRemove: Returns the User flag.
 *
 * @param
 * @return
 * @author
 * @todo
 */
function ajaxPlaylistRemove($itemid) {
    grammafone_connect();
    $id = substr($itemid, 3);
    $query = "DELETE FROM 
                grammafone_playlist 
              WHERE 
                pl_id=$id 
              AND 
                user_id=$_SESSION[sess_userid]";
    mysql_query($query);
    return $itemid;
}


?>
