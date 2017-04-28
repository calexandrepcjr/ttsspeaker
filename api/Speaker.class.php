<?php
include_once('TTS.class.php');
class Speaker {
    private $action;
    private $tts;
    private $currentTTS;
    private $uid;
    const PATH = '../mediapool/';
    const LINKPATH = 'mediapool/';
    
     function __construct($request = false){
         $this->checkPath();
         if ($request){
             $requestParameters = json_decode($request['parameters']);
             $this->uid = (isset($requestParameters->uid)) ? $requestParameters->uid : $request['uid'];
             $this->action = $requestParameters->action;
             if ($this->action == 'speak'){
                 $this->tts = new TTS();
                 $this->tts->setUID($this->uid);
                 $this->tts->setPath(array('path' => self::PATH, 'link' => self::LINKPATH));
                 $this->tts->set($requestParameters);
                 $this->currentTTS = $this->tts->get();
             }
         }
     }

    private function checkPath() {
        if (!file_exists(self::PATH)) {
            mkdir(self::PATH, 0777);
        } else {
            if (!(is_readable(self::PATH) && is_writable(self::PATH))){
                chmod(self::PATH, 0777);
            }
        }
    }

    private function delAudioFile() {
        if ($this->uid != null && is_numeric($this->uid)){
            $file = self::PATH . $this->uid . '.wav';
            exec("rm -f " . $file);
            if (!file_exists($file))
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
        $return = shell_exec($this->currentTTS['verbosis']);

        if (file_exists(self::PATH . "{$this->uid}.wav")){
            echo json_encode(array('address' => self::LINKPATH . "{$this->uid}.wav", 'uid' => $this->uid));
        } else {
            echo json_encode($return);
        }
    }

    public function removeSpeak(){
        $this->delAudioFile();
    }
}
