<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <title>Ajax Workshop 2: Building Tabbed Content</title>
  <script type="text/javascript" src="prototype.js"></script>
  <script type="text/javascript" src="functions.js"></script>
  <script type="text/javascript" src="jw_mp3_player/swfobject.js"></script>
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body onload="init()">
  <div id="load">Loading, Please Wait...</div>

  <div id="container">

    <div id="banner">Tabs Example</div>

    <?php /*These are handled in process.php */?>
    <div class="menu" id="menuSearch">Search</div>
    <div class="menu" id="menuBrowse">Browse</div>
    <div class="menu" id="menuAdd">Add</div>
    <div class="menu" id="menuDelete">Delete</div>
    <div class="menu" id="menuAbout">About</div>

    <div id="content">
      /* Filled by AJAX requests */
    </div>

    <div id="player"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.
      <script type="text/javascript">
        var s2 = new SWFObject("jw_mp3_player/mp3player.swf", "playlist", "320", "320", "7");
        s2.addVariable("file","readdir.php");
        s2.addVariable("backcolor","0xFFFFFF");
        s2.addVariable("frontcolor","0x1b5790");
        s2.addVariable("lightcolor","0xCC0066");
        s2.addVariable("displayheight","0");
        s2.addVariable("showdownload", "true");
        s2.write("player");
      </script>
    </div>
  </div>

  <div id="footer">
    <!-- Creative Commons License -->
    <a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/2.0/deed-music">
      <img alt="Creative Commons License" border="0" src="http://creativecommons.org/images/public/music.gif" />
    </a>
    <br/>
  </div>

</body>

</html>

