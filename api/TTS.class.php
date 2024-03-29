<?php
class TTS {
    private $tts;
    private $technologies;
    private $uploadPath;
    private $uid;
    const PATH = '../mediapool/';
    const LINKPATH = 'mediapool/';
    function __construct(){
        $this->loadTechs();
        $this->setPath();
    }

    public function createTTS($request = false) {
        if ($request && is_object($request)){
            if (!$this->isAvailable($request->tts->name)){
                echo json_encode('This TTS isn\'t available in this server');
                exit();
            }

            $this->tts = array( 'name' => (isset($request->tts->name) && $request->tts->name != '') ? $request->tts->name : 'lianetts',
                'verbosis' => (isset($request->tts->verbosis) && $request->tts->verbosis != '') ? $request->tts->verbosis : $this->technologies[$request->tts->name]['verbosis']
            );
            switch($request->tts->name){
            case 'espeak':
            case 'oldespeak':
                if ($request->tts->lang != '') {
                    $this->tts['lang'] = $request->tts->lang;
                } else {
                    $this->tts['lang'] = 'mb-br4';
                }
                break;
            }
            $this->setText($request->text);
            $this->parser();
        } else {
            echo json_encode('Absence or Invalid parameters');
            exit();
        }
    }

    public function setPath($path = false) {
        if ($path){
            $this->uploadPath = $path;
        } else if(!isset($this->uploadPath)) {
            $this->uploadPath = array('path' => self::PATH, 'link' => self::LINKPATH);
        }
    }

    public function getPath() {
        if (isset($this->uploadPath)){
            return $this->uploadPath;
        } else {
            return array('path' => self::PATH, 'link' => self::LINKPATH);
        }
    }
    public function setUID($uid = false) {
        if ($uid){
            $this->uid = $uid;
        } else {
            echo json_encode('Wrong argument');
            exit();
        }
    }

    public function setText($text = null) {
        //In the future, implement full roman to arabic conversion
        if ($text != null){
            $replace = array(
                ' I' => ' 1',
                ' I ' => ' 1 ',
                ' II' => ' 2',
                ' II ' => ' 2 ',
                'III' => '3',
                'IV' => '4',
                ' V' => ' 5',
                ' V ' => ' 5 '
            );
            $this->text = $this->str_replace_assoc($replace, $text);
        }
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

    public function getAudioSource() {
        if ($this->generateAudio()){
            if (file_exists($this->uploadPath['path'] . "{$this->uid}.wav")){
                if ($this->isAjax()){
                    echo json_encode(array('address' => $this->uploadPath['link'] . "{$this->uid}.wav", 'uid' => $this->uid));
                    exit();
                } else {
                    return array('address' => $this->uploadPath['link'] . "{$this->uid}.wav", 'uid' => $this->uid);
                }
            } else {
                if ($this->isAjax()){
                    echo json_encode(array('message' => 'Audio generation error occurred', 'status' => 500));
                    exit();
                } else {
                    return array('message' => 'Audio generation error occurred', 'status' => 500);
                }
            }
        } else {
            if ($this->isAjax()){
                echo json_encode(array('message' => 'Audio generation error occurred', 'status' => 500));
                exit();
            } else {
                return array('message' => 'Audio generation error occurred', 'status' => 500);
            }
        }
    }

    public function getAvailable() {
        $availableTTSInServer = $this->array_filter_key($this->technologies, array($this, 'isAvailable'));
        if (isset($availableTTSInServer) && is_array($availableTTSInServer)){
            if (is_array($this->tts) && isset($availableTTSInServer[$this->tts['name']])){
                return $this->tts;
            } elseif (count($availableTTSInServer) > 0){
                return $availableTTSInServer;
            } else {
                echo json_encode('Your TTS choice is not available in this server');
                exit();
            }
        } else {
            echo json_encode('There is not any TTS available in this server');
            exit();
        }
    }

    /*Can't use full property of array_filter below 5.6 version ;-;
      Author @h4cc <https://gist.github.com/h4cc>
      https://gist.github.com/h4cc/8e2e3d0f6a8cd9cacde8
     */
    private function array_filter_key(array $array, $callback) {
        $matchedKeys = array_filter(array_keys($array), $callback);
        return array_intersect_key($array, array_flip($matchedKeys));
    }

    // Author Wes Foster http://php.net/manual/pt_BR/function.str-replace.php#95198
    private function str_replace_assoc(array $replace, $subject) {
        return str_replace(array_keys($replace), array_values($replace), $subject);
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
        //Some TTS in the config won't have same name than the verbosis, if fails, try to validate the verbosis
        if (empty($ttsStatus)){
            $ttsVerbosis = explode(' ', $this->technologies[$ttsName]['verbosis']);
            $ttsStatus = shell_exec("which $ttsVerbosis[0]");
        }
        return !empty($ttsStatus);
    }

    private function loadTechs() {
        $iniFileLocation = '../config.ini';
        if (file_exists($iniFileLocation)){
            $iniTechs = parse_ini_file($iniFileLocation, true);
            if (is_array($iniTechs)){
                $this->technologies = $iniTechs;
            } else {
                $this->technologies = array(
                    'lianetts' => array('verbosis' => 'lianetts -g 1 %address% %text%'),
                    'espeak' => array('verbosis' => 'espeak -v %lang% -s 100  %text% -w %address%'),
                    'oldespeak' => array('verbosis' => 'espeak -v %lang% -s 110 %text% -w %address% | mbrola -e /usr/share/mbrola/br4/br4 - %address%')
                );
            }
        } else {
            $this->technologies = array(
                'lianetts' => array('verbosis' => 'lianetts -g 1 %address% %text%'),
                'espeak' => array('verbosis' => 'espeak -v %lang% -s 100  %text% -w %address%'),
                'oldespeak' => array('verbosis' => 'espeak -v %lang% -s 110 %text% -w %address% | mbrola -e /usr/share/mbrola/br4/br4 - %address%')
            );
        }
    }

    private function shellSanitizer($verbosis = false) {
        if (!$verbosis){
            $verbosis = $this->tts['verbosis'];
        } else {
            echo json_encode('API TTS Error - There\'s no verbosis loaded');
        }
        $iniFileLocation = 'config/linuxcommands.ini';
        if ($verbosis && file_exists($iniFileLocation)){
            $shellCommands = parse_ini_file($iniFileLocation, true);
            $totalShellCommands = count($shellCommands['shell']);
            if ($totalShellCommands > 0){
                $itensVerbosis = explode(' ', $verbosis);
                foreach ($itensVerbosis as $iVerbosis) {
                    if (in_array($iVerbosis, $shellCommands['shell'])){
                        echo json_encode('API TTS ERROR Invalid verbosis - Your verbosis contain a dangerous command that can harm your server.');
                        exit();
                    }
                }
                return $verbosis;
            } else {
                echo json_encode('API TTS ERROR - You can\'t run this class without the list of shell commands. Create a .ini file with all linux shell commands that can possibly harm your server');
                exit();
            }
        } else {
            echo json_encode('API TTS ERROR - You can\'t run this class without the list of shell commands. Create a .ini file with all linux shell commands that can possibly harm your server');
            exit();
        }
    }

    private function parser() {
        $replace = array(
            '%address%' => $this->uploadPath['path'] . "{$this->uid}.wav",
            '%text%' => "'{$this->text}'",
            '%lang%' => $this->tts['lang']
        );

        $this->tts['verbosis'] = html_entity_decode($this->str_replace_assoc($replace, $this->shellSanitizer()), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function generateAudio() {
        $currentTTS = $this->get();
        $shellResponse = shell_exec($currentTTS['verbosis']);
        return empty($shellResponse);
    }
}
