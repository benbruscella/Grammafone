<?php
  include_once("includes/grammafone.php");
  set_time_limit(0);
  streamPlay($_GET['i'],$_GET['b'],$_GET['s'],$_GET['u'],$_SERVER['REMOTE_ADDR'])
?>
