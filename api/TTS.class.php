<?php
class TTS {
    private $tts;
    private $availableTTS = array(
        'lianetts' => array('verbosis' => 'lianetts -g 1 %address%'),
        'espeak' => array('verbosis' => 'espeak -v %lang% -s 100 -w %address%')
    );
    private $uploadPath;
    private $uid;
    function __construct(){
    }
    
    /*Can't use full property of array_filter below 5.6 version ;-;
      Author @h4cc <https://gist.github.com/h4cc>
      https://gist.github.com/h4cc/8e2e3d0f6a8cd9cacde8
     */
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
    
    private function isAvailable($ttsName = false) {
         $ttsName = (!$ttsName) ? $this->tts['name'] : $ttsName;
         $ttsStatus = shell_exec("which $ttsName");
         return !empty($ttsStatus);
     }

    
    private function parser() {
        if ($this->tts['lang'] != '' && $this->tts['name'] == 'espeak'){
            $this->tts['verbosis'] = str_replace('%lang%', $this->tts['lang'], $this->tts['verbosis']);
        }
        $this->tts['verbosis'] = str_replace('%address%', $this->uploadPath['path'] . "{$this->uid}.wav '{$this->text}'", $this->tts['verbosis']);
    }

    public function set($request = false) {
        if ($request && is_object($request)){
            if (!$this->isAvailable($request->tts->name))
                 exit("This TTS isn't available in this server");

            $this->tts = array( 'name' => (isset($request->tts->name) && $request->tts->name != '') ? $request->tts->name : 'lianetts',
                                'verbosis' => (isset($request->tts->verbosis) && $request->tts->verbosis != '') ? $request->tts->verbosis : $this->availableTTS[$request->tts->name]['verbosis'] 
            );
             switch($request->tts->name){
                 case 'espeak':
                     if ($request->tts->lang != '') {
                         $this->tts['lang'] = $request->tts->lang;
                     } else {
                         $this->tts['lang'] = 'mb-br4';
                     }
                     break;
             }
             $this->text = $request->text;
             $this->parser();
        } else {
            exit('Absence or Invalid parameters');
        }
    }

    public function setPath($path = false) {
        if ($path)
            $this->uploadPath = $path;
        else
            exit('Wrong argument');
    }

    public function setUID($uid = false) {
        if ($uid)
            $this->uid = $uid;
        else
            exit('Wrong argument');        
    }

     public function setText($text = null) {
         if ($text != null)
             $this->text = $text;
     }

    public function get() {
        return $this->getAvailable();
    }

     public function getText() {
         if ($this->isAjax())
             echo json_encode(array('text' => $this->text));
         else
             return $this->text;
     }

    public function getAvailable() {
        $availableTTSInServer = $this->array_filter_key($this->availableTTS, array($this, 'isAvailable'));
        if (isset($availableTTSInServer) && is_array($availableTTSInServer)){
            if (is_array($this->tts) && isset($availableTTSInServer[$this->tts['name']])){
                return $this->tts;
            } elseif (count($availableTTSInServer) > 0){
                return $availableTTSInServer;
            } else {
                exit('Your TTS choice is not available in this server');
            }
        } else {
            exit('There is not any TTS available in this server');
        }
    }

}