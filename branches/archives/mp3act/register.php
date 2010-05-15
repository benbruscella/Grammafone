<?php 
	 
	include_once("includes/mp3act_functions.php"); 
  include_once("includes/sessions.php");

$error='';
if(!empty($_POST['register']['firstname'])){
  mp3act_connect();
  $query = "SELECT * FROM mp3act_users WHERE username=\"".$_POST['register']['new_username']."\"";
  $result = mysql_query($query);


if(mysql_num_rows($result)>0){
	// User exists
	$error = "Username '".$_POST['register']['new_username']."' already exists";
	$_POST['register']['new_username'] = '';
	
}else{
		$md5 = md5($_POST['register']['new_username']);
		$query = "INSERT INTO mp3act_users VALUES 
							(NULL,\"".$_POST['register']['new_username']."\",\"".$_POST['register']['firstname']."\",\"".$_POST['register']['lastname']."\",
							PASSWORD(\"".$_POST['register']['password']."\"),1,NOW(),1,\"".$_POST['register']['email']."\",\"streaming\",0,\"s\",\"$md5\",\"\",\"\",1,\"\",\"\",0)";
		
		if(mysql_query($query)){
			if(!empty($_POST['invite_code'])){
				$query2 = "DELETE FROM mp3act_invites WHERE invite_code=\"$_POST[invite_code]\"";
				mysql_query($query2);
			}
			header("Location: login.php?userAdded=".$_POST['register']['new_username']);
		}
		else{
			echo $query."<br/>".mysql_error();
		}
}
}
?>
   
  
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
<head>
<title><? echo $GLOBALS['server_title']; ?> | Login</title>
<link rel="Stylesheet" href="css/mp3act_css.php" type="text/css" />
<script type="text/javascript">
function validator()
{
  	var x = document.getElementById('newuser');
    var y = x.getElementsByTagName('input');
    for (var i=0;i<y.length;i++){
    	if(y[i].value == ""){
				document.getElementById("error").innerHTML = "You Are Missing Fields.";
				document.getElementById("password").value = "";
				document.getElementById("password2").value = "";
				return false;
    	}
    }
		
   if(document.getElementById("email").value.indexOf(".") <= 2 && document.getElementById("email").value.indexOf("@") <= 0){
   		document.getElementById("error").innerHTML = "Email Address is Not Valid.";
   		return false;
   }
 
    if(document.getElementById("new_username").value.length < 3 || document.getElementById("new_username").value.length > 11){
    	document.getElementById("error").innerHTML = "Username Must Be Between 3 and 11 characters.";
    	return false;
    }
    if(document.getElementById("password").value != document.getElementById("password2").value){
    	document.getElementById("error").innerHTML = "Passwords Do Not Match.";
    	document.getElementById("password").value = "";
    	document.getElementById("password2").value = "";
    	return false;
    }
   
}
</script>
</head>

<body>
<?php
  $email = '';
  if(isset($_POST['register']['email'])){
    $email = $_POST['register']['email'];
  }
	$invite_mode = getSystemSetting("invite_mode");
	if($invite_mode==1){ 
	  if(isset($_GET['invite'])){
	    $email = checkInviteCode($_GET['invite']);  
    }
  }
?>

<div class="login">

<form name="newuser" id="newuser" action="register.php" onsubmit="return validator()" method="post">
  
    
		<div class="loginbox">
		
		<div id="header"><h1>Register mp3act User</h1>
		Please fill out everything...
		</div>
		<p id="error"> <?php if(!$email && $invite_mode==1){ echo "Your invitation code is invalid"; } echo $error; ?>  </p>
    <?php if($email || $invite_mode==0){ ?><p>
    First Name:<br/><input type="text" size="20" name="register[firstname]" id="firstname" value="<?php echo (isset($_POST['register']['firstname']) ? $_POST['register']['firstname'] : ""); ?>" tabindex=1 /><br/><br/>
    Last Name:<br/><input type="text" size="20" name="register[lastname]" id="lastname" value="<?php echo (isset($_POST['register']['lastname']) ? $_POST['register']['lastname'] : ""); ?>" tabindex=2 /><br/><br/> 
    E-Mail Address:<br/><input type="text" size="30" name="register[email]" id="email" tabindex=3 value="<?php echo $email; ?>" /><br/><br/>
    Desired Username:<br/><input type="text" size="15" name="register[new_username]" id="new_username" value="<?php echo (isset($_POST['register']['username']) ? $_POST['register']['username'] : ""); ?>" tabindex=4 /><br/><br/>
    Password:<br/><input type="password" size="15" name="register[password]" id="password" tabindex=5 /><br/>
    Retype Password:<br/><input type="password" size="15" name="register[password2]" id="password2" tabindex=6 /><br/>
    <?php if($invite_mode==1) { ?> <input type="hidden" name="invite_code" value="<?php echo $_GET['invite']; ?>" /> <?php } ?>
		<br/><br/>
		</p>
		</div>
		<br/><input type="submit" value="Register!" class="btn" tabindex=7 /><br/>
    <?php } else { echo "</div>"; } ?>
   
    
  </form>
	<br/>
	Already Registered? <a href="login.php">Login</a>
 </div> 
  
  </body>
</html>
