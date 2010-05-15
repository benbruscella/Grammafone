<?php
// Copyright 2007 Facebook Corp.  All Rights Reserved. 
// 
// Application: Gramatunes
// File: 'index.php' 
//   This is a sample skeleton for your application. 
// 

require_once 'integrations/facebook/facebook.php';

$appapikey = 'f5b644d76dde9a77e653971305fff94f';
$appsecret = 'b26fe92b2ce52f3146788f28809bd7bd';
$facebook = new Facebook($appapikey, $appsecret);
$user_id = $facebook->require_login();
$web_root = "/home/nirocr/www/beta.gramatunes.com/trunk";

// Greet the currently logged-in user!
echo "<p>Hello, <fb:name uid=\"$user_id\" useyou=\"false\" />!</p>";

//echo "<embed id=\"player\" height=\"32\" width=\"80%\" flashvars=\"autostart=true&skin=../../lib/player/snel.swf&frontcolor=#0f214e&menu=false\" allowfullscreen=\"false\" quality=\"high\" name=\"player\" style=\"\" src=\"../../lib/player/player.swf\" type=\"application/x-shockwave-flash\"/>";
require_once 'index.php';


// Print out at most 25 of the logged-in user's friends,
// using the friends.get API method
echo "<p>Friends:";
$friends = $facebook->api_client->friends_get();
$friends = array_slice($friends, 0, 25);
foreach ($friends as $friend) {
  echo "<br>$friend";
  }
  echo "</p>";
