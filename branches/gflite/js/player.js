function sendEvent(swf,typ,prm) {
        thisMovie(swf).sendEvent(typ,prm);
};

function getUpdate(typ,pr1,pr2,swf) {
    if(typ == 'state') {
        switch(pr1) {
            case 0:
//                alert('Stop');
            break;
            case 1:
//                alert('Buffering');
//                document.getElementById("loading").style.display = 'none'; 
            break;
            
            case 2:
//                alert('Playing');
            break;

            case 3:
//                alert("Case 3");	
            break;
            
            default:
                alert('Note, pLayer state is: '+pr1);
        }        
    }
};
function play() {
    loadFile('player', {file:'http://gflite.grammafone.com/playlist.xml'});
    sendEvent('player', 'playpause');
}

function thisMovie(swf) {
        if (navigator.appName.indexOf("Microsoft") != -1) {
                return window[swf];
        } else {
                return document[swf];
        }
};

function loadFile(swf,obj) {
        thisMovie(swf).loadFile(obj);
};

