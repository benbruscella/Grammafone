<?php
/******************************************************************************
 *
 *  Grammafone Digital Music System - For your digital music collection
 *  http://www.grammafone.com
 *  Copyright (C) 2008 Grammafone.com
 *  Title: Media Uploader - sync.php 
 *  Author: Niroshan Rajadurai
 *  Description: Utilities for uploading files to the music serve from java 
 *  client
 *
 *****************************************************************************/

/******************************************************************************
 *  Includes
 *****************************************************************************/

include_once("base.inc.php"); 


/******************************************************************************
 *  Constants
 *****************************************************************************/

//Return Codes
$SUCCESS_CODE_PASS = 1000;


//Error Codes
$ERROR_CODE_AUTHENTICATION_FAILED = 1001;
$ERROR_CODE_PERMISSION_DENIED = 1002;
$ERROR_CODE_UPLOAD_FAILED = 1003;
$ERROR_CODE_INVALID_MODE = 1004;
$ERROR_CODE_DUPLICATE_FILE = 1005;

/******************************************************************************
 *  Globally Used Variables 
 *****************************************************************************/

$_debug =  false;

//$audioFileName = $_POST['audioFile'];
$modeRequired = $_POST['mode'];
$username = $_POST['username'];
$password = $_POST['password'];
$ipAddress = $_SERVER['REMOTE_ADDR'];
$user_id = 0;
$accessLevel = 0; 


/******************************************************************************
 *  Function Definitions 
 *****************************************************************************/


function logTheUserIn($requiredUsername, $requiredPassword)
{

	$query = "SELECT * FROM grammafone_users 
		WHERE username='$requiredUsername' AND 
		password=PASSWORD('$requiredPassword') AND active=1 LIMIT 1";

	$result = mysql_query($query);

	if(mysql_num_rows($result) > 0)
	{
		$userinfo = mysql_fetch_array($result);

		$_SESSION['sess_userid'] = $userinfo['user_id'];
		$_SESSION['sess_accesslevel'] = $userinfo['accesslevel'];
		if ($_SESSION['sess_accesslevel'] == 10)
		{
			return true;
		}		
	}

	return false;

}

function isTheFileAlreadyUploaded($fileNameOnly)
{
	$target_path = $GLOBALS['upload_path'];
        $query = "SELECT * FROM grammafone_songs WHERE filename=\"" . $GLOBALS['upload_path'] . $fileNameOnly . "\"";
	if(mysql_num_rows(mysql_query($query)) == 0){
		return false; /* does not exist */
	}
	return true;
}

function processFileUpload()
{
	$target_path = $GLOBALS['upload_path'];

	$target_path = $target_path . basename( $_FILES['audioFile']['name']); 

	if(move_uploaded_file($_FILES['audioFile']['tmp_name'], $target_path)) 
	{
		$addMusic = new addMusic;
		if ($_POST['upload_structured'] == "true") // move the file to a location <upload_path>\<artist_name>\<album_name>\file_name.mp3 
		{
			$addMusic->getID3Data($target_path, $goodData);
			$final_path = $GLOBALS['upload_path'] . $goodData['artist'] . "/" . $goodData['album'];
			// need some logic to handle illegal filesystem characters before you do this.
			//$final_file_name = $goodData['artist'] . "-" . $goodData['album'] . "-" . $goodData['track'] . "-" . $goodData['name'];
			// for now just keep the filename the same
			$final_file_name = basename( $_FILES['audioFile']['name']);
			$final_path_file_name = $final_path . "/" . $final_file_name;

			mkdir($final_path, 0777, true);
			rename($target_path,  $final_path_file_name);

			$addMusic->setPath($final_path);	
			$addMusic->setFileList($final_path, "/" . $final_file_name, $songs); 
		}	
		else // move the uploaded file to <upload_path>
		{
			$addMusic->setPath($GLOBALS['upload_path']);	
			$addMusic->setFileList($GLOBALS['upload_path'], basename($_FILES['audioFile']['name']), $songs); 
			// removed as this tries to update every thing in the directory.
			//$addMusic->getSongs($GLOBALS['upload_path'],$songs);
		}	
		$songsAdded = $addMusic->insertSongs();
		if ($songsAdded > 0)
		{
			return true;
		}
	} 
	return false;
}



/******************************************************************************
 *  Main 
 *****************************************************************************/

if ($_debug == true)
{
	echo "User Name = $username\n";
	echo "Password = $password\n";
	echo "Client IP Address = $ipaddress\n";
	echo "Mode Required = $modeRequired\n";
	echo "Upload Path => "; echo $GLOBALS['upload_path']; echo "\n";
}

grammafone_connect();

if (logTheUserIn($username, $password) == true)
{
	switch($modeRequired) 
	{
	case "IS_IT_A_DUPLICATE":
		if (isTheFileAlreadyUploaded(basename( $_POST['audioFileName'])) == true) {
			echo "true";
		}	
		else {
			echo "false";
		}
		break;
	case "FILE_UPLOAD":
		if (isTheFileAlreadyUploaded(basename( $_FILES['audioFile']['name'])) == false)
		{
			
			if (processFileUpload() == true)
			{
				echo $SUCCESS_CODE_PASS;
			}
			else
			{
				echo $ERROR_CODE_UPLOAD_FAILED;
			}
		}
		else
		{
			echo $ERROR_CODE_DUPLICATE_FILE;
		}
		break;
	default:
		echo $ERROR_CODE_INVALID_MODE;
		break;
	}
}
else
{
	echo $ERROR_CODE_AUTHENTICATION_FAILED;
}

?>

