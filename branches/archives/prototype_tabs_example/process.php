<?php

switch($_GET['id']) {
  case 'menuSearch':
    $content = 'Search';
    break;
  case 'menuBrowse':
    $content = 'Browse';
    break;
  case 'menuAdd':
    $content = 'Add';
    break;
  case 'menuDelete':
    $content = 'Support this artist.';
    break;
  case 'menuAbout':
    $content = 'About Us';
    break;
  default:
    $content = 'Unhandled Menu Item...';
    break;								
} 
print $content;

?>

