<?php
include_once('Speaker.class.php');
$speaker = new Speaker($_REQUEST);
switch($speaker->getAction()){
case 'speak':
    $speaker->getSpeak();
    break;
case 'shutup':
    $speaker->removeSpeak();
    break;
}
