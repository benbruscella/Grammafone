<?php
include_once ("includes/mp3act_functions.php");

include("includes/addMusicClass.php");

function fileInDb($file)
{
	mp3act_connect();
	
	$current_access_time = filemtime($file);
	
	$file = addslashes($file);
	
	$query = "SELECT last_modified FROM mp3act_songs WHERE filename='$file'";
	
	$mysql_result = mysql_query($query);
	if (mysql_num_rows($mysql_result) == 0) {
		/* the song is not in the database */
		$ret['in_db'] = false;
		$ret['modified'] = false;
	}
	else
	{
		/* find the access time for the song and compare to current access time */
		$ret['in_db'] = true;

		$row = mysql_fetch_array($mysql_result, MYSQL_ASSOC);
		$previous_access_time = $row["last_modified"];
		mysql_free_result($mysql_result);
		
		if ($current_access_time != $previous_access_time) {
			$ret['modified'] = true;
		}
		else {
			$ret['modified'] = false;
		}
	}
	
	return $ret;
}

function getFileList($path)
{
    if($path{strlen($path)-1} != '/'){
		$path .= "/";
	}

	$path_sl = addslashes($path);

	$files = "";

	if (!is_dir($path))
	{
		$files .= "Given path is not a directory, choose another path";
		return $files;
	}

	mp3act_connect();

	$resdir = @ opendir($path);
	
	if ($resdir != false) {
		while (($entry = readdir($resdir)) !== false) {

			if (is_dir($path.$entry)) {
				if (($entry != ".") && ($entry != ".."))
					$directory[] = $entry;

			} else {
				/* filename, only add if it's an mp3 */
				if (strtolower(substr($entry, strlen($entry) - 4, 4)) == ".mp3") {
					$file[] = $entry;
				}
			}
		}

		closedir($resdir);
		
		//array_multisort((strtolower($directory)), SORT_ASC, SORT_STRING, $directory);

		$count = 1; $dircount = 1;
		if (count($directory) > 0) {
			sort ($directory);
			foreach ($directory as $entry) {
				($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');
	
				$files .= "<div class='impcell'>";
				$files .= "<input type='checkbox' name='importdir[]' value='$count' $alt/>";
				$files .= "<img src='img/gray10x10.png'></img>";
				$files .= "<img src='img/gray10x10.png'></img>";
				$files .= "<a href=\"#\" onclick=\"goToDirectory('$path_sl', $dircount); return false;\" title=\"Go to directory $entry\">$entry</a>";
				$files .= "</div>";
				$count++; $dircount++;
			}
		}

		$count = 1; // reset counter for files
		if (count($file) > 0) {
			sort ($file);
			foreach ($file as $entry) {
				($count%2 == 0 ? $alt = "class=\"alt\"" : $alt = '');

				$fileStatus = fileInDb($path.$entry);

				$files .= "<div class='impcell'>";
				$files .= "<input type='checkbox' name='importfile[]' value='$count' $alt/>"; 
				if ($fileStatus['in_db'] == true) { $files .= "<img src='img/check.png' alt='Song is in database'></img>"; } else { $files .= "<img src='img/gray10x10.png'></img>"; }  
				if ($fileStatus['modified'] == true) { $files .= "<img src='img/modified.png' alt='Song has been modified since it was imported'></img>"; } else { $files .= "<img src='img/gray10x10.png'></img>"; }  
				$files .= "$entry";
				$files .= "</div>";
				$count++;
			}
		}
	}
	else
	{
		return "Directory $path not open for reading, permission problem?";
	}
	
	return $files;
}

function goToDirectory($path,$dirnumber)
{
	/* take the "root" import directory as the top level to constrain the user there,
	 * might be useful later on if a more secure setup is needed */
	$prefix = $_SESSION['sess_import_musicdir'];
	$prefixlen = strlen($prefix);

	// we added slashes when we created the link to keep the javascript code valid.. strip them back out
	$path = stripslashes($path);

	if (strcmp(substr( $path, 0, $prefixlen), $prefix) != 0)
	{
		return "There is a problem with the path... path is $path and session dir is $prefix";

		// Something weird. User trying to get out of his little sandbox?
		$path = $prefix;
	}
	
	// the dirnumber is the number in the directory list we sent the user.
	// if the number is 0 that means go to the path itself.
	if ($dirnumber == 0)
	{
		$_SESSION['browsedir'] = $path;
		return getFileList($path);
	}
		
	if (is_dir($path))
	{
		$resdir = @ opendir($path);
		
		if ($resdir != false)
		{
			while (($entry = readdir($resdir)) !== false)
			{
				if (is_dir($path.$entry))
				{
					if (($entry != ".") && ($entry != ".."))
						$directory[] = $entry;
				}
			}
	
			closedir($resdir);

			if (count($directory) > 0) {
				sort ($directory);

				if (($dirnumber-1 >= 0) && ($dirnumber-1 < count($directory)))
				{
					$newpath = $path.$directory[$dirnumber-1];
					$_SESSION['browsedir'] = $newpath;
					return getFileList($newpath);
				}
			}
		}
	}
		
	$_SESSION['browsedir'] = $path;
	return getFileList($path); // failed to go there... stay where we were.
}

function updateHierarchy()
{
	$path = $_SESSION['browsedir'];
	$prefix = $_SESSION['sess_import_musicdir'];
	$prefixlen = strlen($prefix);

	if (substr( $path, 0, $prefixlen) != $prefix)
	{
		// Something weird. Fix it.
		$path = $prefix;
	}

	// take off the trailing slash
	if (substr( $path, -1 ) == '/') $path = substr( $path, 0, -1 );

	// take off the prefix and treat it specially
	$path = substr($path, $prefixlen);

	$path_sl = addslashes($prefix);
	$hierarchy = "<a href=\"#\" onclick=\"goToDirectory('$path_sl', 0); return false;\" title=\"Go to directory $prefix\">$prefix</a>";

	// the path is deeper than just the import path, show the rest
	// note that the path should start with a slash here
	if ((strlen($path) > 0) && ($path{0} == '/')) 
	{
		// process the rest
		$dirs = split('/', substr($path, 1));
		
		$buildingPath = $prefix;
		foreach ($dirs as $entry)
		{
			$buildingPath .= "/".$entry;
			$path_sl = addslashes($buildingPath);
			$hierarchy .= " &#187; <a href=\"#\" onclick=\"goToDirectory('$path_sl', 0); return false;\" title=\"Go to directory $buildingPath\">$entry</a>";
		}
	}
	
	return $hierarchy;
}

function queueImportDirectories($path, $importlist)
{
	// path that's passed into this function doesn't have a trailing slash
    if($path{strlen($path)-1} != '/'){
		$path .= "/";
	}

	/* take the "root" import directory as the top level to constrain the user there,
	 * might be useful later on if a more secure setup is needed */
	$prefix = $_SESSION['sess_import_musicdir'];
	$prefixlen = strlen($prefix);

	if (strcmp(substr( $path, 0, $prefixlen), $prefix) != 0)
	{
		echo "There is a problem with the path... path is $path and session dir is $prefix";

		// Something weird. User trying to get out of his little sandbox?
		$path = $prefix;
	}
	
	
	if (is_dir($path))
	{
		$resdir = @ opendir($path);
		
		if ($resdir != false)
		{
			while (($entry = readdir($resdir)) !== false)
			{
				if (is_dir($path.$entry))
				{
					if (($entry != ".") && ($entry != ".."))
						$directory[] = $entry;
				}
			}
	
			closedir($resdir);

			if (count($directory) > 0) {
				sort ($directory);

				/* mark all directories */
				if (count($importlist)) {
					foreach ($importlist as $dirnumber)
					{
						if (($dirnumber-1 >= 0) && ($dirnumber-1 < count($directory)))
						{
							$newpath = $path.$directory[$dirnumber-1];
							
							$importqueue[] = $newpath;
						}
					}
				}
			}
		}
	}
	
	return $importqueue;
}

function queueImportFiles($path, $importlist)
{
	// path that's passed into this function doesn't have a trailing slash
    if($path{strlen($path)-1} != '/'){
		$path .= "/";
	}

	/* take the "root" import directory as the top level to constrain the user there,
	 * might be useful later on if a more secure setup is needed */
	$prefix = $_SESSION['sess_import_musicdir'];
	$prefixlen = strlen($prefix);

	if (strcmp(substr( $path, 0, $prefixlen), $prefix) != 0)
	{
		echo "There is a problem with the path... path is $path and session dir is $prefix";

		// Something weird. User trying to get out of his little sandbox?
		$path = $prefix;
	}
	
	if (is_dir($path))
	{
		$resdir = @ opendir($path);
		
		if ($resdir != false)
		{
			while (($entry = readdir($resdir)) !== false)
			{
				if (!is_dir($path.$entry))
				{
					/* filename, only add if it's an mp3 */
					if (strtolower(substr($entry, strlen($entry) - 4, 4)) == ".mp3") {
						$file[] = $entry;
					}
				}
			}
	
			closedir($resdir);

			if (count($file) > 0) {
				sort ($file);

				/* mark all files */
				if (count($importlist)) {
					foreach ($importlist as $filenumber)
					{
						if (($filenumber-1 >= 0) && ($filenumber-1 < count($file)))
						{
							$newfile = $path.$file[$filenumber-1];
							$importqueue[] = $newfile;
						}
					}
				}
			}
		}
	}

	return $importqueue;
}

include_once("includes/sessions.php");
require("includes/Sajax.php");
$sajax_remote_uri = 'browse.php';
$sajax_request_type = "POST";
//$sajax_debug_mode = true;

sajax_init();
// list of functions to export
sajax_export("goToDirectory", "updateHierarchy"); 
sajax_handle_client_request(); // serve client instances

//set_time_dlimit(30000);

// This dialog has 3 modes:
// - getpath mode
// - browser mode
// - import mode
//
// in getpath mode we show a dialog requesting the import directory
// in browser mode we allow the user to navigate the filesystem
// in import mode we import the files the user selected
//
// to detect the mode see this code:
 
/* if browse gets called without any parameters whatsoever that means
 * the dialog was just opened, and we need to reset the session variables!!
 */

if (isset($_GET['import']) )
{
	// import form submitted.. get the file/dirnames

	if (isset($_GET['importdir'])) {
		$queuedirs = queueImportDirectories($_SESSION['browsedir'], $_GET['importdir']);
	}

	if (isset($_GET['importfile'])) {
		$queuefiles = queueImportFiles($_SESSION['browsedir'], $_GET['importfile']);
	}

	$dialogmode = "import";
}
else if (isset($_GET['add']) && $_GET['add'] == 1 && isset($_GET['musicpath']))
{
	/* if the 'add' form was submitted, we can start the browser. store the
	 * path that was set in the form!
	 */

	$path = $_GET['musicpath'];
	
	if (substr( $path, -1 ) == '/') $path = substr( $path, 0, -1 );

	if (!is_dir($path))
	{
		$dialogmode = "getpath";
		$wrong_directory = true;
	}
	else
	{
		$_SESSION['sess_import_musicdir'] = $path;
	
		if (isset($_GET['startbrowser']))
		{	
			$dialogmode = "browse";
		}
		else
		{
			// user wants to import not browse
			$dialogmode = "import";
			
			unset($queuedirs); 
			unset($queuefiles); 
			$queuedirs[] = $path;
		}
	}
}
else {
	unset($_SESSION['sess_import_musicdir']);
	unset($_SESSION['musicpath']);
	unset($_SESSION['browsedir']);
	
	$dialogmode = "getpath";
	$wrong_directory = false; // didn't come here because of wrong dir
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="Stylesheet" href="css/mp3act_css.php" type="text/css" />
    <title><?=$GLOBALS['server_title']; ?> | Add Music</title>
<script type="text/javascript">
<?php if ($dialogmode == "getpath") { ?>
function validator()
{
  var x = document.getElementById('musicpath').value;
        if(x == "" || x == "/path/to/music"){
                document.getElementById("error").innerHTML = "The path you entered is invalid";

                return false;
        }
}
<?php
}

// if we're in browser mode, put all of our neat javascript
// detect browser mode by detecting either 'add' form submitted
// (otherwise we're either in getpath mode or import mode)
if($dialogmode == "browse"){ ?>
		function selectAll() {
		   for (var i=0;i< document.importForm.elements.length;i++) {
		      if (    (document.importForm.elements[i].name == 'importdir[]')
		           || (document.importForm.elements[i].name == 'importfile[]') ) {
		         document.importForm.elements[i].checked = true;
		      }
		   }
		}
		
		function resetAll() {
		   for (var i=0;i< document.importForm.elements.length;i++) {
		      if (    (document.importForm.elements[i].name == 'importdir[]')
		           || (document.importForm.elements[i].name == 'importfile[]') ) {
		         document.importForm.elements[i].checked = false;
		      }
		   }
		}
		
		function hierarchyUpdate_cb(hier)
		{
			document.getElementById("hierarchy").innerHTML = hier;
		}
		
		function fileListRetrieved_cb(stat)
		{
			document.getElementById("imptable").innerHTML = stat;
			
			x_updateHierarchy(hierarchyUpdate_cb);
		}

    	function goToDirectory(path, dirnum)
    	{
    		document.getElementById("imptable").innerHTML = "Loading...";
    		x_goToDirectory(path,dirnum,fileListRetrieved_cb);
    	}

		function doFirstFileList()
		{
<?php
        	$path = $_SESSION['sess_import_musicdir'];
	        	
	        if($path{strlen($path)-1} != '/'){
		        $path .= "/";
	        }
	
			echo "var x = \"$path\";\n";
?>
			goToDirectory(x, 0);
			
			return 0;
		}
<?php }?>

	<?php sajax_show_javascript(); ?>

</script>
	<script type="text/javascript" src="includes/fat.js"></script>
	<script src="javascripts/prototype.js" type="text/javascript"></script>
    <script src="javascripts/scriptaculous.js" type="text/javascript"></script>
</head>

<?php
// in case we just entered browse mode, show the file list.
if ($dialogmode == "browse") { ?>
<body onLoad='doFirstFileList()'>
<?php } else { ?>
<body>
<?php }?>

<div id="wrap">
	<div id="header">
<?php if($dialogmode == "browse"){ ?>
        <h1>mp3act Add Music</h1>
        Browse for your music files and select which ones to import.
<?php } else if ($dialogmode == "import"){ ?>
        <h1>mp3act Add Music</h1>
        Importing music, please wait!
<?php } else {?>
        <h1>mp3act Add Music</h1>
        Enter a music directory on the server.
<?php } ?>
	
	</div>
	<p id="error" class='pad'>
<?php if ($wrong_directory == 1) echo "Directory does not exist or not readable, please select another one"; ?>	
	</p>

<?php if ($dialogmode == "getpath")
 { ?>
    <form action='browse.php' method='get' onsubmit='return validator()'>
    <p class='pad'><input type='hidden' value=1 name='add' /><input type='text' onfocus='this.select()' name='musicpath' id='musicpath' size='45' id='musicpath' value='/path/to/music' /><br/><br/>
	<input type='checkbox' name='startbrowser' id='startbrowser' CHECKED/>Browse this path<br/><br/>
    <input type='submit' value='add music' class='btn'" /><br/><br/>Be Patient While Music is Added. It could take several minutes.</p> 
    </form>
<?php } else if ($dialogmode == "browse"){ ?>

	<div id="importbuttons">
		<a href="#" onclick="selectAll();" class="btn">Select all</a>
		<a href="#" onclick="resetAll();" class="btn">Reset all</a>
		<a href="#" onclick="document.importForm.submit();" class="btn">Import selected</a>
	</div>
	 
	<div id="hierarchy"></div>
	
	<div id="filebox">
		<div class="head">
			<h2>File list</h2>
		</div>
		
		<form name="importForm" method="get" action="browse.php">
			<input type='hidden' value=1 name='import' />
			<div id="imptable">
			</div>
		</form>
	</div>

<?php
 	} else {
		// do the import.. 

		set_time_limit(0); // no time limit, this can take a while eh!

		session_write_close(); // otherwise progress isn't shown!

		echo "<div class='box'><div class=\"head\"><h2>Import results</h2></div><ul>";    	

		$addMusic = new addMusic;

		/* start by doing directories. progress per directory. */
		if (count($queuedirs) > 0)
		{
			foreach ($queuedirs as $importdir) {
				echo "<li>Importing directory $importdir</li>";

			    if($path{strlen($importdir)-1} != '/'){
					$importdir .= "/";
				}
	
				$addMusic->setPath($importdir);
				unset($songs);
				$addMusic->getSongs($importdir,$songs);
				$songsAdded = $addMusic->insertSongs();
	
				echo "<li>Added <strong>$songsAdded Songs</strong> To The Database</li>";
			}    	
		}
		
		/* then do files. here we can have one progress bar for all! */
		if (count($queuefiles) > 0)
		{
			echo "<li>Adding separate songs to the database</li>";
			mp3act_connect();
			$last_timestamp = 0;
			$getID3 = new getID3;
			$songsAdded = 0;

			$num_files = count($queuefiles);
			foreach ($queuefiles as $importfile) {
				echo "<li>Importing file $importfile</li>";
				
				/* Set the database status */
				$cur_fileno++;
				
				// update (at most) every second!
				$current_timestamp = time();
				if ($current_timestamp != $last_timestamp)
				{		
					$addMusic->outputProgressJavascript((int)(($cur_fileno*100)/$num_files));
					$last_timestamp = $current_timestamp;
				}

				$songsAdded += $addMusic->importOneSong($getID3, $importfile);				
			}    	

			$addMusic->outputProgressJavascript(100);
			echo "<li>Added <strong>$songsAdded Songs</strong> To The Database</li>";
		}
		
		echo "</ul></div>";    	

 	}
?>

</div>
<br/>
<a href="#" onclick="window.close()" title="Close The Add Music Window">Close Window</a>
</body>
</html>
