<?php
class Speaker {
    private $text;
    private $action;
    private $technology;
    private $uid;

    function __construct($request, $technology = 'lianetts'){
	$requestParameters = json_decode($request['parameters']);
	$this->technology = $technology;
	$this->text = $requestParameters->text;
	$this->action = $requestParameters->action;
	$this->uid = $request['uid'];

	if (!$this->isTechnologyAvailable())
	    exit("This technology isn't available in this server");
    }

    private function isAjax() {
	if ($this->uid) {
	    return true;
	} else {
	    return false;
	}
    }

    private function isTechnologyAvailable() {
	$techStatus = shell_exec("which $this->technology");
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

    public function getSpeak(){
	switch($this->technology){
	    case 'lianetts':
		exec("lianetts -g 1 ../mediapool/{$this->uid}.wav $this->text");
		break;
	    case 'espeak':
		exec("espeak -v mb-br4 -s 110 -w ../mediapool/{$this->uid}.wav '{$this->text}'");
		break;
	}
    }
}
