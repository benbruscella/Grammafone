<?php
include_once("base.inc.php");
set_time_limit(0);

function stream($id, $rate=0, $stereo="s",$user='',$ip=''){
	grammafone_connect();
	// check to see if IP is in the verified IP DB for that user
	if(verifyIP($user,$ip)){
		//$query="SELECT filename,size,length FROM grammafone_songs WHERE song_id=$id";
		
		$query = "SELECT grammafone_artists.artist_name, 
		grammafone_songs.name,
		grammafone_songs.bitrate, 
		grammafone_songs.length as length, 
		grammafone_songs.filename as filename, 
		grammafone_songs.size as size 
		FROM grammafone_songs,grammafone_artists 
		WHERE grammafone_songs.song_id=$id 
		AND grammafone_artists.artist_id=grammafone_songs.artist_id";

		$result=mysql_query($query);
		$row = mysql_fetch_array($result);
		fb($row);
		updateNumPlays($id,0,$user,'player');
		clearstatcache(); // flush buffer
		
  
		$file['name'] = basename($row['filename']);
		$mp3out = '';
		if(getSystemSetting("lamebin") != "" && $rate != 0){
			$row['size'] = (($row['length'] + 1) * $rate * 1000)/8;
			$mp3out = getSystemSetting("lamebin")." -b $rate -s $stereo --silent --nores --mp3input -h \"".addslashes($row['filename'])."\" -";
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
		header('X-Pad: avoid browser bug');
		header('Cache-Control: no-cache');		
		session_write_close();		
		// Run the command, and read back the results at the bitrate size + 1K.
		$blocksize=($row['bitrate']*1024)+1024;
		$totaldata=0;
		if($rate!=0 && $mode==1){
			$temp = @popen($mp3out, "r");
			while (($data = @fread($temp, $blocksize )) && ($totaldata <= $size ) ) {
				echo $data; $totaldata+=$blocksize; 
			}
			pclose($temp);
		}
		elseif($rate!=0 ){
			$temp = @popen($mp3out, "r");
			while ($data = @fread($temp, $blocksize) ) {
				echo $data; 
			}
			pclose($temp);
		}
		elseif($mode==1 ){
			$temp = @fopen($mp3out, "r");
			while (!feof($temp)  && ($totaldata <= $size ) ) {
				$data = @fread($temp, $blocksize); echo $data; $totaldata+=$blocksize; 
			}
			fclose($temp);
		}
		else {
			$temp = @fopen($mp3out, "r");
			while (!feof($temp) ) {
				$data = @fread($temp, $blocksize); echo $data; 
			}
			fclose($temp);
		}
	} // end IF for verify IP
	exit;
}

fb("Now Playing: i=" . $_GET['i'] . ", b=" . $_GET['b'] . ", s=" . $_GET['s'] . ", u=" . $_GET['u'] . " on: " . $_SERVER['REMOTE_ADDR']);

// Play Now
stream($_GET['i'],$_GET['b'],$_GET['s'],$_GET['u'],$_SERVER['REMOTE_ADDR'])	

?>
