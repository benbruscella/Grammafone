var widths = new Array(20,14,200,50,100,100,"*");
function fillWithEmptyRows(){
	pixelsPerRow = 20;
	pixelsUsedFromPlaylist = $(".playlistItem").length*pixelsPerRow;
	maxRows = Math.ceil(($(document).height()-90-pixelsUsedFromPlaylist)/pixelsPerRow);
	$(".dummyCell").empty();
	rowCounter = 1;
	for (var i=0;i<maxRows;i++){
		var row = $('<tr class="dummyCell"></tr>');
		if (rowCounter%2==0){
			row.attr("class","dummyCell evenRow");
		}
		for (var j=0;j<7;j++){
			var col = $('<td width="'+widths[j]+'">&nbsp;</td>');
			if (j>0)
				col.attr("class","playlistCell");
			col.appendTo(row);
		}
		row.appendTo('#playlistContent');	
		rowCounter++;
	}
}



function setupInterface(){
	$("#playlistGridBox").height(0);
	fillWithEmptyRows();
	$("#playlistGridBox").height($(document).height()-90);
	$("#playlistGridBox").css("left",$("#playlistBox").width()+1);
	$("#playlistGridBox").width($(document).width()-$("#playlistBox").width()-2);
	$("#playlistGridBox").noSelect();

	$("#playlistBox").height($("#playlistGridBox").height()-$("#nowPlayingBox").height());
	$("#playlistBox").noSelect();
	$("#nowPlayingBox").css("top",$("#playlistBox").height()+$("#topBar").height());
	
	
	$("#pbarContainer").width($("#progressTable").width()-150);
	maxWidth = $("#pbarContainer").width();
}
$(window).resize(function(){
	setupInterface();
});