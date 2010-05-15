var pladd_play = 'false';


function createSortlist () {
	$("#playlist").sortable({
		update: function(event, ui) { 
			x_playlist_update( getPlayList(), playlist_update_cb ); 
		}
	});
}

function empty_cb(new_data) {
}

function newWindow(type,id){
	if(type == 'add')
		newwindow = window.open('add.php','addmusic','height=400,width=500,scrollbars=yes,resizable=yes');
	else if(type == 'download')
		newwindow = window.open('download.php?id='+id,'download','height=200,width=350,scrollbars=yes,resizable=yes');

	if (window.focus) {newwindow.focus()}
}


function switchPage(newpage) {
    prevpage = page;
    page = newpage;

    updateBox(page, 0);
    setPageTitle();
    setCurrentPage();
}

function switchMode(newmode) {
    if (newmode == mode) {
	//do nothing 
	} else {
	x_switchMode(newmode, switchMode_cb);
    }
}

function setPLTitle() {
    if (mode == 'player')
	newmode = 'Player';
    if (mode == 'streaming')
	newmode = 'Streaming';
    document.getElementById("pl_title").innerHTML = newmode + " Playlist";
}

function viewPlaylist_cb(new_data) {
    document.getElementById("playlist").innerHTML = '';
    pladd_cb(new_data);
}

function switchMode_cb(new_data) {
    mode = new_data;
    setPLTitle();
    x_playlistInfo(plinfo_cb);
    x_viewPlaylist(viewPlaylist_cb);
}

function setCurrentPage() {
    var x = document.getElementById('nav');
    var y = x.getElementsByTagName('a');
    for (var i = 0; i < y.length; i++) {
	y[i].removeAttribute("class");
	if (y[i].id == page)
	    y[i].setAttribute('class', 'c');
    }
}

function getDropDown(type, id) {
    x_getDropDown(type, id, getDropDown_cb);
}

function getDropDown_cb(new_data) {
    ul = document.getElementById("browse_ul");
    ul.innerHTML = new_data;
    ul.style.display = 'block';
}

function closeDropDown() {
    ul = document.getElementById("browse_ul");
    ul.style.display = 'none';
    ul.innerHTML = '';
}

function savePL(type, data) {
    if (type == 'open') {
	var save_form = "<h2>Save Playlist</h2><form onsubmit='return savePL(\"save\",this)' method='get' action=''><strong>Playlist Name</strong><br/><input type='text' name='save_pl_name' id='save_pl_name' size='25' /><br/><input type='checkbox' name='pl_private' id='pl_private' /> Private Playlist<br/><br/><input type='submit' value='save' /> <input type='button' onclick=\"savePL('close',0); return false;\" value='cancel' /></form> ";
	document.getElementById("box_extra").innerHTML = save_form;
	document.getElementById("box_extra").style.display = 'block';
    } else if (type == 'save') {
	var pl_name = data.save_pl_name.value;
	var prvt = 0;
	if (data.pl_private.checked == true)
	    prvt = 1;
	x_savePlaylist(pl_name, prvt, save_Playlist_cb);
	return false;
    } else if (type == 'close')
	document.getElementById("box_extra").style.display = 'none';
}

function save_Playlist_cb(new_data) {
    box = document.getElementById("box_extra");
    box.innerHTML = new_data;
    setTimeout("box.style.display='none'", "1250");
}

function movePLItem(direction, item) {
    var parent = item.parentNode;
    var other;
    if (direction == "up") {
	other = item.previousSibling;
	if (other)
	    parent.insertBefore(parent.removeChild(item), other);
    } else if (direction == "down") {
	other = item.nextSibling;
	if (other)
	    parent.insertBefore(parent.removeChild(other), item);
    }
    if (other)
	Fat.fade_element(other.id, null, 900, '#ffcc99', '#f3f3f3');
    Fat.fade_element(item.id, null, 900, '#ffcc99', '#f3f3f3');
    playlisting.onUpdate('playlist');
}

function setBgcolor(id, c) {
    if (id != ('pl' + nowplaying)) {
	var o = document.getElementById(id);
	o.style.backgroundColor = c;
    }
}

function refresh_cb(new_data) {
    if (new_data[0] == 1) {} else {
	document.getElementById("current").innerHTML = new_data[0];
	isplaying = new_data[2];
	if (new_data[1] > 0) {

	    // highlight current song
	    oldsong = nowplaying;
	    nowplaying = new_data[1];
	    if (oldsong != 0 && oldsong != new_data[1]) {
		var old = document.getElementById('pl' + oldsong);
		old.removeAttribute('class');
		Fat.fade_element('pl' + oldsong, null, null, '#96D1EF', '#f3f3f3');

		Fat.fade_element('pl' + new_data[1], null, 1400, '#f3f3f3', '#96D1EF');
	    }
	    var current = document.getElementById('pl' + new_data[1]);

	    current.setAttribute('class', 'currentplay');
	    document.getElementById('stop').style.display = 'inline';
	    document.getElementById('play').style.display = 'none';

	} else if (nowplaying != 0 && isplaying == 0) {
	    document.getElementById('pl' + nowplaying).removeAttribute('class');
	    Fat.fade_element('pl' + nowplaying, null, null, '#96D1EF', '#f3f3f3');
	    nowplaying = 0;
	    document.getElementById('stop').style.display = 'none';
	    document.getElementById('play').style.display = 'inline';
	} else if (isplaying == 0) {
	    document.getElementById('stop').style.display = 'none';
	    document.getElementById('play').style.display = 'inline';
	} else if (isplaying == 1) {
	    document.getElementById('stop').style.display = 'inline';
	    document.getElementById('play').style.display = 'none';
	}
    }
//    setTimeout("refresh()", 20000);
}

function refresh() {
    if (mode == 'player') {
	var artist = $("artist").innerHTML;
	var song = $("song").innerHTML;
	x_ajaxSongGetCurrent(artist, song, refresh_cb);
    }
}

function setPageTitle() {
    var pages = new Array()
    pages["search"] = "Search Music";
    pages["browse"] = "Music Browser";
    pages["prefs"] = "User Account Preferences";
    pages["random"] = "Create a Random Mix";
    pages["playlists"] = "Load a Saved Playlist";
    pages["admin"] = "Gramaphone.FM Administration";
    pages["about"] = "About Gramaphone.FM";
    document.getElementById("pagetitle").innerHTML = pages[page];

}

function getRandItems(type) {
    document.getElementById("breadcrumb").innerHTML = '';
    x_getRandItems(type, getRandItems_cb);
}

function getRandItems_cb(new_data) {
    document.getElementById("rand_items").innerHTML = new_data;
}

function updateBox_cb(new_data) {
    if (new_data[1] > 0) {
	// how do I distinguish array and string?
	document.getElementById("info").innerHTML = new_data[0];
    } else {
    	document.getElementById("info").innerHTML = new_data;
    }
    document.getElementById("loading").style.display = 'none';

    if (clearbc == 1)
	breadcrumb();
    clearbc = 1;

}

function updateBox(type, itemid) {
    // dirty hack to prevent opening the album just after succesful drop
    if (type == 'album' && just_added != '' && just_added == itemid) {
	just_added = '';
	return;
    }

    document.getElementById("loading").style.display = 'block';
    x_musicLookup(type, itemid, updateBox_cb);

    if (type == 'genre' || type == 'letter') {
	bc_parenttype = '';
	bc_parentitem = '';
    } else if (type == 'album' || (type == 'artist' && bc_parenttype != '')) {
	if (bc_childtype == 'all') {
	    bc_parenttype = bc_childtype;
	    bc_parentitem = bc_childitem;
	}
    } else if (type == 'browse' || type == 'search' || type == 'about' || type == 'prefs' || type == 'random' || type == 'admin' || type == 'playlists' || type == 'stats') {

	bc_parenttype = '';
	bc_parentitem = '';
	itemid = '';
	type = '';
    } else {
	bc_parenttype = bc_childtype;
	bc_parentitem = bc_childitem;
    }

    bc_childitem = itemid;
    bc_childtype = type;
}

function deletePlaylist(id) {
    if (confirm("Are you sure you want to DELETE THIS SAVED PLAYLIST?")) {
	x_deletePlaylist(id, deletePlaylist_cb);
    }
}

function deletePlaylist_cb(new_data) {
    // reload saved PL page
    clearbc = 0;
    x_musicLookup('playlists', 0, updateBox_cb);
    setMsgText("Saved Playlist Successfully Deleted");
}

function plrem(item) {
	x_ajaxPlaylistRemove(item,plrem_cb);
	createSortlist();
}

function plrem_cb(rem){
	p = document.getElementById("playlist");
	d_nested = document.getElementById(rem);
	throwaway_node = p.removeChild(d_nested);
	x_playlistInfo(plinfo_cb);
}
			
function pladd(type, id) {
    x_playlist_add(type, id, pladd_cb);
}

function playlist_update_cb(str) {
    var list = document.getElementById("playlist").getElementsByTagName("li");
    var links = document.getElementById("playlist").getElementsByTagName("a");
    for (var i = 0; i < list.length; i++) {
	var pl_id = "pl_" + i;
	var pl_cmd = "play(\'playlist\', \'" + pl_id + "\')";
	list[i].setAttribute("id", pl_id);
	list[i].setAttribute("ondblclick", pl_cmd);
	/* normally first link is remove, second link is play */
	links[(i*2)+1].setAttribute("onclick", pl_cmd);
	var p = list[i];
	p.style.zIndex = list.length+5-i;
    }
}

function pladd_cb(new_data) {
	if (new_data[0] == 1) {
		// alert( "pladd_cb new_data[0] = 1" );
		x_viewPlaylist(viewPlaylist_cb);
		x_playlistInfo(plinfo_cb);
	} else {
		//adding to innerHTML does not work because then the HTML tree
		//is completely reparsed (a+=b --> a=(a+b), where a+b is reparsed)
		//and looses custom added properties (or so I think)
		document.getElementById("playlist").innerHTML += new_data[0];
	
		for (var i = 2; i < new_data[1] + 2; i++) {
			if ((pladd_play == 'true') && (i== 2))  {
				/* there is a race condition here, so we need to update the highlight here */
				Fat.fade_element(new_data[i], null, 500, '#B4EAA2', '#78f3f3'); 
			}
			else {
				Fat.fade_element(new_data[i], null, 500, '#B4EAA2', '#f3f3f3');
			}
		}
		x_playlistInfo(plinfo_cb);
			createSortlist();		
	    if (pladd_play == 'true') {
		    var list = document.getElementById("playlist").getElementsByTagName("li");
		pladd_play = 'false';
		play('playlist', list[(list.length - new_data[1])].getAttribute('id'));
	    }
	}
}

function getPlayList() {
    var str = "";
    var list = document.getElementById("playlist").getElementsByTagName("li");
    for (var i = 0; i < list.length; i++) {
	if (i > 0)
	    str += ",";
	str += list[i].getAttribute("song");
    }
    return str;
}

function plclear() {
    x_clearPlaylist(plinfo_cb);
    document.getElementById("playlist").innerHTML = "";
}

function plinfo_cb(new_data) {
    document.getElementById("pl_info").innerHTML = new_data;
}

function breadcrumb() {
    x_buildBreadcrumb(page, bc_parenttype, bc_parentitem, bc_childtype, bc_childitem, breadcrumb_cb);
}

function breadcrumb_cb(new_data) {
    //if(new_data!="")
    document.getElementById("breadcrumb").innerHTML = new_data;
}


function stream(type, id) {
    x_play(0, 'stop', 0, play_cb);
	document.getElementById('hidden').src = null;
	document.getElementById("hidden").src = "hidden.php?type=" + type + "&id=" + id + '&mode=streaming';
}

function play(type, ident) {
    document.getElementById("loading").style.display = 'block';

    if ((type == 'song') || (type == 'album')) {
	pladd(type, ident);
	pladd_play = 'true';
    }
    else if (type == 'playlist') {

	var list = document.getElementById("playlist").getElementsByTagName("li");

	if ( ident == -1) {
	    /* check there is a valid playlist with an element on it */
	    if (list.length > 0) {
		/* Start from the top */
		ident = list[0].getAttribute('song');
		list[0].setAttribute('current_playing', 'yes');
	    }
	}
	else if ( ident == 'next')  {
	    ident = getNextTrack('fwd');
	}
	else if ( ident == 'prev')  {
	    ident = getNextTrack('rev');
	}
	else { 
	    var i = 0;
	    /* we probably just double clicked a track, so remove the last playing one if we were playing */
	    for (i = 0; (i < list.length) && ( list[i].getAttribute('id') != ident); i++);

	    if (i != list.length) {
		removeLastPlayingTrack(list);
		ident = list[i].getAttribute('song');
		list[i].setAttribute('current_playing', 'yes');
	    }
	    else {
		ident = 0;
	    }
	}

	if (ident != 0)  {
	    loadFile('player', {file:'hidden.php?type=song' + '&id=' + ident+ '&mode=player'});
	}
    }
    else if (type == 'stop') {
	x_play('player', 'stop', 0, play_cb);
    }
    else {
	alert("Feature Coming Soon");    
    }
}

function randPlay(data) {
    var type = data.random_type.value;
    if (type == "") {
	setMsgText("You must choose a random type");
	return false;
    }
    var num = 0;
    if (mode == 'player')
	num = data.random_count.value;
    var items = '';
    if (type != 'all') {
	for (var i = 0; i < data.random_items.options.length; i++) {
	    if (data.random_items.options[i].selected == true)
		items += data.random_items.options[i].value + " ";
	}

	if (items == "") {
	    setMsgText("You must choose at least one random item");
	    return false;
	}
    }
    if (mode == 'streaming') {
	document.getElementById('hidden').src = null;
	document.getElementById("hidden").src = "hidden.php?type=" + type + "&num=" + num + "&items=" + items;
    } else {
	x_randPlay(mode, type, num, items, play_cb);
    }
    return false;

}

function play_cb(new_data) {
	if (new_data != "") {
	}
	else {
		sendEvent('player', 'stop');
	}	

    refresh();
    document.getElementById("loading").style.display = 'none';    
}

function showAlbumArt(mode) {
    document.getElementById('bigart').style.display = mode;
}

function download(id) {
    document.getElementById('hidden').src = null;
    document.getElementById("hidden").src = "hidden.php?type=dl" + "&id=" + id;
}

function addmusic(form) {
    document.getElementById("current").innerHTML = form.musicpath.value;
    return false;
}

function adminAddUser(form) {
    if (form != "") {
	if (form.firstname.value == '' || form.lastname.value == '' || form.username.value == '' || form.password.value == '' || form.password2.value == '' || form.email.value == '') {
	    setMsgText("Required Fields Are Empty");
	    return false;
	}

	if (form.password.value != form.password2.value) {
	    setMsgText("Password Do Not Match");
	    document.getElementById("password").value = "";
	    document.getElementById("password2").value = "";
	    return false;
	}
	if (form.email.value.indexOf(".") <= 2 && form.email.value.indexOf("@") <= 0) {
	    setMsgText("Email Address is Invalid");
	    document.getElementById("email").focus();
	    return false;
	}
	x_adminAddUser(form.firstname.value, form.lastname.value, form.username.value, form.email.value, form.perms.value, form.password.value, adminAddUser_cb);
	return false;

    } else {
	x_adminAddUser('', '', '', '', '', '', updateBox_cb);
    }

    return false;
}

function adminAddUser_cb(new_data) {
    clearbc = 0;
    if (new_data == 1) {
	updateBox('admin', 0);
	setMsgText("User Successfully Added");
    } else {
	setMsgText("Username is Already Taken. Try Another.");
	document.getElementById("username").value = "";
	document.getElementById("username").focus();
    }
}

function adminEditUsers(user, action, form) {
    if (user != 0) {
	if (action == 'del') {
	    if (confirm('Are you Sure you want to DELETE THE USER?')) {
		x_adminEditUsers(user, action, adminEditUsers_cb);
	    }

	} else if (action == 'mod') {
	    x_adminEditUsers(user, 'mod', form.active.value, form.perms.value, adminEditUsers_cb);
	} else {
	    x_adminEditUsers(user, 'user', updateBox_cb);
	}
    } else {
	x_adminEditUsers(updateBox_cb);
    }
    return false;
}

function adminEditUsers_cb(new_data) {
    clearbc = 0;
    x_adminEditUsers(updateBox_cb);
    if (new_data == 1) {
	setMsgText("User Successfully Deleted");

    }
    if (new_data == 2) {
	setMsgText("User Successfully Updated");
    }
}

function setMsgText(text) {
    try {
	document.getElementById("breadcrumb").innerHTML = "<span class='error'>" + text + "</span>";
	Fat.fade_element('breadcrumb', null, 2000, '#F5C2C2', '#ffffff');
    }
    catch(err) {
	alert(text);
    }    
}

function editSettings_cb(new_data) {
    if (new_data == 1) {
	clearbc = 0;
	updateBox('admin', 0);
	setMsgText("New Settings Saved");
    }
}

function editSettings(form) {
    if (form != 0) {
	x_editSettings(1, form.invite.value, form.downloads.value, form.amazonid.value, form.upload_path.value, form.sample_mode.value, form.lamebin.value, editSettings_cb);
    } else {
	x_editSettings(0, '', '', '', '', '', '',  updateBox_cb);
    }
    return false;
}

function editUser_cb(new_data) {
    if (new_data == 1) {
	clearbc = 0;
	updateBox('prefs', 0);

	setMsgText("New Settings Saved");
    }

}

function editUser(type, form) {
    if (form != 0) {
	if (type == 'info') {
	    x_editUser(type, form.firstname.value, form.lastname.value, form.email.value, 0, '', '', '', editUser_cb);
	} else if (type == 'settings') {
	    x_editUser(type, form.default_playmode.value, form.default_bitrate.value, form.default_stereo.value, form.theme_id.value, form.as_username.value, form.as_password.value, form.as_type.value, editUser_cb);
	} else if (type == 'pass') {
	    if (form.new_password.value != form.new_password2.value) {
		setMsgText("New Passwords Do Not Match");
	    } else {
		x_editUser(type, form.old_password.value, form.new_password.value, '', 0, '', '', '', editUser_cb);
	    }
	}
    } else {
	x_editUser(type, '', '', '', 0, '', '', '', updateBox_cb);
    }
    return false;
}

function searchMusic(form) {
    if (form.searchbox.value == '' || form.searchbox.value == '[enter your search terms]') {
	setMsgText("You Must Enter Something to Search For");
    } else {
//        document.getElementById("breadcrumb").innerHTML = "";
	x_searchMusic(form.searchbox.value, form.search_options.value, updateBox_cb);
    }
    return false;
}

function clearDB_cb(new_data) {
    if (new_data == 1)
	setMsgText("Database Successfully Cleared");
}

function clearDB() {
    if (confirm("Are you sure you want to RESET THE MUSIC DATABASE? This will remove all data regarding music and music stastics.")) {
	x_resetDatabase(clearDB_cb);
    }
}

function sendInvite(form) {
    x_createInviteCode(form.email.value, sendInvite_cb);
    return false;
}

function sendInvite_cb(new_data) {
    if (new_data == 1) {
	setMsgText("Invitation Successfully Sent");
	document.getElementById("email").value = "";
    }
}

function submitScrobbler(userid) {
    x_submitScrobbler(userid, empty_cb);
    setMsgText("AudioScrobbler Submission Attempted");
    return false;
}

function init() {
	createSortlist();
	setPageTitle();
	x_viewPlaylist(viewPlaylist_cb);
	x_playlistInfo(plinfo_cb);
	x_play(0, 'stop', 0, play_cb);
	setPLTitle();
	setCurrentPage();
	updateBox(page, 0);
};


