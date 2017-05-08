function Speaker(parameters) {
  sessionStorage.clear();
  this.parameters = parameters;
  //http://www.openjs.com/scripts/jx/
  if (this.urlExists('assets/js/jx-3.01a.min.js')){
    this.path = '';
  } else {
    this.path = '../medvoice/';
  }
  if (typeof jx == 'undefined'){
    var imported = document.createElement('script');
    var self = this;
    imported.type = 'text/javascript';
    imported.src = this.path + 'assets/js/jx-3.01a.min.js';
    imported.onreadystatechange = function() {
      if (this.readyState == 'complete') helper();
    }
    imported.onload = self.load;
    document.head.appendChild(imported);
  }
  if (typeof this.parameters == 'object'){
    this.binder = (typeof this.parameters.binder == 'undefined') ? 'load' : this.parameters.binder;
    if (typeof this.parameters.secondaryTarget == 'object') {
      this.target = this.parameters.secondaryTarget;
    }

    if (typeof this.parameters.target == 'object') {
      this.binder = (this.binder == 'load') ? 'change' : this.binder;
    } else {
      this.target = window;
    }
  }
  console.log(parameters);
  if (this.binder == 'speak'){
    var tech;
    if (typeof this.parameters.target == 'undefined' && typeof this.value == 'undefined') {
      console.log('Invalid target');
      return false;
    }
    if (typeof this.parameters.tts != 'undefined'){
      switch (typeof this.parameters.tts.name){
        case 'object':
          tech = this.parameters.tts.name.value;
          break;
        case 'string':
          tech = this.parameters.tts.name;
          break;
        case 'undefined':
          tech = '';
      }
    } else {
      tech = '';
    }
    data = {
      parameters: {
        text: (this.binder == 'load' || 'speak') ? this.parameters.target : this.parameters.target.value,
        tts: {
          name: tech,
          lang: (typeof this.parameters.tts != 'undefined' && typeof this.parameters.tts.lang != 'undefined') ? this.parameters.tts.lang : ''
        }
      },
      repeat: (typeof this.parameters.repeat == 'undefined') ? 0 : this.parameters.repeat, //How many plays do you want (loop)
      interval: (typeof this.parameters.interval == 'undefined') ? 2000 : this.parameters.interval //ms interval between plays
    };
    if (!sessionStorage.getItem('audioSession'))
      this.speak(data);
    else
      console.log('Your audio session does not finished yet!');
  } else {
    this.target.addEventListener(this.binder, function(){
      var tech;
      if (typeof this.parameters.target == 'undefined' && typeof this.value == 'undefined') {
        console.log('Invalid target');
        return false;
      }
      switch (typeof this.parameters.tts.name){
        case 'object':
          tech = this.parameters.tts.name.value;
          break;
        case 'string':
          tech = this.parameters.tts.name;
          break;
        case 'undefined':
          tech = '';
      }
      data = {
        parameters: {
          text: (this.binder == 'load') ? this.parameters.target : this.parameters.target.value,
          tts: {
            name: tech,
            lang: (typeof this.parameters.tts.lang != 'undefined') ? this.parameters.tts.lang : ''
          }
        },
        repeat: (typeof this.parameters.repeat == 'undefined') ? 0 : this.parameters.repeat, //How many plays do you want (loop)
        interval: (typeof this.parameters.interval == 'undefined') ? 2000 : this.parameters.interval //ms interval between plays
      };
      if (!sessionStorage.getItem('audioSession'))
        this.speak(data);
      else
        console.log('Your audio session does not finished yet!');
    });
  }
}

Speaker.prototype.load = function(){
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
      jx.load(this.path + 'api/speaker.php?parameters=' + encodeURIComponent(JSON.stringify(parameters)), function(response){
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
  jx.load(this.path + 'api/speaker.php?parameters=' + encodeURIComponent(JSON.stringify(data.parameters)), function(response){
    if (response != null){
      playData = {
        audioAddress: response.address,
        repeat: data.repeat,
        interval: data.interval,
        uid: response.uid
      };
      this.audioPlay(playData);
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

Speaker.prototype.urlExists = function(url){
  var http = new XMLHttpRequest();
  http.open('HEAD', url, false);
  http.onreadystatechange = function(){
    if(request.readyState==4){
      return true;
    }else{
      return false;
    }
  }
}

