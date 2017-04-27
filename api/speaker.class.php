<?php
class Speaker {
    private $text;
    private $action;
    private $technology;
    private $uid;
    const PATH = '../mediapool/';
    const LINKPATH = 'mediapool/';
    private $availableTechs = array(
        'lianetts' => array('verbosis' => 'lianetts -g 1 %address%'),
        'espeak' => array('verbosis' => 'espeak -v %lang% -s 100 -w %address%')
    );
    
     function __construct($request = false){
         if ($request){
             $requestParameters = json_decode($request['parameters']);
             $this->technology = array( 'name' => (isset($requestParameters->technology->name) && $requestParameters->technology->name != '') ? $requestParameters->technology->name : 'lianetts'
             );
             switch($requestParameters->technology->name){
                 case 'espeak':
                     if ($requestParameters->technology->lang != '') {
                         $this->technology['lang'] = $requestParameters->technology->lang;
                     } else {
                         $this->technology['lang'] = 'mb-br4';
                     }
                     break;
             }
             $this->text = $requestParameters->text;
             $this->action = $requestParameters->action;
             $this->uid = (isset($requestParameters->uid)) ? $requestParameters->uid : $request['uid'];

             if (!$this->isTechnologyAvailable())
                 exit("This technology isn't available in this server");
         }
     }
    
    //Can't use full property of array_filter below 5.6 version ;-;
    private function array_filter_key(array $array, $callback) {
        $matchedKeys = array_filter(array_keys($array), $callback);
        return array_intersect_key($array, array_flip($matchedKeys));
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

     private function isAjax() {
         if ($this->uid) {
             return true;
         } else {
             return false;
         }
     }

     private function isTechnologyAvailable($tech = false) {
         $tech = (!$tech) ? $this->technology['name'] : $tech;
         $techStatus = shell_exec("which $tech");
         return !empty($techStatus);
     }

     public function getText() {
         if ($this->isAjax())
             echo json_encode(array('text' => $this->text));
         else
             return $this->text;
     }

     public function setText($text = null) {
         if ($text != null)
             $this->text = $text;
     }

     public function getAction() {
         return $this->action;
     }

    public function getAvailableTechs($tech = false) {
        $availableTechsInServer = $this->array_filter_key($this->availableTechs, array($this, 'isTechnologyAvailable'));
        return ($tech) ? $this->availableTechs[$tech] : $this->availableTechs;
    }
    
    public function getSpeak(){
        $currentTech = $this->getAvailableTechs($this->technology['name']);
        if ($this->technology['lang'] != '' && $this->technology['name'] == 'espeak'){
            $currentTech['verbosis'] = str_replace('%lang%', $this->technology['lang'], $currentTech['verbosis']);
        }
        $return = shell_exec(str_replace('%address%', self::PATH . "{$this->uid}.wav '{$this->text}'", $currentTech['verbosis']));
        
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
