	var _sm;
	var sound;
	var currentMp3Id = -1;

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
				$("#prev").attr({src : "Content/ajtunes_prev.jpg"});
				$("#next").attr({src : "Content/ajtunes_next.jpg"});
				$(this).attr({src : "Content/ajtunes_pause.jpg"});
				$(this).attr({isPlay : "false"});	
			}
		}
		else {
			if (sound)
				sound.togglePause();
			$(this).attr({src : "Content/ajtunes_play.jpg"});
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
	$("#prev").attr({src : "Content/ajtunes_prev_disabled.jpg"});
	$("#next").attr({src : "Content/ajtunes_next_disabled.jpg"});
	$("#playstop").attr({src : "Content/ajtunes_play.jpg"});
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
		
	
function itemizer(mp3){
	//var mp3 = $(this);
	var row = $('<tr></tr>');
	if (rowCounter%2==0){
		row.attr("class","playlistItem evenRow");
	}
	else {
		row.attr("class", "playlistItem")
	}
	var col1 = $('<td width="'+widths[0]+'" align="right"></td>');
	var col1b = $('<td width="'+widths[1]+'" class="playlistCell playingIcon" align="center"></td>');
	var col2 = $('<td width="'+widths[2]+'" class="playlistCell"></td>');
	var col3 = $('<td width="'+widths[3]+'" class="playlistCell" align="right"></td>');
	var col4 = $('<td width="'+widths[4]+'" class="playlistCell"></td>');
	var col5 = $('<td width="'+widths[5]+'" class="playlistCell"></td>');
	var col6 = $('<td width="'+widths[6]+'" class="playlistCell">&nbsp;</td>');
	if (mp3.attr("id")==currentMp3Id){
		col1b.html('<img src="Content/ajtunes_playing.gif">');
	}

	var itemObj = $('<div></div>');

	row.attr("id","item"+mp3.attr("id"));
	row.attr("path",mp3.attr("path"));
	row.attr("title",mp3.attr("title"));
	row.attr("artist",mp3.attr("artist"));
	row.bind("click", function(){
		tempRow = 1;
		$(".playlistItem").each(function(){
			if (tempRow%2==0){
				$(this).attr("class","playlistItem evenRow");
			}
			else {
				$(this).attr("class", "playlistItem")
			}
			tempRow++;
		});
		$(this).attr("class","playlistItem selectedRow");
	});
	row.bind("dblclick", function(){
		currentMp3Id = mp3.attr("id");
		$("#prev").attr({src : "Content/ajtunes_prev.jpg"});
		$("#next").attr({src : "Content/ajtunes_next.jpg"});
		$("#playstop").attr({src : "Content/ajtunes_pause.jpg"});
		$("#playstop").attr({isPlay : "false"});
		$(".playingIcon").each(function(){
			$(this).html('');
		});
		col1b.html('<img src="Content/ajtunes_playing.gif">');
		if (mp3.attr("art") && mp3.attr("art")!=""){
			$("#nothing").hide();
			$("#cover").attr({src : mp3.attr("art")});
			$("#cover").show();
		}
		else {
			$("#nothing").html("No Artwork");
			$("#nothing").show();
			$("#cover").hide();
		}
		
		_sm.destroySound('currentItem');
		clearTimeout(callTimeout);
		_sm.createSound('currentItem','stream.php?id=' + currentMp3Id);
		sound = _sm.createSound({
			id:'currentItem',
			url:'stream.php?id=' + currentMp3Id										 
		});
		if ($("#slider").attr("hasBeenMoved")=="false"){
			sound.setVolume(50);
			$("#slider").animate({"left": "28px"});
		}
		else {
			sound.setVolume(readVolumeSlider());
		}
		$("#logo").hide();
		$("#pbarContainer").show();
		$("#songTitle").html($(this).attr("title")+"<br>"+$(this).attr("artist"));
		callTimeout = setTimeout(displayCurrentPosition,0);
		sound.togglePause();
	});

	
	col1.html(rowCounter);
	col1.appendTo(row);
	col1b.appendTo(row);
	col2.html(mp3.attr("title"));
	col2.appendTo(row);
	col3.html(mp3.attr("time"));
	col3.appendTo(row);
	col4.html(mp3.attr("artist"));
	col4.appendTo(row);
	col5.html(mp3.attr("album"));
	col5.appendTo(row);
	col6.appendTo(row);
	row.appendTo('#playlistContent');	
	rowCounter++;
}
		
function init() {
	callTimeout = setTimeout(displayCurrentPosition,0);

	setupProgressBar();

	
	//*************
	// PLAYLISTS RETRIEVAL
	//*************
	// read the playlists
	

	
	$.ajax({
		type: "GET",
		url: "getPlaylists.php",
   		data: "",
   		dataType: "xml",
        error:function (xhr, ajaxOptions, thrownError){
        		alert(xhr.statusText);
		},       
		success: function(xml){
			$("playlist",xml).each(function() {
			    var playlist = $(this);
			
				var row = $('<tr></tr>');
				row.attr("class","playlistTitle");
				var playlistIcon = $('<td width="30" align="right" style="padding:3px"><img src="Content/ajtunes_playlist.gif"/></td>');
				var playlistName = $('<td valign="middle" style="padding:3px"></td>');
				row.bind("click", function(){
					rowCounter = 1;
					$(".playlistTitle").each(function(){
						$(this).attr("class","playlistTitle");
					});
					row.attr("class","playlistTitle selectedRow");

					$('#playlistContent').empty();
					$.ajax({
						type: "GET",
						url: "getPlaylist.php?id="+playlist.attr("id")+"&"+Math.random(),
				   		data: "",
				   		dataType: "xml",
						success: function(xml){
							$("mp3",xml).each(function() {
							    itemizer($(this));
							});
							fillWithEmptyRows();
						} 
					});
		
			
			    });
				playlistName.html(playlist.attr("title"));
				playlistIcon.appendTo(row);
				playlistName.appendTo(row);
				row.appendTo('#playlists');
			});
			$($(".playlistTitle")[0]).trigger('click');
		}
	});
	
	$("#libraryMusic").click(function(){
		rowCounter = 1;
		$(".playlistTitle").each(function(){
			$(this).attr("class","playlistTitle");
		});
		$(this).attr("class","playlistTitle selectedRow");

		$('#playlistContent').empty();
		$.ajax({
			type: "GET",
			url: "getPlaylist.php?"+Math.random(),
	   		data: "",
			success: function(xml){
				$(xml).find("mp3").each(function() {
				    itemizer($(this));
				});
				fillWithEmptyRows();
			}
		});
	});

	
	
	setupPlayer();
	setupVolume();
	setupInterface();

	// soundmanager configuration
	soundManager.debugMode = false;
	soundManager.consoleOnly = false;
	soundManager.flashVersion = 8;
	soundManager.onload = function() {
		_sm = soundManager;
	};
}
