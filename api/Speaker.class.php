<?php
include_once('TTS.class.php');
class Speaker {
    private $action;
    private $tts;
    private $uid;
    private $path;

    function __construct($request = false){
        if ($request){
            $requestParameters = json_decode($request['parameters']);
            $this->uid = (isset($requestParameters->uid)) ? $requestParameters->uid : $request['uid'];
            $this->action = $requestParameters->action;
            if ($this->action == 'speak'){
                $this->tts = new TTS();
                $this->tts->setUID($this->uid);
                if (isset($requestParameters->path)){
                    $this->path = array('path' => $requestParameters->path->name, 'link' => $requestParameters->path->link);
                    $this->tts->setPath($this->path);
                } else {
                    $this->path = $this->tts->getPath();
                }
                $this->checkPath();
                $this->tts->createTTS($requestParameters);
            } else {
                $this->path = $requestParameters->address;
            }
        }
    }

    private function checkPath() {
        if (!file_exists($this->path['path'])) {
            mkdir($this->path['path'], 0777);
        } else {
            if (!(is_readable($this->path['path']) && is_writable($this->path['path']))){
                chmod($this->path['path'], 0777);
            }
        }
    }

    private function delAudioFile() {
        if ($this->path != null){
            $this->path = explode('/', $this->path);
            $lastElementPath = count($this->path) - 1;
            $audioFilename = $this->path[$lastElementPath];
            exec("rm -f " . $this->path['path'] . $audioFilename);
            if (!file_exists($this->path['path'] . $audioFilename))
                echo json_encode(1);
            else
                echo json_encode(0);
        } else {
            echo json_encode(0);
        }
    }


    public function getAction() {
        return $this->action;
    }

    public function getSpeak(){
        $this->tts->getAudioSource();
    }

    public function removeSpeak(){
        $this->delAudioFile();
    }
}
