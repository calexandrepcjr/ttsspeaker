<?php
class TTS {
    private $tts;
    private $technologies;
    private $uploadPath;
    private $uid;
    function __construct(){
        $this->loadTechs();
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
                    'lianetts' => array('verbosis' => 'lianetts -g 1 %address%'),
                    'espeak' => array('verbosis' => 'espeak -v %lang% -s 100 -w %address%'),
                    'oldespeak' => array('verbosis' => 'espeak -v %lang% -s 100 -w %address%')
                );
            }
        } else {
            $this->technologies = array(
                'lianetts' => array('verbosis' => 'lianetts -g 1 %address%'),
                'espeak' => array('verbosis' => 'espeak -v %lang% -s 100 -w %address%'),
                'oldespeak' => array('verbosis' => 'espeak -v %lang% -s 100 -w %address%')
            );
        }
    }

    private function shellSanitizer($verbosis = false) {
        if (!$verbosis){
            $verbosis = $this->tts['verbosis'];
        }
        $iniFileLocation = 'config/linuxcommands.ini';
        if ($verbosis && file_exists($iniFileLocation)){
            $shellCommands = parse_ini_file($iniFileLocation, true);
            $totalShellCommands = count($shellCommands['shell']);
            if ($totalShellCommands > 0){
                $initialVerbosis = explode(' ', $verbosis);
                if (in_array($initialVerbosis[0], $shellCommands['shell'])){
                    echo json_encode('Invalid verbosis - Your verbosis contain a dangerous command that can harm your server.');
                    exit();
                } else {
                    return $verbosis;
                }
            } else {
                echo json_encode('You can\'t run this class without the list of shell commands. Create a .ini file with all linux shell commands that can possibly harm your server');
                exit();
            }
        } else {
            echo json_encode('You can\'t run this class without the list of shell commands. Create a .ini file with all linux shell commands that can possibly harm your server');
            exit();
        }
    }

    private function parser() {
        $replace = array(
            '%address%' => $this->uploadPath['path'] . "{$this->uid}.wav '{$this->text}'",
            '%lang%' => $this->tts['lang']
        );

        $this->tts['verbosis'] = escapeshellcmd($this->str_replace_assoc($replace, $this->shellSanitizer()));
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
        } else {
            echo json_encode('Wrong argument');
            exit();
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

}
