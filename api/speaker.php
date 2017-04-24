<?php
    include_once('speaker.class.php');
$speaker = new Speaker($_REQUEST, 'espeak');
    switch($speaker->getAction()){
        case 'speak':
            $speaker->getText();
            $speaker->getSpeak();
            break;
    }
