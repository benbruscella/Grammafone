<?php // hidden iframe to process streaming
include("base.inc.php");

if($_GET['mode'] == 'player'){
    $mode = 'player';
}
else {
    $mode = 'streaming';
}

/* Play the Music */
if(isset($_GET['type'])){
    if($_GET['type'] == 'artists' || $_GET['type'] == 'genre' || $_GET['type'] == 'albums' || $_GET['type'] == 'all'){
        echo randPlay($mode,$_GET['type'],$_GET['num'],$_GET['items']);
    }
    else{
        echo play($mode,$_GET['type'],$_GET['id']);
    }
}
?>


