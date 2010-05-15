<?php

require_once('getid3/getid3.php');

class addMusic {
  var $pathToSearch;
  var $displayResults;
  var $fileList;

  /* Constructor */
  function addMusic() {
    $this->pathToSearch = "";
    $this->displayResults = 0;
  }

  /* Set the path variable */
  function setPath ($newPath) {
    $this->pathToSearch = $newPath;
  }

  function setDisplayResults ($switch) {
    $this->displayResults = $switch;
  }

  function &getSongs($path,&$filelist) {
    if (!is_dir($path)) {
      return NULL;
    }
    $resdir = @opendir ($path) or die("Error reading ".$path);

    // It's the secret for this recursive function
    $ret = &$filelist;

    while ( ($entry=readdir($resdir))!==false ) {
      if ( is_dir($path."/".$entry)=="dir" ) { //if it's a directory...

        if ($entry!="." and $entry!="..") {//do not use ./ or ../
          // Call himself using the same result array
          $this->getSongs($path."".$entry."/",$ret);
        }
      } 
      else {
        // if it is not a dir, read the filenames
        if(substr($entry,strlen($entry)-4,4) == ".mp3") {
          $ret[$path][] = addslashes($entry);
        }
      }//if
    }//while

    closedir($resdir);
    $this->fileList = $ret;
  }

  function insertSongs() { 

    $getID3 = new getID3;
    mp3act_connect();
    $time = time();
    $i=0;
    $current_album = 0;
    $current_artist = 0;

    foreach($this->fileList as $path => $files){
      $path = addslashes($path);
      foreach($files as $song){
      set_time_limit(20);
      $flag=0;
      $errors='';
      $tagInfo=array();
      $tagInfo = $getID3->analyze(stripslashes($path)."".stripslashes($song));
      $song = addslashes($song);
      $query = "SELECT * FROM mp3act_songs WHERE filename=\"".$path.$song."\"";

      if(mysql_num_rows(mysql_query($query)) == 0) {

      if(1) {
        #get artist
        if(!( $goodData["artist"] = addslashes(ucwords($tagInfo['tags']['id3v2']['artist'][0])) )) {
          if(!($goodData["artist"] = addslashes(ucwords($tagInfo['tags']['id3v1']['artist'][0])) )) {
            $errors .= "Missing Artist Name<br/>\n";
            $flag=1;
          }
        }
        #get Album
        if(!( $goodData["album"] = addslashes(ucwords($tagInfo['tags']['id3v2']['album'][0])) )) {
          if(!($goodData["album"] = addslashes(ucwords($tagInfo['tags']['id3v1']['album'][0])) )) {
            $errors .= "Missing Album Name<br/>\n";
            $flag=1; 
          }
        }
        # get Song Title
        if(!( $goodData["name"] = addslashes(ucwords($tagInfo['tags']['id3v2']['title'][0])) )) {
          if(!($goodData["name"] = addslashes(ucwords($tagInfo['tags']['id3v1']['title'][0])) )) {
            $errors .= "Missing Song Title<br/>\n";
            $flag=1; 
          }
        }
        # get track number
        if(!( $goodData["track"] = $tagInfo['tags']['id3v2']['track'][0] )) {
          if(!($goodData["track"] = $tagInfo['tags']['id3v1']['track'][0] )) {
            $goodData["track"] = 1;
          }
        }
        #Get Genre (if tagged, but don't require; give untagged ID3v1 genre 'other')
        if (isset($tagInfo['tags']['id3v2']['genre'][0])) {
          $goodData['genre'] = addslashes(ucwords($tagInfo['tags']['id3v2']['genre'][0]));
        } 
        elseif (isset($tagInfo['tags']['id3v1']['genre'][0])) {
          $goodData['genre'] = addslashes(ucwords($tagInfo['tags']['id3v1']['genre'][0]));
        } 
        else {
            $goodData['genre'] = "Other";
          }
          #Get Year (default to '0')
          if(isset($tagInfo['tags']['id3v2']['year'][0])){
            $goodData["year"] = $tagInfo['tags']['id3v2']['year'][0];
          }elseif(isset($tagInfo['tags']['id3v1']['year'][0])) {
            $goodData["year"] = $tagInfo['tags']['id3v1']['year'][0];
          }
          else{
            $goodData["year"] = 0;
          }
          
          $goodData["length"] = $tagInfo['playtime_seconds'];
          $goodData["size"] = $tagInfo['filesize'];
          $goodData["bitrate"] = ($tagInfo['bitrate']/1000);
					$goodData["type"] = $tagInfo['fileformat'];
        
        
        if($flag!=1){
         		$artist = $goodData['artist'];
            	$prefix = '';
            	if(substr($goodData['artist'], 0, 4) == "The "){
            		$artist = substr($goodData['artist'], 4);
            		$prefix = "The ";
            	}
          	 $query = "SELECT artist_id FROM mp3act_artists WHERE artist_name=\"$artist\"";
            //echo $query;
            $result = mysql_query($query);
            $artistid = mysql_fetch_array($result);
            if($artistid['artist_id'] > 0){
            	$current_artist = $artistid['artist_id'];
            }
            else{
            	
           		$query = "INSERT INTO mp3act_artists VALUES (NULL,\"$artist\",\"$prefix\")";
           		
           		$result = mysql_query($query);
           		$current_artist = mysql_insert_id();
						}  
						
						
            $query = "SELECT album_id FROM mp3act_albums WHERE album_name=\"$goodData[album]\" AND artist_id=$current_artist";
            //echo $query;
            $result = mysql_query($query);
            $album = mysql_fetch_array($result);
            if($album['album_id'] > 0){
            	$current_album = $album['album_id'];
            }
            else{
           		$query = "INSERT INTO mp3act_albums VALUES (NULL,\"$goodData[album]\",$current_artist,\"$goodData[genre]\",$goodData[year],\"\")";
           		
           		$result = mysql_query($query);
           		$current_album = mysql_insert_id();
						}           
            $query = "INSERT INTO mp3act_songs VALUES";
            $query.=" (NULL,$current_artist,$current_album,NOW(),\"$goodData[name]\",$goodData[track],";
            $query.="\"$goodData[length]\",$goodData[size],$goodData[bitrate],\"$goodData[type]\",0,";
            $query.="\"$path$song\",0)";
        	
            if(mysql_query($query)){
              if($this->displayResults ==1){
                $results[$i] = "Added ".$goodData["name"]." By ".$goodData["artist"]."<br>\n";
              }
              $i=$i+1;
            }
            else{
            	echo mysql_error();
            }
          }
          else{
              echo "<strong>".$path."".$song." Has Errors:</strong><br/>\n";
              echo $errors."<br/>\n";
          }
        }
      }
      } 
      
    }
    $this->updateGenres();
    $this->updateStats();
    
    $time2 = time()-$time;
    echo "<br/>".$time2." secs";
    if($this->displayResults == 1){
      if($i == 0){
        $results[0] = "No New Songs Added to Database<br>\n";
      }
      return $results;
    }
    return $i;
  }

  function updateStats(){
    mp3act_connect();
    $query = "SELECT COUNT(DISTINCT album_id) as num_albums, 
              COUNT(DISTINCT artist_id) as num_artists, 
              COUNT(song_id) as num_songs, 
              SEC_TO_TIME(SUM(length)) as total_time, 
              SUM(size)/1024000000 as total_size 
              FROM mp3act_songs"; 
    $result = mysql_query($query);
    $row = mysql_fetch_assoc($result);
    $query="DELETE FROM mp3act_stats";
    mysql_query($query);
    $query2 = "SELECT COUNT(genre_id) as num_genres FROM mp3act_genres";
     $result2= mysql_query($query2);
    $row2 = mysql_fetch_assoc($result2);
    
    $query3="INSERT INTO mp3act_stats VALUES ( 
            ".$row['num_artists'].", 
            ".$row['num_albums'].",
            ".$row['num_songs'].",
            ".$row2['num_genres'].",
            \"".$row['total_time']."\",
            \"".$row['total_size']."GB\")"."";
    mysql_query($query3);
  }

  function updateGenres(){
    mp3act_connect();
    $query = "DELETE FROM mp3act_genres";
    mysql_query($query);
    $query = "SELECT album_genre FROM mp3act_albums GROUP BY album_genre";
    $result = mysql_query($query);
    while($genre = mysql_fetch_assoc($result)){
      $query = "INSERT INTO mp3act_genres VALUES (NULL,\"".$genre['album_genre']."\")";
      mysql_query($query);
    }
    
  }
	/* This function generates a form for the user to alter and adjust.
   * The current status is that: 
   *    it reads tags for information.
   *    If it doesn't exist, it makes a few assumptions:
   *      - if ID3 tag has no title, it will try (a portion of) the file name
   *      - if ID3 tag has no track, it will try a portion of the filename
   *      - if ID3 has no album, it will try the parent folder
   *      - if ID3 has no artist, it will try the parent's parent folder
   * It does have a few flaws - which need attention:
   * - It might be more comprehensible if we made some kind of delimiter for each 
   *   directory
   * - per-file option to adjust the tags or files (echo-ing filename is needed 
   *   then
   * - eye candy : alternating colors per file.. a bit like the rows in the return
   *   list of search
   */
  function editSongs($songs) {
    $genres = array(" ", "-----", "A capella", "Acid", "Acid Jazz", "Acid Punk", "Acoustic", "Alternative Rock", 
	                "Alternative", "Ambient", "Avantgarde", "Ballad", "Bass", "Bebob", "Big", "Band", "Bluegrass", 
					"Blues", "Booty", "Bass", "Cabaret", "Celtic", "Chamber", "Music", "Chanson", "Chorus", 
					"Christian Rap", "Classic Rock", "Classical", "Club", "Comedy", "Country", "Cult", "Dance", 
					"Dance Hall", "Darkwave", "Death", "Metal", "Disco", "Dream", "Drum Solo", "Duet", "Easy Listening", 
					"Electronic", "Ethnic", "Euro-House", "Euro-Techno", "Eurodance", "Fast Fusion", "Folk", "Folk-Rock", 
					"Folklore", "Freestyle", "Funk", "Fusion", "Game", "Gangsta", "Gospel", "Gothic", "Gothic Rock", 
					"Grunge", "Hard Rock", "Hip-Hop", "House", "Humour", "Industrial", "Instrumental", "Instrumental Pop", 
					"Instrumental Rock", "Jazz", "Jazz+Funk", "Jungle", "Latin", "Lo-Fi", "Meditative", "Metal", 
					"Musical", "National", "Folk", "Native American", "New Age", "New Wave", "Noise", "Oldies", "Opera", 
					"Other", "Polka", "Pop", "Pop-Folk", "Pop/Funk", "Porn Groove", "Power Ballad", "Pranks", "Primus", 
					"Progressive Rock", "Psychadelic", "Psychedelic Rock", "Punk", "Punk Rock", "R&B", "Rap", "Rave", 
					"Reggae", "Retro", "Revival", "Rhythmic Soul", "Rock", "Rock & Roll", "Samba", "Satire", 
					"Showtunes", "Ska", "Slow Jam", "Slow Rock", "Sonata", "Soul", "Sound", "Clip", "Soundtrack", 
					"Southern Rock", "Space", "Speech", "Swing", "Symphonic Rock", "Symphony", "Tango", "Techno", 
					"Techno-Industrial", "Top 40", "Trailer", "Trance", "Tribal", "Trip-Hop", "Vocal");
	
	$tmptracks = 1;
	/* OK, first let us create a great accumulation of tag and file info:
	 * My temporary array should be:
	 * filename - tracknumber - title - album - artist - total tracks - year - Genre
	 */
	$file_array = array();
	$tmparray = array();
	
    /* Now we will check for three things at once what we don't know we'll just give it a shot
	 * These tags are the same for all, why wasting precious pocessor time?
	 */
	$tagInfo = array();
	$tagInfo = GetAllMP3Info(current($songs));
	$year = date("Y");
	$genre = "Unknown";
	$field = 0;
	$title;
	$album;
	$artist;
	$totaltracks;
		
	if ($tagInfo['exist']) {
	  // The total number of tracks:
	  if ($tagInfo['tags']['id3v2']['totaltracks'] != null ) {
		  $totaltracks = $tagInfo['tags']['id3v2']['totaltracks'];
	  }
	  else {
	    $totaltracks = count($songs)-1;
	  }
    }
	$tmparray['totaltracks'] = $totaltracks;
	
	// Start the table
	echo '<form name="adjustments" action="left.php?p=Submit" target="left" method=post><table>';
	
	foreach ($songs as $song) {
	  // The ID3 headers:
	  $tagInfo = array();
	  $tagInfo = GetAllMP3Info($song);
	  $tmparray['filename'] = $song;
      /*************************************************************************************************
	   * First the track numbers. First check if the filename has been numbered
	   */
	  $file = substr(strrchr($song, '/'), 1);
      //now cut them in two, just to see if they are numbered by filename:
      $firsthalf = trim(substr($file, 0, strpos($file, '-')));
      $secondhalf = trim(strstr(strrchr($file, "-"), " "));
      $secondhalf = trim(substr($secondhalf, 0, strpos($secondhalf, ".mp3")));
	  
	  $tmparray['track'] =  $tmptracks++;  
      if (ereg("[0-9]+", $firsthalf)){ 
	    // OK this looks like numbering. Let's take a gamble at it.
	    $tmparray['track'] =  $firsthalf;
	  }
      else {
        // Hmm... that didn't work. Let's try the ID3 headers:
		if ($tagInfo['exist']) {
          if ($tagInfo['tags']['id3v2']['track'] != null ) {
		    $tmparray['track'] = $tagInfo['tags']['id3v2']['track'];
		  }
		  else if ($tagInfo['tags']['id3v1']['track'] != null) {
		    $tmparray['track'] = $tagInfo['tags']['id3v1']['track'];
		  }
		}
      } 
	  
	  
	
	  /**************************************************************************************************
	   * OK track numbers have been detected. Now on to the title
	   * We trust the id3v2 tag to be te most trustworthy source:
	   */
	  $tmparray['title'] = ucwords(addslashes($secondhalf));
	  if ($tagInfo['exist']) {
        if (eregi("[a-z0-9]+", $tagInfo['tags']['id3v2']['title'])) {
		  $tmparray['title'] = addslashes(ucwords($tagInfo['tags']['id3v2']['title']));
		}
		else if (eregi("[a-z0-9]+", $tagInfo['tags']['id3v1']['title'])) {
		  $tmparray['title'] = addslashes(ucwords($tagInfo['tags']['id3v1']['title']));
		}
	  }
      

	  /****************************************************************************************************
	   * Now we have set the titles. Now, we assume a few things: id3v2 has the album. 
	   * If not, ID3v1 will. If that fails, we use the name of the parent folder.
	   * We trust the id3v2 tag to be te most trustworthy source: So first check the parent folder, as this
	   * will be overwritten if something has been found
	   */
	  $albumdir = array();
	  $albumdir = explode('/', $song);
	  $album = addslashes(ucwords(trim($albumdir[count($albumdir)-2])));
	  $parent = addslashes(ucwords(trim($albumdir[count($albumdir)-3])));
	  $tmparray['album'] = ucwords($album);
	  
	  /* check if parent folder isn't an alphabet folder by any chance. If so, album is not available.
	  if (eregi("[a-z0-9]", $parent)){ 
	    $tmparray['album'] = "n/a";
	  }*/
	  
	  
	  if ($tagInfo['exist']) {
        if (eregi("[a-z0-9]+", $tagInfo['tags']['id3v2']['album'] )) {
	      $tmparray['album'] = addslashes(ucwords($tagInfo['tags']['id3v2']['album']));
	    }
  	    else if (eregi("[a-z0-9]+", $tagInfo['tags']['id3v1']['album'] )) {
	      $tmparray['album'] = addslashes(ucwords($tagInfo['tags']['id3v1']['album']));
	    }
	  }
	   	
	 /*****************************************************************************************************
	  * Now we have set the titles. Now, we assume a few things: id3v2 has the artist. 
	  * If not, ID3v1 will. If that fails, we use the name of the parent's parent folder.
	  * We trust the id3v2 tag to be te most trustworthy source:
	  */
	 $artistdir = array();
	 $artistdir = explode('/', $song);
	 $artist = addslashes(ucwords(trim($albumdir[count($artistdir)-3])));
	 $tmparray['artist'] = ucwords($artist);
	 
	 // check if parent folder isn't an alphabet folder by any chance. If so, artist is actually the entry that
	 /* came up in the last test of album.
	  if (eregi("[a-z0-9]", $artist)){ 
	    $tmparray['artist'] = $album;
	  }*/
	 
	 if ($tagInfo['exist']) {
       if (eregi("[a-z0-9]+", $tagInfo['tags']['id3v2']['artist'] )) {
  	     $tmparray['artist'] =  addslashes(ucwords($tagInfo['tags']['id3v2']['artist']));
	   }
	   else if (eregi("[a-z0-9]+", $tagInfo['tags']['id3v1']['artist'] != null)) {
		 $tmparray['artist'] =  addslashes(ucwords($tagInfo['tags']['id3v1']['artist']));
	   }
	 }
	 
	 /*****************************************************************************************************
	  * We must not forget to adjust the year, genre and total. 
	  */
	if ($tagInfo['exist']) {
        // The year:
        if ($tagInfo['tags']['id3v2']['year'] != null ) {
  	    $tmparray['year'] = $tagInfo['tags']['id3v2']['year'];
        }
        else if ($tagInfo['tags']['id3v1']['year'] != null) {
          $tmparray['year'] = $tagInfo['tags']['id3v1']['year'];
	    }
	    else {
	      $tmparray['year'] = date("Y");
	    }
	  
  	  // The genre:
  	    if ($tagInfo['tags']['id3v2']['genre'] != null ) {
	      $tmparray['genre'] = $tagInfo['tags']['id3v2']['genre'];
	    }
	    else if ($tagInfo['tags']['id3v1']['genre'] != null) {
	      $tmparray['genre'] = $tagInfo['tags']['id3v1']['genre'];
	    }
	    else {
 	      $tmparray['genre'] = "Unknown";
	    }
	  
	  // The total number of tracks:
	  if ($tagInfo['tags']['id3v2']['totaltracks'] != null ) {
		  $tmparray['totaltracks'] = $tagInfo['tags']['id3v2']['totaltracks'];
	  }
    }
	 /*****************************************************************************************************
	  * Let's put thist in the table:
	  * Because $_POST is a horror when having multiple files, I will use numeric names for each field,
	  * and treat it as an circular array. When adding fields which need to be handled please adjust this
	  * AND setFormattedFiles() ! Else you break it. Terribly I might add..
	  * field convention is as follows: 
	  * %8 = 0 => file location field
	  * %8 = 1 => track field
	  * %8 = 2 => total tracks    -- This field is available, but not yet implemented.
	  * %8 = 3 => title field 
	  * %8 = 4 => album field
	  * %8 = 5 => artist field
	  * %8 = 6 => year field
	  * %8 = 7 => genre field
	  */
  	  if ($tmparray['title'] != "") {
	    echo '<tr>
				<td>
					<input type="hidden" name="' . $field++ . '" value="' . $tmparray['filename'] . '"/>
					<input type="text" size="3" name="' . $field++ . '" value="' . $tmparray['track'] . '"/> /
					<input type="text" size="3" name="' . $field++ . '" value="' . $tmparray['totaltracks'] . '"/>
				</td>
				<td>
					<input type="text" size="20" name="' . $field++ . '" value="' . $tmparray['title'] . '"/>
				</td>
			</tr>
			<tr>
				<td>
					<input type="text" size="20" name="' . $field++ . '" value="' . $tmparray['album'] . '"/>
				</td>
				<td>
					<input type="text" size="20" name="' . $field++ . '" value="' . $tmparray['artist'] . '"/>
				</td>
			</tr>
			<tr>
				<td>
					<select name="' . $field++ . '">'; 
		
		for ($i = 1800 ; $i < date("Y"); $i++) {
		  if ($tmparray['year'] == $i){
		    echo ' <option value="' . $i . '" selected>' . $i . '</option>';
          }
		  else {
		    echo ' <option value="' . $i . '">' . $i . '</option> ' ;
		  }
		}
		
		echo '</select></td><td><select name="' . $field++ . '">';
		echo '<option value="' . $tmparray['genre'] . '" selected>' . $tmparray['genre'] . '</option';
		foreach ($genres as $genre){
   	      echo '<option value="' . $genre . '">' . $genre . '</option>';
		}
		
		echo '		</select>
				</td>
		     </tr>
			 <tr>
			 	<td><br></td>
			</tr>';
	  }
	  $track++;
	}
	echo '<tr/><tr>
            <td>Adjust: </td>
		  </tr>
		  <tr>
		    <td><input type="checkbox" name="alterfiles"/> ID3 Tags</input></td>
			<td><input type="checkbox" name="alterpath"/> Filenames</input></td>
		 </tr></table><input type="submit" value="Add Files" class="btn"></form>';
	
	echo "Debug information: <br><br>";
	echo "None Set";
	//print_r($genres);	
	//print_r($filearray);
  }
  
  /* This function does the actual mangling and database stuff. 
   * It seems to have trouble with albums. Up to now I haven't been able to reproduce the 
   * error and pinpoint what is happening. Maybe soon something comes up.
   * The script must have some more versatility (TODO):
   *  - when there is no album as parent folder it should be detected (maybe path in config?)
   *  - Some more error handling/debug information would be extremely handy.
   *  - It may be very interesting to make a new function of the actual database insert. We now
   *    have two nearly equal constructions, one in this function and one in insertSongs().
   */
  function setFormattedFiles($files) {
    $goodData = array();
	$song;
	
	for ($i = 0 ; $i < count($files) - 2 ; $i++) {
	  $flag = 1;
  
	  switch ($i%8) {
	    case 0:  // file location field	
		  $song = $files[$i];
		  $flag = 1;
		  break;
		
		case 1:  // %8 = 1 =>  track field
		  $goodData["track"] = $files[$i];
		  $flag = 1;
		  break;
		
		case 2:  // %8 = 2 =>  total tracks  !! NOT IMPLEMENTED YET !!
		  //echo "Total: " . $files[$i] . "<br>";
		  $flag = 1;
		  break;
		
		case 3:  // %8 = 3 =>  title field 
		  $goodData["name"] = addslashes($files[$i]);
		  $flag = 1;
		  break;
		
		case 4:  // %8 = 4 =>  album field
		  $goodData["album"] = addslashes($files[$i]);
		  $flag = 1;
		  break;
		
		case 5:  // %8 = 5 =>  artist field
		  $goodData["artist"] = addslashes($files[$i]);
		  $flag = 1;
		  break;
		
		case 6:  // %8 = 6 =>  year field
		  $goodData["year"] = $files[$i];
		  $flag = 1;
		  break;
		
		case 7:  // %8 = 7 =>  genre field
		  $goodData["genre"] = addslashes($files[$i]);
		  $flag = 0;
		  break;
		
		default:
		  die("Something went terribly wrong. Looks like a very old Pentium!");
      }
	  if ($flag != 1){
	    $tagInfo = array();
        $tagInfo = GetAllMP3Info($song);
	    
	    $goodData["length"] = $tagInfo['playtime_seconds'];
        $goodData["size"] = $tagInfo['filesize'];
        $goodData["bitrate"] = ($tagInfo['bitrate']/1000);
        $goodData["comment"] = "none";
        $goodData["signature"] = md5($song);
	    
	    /*************************************************************************
	     * OK all data has been collected for this song. Let's insert this in the 
	     * database!
		 * If this has to work well, we first have to look up the right album number
		 * if it exists, else we try and find the highest album no, and increment 
		 * that with one.
		 */
		$query = 'select album_num from mp3act_music where album = "' . $goodData['album'] . '" and artist = "' . $goodData['artist'] . '" LIMIT 1'; 
		$result = mysql_query($query);
        $result  = mysql_fetch_array($result);
		if ($result == null) {
		  $query = "SELECT MAX(album_num)as count FROM mp3act_music";
          $result = mysql_query($query);
          $row = mysql_fetch_array($result);
          $num_albums = $row['count']+1;// get max and increasetofiles;
		}
		else {
		  $album_num = $result;
		}
		
		/**************************************************************************
		 * If the user has requested it, the headers of the song itself must also
		 * be altered.
		 */
		if ($files['alterfiles']) {
		  $this->alterFile($song, $goodData);
		}
		
		/**************************************************************************
		 * If the user has requested it, the filename of the song itself must also
		 * be altered.
		 */
		if ($files['alterpath']) {
		  // First generate the filename:
		  $filename = $goodData['track'] . " - " . $goodData['name'] . ".mp3";
		  $path = substr($song, 0, strrpos($song, '/')) . "/";
		  
		  $result = $this->alterPath($song, $path . $filename);
		  
		  if ($result) {
		    $song = $path . $filename;
		  }
		}
		
		$query = "SELECT * FROM mp3act_music WHERE filename=\"".$song."\"";
        if(mysql_num_rows(mysql_query($query)) == 0){
	      $query = "INSERT INTO mp3act_music  (id,artist,album,album_num,track,name,genre,length,size,bitrate,year,date_entered,date_lastplay,filename,numplays,signature,comment,random) ";
          $query.="VALUES (NULL,\"$goodData[artist]\",\"$goodData[album]\",$num_albums,$goodData[track],\"$goodData[name]\",";
          $query.="\"$goodData[genre]\",\"$goodData[length]\",$goodData[size],$goodData[bitrate],$goodData[year],";
          $query.="NOW(),\"0000-00-00 00:00:00\",\"$song\",0,\"$goodData[signature]\",\"$goodData[comment]\",0)";
	
	      connect();
	      if(mysql_query($query)){
             echo "Added " . $goodData['name'] . " by " . $goodData['artist'] . ".<br>\n";
             if($this->displayResults ==1){
             }
	      }
	      $this->updateStats();
          $this->updateGenres();
	    }
	    else{
          echo "Already exists: " . $goodData['name'] . " by " . $goodData['artist'] . " - Determined by path.<br>";
        }
	  } 
    }
  }
  
  /*
   * This function requires the files to be either owned by the apache user, or better, be writeable by the group.
   * on my gentoo systems, I have added apache to the group audio. I think this is safest to do - no root access,
   * but still access to the sound related stuff. 
   * In bash terms: chown -R root:audio $pathToSearch && chmod -R 775 $$pathToSearch
   */
  function alterFile($file, $data) {
	echo 'starting to write tag to ' . $file . "<br>";
	$data['tags']['id3v2']['TIT2']['encodingid'] = 0;
	$data['tags']['id3v2']['TPE1']['encodingid'] = 0;
	$data['tags']['id3v2']['TALB']['encodingid'] = 0;
	$data['tags']['id3v2']['TYER']['encodingid'] = 0;
	$data['tags']['id3v2']['TRCK']['encodingid'] = 0;
	$data['tags']['id3v2']['TCON']['encodingid'] = 0;
	//$data['tags']['id3v2']['COMM'][0]['encodingid'] = 0;
	//$data['tags']['id3v2']['COMM'][0]['language'] = 'eng';
	$data['tags']['id3v2']['TIT2']['data'] = $data['name'];
	$data['tags']['id3v2']['TPE1']['data'] = $data['artist'];
	$data['tags']['id3v2']['TALB']['data'] = $data['album'];
	$data['tags']['id3v2']['TYER']['data'] = $data['year'];
	$data['tags']['id3v2']['TRCK']['data'] = $data['track'];
	$data['tags']['id3v2']['TCON']['data'] = '(' . $data['genre'] . ')';
	//$data['tags']['id3v2']['COMM'][0]['data'] = $data['comment'];
    //$filename, $title='', $artist='', $album='', $year='', $comment='', $genre=255, $track='', $showerrors=FALSE
	
	// The  COMM fields generated errors. Commenting them out dit not render any trouble while altering the file, but
	// still - we need to check what is going on here..
	
	echo 'writing ID3v1 changes...';
	$result = WriteID3v1($file, $data['name'], $data['artist'], $data['album'], $data['year'], $data['comment'], $data['genre'], $data['track'], TRUE);
	if ($result) {
      echo "success<br>";
	}
	else {
	  echo "FAILED";
	}
	echo 'writing ID3v2 changes...';
	$result = WriteID3v2($file, $data, 3, 0, TRUE, 0, TRUE);
	if ($result) {
		echo "success<br>";
	}
	else {
	  echo "FAILED";
	}
  }
  
  /************************************************************************************************
   * This function requires the files to be either owned by the apache user, or better, 
   * be writeable by the group. On my gentoo systems, I have added apache to the group audio. 
   * I think this is safest to do - no root access, but still access to the sound related stuff. 
   * In bash terms: chown -R root:audio $pathToSearch && chmod -R 775 $$pathToSearch
   ************************************************************************************************/
  function alterPath($source, $dest) {
    $result = rename($source, $dest);
    if ($result) {
      echo "Rename Successful <br>";
      return true;
    }
    else {
      die("Something went wrong. We'll have to abort.<br>");
    }
    return false;
  }

} #End addMusic Class

?>
