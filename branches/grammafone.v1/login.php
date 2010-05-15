<?php 
include_once("includes/grammafone.php");
include_once("includes/sessions.php");

  $badlogin = 0;
  if(isset($_GET['logout']) && ($_GET['logout'] == 1)){
    $_SESSION = array();
    session_destroy();
    setcookie("mp3act_cookie"," ",time()-3600);
    header("Location: login.php?loggedout=1");
  }

  if(isLoggedIn()){
    header("Location: index.php");
  }
  
  if(isset($_POST['username']) && $_POST['username'] != "" && isset($_POST['password']) && $_POST['password'] != ""){
    mp3act_connect();
      
    $query = "SELECT * FROM mp3act_users 
                       WHERE username='$_POST[username]' 
                       AND password=PASSWORD('$_POST[password]') AND active=1 LIMIT 1";

    $result = mysql_query($query);
    if(mysql_num_rows($result) > 0){
      $userinfo = mysql_fetch_array($result);
      
      $_SESSION['sess_username'] = $userinfo['username'];
      $_SESSION['sess_firstname'] = $userinfo['firstname'];
      $_SESSION['sess_lastname'] = $userinfo['lastname'];
      $_SESSION['sess_userid'] = $userinfo['user_id'];
      $_SESSION['sess_accesslevel'] = $userinfo['accesslevel'];
      $_SESSION['sess_playmode'] = $userinfo['default_mode'];

      if(getSystemSetting("mp3bin") == ""){
					$_SESSION['sess_playmode'] = 'streaming';
				}
        $_SESSION['sess_stereo'] = $userinfo['default_stereo'];
        $_SESSION['sess_bitrate'] = $userinfo['default_bitrate'];
				$_SESSION['sess_usermd5'] = $userinfo['md5'];
				$_SESSION['sess_theme_id'] = $userinfo['theme_id'];
				$_SESSION['sess_last_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['sess_logged_in'] = 1;
        
        $query = "UPDATE mp3act_users SET last_login=NOW(), last_ip=\"$_SERVER[REMOTE_ADDR]\" WHERE user_id=$userinfo[user_id]";
        mysql_query($query);
				
				if(isset($_POST['remember']) && ($_POST['remember'] == 1)){
					$time = time();
					$md5time = md5($time);
					setcookie("mp3act_cookie",$md5time,time()+60*60*24*30);
					$query = "INSERT INTO mp3act_logins VALUES (NULL,$userinfo[user_id],\"$time\",\"$md5time\")";
					mysql_query($query);
				}
				header("Location: index.php");
      }
      else{
        $badlogin = 1;
      }
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>
<title><? echo $GLOBALS['server_title']." ".getSystemSetting("version"); ?> | Login</title>
<link rel="Stylesheet" href="css/grammafone_css.php" type="text/css" />

</head>

<body onload="document.login.username.focus()">

<div class="login">

<form name="login" action="login.php" method="post">
  
    
		<div class="loginbox">
		<div id="header"><h1>mp3act Login</h1>
		<?php

    if($badlogin == 1){
      echo "<span class=\"error\">Login Failed.  Try Again.</span>\n";
    }
    elseif(isset($_GET['notLoggedIn']) && $_GET['notLoggedIn'] == 1){
      echo "<span class=\"error\">Not Logged In....</span>\n";
    }
    elseif(isset($_GET['loggedout']) && $_GET['loggedout'] == 1){
      echo "Successfully Logged Out\n";
    }
    elseif(isset($_GET['userAdded']) && $_GET['userAdded'] != ""){
      echo "Added New User: <strong>$_GET[userAdded]</strong><br/>Please Login.\n";
    }
    else{
      echo "Please Login....\n";
     } ?>
		</div>
    <p>
    Username:<br/><input type="text" size="15" title="Enter Username Here" name="username" tabindex=1 /><br/><br/>
    Password:<br/><input type="password" size="15" title="Enter Password Here" name="password" tabindex=2 /><br/>
		<br/><input type="checkbox" name="remember" value="1" tabindex=3 class="check" /> stay logged in for 30 days<br/>
		</p>
		</div>
		<br/><input type="submit" value="Login" class="btn" tabindex=4 /><br/><br/>
		<a href="register.php">register</a> | <a href="password.php">forgot your password?</a>
   
   
    
  </form>
	
 </div> 
  
  </body>
</html>
