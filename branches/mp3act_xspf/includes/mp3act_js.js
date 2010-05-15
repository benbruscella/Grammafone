    	window.onload=function(){ init(); };
    	
    	function init(){
    		setPageTitle();
    		x_viewPlaylist(viewPlaylist_cb);
    		x_playlistInfo(plinfo_cb);
    		setPLTitle();
    		setCurrentPage();
    		setControls();
    		updateBox(page,0);
    	}
    	function empty_cb(new_data){
    	  
    	}
    	
    	function newWindow(type,id){
    		if(type == 'add')
    			newwindow = window.open('add.php','addmusic','height=400,width=500,scrollbars=yes,resizable=yes');
    		else if(type == 'download')
    			newwindow = window.open('download.php?id='+id,'download','height=200,width=350,scrollbars=yes,resizable=yes');

    		if (window.focus) {newwindow.focus()}
    	}
    	
    	function setPageNav(){
    		//document.getElementById("breadcrumb").innerHTML = prevpage;
    	}
    
    	function switchPage(newpage){
    		prevpage = page;
    		page = newpage;
    	
    		updateBox(page,0);
    		setPageTitle();
    		setCurrentPage();
    		//setPageNav();
    	}
    	
    	function switchMode(newmode){
    		if(newmode == mode){ //do nothing 
    		}
    		else{
    			x_switchMode(newmode,switchMode_cb);
    		}
    	}
    	
    	function setPLTitle(){
    		if(mode == 'streaming')
    			newmode = 'Streaming';
				if(mode == 'jukebox')
    			newmode = 'Jukebox';
    		document.getElementById("pl_title").innerHTML = newmode + " Playlist";
    	}
    	
    	function viewPlaylist_cb(new_data){
    		document.getElementById("playlist").innerHTML = new_data;
    	}
    	
    	function switchMode_cb(new_data){
    		mode = new_data;
    		setControls();
    		setPLTitle();
    		x_playlistInfo(plinfo_cb);
    		x_viewPlaylist(viewPlaylist_cb);
    	}
    	
    	function setCurrentPage(){
    		var x = document.getElementById('nav');
    		var y = x.getElementsByTagName('a');
    		for (var i=0;i<y.length;i++){
 					y[i].removeAttribute("class");
 					if(y[i].id == page)
 						y[i].setAttribute('class','c');
				}
    	}
    	
    	function getDropDown(type,id){
    		x_getDropDown(type,id,getDropDown_cb);
    	}
    	
    	function getDropDown_cb(new_data){
    		ul = document.getElementById("browse_ul");
    		ul.innerHTML = new_data;
    		ul.style.display = 'block';
    	}
    	
    	function closeDropDown(){
    		ul = document.getElementById("browse_ul");
    		ul.style.display = 'none';
    		ul.innerHTML = '';
    	}
    	
    	function setControls(){
    		if(mode=='streaming')
    				document.getElementById("controls").innerHTML = '<div class="current" id="current">&nbsp;<strong class="right">Streaming Mode Active</strong></div>';
				else{
					document.getElementById("controls").innerHTML = '<div class="buttons"><a href="#" onclick="play(\'prev\',nowplaying); return false;" title="Previous Song"><img src="img/rew_big.gif" /></a><a href="#" id="play" onclick="play(\'pl\',0); return false;" title="Play Playlist"><img src="img/play_big.gif" /></a><a href="#" id="stop" onclick="play(\'stop\',0); return false;" title="Stop Music"><img src="img/stop_big.gif" /></a><a href="#" onclick="play(\'next\',0); return false;" title="Next Song"><img src="img/ff_big.gif" /></a><br/><!--<img src="img/vol.gif" />--></div><div class="current" id="current"><span id="artist"></span><span id="song"></span></div>';
					refresh();
					setTimeout("refresh()", 10000);
				}
    	}
    	
    	function savePL(type,data){
    		if(type=='open'){
    			var save_form = "<h2>Save Playlist</h2><form onsubmit='return savePL(\"save\",this)' method='get' action=''><strong>Playlist Name</strong><br/><input type='text' name='save_pl_name' id='save_pl_name' size='25' /><br/><input type='checkbox' name='pl_private' id='pl_private' /> Private Playlist<br/><br/><input type='submit' value='save' /> <input type='button' onclick=\"savePL('close',0); return false;\" value='cancel' /></form> ";
    			document.getElementById("box_extra").innerHTML = save_form;
    			document.getElementById("box_extra").style.display = 'block';
    		}
    		else if(type=='save'){
    			var pl_name = data.save_pl_name.value;
    			var prvt = 0;
    			if(data.pl_private.checked==true)
    				prvt = 1;
    			x_savePlaylist(pl_name,prvt,save_Playlist_cb);
    			return false;
    		}
    		else if(type=='close')
    			document.getElementById("box_extra").style.display = 'none';
    	}
    	
    	function save_Playlist_cb(new_data){
    		box = document.getElementById("box_extra");
    		box.innerHTML = new_data;
    		setTimeout("box.style.display='none'","1250");
    	}
    	
    	function movePLItem(direction,item){
				var y;
				var temp;
    		if(direction == "up")
    			y = item.previousSibling;
    		else if(direction == "down")
					y = item.nextSibling;
					
				if(y && y.nodeName == 'LI'){	
    			pl_move(y.id,item.id);
    			
    			var temp = y.innerHTML;
    			y.innerHTML = item.innerHTML;
    			item.innerHTML = temp;
    			Fat.fade_element(y.id,null,900,'#ffcc99','#f3f3f3');
    		}
    	}
    	
    	function setBgcolor(id, c)
			{
				if(id != ('pl'+nowplaying)){
				var o = document.getElementById(id);
				o.style.backgroundColor = c;
				}
			}
			
    	function refresh_cb(new_data) {
    		if(new_data[0] == 1){
    		}	
    		else{
    			document.getElementById("current").innerHTML = new_data[0];
    			isplaying = new_data[2];
    			if(new_data[1] > 0){
    				
    				// highlight current song
    				oldsong = nowplaying;
    				nowplaying = new_data[1];
    				if(oldsong != 0 && oldsong != new_data[1]){
    						var old = document.getElementById('pl'+oldsong);
								old.removeAttribute('class');
								Fat.fade_element('pl'+oldsong,null,null,'#96D1EF','#f3f3f3');
								
								Fat.fade_element('pl'+new_data[1],null,1400,'#f3f3f3','#96D1EF');
    				}
    				var current = document.getElementById('pl'+new_data[1]);
    				
    				current.setAttribute('class','currentplay');
    				document.getElementById('stop').style.display = 'inline';
    				document.getElementById('play').style.display = 'none';
    				
    			}else if(nowplaying!=0 && isplaying==0){
    				document.getElementById('pl'+nowplaying).removeAttribute('class');
    				Fat.fade_element('pl'+nowplaying,null,null,'#96D1EF','#f3f3f3');
    				nowplaying = 0;
    				document.getElementById('stop').style.display = 'none';
    				document.getElementById('play').style.display = 'inline';
    			}
    			else if(isplaying==0){
    				document.getElementById('stop').style.display = 'none';
    				document.getElementById('play').style.display = 'inline';
    			}
    			else if(isplaying==1){
    					document.getElementById('stop').style.display = 'inline';
    				document.getElementById('play').style.display = 'none';
    			}
    		}
				setTimeout("refresh()", 20000);
			}
	
			function refresh(){
				if(mode=='jukebox'){
					var artist = document.getElementById("artist").innerHTML;
					var song = document.getElementById("song").innerHTML;
					x_getCurrentSong(artist,song,refresh_cb);
				}
			}
		
			function setPageTitle(){
				var pages= new Array()
				pages["browse"]="Browse Music";
				pages["search"]="Search Music";
				pages["prefs"]="User Account Preferences";
				pages["random"]="Create a Random Mix";
				pages["playlists"]="Load a Saved Playlist";
				pages["stats"]="Server Statistics";
				pages["admin"]="mp3act Administration";
				pages["about"]="About mp3act";
				document.getElementById("pagetitle").innerHTML = pages[page];
				
			}
			
			function getRandItems(type){
			  document.getElementById("breadcrumb").innerHTML = '';
				x_getRandItems(type,getRandItems_cb);
			}
			
			function getRandItems_cb(new_data){
				document.getElementById("rand_items").innerHTML = new_data;
			}
			
			function updateBox_cb(new_data){
				document.getElementById("info").innerHTML = new_data;
				document.getElementById("loading").style.display = 'none';
				
				if(clearbc==1)
					breadcrumb();
				clearbc = 1;
			
			}
			
			function updateBox(type,itemid){
				document.getElementById("loading").style.display = 'block';
				x_musicLookup(type,itemid,updateBox_cb);
				
				if(type == 'genre' || type == 'letter'){
					bc_parenttype = '';
					bc_parentitem = '';
				}
				else if(type == 'album' || (type == 'artist' && bc_parenttype != '')){
					if(bc_childtype == 'all'){
						bc_parenttype = bc_childtype;
						bc_parentitem = bc_childitem;
					}
				}
				else if(type == 'browse' || type == 'search' || type == 'about' || type == 'prefs' || type == 'random' || type == 'admin' || type == 'playlists' || type == 'stats'){

					bc_parenttype = '';
					bc_parentitem = '';
					itemid='';
					type='';
				}
				else{
					bc_parenttype = bc_childtype;
					bc_parentitem = bc_childitem;
				}
				
				bc_childitem = itemid;
				bc_childtype = type;
				
			
				
					
			}
			
			function deletePlaylist(id){
				if(confirm("Are you sure you want to DELETE THIS SAVED PLAYLIST?")){
					x_deletePlaylist(id,deletePlaylist_cb);
				}
			}
			
			function deletePlaylist_cb(new_data){
				// reload saved PL page
				clearbc = 0;
				x_musicLookup('playlists',0,updateBox_cb);
				setMsgText("Saved Playlist Successfully Deleted");
			}
			
			function plrem(item){
				x_playlist_rem(item,plrem_cb);
			}
			
			function plrem_cb(rem){
				p = document.getElementById("playlist");
				d_nested = document.getElementById(rem);
				throwaway_node = p.removeChild(d_nested);
				x_playlistInfo(plinfo_cb);
			}
			
			function pladd(type,id){
				x_playlist_add(type,id,pladd_cb);
			}
			
			function pladd_cb(new_data){
				
				if(new_data[0] == 1){
					x_viewPlaylist(viewPlaylist_cb);
    			x_playlistInfo(plinfo_cb);
				}
				else{
					document.getElementById("playlist").innerHTML += new_data[0];
					
					for(var i=2; i<new_data[1]+2; i++){
						Fat.fade_element(new_data[i],null,1400,'#B4EAA2','#f3f3f3');
					}
					x_playlistInfo(plinfo_cb);
				}
			}
			
			function pl_move(item1,item2){
				x_playlist_move(item1,item2,pl_move_cb);
			}
			
			function pl_move_cb(){
					// do nothing
			}
			
			function plclear(){
				x_clearPlaylist(plinfo_cb);
				document.getElementById("playlist").innerHTML = "";
			}
			
			function plinfo_cb(new_data){
				document.getElementById("pl_info").innerHTML = new_data;
			}
			
			function breadcrumb(){
					x_buildBreadcrumb(page,bc_parenttype,bc_parentitem,bc_childtype,bc_childitem,breadcrumb_cb);
			}
			
			function breadcrumb_cb(new_data){
				//if(new_data!="")
					document.getElementById("breadcrumb").innerHTML = new_data;
			}
			
			function play(type,id){
				if(mode == 'streaming'){
					document.getElementById('hidden').src = null;
					document.getElementById("hidden").src = "hidden.php?type="+type+"&id="+id;
				}
				else{ 
						x_play(mode,type,id,play_cb);
				}
			}
			
			function randPlay(data){
			  
				var type = data.random_type.value;
				if(type == ""){
					setMsgText("You must choose a random type");
					return false;
				}
				var num=0;
				if(mode == 'streaming')
					num = data.random_count.value;
				var items ='';
				if(type != 'all'){
					for(var i=0;i<data.random_items.options.length;i++){
						if(data.random_items.options[i].selected == true)
						 items += data.random_items.options[i].value+" ";
					}
					
					if(items == ""){
					  setMsgText("You must choose at least one random item");
					  return false;
					}
				}
				if(mode == 'streaming'){
					document.getElementById('hidden').src = null;
					document.getElementById("hidden").src = "hidden.php?type="+type+"&num="+num+"&items="+items;
				}
				else{ 
						x_randPlay(mode,type,num,items,play_cb);
				}
				return false;

			}
			
			function play_cb(new_data){
				refresh();
			}
			
			function showAlbumArt(mode){
				document.getElementById('bigart').style.display = mode;
			}
			
			function download(id){
				document.getElementById('hidden').src = null;
				document.getElementById("hidden").src = "hidden.php?type=dl"+"&id="+id;
			}
			
			function addmusic(form){
				document.getElementById("current").innerHTML = form.musicpath.value;
				return false;
			}
		
			  function adminAddUser(form){
				document.getElementById("breadcrumb").innerHTML = "";
	       
	      if(form!=""){
	        if(form.firstname.value == '' || form.lastname.value == '' || form.username.value == '' || form.password.value == '' || form.password2.value == '' || form.email.value == ''){
	          setMsgText("Required Fields Are Empty");
	          return false;
	        }
	        
	        if(form.password.value != form.password2.value){
	          setMsgText("Password Do Not Match");
	          document.getElementById("password").value = "";
	          document.getElementById("password2").value = "";
	          return false;
	        }
	        if(form.email.value.indexOf(".") <= 2 && form.email.value.indexOf("@") <= 0){
	          setMsgText("Email Address is Invalid");
	          document.getElementById("email").focus();
	          return false;
          }
	          x_adminAddUser(form.firstname.value,form.lastname.value,form.username.value,form.email.value,form.perms.value,form.password.value,adminAddUser_cb);
	          return false;
	       
	      }
	      else{
	        x_adminAddUser('','','','','','',updateBox_cb);
	      }
					
			
				return false;
			}

			function adminAddUser_cb(new_data){
				clearbc=0;
				if(new_data==1){
				  updateBox('admin',0);
					setMsgText("User Successfully Added");
				}else{
				  setMsgText("Username is Already Taken. Try Another.");
				  document.getElementById("username").value = "";
				  document.getElementById("username").focus();
				}
			}
			
			function adminEditUsers(user,action,form){
				document.getElementById("breadcrumb").innerHTML = "";
				if(user!=0){
					if(action == 'del'){
						if(confirm('Are you Sure you want to DELETE THE USER?')){
							x_adminEditUsers(user,action,adminEditUsers_cb);
						}
					
					}
					else if(action == 'mod'){
						x_adminEditUsers(user,'mod',form.active.value,form.perms.value,adminEditUsers_cb);
					}else{
						x_adminEditUsers(user,'user',updateBox_cb);
					}			
				}
				else{
					x_adminEditUsers(updateBox_cb);
				}
				return false;
			}
			
			function adminEditUsers_cb(new_data){
				clearbc=0;
				x_adminEditUsers(updateBox_cb);
				if(new_data==1){
					setMsgText("User Successfully Deleted");
						
				}
				if(new_data==2){
					setMsgText("User Successfully Updated");
				}
			}
			
			function setMsgText(text){
					document.getElementById("breadcrumb").innerHTML = "<span class='error'>"+text+"</span>";
					Fat.fade_element('breadcrumb',null,2000,'#F5C2C2','#ffffff');
			}
			
			function editSettings_cb(new_data){
				if(new_data == 1){
					clearbc = 0;
					updateBox('admin',0);
					setMsgText("New Settings Saved");
				}
			}
			
			function editSettings(form){
				if(form != 0){
					x_editSettings(1,form.invite.value,form.downloads.value,form.amazonid.value,form.upload_path.value,form.sample_mode.value,form.mp3bin.value,form.lamebin.value,form.phpbin.value,editSettings_cb);
				}
				else{
					x_editSettings(0,'','','','','','','','',updateBox_cb);
				}
				return false;
			}
			
			function editUser_cb(new_data){
				if(new_data == 1){
					clearbc = 0;
					updateBox('prefs',0);
					
					setMsgText("New Settings Saved");
				}
				
			}
			
			function editUser(type,form){
				if(form != 0){
					if(type == 'info'){
						x_editUser(type,form.firstname.value,form.lastname.value,form.email.value,0,'','','',editUser_cb);
					}
					else if(type == 'settings'){
						x_editUser(type,form.default_playmode.value,form.default_bitrate.value,form.default_stereo.value,form.theme_id.value,form.as_username.value,form.as_password.value,form.as_type.value,editUser_cb);
					}
					else if(type == 'pass'){
						if(form.new_password.value != form.new_password2.value){
								setMsgText("New Passwords Do Not Match");
						}else{
							document.getElementById("breadcrumb").innerHTML = "";
							x_editUser(type,form.old_password.value,form.new_password.value,'',0,'','','',editUser_cb);
						}
					}
				}else{
					x_editUser(type,'','','',0,'','','',updateBox_cb);
				}
				return false;
			}
			
			function searchMusic(form){
				if(form.searchbox.value == '' || form.searchbox.value == '[enter your search terms]'){
					setMsgText("You Must Enter Something to Search For");
				}
				else{
					document.getElementById("breadcrumb").innerHTML = "";
					x_searchMusic(form.searchbox.value,form.search_options.value,updateBox_cb);
				}
				return false;
			}
			
			function clearDB_cb(new_data){
				if(new_data == 1)
					setMsgText("Database Successfully Cleared");
			}
			
			function clearDB(){
			if(confirm("Are you sure you want to RESET THE MUSIC DATABASE? This will remove all data regarding music and music stastics.")){
				x_resetDatabase(clearDB_cb);
				}
			}
			
			function sendInvite(form){
				x_createInviteCode(form.email.value,sendInvite_cb);
				return false;
			}
			
			function sendInvite_cb(new_data){
				if(new_data == 1){
				  setMsgText("Invitation Successfully Sent");
				  document.getElementById("email").value = "";
				}
			}
			
			function submitScrobbler(userid){
			  x_submitScrobbler(userid,empty_cb);
			  setMsgText("AudioScrobbler Submission Attempted");
			  return false;
			}