<?php
include_once("includes/archive.php");
include_once("includes/mp3act_functions.php"); 
/*
Why Don't Sesssions work here???

include_once("includes/sessions.php");

 if(!isLoggedIn()){
  header("Location: login.php?notLoggedIn=1");
}*/
if(isset($_GET['d'])){
	download($_GET['id']);
	exit;
}
?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>
<title><? echo $GLOBALS['server_title']; ?> | Download Music</title>
<link rel="Stylesheet" href="css/mp3act_css.php" type="text/css" />
<meta http-equiv="refresh" content="5; url=download.php?d=1&id=<?php echo $_GET['id']; ?>">

</head>
<body>

<div id="wrap">
	<div id="header">
		<h1>mp3act Download Album</h1>
		
	</div>
	<p class='pad'>
	Your Download Should Begin Soon.  If it does not automatically begin click the link below:<br/><br/>
	<strong><a href="download.php?d=1&id=<?php echo $_GET['id']; ?>" title="Start the Download">Start the Download</a></strong>
 	</p>

</div>
<br/>
<a href="#" onclick="window.close()" title="Close The Download Window">Close Window</a>
</body>
</html>