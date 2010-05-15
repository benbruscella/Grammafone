function init () {
  var tabs = document.getElementsByClassName('menu');
  for (var i = 0; i < tabs.length; i++) {
    $(tabs[i].id).onclick = function () {
      getMenuData(this.id);
    }
  }
}

function getMenuData(id) {
  var parms = 'id=' + id;
  if (id == 'menuAdd') {
    newwindow = window.open('addMusic.php','addmusic','height=400,width=500,scrollbars=yes,resizable=yes');
  }
  else {
    var url = 'process.php';
    var myAjax = new Ajax.Request( url, {
                                    method: 'get', 
                                    parameters: parms, 
                                    onCreate: _showLoad, 
                                    onComplete: _showResponse,
                                    onSuccess: _clearLoad
                                  } );
  }
}













/*******************************************************
 * Private Functions
 *******************************************************/
function _showLoad () {
  console.log("_showLoad");
  $('load').style.display = 'block';
}
function _clearLoad () {
  console.log("_clearLoad");
  $('load').style.display = 'none';
}
function _showResponse (originalRequest) {
  console.log("_showResponse");
  var newData = originalRequest.responseText;
  $('content').innerHTML = newData;
}

