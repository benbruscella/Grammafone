<?php
/**
* mp3act AudioScrobbler Plugin Class
* based on audioPod PHP script (http://projects.afterglo.ws/wiki/AudioPodHome)
*/
 

class track {
	var $trackID;
	var $trackLen;
	var $artistName;
	var $albumName;
	var $trackName;
	var $playCount;
	var $playTime;
}



class scrobbler {
	var $errorMsg;
	var $username;
	var $password;
	var $challenge;
	var $submitHost;
	var $submitPort;
	var $submitURL;
	var $queuedTracks;

	function scrobbler($username, $password) {
		$this->errorMsg = '';
		$this->username = $username;
		$this->password = $password;
		$this->challenge = '';
		$this->queuedTracks = array();
	}

  function getErrorMsg(){
    return $this->errorMsg;
  }
  function getQueueCount(){
    return count($this->queuedTracks);
  }
	function handshake() {
		$asSocket = @fsockopen('post.audioscrobbler.com', 80, $errno, $errstr, 10);
		if(!$asSocket) {
			$this->errorMsg = $errstr;
			return FALSE;
		}

		$username = rawurlencode($this->username);
		fwrite($asSocket, "GET /?hs=true&p=1.1&c=m3a&v=0.1&u=".$username." HTTP/1.1\r\n");
		fwrite($asSocket, "Host: post.audioscrobbler.com\r\n");
		fwrite($asSocket, "Accept: */*\r\n\r\n");

		$buffer = '';
		while(!feof($asSocket)) {
			$buffer .= fread($asSocket, 8192);
		}
		fclose($asSocket);

		$splitResponse = preg_split("/\r\n\r\n/", $buffer);
		if(!isset($splitResponse[1])) {
			$this->errorMsg = 'Did not receive a valid response';
			return FALSE;
		}
		$response = explode("\n", $splitResponse[1]);

		if(substr($response[0], 0, 6) == 'FAILED') {
			$this->errorMsg = substr($response[0], 7);
			return FALSE;
		}
		if(substr($response[0], 0, 7) == 'BADUSER') {
			$this->errorMsg = 'Invalid Username';
			return FALSE;
		}
		if(substr($response[0], 0, 6) == 'UPDATE') {
			$this->errorMsg = 'You need to update your client: '.substr($response[0], 7);
			return FALSE;
		}

		if(preg_match('/http:\/\/(.*):(\d+)(.*)/', $response[2], $matches)) {
			$this->submitHost = $matches[1];
			$this->submitPort = $matches[2];
			$this->submitURL = $matches[3];
		} else {
			$this->errorMsg = 'Invalid POST URL returned, unable to continue';
			return FALSE;
		}

		$this->challenge = $response[1];
		return TRUE;
	}

	function queueTrack($artist, $album, $track, $timestamp, $length) {
		$date = gmdate('Y-m-d H:i:s', $timestamp);
		$mydate = date('Y-m-d H:i:s T', $timestamp);

		if($length < 30) {
			//printf("Skipping: %-25.25s  %-25.25s  %-25.25s  (%-4.4d secs), too short\n", $artist, $album, $track, $length);
			return FALSE;
		} else {
			//printf("Queueing: %-25.25s  %-25.25s  %-25.25s  (%-4.4d secs)\n", $artist, $album, $track, $length);
			//printf("          %23.23s (%23.23s)\n", $date." UTC", $mydate);
		}

		$newtrack = array();
		$newtrack['artist'] = $artist;
		$newtrack['album'] = $album;
		$newtrack['track'] = $track;
		$newtrack['length'] = $length;
		$newtrack['time'] = $date;

		$this->queuedTracks[$timestamp] = $newtrack;
		return TRUE;
	}

	function submitTracks() {
		if(count($this->queuedTracks) == 0) {
			$this->errorMsg = "No tracks to submit\n";
			return FALSE;
		}

		ksort($this->queuedTracks); //sort array by timestamp

		$queryStr = 'u='.rawurlencode($this->username).'&s='.rawurlencode(md5( md5($this->password).$this->challenge)).'&';
		$i = 0;
		foreach($this->queuedTracks as $track) {
			$queryStr .= "a[$i]=".rawurlencode($track['artist'])."&t[$i]=".rawurlencode($track['track'])."&b[$i]=".rawurlencode($track['album'])."&";
			$queryStr .= "m[$i]=&l[$i]=".rawurlencode($track['length'])."&i[$i]=".rawurlencode($track['time'])."&";
			$i++;
		}
		$asSocket = @fsockopen($this->submitHost, $this->submitPort, $errno, $errstr, 10);
		if(!$asSocket) {
			$this->errorMsg = $errstr;
			return FALSE;
		}

		$action = "POST ".$this->submitURL." HTTP/1.0\r\n";
		fwrite($asSocket, $action);
		fwrite($asSocket, "Host: ".$this->submitHost."\r\n");
		fwrite($asSocket, "Accept: */*\r\n");
		fwrite($asSocket, "Content-type: application/x-www-form-urlencoded\r\n");
		fwrite($asSocket, "Content-length: ".strlen($queryStr)."\r\n\r\n");

		fwrite($asSocket, $queryStr."\r\n\r\n");

		$buffer = '';
		while(!feof($asSocket)) {
			$buffer .= fread($asSocket, 8192);
		}
		fclose($asSocket);

		$splitResponse = preg_split("/\r\n\r\n/", $buffer);
		if(!isset($splitResponse[1])) {
			$this->errorMsg = 'Did not receive a valid response';
			return FALSE;
		}
		$response = explode("\n", $splitResponse[1]);

		if(!isset($response[0])) {
			$this->errorMsg = 'Unknown error submitting tracks'.
					  "\nDebug output:\n".$buffer;
			return FALSE;
		}
		if(substr($response[0], 0, 6) == 'FAILED') {
			$this->errorMsg = substr($response[6], 7);
			return FALSE;
		}
		if(substr($response[0], 0, 7) == 'BADAUTH') {
			$this->errorMsg = 'Invalid username/password';
			return FALSE;
		}
		if(substr($response[0], 0, 2) != 'OK') {
			$this->errorMsg = 'Unknown error submitting tracks'.
					  "\nDebug output:\n".$buffer;
			return FALSE;
		}

		return TRUE;
	}

}


?>
