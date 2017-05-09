<?php
include_once('TTS.class.php');
class Speaker {
    private $action;
    private $tts;
    private $uid;
    private $path;
    const PATH = '../mediapool/';
    const LINKPATH = 'mediapool/';

    function __construct($request = false){
        if ($request){
            $requestParameters = json_decode($request['parameters']);
            $this->uid = (isset($requestParameters->uid)) ? $requestParameters->uid : $request['uid'];
            $this->action = $requestParameters->action;
            if ($this->action == 'speak'){
                $this->tts = new TTS();
                $this->tts->setUID($this->uid);
                $this->path = (isset($requestParameters->path)) ? array('path' => $requestParameters->path->name, 'link' => $requestParameters->path->link) : array('path' => self::PATH, 'link' => self::LINKPATH);
                $this->tts->setPath($this->path);
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
            exec("rm -f " . self::PATH . $audioFilename);
            if (!file_exists(self::PATH . $audioFilename))
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
        $currentTTS = $this->tts->get();
        $return = shell_exec($currentTTS['verbosis']);

        if (file_exists($this->path['path'] . "{$this->uid}.wav")){
            echo json_encode(array('address' => $this->path['link'] . "{$this->uid}.wav", 'uid' => $this->uid));
        } else {
            echo json_encode($return);
        }
    }

    public function removeSpeak(){
        $this->delAudioFile();
    }
}
