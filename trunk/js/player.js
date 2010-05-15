/******************************************************************************
 *
 *  Grammafone Digital Music System - For your digital music collection
 *  http://www.grammafone.com
 *  Copyright (C) 2008 Grammafone.com
 *  Title: JW Player Utilities
 *  Author: Niroshan Rajadurai
 *  Description: Utilites required by JW Player to control start, stop,
 *  load tracks, and get completion notification. Documentation for player can
 *  be found here: http://www.jeroenwijering.com/?item=Supported_Flashvars
 *
 *****************************************************************************/

var player;


function removeLastPlayingTrack(list) {

    var i = 0;

    for( i = 0; (i < list.length) && (list[i].getAttribute('current_playing') != 'yes') ; i++);

    if (i != list.length) {
	list[i].setAttribute('current_playing', 'no');
	list[i].setAttribute('style', 'background-color: rgb(243, 243, 243);');
	    list[i].setAttribute('onmouseout', 'setBgcolor(this.id,\'#f3f3f3\'); return false;');
    }
    return i;
    player.sendEvent("STOP");
    player.sendEvent("LOAD", 0);
}


function getNextTrack(direction)
{
    var list = document.getElementById("playlist").getElementsByTagName("li");

    if (list.length > 0) {
	var currentPlayingTrack = removeLastPlayingTrack(list);

	// If we found the current track, then increment to the next track
	// manage wrap around (we should have a switch for this)
	//
	if (currentPlayingTrack != list.length) {

	    if (direction == 'fwd' ) {
		currentPlayingTrack++;
		if (currentPlayingTrack >= list.length)
		{
		    currentPlayingTrack =0;
		}
	    }
	    else if (direction == 'rev' ) {
		currentPlayingTrack--;
		if (currentPlayingTrack < 0)
		{
		    currentPlayingTrack = list.length - 1;
		}
	    }

	}
	else {
	    currentPlayingTrack = 0;
	}

	list[currentPlayingTrack].setAttribute('current_playing', 'yes');
	return list[currentPlayingTrack].getAttribute('song');
    }
    else {
	document.getElementById("loading").style.display = 'none'; 
    }

};


function loadFile(swf,obj) {
	player.sendEvent("STOP");
	player.sendEvent("SEEK", "0");
	player.sendEvent("LOAD",obj);
};

function checkState(obj) {
	if(obj['newstate'] == 'COMPLETED') {
		play('playlist', 'next');
	}	
	if(obj['newstate'] == 'PLAYING') {
		refresh();
		document.getElementById("loading").style.display = 'none'; 
	var list = document.getElementById("playlist").getElementsByTagName("li");
	    for (var i = 0; (i < list.length) && (list[i].getAttribute("current_playing") != "yes"); i++);
	if (i != list.length) {
	    list[i].setAttribute('style', 'background-color: rgb(120, 243, 243)');
	    list[i].setAttribute('onmouseout', 'setBgcolor(this.id,\'#78f3f3\'); return false');
	}
		
	}
}

function playerReady(obj) {
	//alert('the videoplayer '+obj['id']+' has been instantiated');
	player = document.getElementById('player');
	player.addModelListener("STATE"," checkState");
};


function playerShow() {
	var sok = new SWFObject("lib/player/player.swf","player",'80%',"32","8");
	sok.addVariable('autostart','true');
	sok.addVariable("skin", "lib/player/snel.swf");
	sok.addVariable("frontcolor", "#0f214e");
	sok.addParam("allowfullscreen","false");
/*	sok.addVariable("menu", "false");*/
	sok.write('jwplayer');
}

/***************************** END OF File ***********************************/
