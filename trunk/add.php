<?php
/******************************************************************************
 *
 *  Grammafone Digital Music System - For your digital music collection
 *  http://www.grammafone.com
 *  Copyright (C) 2008 Grammafone.com
 *  Title: Media Uploader - add.php 
 *  Author: Niroshan Rajadurai
 *  Description: Utilities for uploading files to the music server, and then
 *  syncing this data with the database.
 *  The Utility should support:
 *  - Upload of MP3 file 
 *  - Upload of image file (jpg, gif, bmp, png)
 *  - Upload of tar, tar.gz or zip file containing
 *  The program should be able to work out the type of file uploaded and sync
 *  it to the right area of the database.
 *  Notes:
 *  1. make sure the max site set in here matched the mas upload file size in 
 *     php.ini
 *  2. TBD
 *     a. extract tar, tar.gz or zip file
 *     b. smart storing of data so that we don't have to rescan the upload area 
 *        for the file
 *     c. automatically updating the database based on the type of file uploaded
 *
 *****************************************************************************/

include_once("base.inc.php"); 

if(!isLoggedIn()){
	header("Location: login.php?notLoggedIn=1");
}
//set_time_limit(30000);

?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>
<title><?=$GLOBALS['server_title']; ?> | Add Music</title>
<link rel="Stylesheet" href="css/grammafone.css" type="text/css" />
<?php if(isset($_GET['add']) && $_GET['add'] == 1 && isset($_GET['musicpath'])){ ?>
<meta http-equiv="refresh" content="2; url=add.php?musicpath=<?php echo $_GET['musicpath']; ?>">
<?php } ?>
<script type="text/javascript">

function validator()
{
	var x = document.getElementById('musicpath').value;
	if(x == "" || x == "/path/to/music"){
		document.getElementById("error").innerHTML = "The path you entered is invalid";

		return false;
	}
}

function progress(){
	document.getElementById("error").innerHTML = "<img src='img/progress_bar.gif' /><br/>File is uploading.  Please Wait.";
}
</script>

</head>
<body>

<div id="wrap">
    <div id="header">
	<h1>Gramophone.FM Add Music</h1>
	Enter a music directory on the server or upload a file.
    </div>
    <p id="error" class='pad'></p>
<?php
if(isset($_GET['add']) && $_GET['add']==1 && isset($_GET['musicpath'])){
	echo "<p class='pad'>"; 
	echo "<img src='img/progress_bar.gif' /><br/><br/>New Music is being added.  This could take several minutes.<br/>";
	echo "</p>";

}
elseif(isset($_GET['musicpath']) && is_dir($_GET['musicpath']) ){

	echo "<p class='pad'>";

	$path = $_GET['musicpath'];
	if($path{strlen($path)-1} != '/'){
		$path .= "/";
	}
	$addMusic = new addMusic;
	$addMusic->setPath($path);
	$addMusic->getSongs($path,$songs);
	$songsAdded = $addMusic->insertSongs();
	//$songsAdded = $addMusic->insertSongs($addMusic->getSongs($path,&$songs));

	echo "<br/>Added <strong>$songsAdded Songs</strong> To The Database";
	echo "</p>";
}
elseif(isset($_FILES['musicfile']['name'])){
	/* Copy the file to the repository */
	echo "<pre>";
	print_r($_FILES);
	echo "</pre>";
	// echo $_FILES['musicfile']['tmp_name'];
	// echo "<br/>".$_FILES['musicfile']['name'];
	if (move_uploaded_file($_FILES['musicfile']['tmp_name'], $GLOBALS[upload_path]."/".$_FILES['musicfile']['name'])) {
		echo "<br>The File has been uploaded succesfully.";

		/* Add the file to the database */
		echo "<form action='add.php' method='get' onsubmit='return validator()'>\n";
		echo "<p class='pad'><input type='hidden' onfocus='this.select()' name='musicpath' id='musicpath' size='45' id='musicpath' value='";
		echo $GLOBALS[upload_path];
		echo "' /><br/><br/>";
		echo "<input type='submit' value='Update Database' class='btn' /><br/><br/>Be Patient While Music is Added. It could take several minutes.</p>";
		echo "</form>";
	}
	else {
		echo "<br> The was an error uploading the file, the maximum file size permited is 20MB. <br>";
	}
}
elseif(isset($_POST['musicfile'])){
	echo "<br> Uploading File <br>";
}
else{
	echo "<form action='add.php' method='get' onsubmit='return validator()'>\n";
	echo "<p class='pad'><input type='hidden' value=1 name='add' /><input type='text' onfocus='this.select()' name='musicpath' id='musicpath' size='45' id='musicpath' value='/path/to/music' /><br/><br/>";
	echo "<input type='submit' value='add music' class='btn' /><br/><br/>Be Patient While Music is Added. It could take several minutes.</p>";
	echo "</form>";
	/* UPLOADING */
	if(isset($GLOBALS[upload_path]) && is_dir($GLOBALS[upload_path]) ){
		echo "<form name='upload' enctype='multipart/form-data' method='post' action='add.php' onsubmit='return progress()'>";
		echo "<input type='hidden' name='MAX_FILE_SIZE' value='200000000' />";
		echo "<p class='pad'><strong>Upload a File (.mp3 .zip .tar)</strong><br/>\n";
		echo "<input name='musicfile' type='file' id='musicfile' size='35' /><br/><br/>\n";
		echo "<input type='submit' value='upload file' class='btn' /></p>";
		echo "</form>";
	}

}

?>  

</div>
</body>
</html>
