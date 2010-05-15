<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Gramophone.FM</title>	
	<link rel="stylesheet" href="css/style.css" type="text/css">
	<script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
	<script src="js/jquery-ui.min.js" type="text/javascript"></script>
	<script src="js/jquery-ui.min.js" type="text/javascript"></script>
	<script src="js/soundmanager2.js" type="text/javascript" ></script>
	<script src="js/gramophone.js" type="text/javascript" ></script>
	<script>
	    $(document).ready(function(){
			init();		
		});
	</script>
</head>
<body>
	<div id="controls">
		<span id="previous" class="previous-disabled"></span>
		<span id="playpause" class="play"></span>
		<span id="next" class="next-disabled"></span>
		<span id="volume-min"></span>
		<span id="volume"><img id="slider" src="images/volume_slider.jpg" width="13" height="71"></span>
		<span id="volume-max"></span>
		<span id="progress-left"></span>
		<span id="progress">
			<span id="pbarCompletedStatus"></span>
			<span id="pbarContainer">
				<span id="pbarCompleted"></span>
				<span id="pbarLeft"></span>
			</span>
			<span id="pbarLeftStatus"></span>
		</span>
		<span id="progress-right"></span>
	</div>
	
	<div id="playlist">
	<ul id="songs"></ul>
	</div>
	
</body> 
</html>
