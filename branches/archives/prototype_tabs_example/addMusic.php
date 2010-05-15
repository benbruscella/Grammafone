<?php
  include_once("addMusicClass.php");
  include_once("grammafone.php"); 
#  include_once("includes/sessions.php");
#  if(!isLoggedIn()){
#    header("Location: login.php?notLoggedIn=1");
#  }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>
<title>
  <?=$GLOBALS['server_title']; ?> | Add Music
</title>

<link rel="Stylesheet" href="grammafone_css.php" type="text/css" />

<?php 
  if(isset($_GET['add']) && $_GET['add'] == 1 && isset($_GET['musicpath'])){ 
?>
  <meta http-equiv="refresh" content="2; url=addMusic.php?musicpath=<?php echo $_GET['musicpath']; ?>">
<?php 
  } 
?>

<script type="text/javascript">

  function validator() {
    var x = document.getElementById('musicpath').value;
    if(x == "" || x == "/path/to/music"){
      document.getElementById("error").innerHTML = "The path you entered is invalid";
      return false;
    }
  }

  function progress() {
    document.getElementById("error").innerHTML = "<img src='img/progress_bar.gif' /><br/>File is uploading.  Please Wait.";
  }
</script>

</head>

<body>

<div id="wrap">
  <div id="header">
    <h1>mp3act Add Music</h1>
    Enter a music directory on the server or upload a file.
  </div>
  <p id="error" class='pad'></p>
  <?php
    if(isset($_GET['add']) && $_GET['add']==1 && isset($_GET['musicpath'])) {
      echo "<p class='pad'>"; 
      echo "<img src='img/progress_bar.gif' /><br/><br/>New Music is being added.  This could take several minutes.<br/>";
      echo "</p>";
    }
    elseif(isset($_GET['musicpath']) && is_dir($_GET['musicpath']) ) {
      echo "<p class='pad'>";
      $path = $_GET['musicpath'];
      if($path{strlen($path)-1} != '/') {
        $path .= "/";
      }
      $addMusic = new addMusic;
      $addMusic->setPath($path);
      $addMusic->getSongs($path,$songs);
      $songsAdded = $addMusic->insertSongs();
      echo "<br/>Added <strong>$songsAdded Songs</strong> To The Database";
      echo "</p>";
    }
    elseif(isset($_FILES['musicfile']['name'])) {
      echo "<pre>";
      print_r($_FILES);
      echo "</pre>";
      echo $_FILES['musicfile']['tmp_name'];
      echo "<br/>".$_FILES['musicfile']['name'];
      move_uploaded_file($_FILES['musicfile']['tmp_name'], $GLOBALS[upload_path]."/".$_FILES['musicfile']['name']);
    }
    else {
      echo "<form action='addMusic.php' method='get' onsubmit='return validator()'>\n";
      echo "<p class='pad'><input type='hidden' value=1 name='add' /><input type='text' onfocus='this.select()' name='musicpath' id='musicpath' size='45' id='musicpath' value='/path/to/music' /><br/><br/>";
      echo "<input type='submit' value='add music' class='btn' /><br/><br/>Be Patient While Music is Added. It could take several minutes.</p>";
      echo "</form>";
    }
  ?>  
</div>
<br/>
<a href="#" onclick="window.close()" title="Close The Add Music Window">Close Window</a>
</body>
</html>

