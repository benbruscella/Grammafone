<%@ Page Language="C#" MasterPageFile="~/Views/Shared/Site.Master" Inherits="System.Web.Mvc.ViewPage" %>

<asp:Content ID="indexTitle" ContentPlaceHolderID="TitleContent" runat="server">
    Home Page
</asp:Content>

<asp:Content ID="indexContent" ContentPlaceHolderID="MainContent" runat="server">
	<script>
	    $(document).ready(function(){
			init();		
		});
	</script>

<table width="100%" border="0" cellspacing="0" cellpadding="0" id="topBar">
  <tr>
	<td  width="50">
    	&nbsp;
    </td>
    <td  width="242" align="center">
    	<table id="controls" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td>
					<img id="prev" src="Content/ajtunes_prev_disabled.jpg" width="35" height="71" alt="Ajtunes Prev">
				</td>
				<td>
					<img id="playstop" src="Content/ajtunes_play.jpg" width="42" height="71" isPlay="true">
				</td>
				<td>
					<img id="next" src="Content/ajtunes_next_disabled.jpg" width="35" height="71" alt="Ajtunes Next">
				</td>
				<td width="30">
					<img id="volMin" src="Content/ajtunes_vol_min.jpg" width="30" height="71" alt="Ajtunes Vol Min">
				</td>
				<td id="volume" width="70" background="Content/ajtunes_vol_bar.jpg">
					<img id="slider" src="Content/ajtunes_vol_slider.jpg" width="13" height="71" hasBeenMoved="false">
				</td>
				<td width="30">
					<img id="volMax" src="Content/ajtunes_vol_max.jpg" width="30" height="71" alt="Ajtunes Vol Max">
				</td>
			</tr>
		</table>
    </td>
    <td width="11" height="71"><img src="Content/ajtunes_progress_c1.jpg" width="11" height="71"></td>
    <td background="Content/ajtunes_progress_c2.jpg" valign="middle" id="progressTable" >
	<table align="center"  cellspacing="0" cellpadding="0">
		<tr>
			<td id="songTitle" align="center" colspan="3" class="songTitle">
				
			</td>
		</tr>
		<tr>
			<td>
				<div id="pbarCompletedStatus" align="center"></div>
			</td>
			<td align="center">
				<img src="Content/ajtunes_logo.jpg" id="logo">
				<div id="pbarContainer" align="center">
					<div id="pbarCompleted"></div>
					<div id="pbarLeft"></div>
				</div>
			</td>
			<td>
				<div id="pbarLeftStatus" align="center"></div>
			</td>
		</tr>
	</table>
	</td>
    <td width="11" height="71"><img src="Content/ajtunes_progress_c3.jpg" width="11" height="71"></td>
    <td background="Content/ajtunes_topbar.jpg" width="200">&nbsp;</td>
  </tr>
</table>
	
	<div style="background-color:#D1D7E2;width:250px;border-right:1px solid #404040;position:absolute" valign="top" id="playlistBox">
		<table>
			<tr>
				<td width="250" align="left">
					<img src="Content/ajtunes_library.jpg" width="76" height="25" alt="Ajtunes Library">
				</td>
			</tr>
				<tr>
					<td width="250" align="left">
						<table width="100%" cellspacing="0" cellpadding="0" border="0">
							<tr id="libraryMusic" class="playlistTitle">
								<td width="30" align="right" style="padding:3px"><img src="Content/ajtunes_playlist.gif"/></td>
								
								<td valign="middle" style="padding:3px" >Music</td>
							</tr>
						</table>
					</td>
				</tr>
			<tr>
				<td width="250" align="left">
					<img src="Content/ajtunes_playlists.jpg" width="76" height="25" alt="Ajtunes Playlists">
				</td>
			</tr>
			<tr>
				<td width="250" align="left">
					<table id="playlists" width="100%" cellspacing="0" cellpadding="0" border="0">
					</table>
				</td>
			</tr>
		</table>
	</div>
	
	<div id="playlistGridBox" style="position:absolute">
		<table id="playlistPanel" width="100%" cellspacing="0" cellpadding="0">
			<thead>
		    		<tr>
						<td class="playlistColumn" width="34" colspan="2">&nbsp;</td>
		            	<td class="playlistColumn" width="200">Name</td>
		            	<td class="playlistColumn" align="right" width="50">Time</td>
		            	<td class="playlistColumn"  width="100">Artist</td>
		            	<td class="playlistColumn"  width="100">Album</td>
						<td class="playlistColumn" >&nbsp;</td>
		            </tr>
		    </thead>
		    <tbody id="playlistContent">
		    </tbody>
		</table>
	</div>

	<div id="nowPlayingBox">
		<table>
			<tr>
				<td id="nowPlaying" class="playlistColumn" width="250" align="center">
					Now playing
				</td>
			</tr>
			<tr>
				<td width="250" height="250" align="center" valign="middle" class="nowPlaying">
					<span id="nothing">
						Nothing<br/>
						Playing
					</span>
					<img src="" width="244" height="244" id="cover" style="display:none">
				</td>
			</tr>
		</table>
	</div>
</asp:Content>
