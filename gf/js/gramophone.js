var _sm;
var sound;

function setSizes(){
	$("#progress").width($("#controls").width()-510);
	$("#pbarContainer").width($("#progress").width());
}


function readVolumeSlider(){
	var volume = (70-($("#volume-max").offset().left-$("#slider").offset().left))*(100/57);
	return volume;
}

function setVolume(){
	$("#slider").draggable({
		axis: "x",
		containment: $("#volume"),
		drag: function(e,ui){
			if (sound)
				sound.setVolume(readVolumeSlider());
		}
	});
	$("#volume").click(function(e){
		var left = e.pageX-$(this).offset().left-6;
		if (left <=0)
			left=0;
		if (left>=57)
			left = 57;
		$("#slider").animate({"left": left+"px"});
		if (sound)
			sound.setVolume(Math.ceil(left*100/57));
	});
	
	$("#volume-min").click(function(){
		$("#slider").animate({"left": "0px"});
		if (sound)
			sound.setVolume(0);
	});

	$("#volume-max").click(function(){
		$("#slider").animate({"left": "57px"});
		if (sound)
			sound.setVolume(100);
	});
}		



function setPlayer(){
	$("#playpause").click(function(){
		if (sound){
			sound.togglePause();
		}
		if ($(this).hasClass('pause')==true){
			$(this).removeClass('pause').addClass('play');
		}
		else {
			$(this).removeClass('play').addClass('pause');
		}
	});
}

function setupProgressBar(){
	// first of all, hide the progress bar
	//$("#pbarContainer").hide();
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
	$("#pbarContainer").width($("#progress").width()); // the width depends on the width of the table
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

function init() {
	callTimeout = setTimeout(displayCurrentPosition,0);
	setupProgressBar();
	// soundmanager configuration
	soundManager.debugMode = true;
	soundManager.consoleOnly = true;
	soundManager.flashVersion = 9;
	soundManager.url = 'js';
	soundManager.useHighPerformance = true;
	soundManager.useFastPolling = true;
	soundManager.onload = function() {
		_sm = soundManager;
	};
	setSizes();
	setPlayer();
	setVolume();
	
	$.ajax({
		type: "GET",
		url: "itunes.php",
		contentType: "application/json; charset=utf-8",
   		data: "",
   		dataType: "json",
		success: function(data){
			$.each(data, function(){
				var row = $('<li id=' + this.Location +'>' + this.Artist + ' - ' + this.Album + ' - ' + this['Track Number'] +' - ' + this.Name + '</li>');
				var loc = this.Location;
				$("#songs").append(row);
				row.bind("dblclick", function(){
					$("#playpause").removeClass('play').addClass('pause');
					_sm.destroySound('song');
					clearTimeout(callTimeout);
					sound = _sm.createSound({
						id:'song',
						url:'stream.php?id=' + this.id
					});
					callTimeout = setTimeout(displayCurrentPosition,0);
					$("#pbarContainer").show();
					sound.togglePause();
				});				
			});
		}
	});
}



$(window).resize(function(){
	setSizes();
});
