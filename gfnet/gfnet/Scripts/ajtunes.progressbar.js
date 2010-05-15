	//*************
	// PROGRESS BAR HANDLING
	//*************
	function setupProgressBar(){
		// first of all, hide the progress bar
		$("#pbarContainer").hide();
		// set maxWidth to the progress
		var maxWidth = $("#pbarContainer").width();
		$("#pbarLeft").width($("#pbarContainer").width());
		var maxValue;
	
		// handles clicks on the progress bar
		$("#pbarContainer").click(function(ev) {
			mouseX = ev.pageX;
			clickAt = mouseX-$(this).offset().left;
			if (sound){
				sound.setPosition((clickAt*sound.duration)/$("#pbarContainer").width());
			}
		});
	}
	// converts a nominal value to pixels
	function getCompletedWidthFromValue(valueToDisplay){
		var valuePerPixel = maxWidth / maxValue;
		return Math.floor(valuePerPixel * valueToDisplay);
	}
	// animate the progress bar
	function animation(value){
		$("#pbarContainer").width($("#progressTable").width()-150); // the width depends on the width of the table
		maxWidth = $("#pbarContainer").width();
		toDisp = getCompletedWidthFromValue(value);
		$("#pbarCompleted").width(toDisp);
		$("#pbarLeft").width(maxWidth-toDisp);
	}
	// converts the current sound position into a value displayed in the progressbar
	function displayCurrentPosition(){
		if (sound && sound.playState == 1){
			if (sound.position > 0){
				maxValue = sound.duration;
				var curPos = sound.position;
				animation(curPos);
				$("#pbarCompletedStatus").text(time(curPos));
				$("#pbarLeftStatus").text("-"+time(sound.duration-curPos));
			}
			callTimeout = setTimeout(displayCurrentPosition,100);
		}
		if (sound && sound.playState != 1){
			$("#next").trigger('click');
		}
	}
	function two(x) {return ((x>9)?"":"0")+x}
	function time(ms) {
		var sec = Math.floor(ms/1000);
		var min = Math.floor(sec/60);
		sec = sec % 60;
		t = two(sec);
		var hr = Math.floor(min/60);
		min = min % 60;
		t = two(min) + ":" + t;
		hr = hr % 60;
		if (two(hr)>0)
			t = two(hr) + ":" + t;
		return t;
	}