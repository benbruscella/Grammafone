<?php
include("base.inc.php");
    
    /* SEND PASSWORD */
    $error = '';
    if(!empty($_POST['email'])) {
        grammafone_connect();
        if(sendPassword($_POST['email'])) {
            $error = "A new password has been sent to: $_POST[email].";
        }
        else {
            $error = "Email Address is not a valid account";
        }
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title><? echo $GLOBALS['server_title']; ?> | Login</title>
    <link rel="Stylesheet" href="css/grammafone.css" type="text/css" />
    <script type="text/javascript">
        function validator() {
            if(document.getElementById("email").value == "") {
                document.getElementById("error").innerHTML = "Email Address is Blank";
                return false;
            }
        }
    </script>
</head>

<body>
<div class="login">
    <form name="reminder" id="reminder" action="password.php" onsubmit="return validator()" method="post">
        <div class="loginbox">
            <div id="header"><h1>Password Reminder</h1>
                A new password will be sent to you.
            </div>
            <p id="error"> <?php echo $error; ?>  </p>
            <p>Your E-Mail Address:<br/><input type="text" size="30" name="email" id="email" tabindex=3 value="" /><br/><br/>
            </p>
        </div>
        <br/><input type="submit" value="Send Password" class="btn" tabindex=7 /><br/>
    </form>
    <br/>Just Remembered It? <a href="login.php">Login</a>
</div> 
</body>
</html>

