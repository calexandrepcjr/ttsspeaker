<?php
    include_once('speaker.class.php');
    $speaker = new Speaker($_REQUEST);
    switch($speaker->getAction()){
        case 'speak':
            $speaker->getSpeak();
            break;
    }
