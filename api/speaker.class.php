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
        'espeak' => array('verbosis' => 'espeak -v mb-br4 -s 100 -w %address%')
    );
    
     function __construct($request = false){
         if ($request){
             $requestParameters = json_decode($request['parameters']);
             $this->technology = (isset($requestParameters->technology) && $requestParameters->technology != '') ? $requestParameters->technology : 'lianetts';
             $this->text = $requestParameters->text;
             $this->action = $requestParameters->action;
             $this->uid = $request['uid'];

             if (!$this->isTechnologyAvailable())
                 exit("This technology isn't available in this server");
         }
     }
    
    //Can't use full property of array_filter below 5.6 version ;-;
    private function array_filter_key(array $array, $callback) {
        $matchedKeys = array_filter(array_keys($array), $callback);
        return array_intersect_key($array, array_flip($matchedKeys));
    }

     private function isAjax() {
         if ($this->uid) {
             return true;
         } else {
             return false;
         }
     }

     private function isTechnologyAvailable($tech = false) {
         $tech = (!$tech) ? $this->technology : $tech;
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
        $currentTech = $this->getAvailableTechs($this->technology);
        exec(str_replace('%address%', self::PATH . "{$this->uid}.wav '{$this->text}'",$currentTech['verbosis']));
        
        if (file_exists(self::PATH . "{$this->uid}.wav")){
            echo json_encode(self::LINKPATH . "{$this->uid}.wav");
        }
    }
}
