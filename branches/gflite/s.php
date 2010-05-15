<?php
    require("lib/Sajax.php");
    sajax_init();
    $sajax_debug_mode = 1;
    sajax_export("display");
    sajax_handle_client_request();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>SAJAX test</title>
<script type="text/javascript"> 
    <?php sajax_show_javascript(); ?>
</script>
<script type="text/javascript" src="js/prototype.js"></script>
<script type="text/javascript" src="js/player.js"></script>	
<script type="text/javascript" src="lib/mediaplayer/swfobject.js"></script>
</head>

<body>
    <div id="playing">
        <h3>SAJAX test</h3>
        <a href="#" onclick="play(); return false;" title="Play This Playlist Now">play</a>
        <div id="jwplayer"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.
            <script type="text/javascript">
                var sok = new SWFObject("lib/mediaplayer/mediaplayer.swf","player","260","320","8");
                sok.addVariable("displayheight","200");
                sok.addVariable("javascriptid","player");
                sok.addVariable("enablejs","true");
                sok.addVariable("type","mp3");
                sok.setAttribute('style', '');
                sok.addVariable('autostart','true');				
                sok.write('jwplayer');
            </script>    
        </div>
        <div id="win">Go</div>
    </div>
</body>
</html>
