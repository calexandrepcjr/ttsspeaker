function Speaker(parameters) {
    sessionStorage.clear();
    var imported = document.createElement('script');
    //http://www.openjs.com/scripts/jx/
    imported.src = 'assets/js/jx-3.01a.min.js';
    document.head.appendChild(imported);

    if (typeof parameters == 'object'){
	this.binder = (typeof parameters.binder == 'undefined') ? 'load' : parameters.binder;
	if (typeof parameters.secondaryTarget == 'object') {
	    this.target = parameters.secondaryTarget;
	}
	
	if (typeof parameters.target == 'object') {
	    
	    this.binder = (this.binder == 'load') ? 'change' : this.binder;
	} else {
	    this.target = window;
	}
    }
    this.target.addEventListener(this.binder, function(){
	var tech;
	if (typeof parameters.target == 'undefined' && typeof this.value == 'undefined') {
	    console.log('Invalid target');
	    return false;
	}
	switch (typeof parameters.tts.name){
	    case 'object':
		tech = parameters.tts.name.value;
		break;
	    case 'string':
		tech = parameters.tts.name;
		break;
	    case 'undefined':
		tech = '';
	}
	 data = {
	     parameters: {
		 text: (this.binder == 'load') ? parameters.target : parameters.target.value,
		tts: {
		    name: tech,
		    lang: (typeof parameters.tts.lang != 'undefined') ? parameters.tts.lang : ''
		}
	    },         
	     repeat: (typeof parameters.repeat == 'undefined') ? 0 : parameters.repeat, //How many plays do you want (loop)
	     interval: (typeof parameters.interval == 'undefined') ? 2000 : parameters.interval //ms interval between plays
	 };
	if (!sessionStorage.getItem('audioSession'))
	    Speaker.prototype.speak(data);
	else
	    console.log('Your audio session does not finished yet!');
    });
}

Speaker.prototype.audioPlay = function(data){
    var audio = new Audio(data.audioAddress);
    audio.crossOrigin = 'anonymous';
    audio.addEventListener('ended', function() {
	if (data.repeat > 0){
	    setTimeout(function(){
		audio.currentTime = 0;
		audio.play();
		data.repeat--;
	    }, data.interval);
	} else {
	    this.pause();
	    parameters = {
		action: 'shutup',
		uid: data.uid
	    };
	    jx.load('api/speaker.php?parameters=' + encodeURIComponent(JSON.stringify(parameters)), function(response){
		if (response == 1) {
		    sessionStorage.clear();
		    console.log('Audio ended');
		} else {
		    console.log('Audio remotion routine broked!');
		}
	    }, 'json');
	}
    });

    if (!sessionStorage.getItem('audioSession')) {
	audio.play();
	sessionStorage.setItem('audioSession', false);
    }
    
}

Speaker.prototype.speak = function(data){
    data.parameters.action = 'speak';
    jx.load('api/speaker.php?parameters=' + encodeURIComponent(JSON.stringify(data.parameters)), function(response){
	if (response != null){
	    playData = {
		audioAddress: response.address,
		repeat: data.repeat,
		interval: data.interval,
		uid: response.uid
	    }; 
	    Speaker.prototype.audioPlay(playData);
	} else {
	    if (data.parameters.tts.lang == ''){
		console.log("API Error: Can't make the audio");
		return false;
	    } else {
		console.log("API Error: Inexistent language");
		return false;
	    }
	}
    }, 'json');
}
