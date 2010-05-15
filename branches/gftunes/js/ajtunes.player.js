function setupPlayer(){
	$("#playstop").click(function(){
		if ($(this).attr("isPlay")=="true"){
			var changeStatus = false;
			if (sound){
				sound.togglePause();
				changeStatus = true;
			}
			else {
				if ($(".playlistItem").length > 0){
					$($(".playlistItem")[0]).trigger('dblclick');
					changeStatus = true;
				}
			}
			if (changeStatus){
				$("#prev").attr({src : "images/ajtunes_prev.jpg"});
				$("#next").attr({src : "images/ajtunes_next.jpg"});
				$(this).attr({src : "images/ajtunes_pause.jpg"});
				$(this).attr({isPlay : "false"});	
			}
		}
		else {
			if (sound)
				sound.togglePause();
			$(this).attr({src : "images/ajtunes_play.jpg"});
			$(this).attr({isPlay : "true"});
		}
	});

	$("#prev").click(function(){
		if (sound){
			if ($(".playlistItem").length > 0){
				if (sound.position < 800){
					var currentIndex = -1;
					index = 0;
					$(".playlistItem").each(function(){
						if ("item"+currentMp3Id == $(this).attr("id")){
							currentIndex = index;
						}
						index++;
					});
					if (currentIndex>0){
						$($(".playlistItem")[currentIndex-1]).trigger('dblclick');
					}
					else {
						resetPlayer();
					}
				}
				else {
					sound.setPosition(0);
				}
			}
		}
	});
	$("#next").click(function(){
		if (sound){
			if ($(".playlistItem").length > 0){
				var currentIndex = -1;
				index = 0;
				$(".playlistItem").each(function(){
					if ("item"+currentMp3Id == $(this).attr("id")){
						currentIndex = index;
					}
					index++;
				});
				if ($(".playlistItem").length > currentIndex+1){
					$($(".playlistItem")[currentIndex+1]).trigger('dblclick');
				}
				else {
					resetPlayer();
				}
			}
		}
	});
	$("#nowPlaying").click(function (){$(".nowPlaying").toggle()});
}
		
		
function resetPlayer(){
	currentMp3Id = -1;
	$("#prev").attr({src : "images/ajtunes_prev_disabled.jpg"});
	$("#next").attr({src : "images/ajtunes_next_disabled.jpg"});
	$("#playstop").attr({src : "images/ajtunes_play.jpg"});
	$("#playstop").attr({isPlay : "true"});	
	if (sound && sound.playState == 1){
		sound.stop();
		sound = null;
	}
	$(".playingIcon").each(function(){
		$(this).html('');
	});	
	$("#pbarContainer").hide();
	$("#songTitle").text("");
	$("#pbarCompletedStatus").text("");
	$("#pbarLeftStatus").text("");
	$("#logo").show();
	$("#cover").hide();
	$("#nothing").html("Nothing<br>Playing");
	$("#nothing").show();
}
		
function setupVolume(){
	$("#slider").draggable({
		axis: "x",
		containment: $("#volume"),
		drag: function(e,ui){
			if (sound)
				sound.setVolume(readVolumeSlider());
			$(this).attr("hasBeenMoved","true");
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
	
	$("#volMin").click(function(){
		$("#slider").animate({"left": "0px"});
		if (sound)
			sound.setVolume(0);
	});

	$("#volMax").click(function(){
		$("#slider").animate({"left": "57px"});
		if (sound)
			sound.setVolume(100);
	});
}		

function readVolumeSlider(){
	var volume = (70-($("#volMax").offset().left-$("#slider").offset().left))*(100/57);
	return volume;
}
		
	