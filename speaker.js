function Speaker(parameters) {
  sessionStorage.clear();
  this.parameters = parameters;
  var self = this;
  //http://www.openjs.com/scripts/jx/
  this.urlExists('assets/js/jx-3.01a.min.js').then(function(exists) {
    this.baseUrl = [];
    if (exists){
      self.path = '';
    } else {
      this.pathname = window.location.pathname.split('/');
      //Normally in the dev environment the project will be inside a path into htdocs dir
      if (window.location.host == 'localhost'){
        this.subDirs = this.pathname.length - 3;
      } else {
        this.subDirs = this.pathname.length - 2;
      }
      this.baseUrl = '../';
      this.baseUrl = this.baseUrl.repeat(this.subDirs);
      self.path = this.baseUrl + 'medvoice/';
    }
    self.checkDependencies();
  });
}

Speaker.prototype.checkDependencies = function() {
  if (typeof jx == 'undefined'){
    var imported = document.createElement('script');
    imported.type = 'text/javascript';
    imported.src = this.path + 'assets/js/jx-3.01a.min.js';
    imported.addEventListener('load', this.load.bind(this));
    document.head.appendChild(imported);
  } else {
    this.load();
  }
}

Speaker.prototype.load = function(){
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
  if (typeof this.parameters.target == 'undefined' && typeof this.value == 'undefined') {
    console.log('Invalid target');
    return false;
  }

  if (this.binder == 'speak'){
    this.loadParameters();
  } else {
    var self = this;
    this.target.addEventListener(this.binder, function(){
      self.loadParameters();
    });
  }
}

Speaker.prototype.loadParameters = function() {
  var tech;
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
  this.data = {
    parameters: {
      text: (typeof this.parameters.target == 'string') ? this.parameters.target : this.parameters.target.value,
      tts: {
        name: tech,
        lang: (typeof this.parameters.tts != 'undefined' && typeof this.parameters.tts.lang != 'undefined') ? this.parameters.tts.lang : ''
      },
      path: this.parameters.path
    },
    repeat: (typeof this.parameters.repeat == 'undefined') ? 0 : this.parameters.repeat, //How many plays do you want (loop)
    interval: (typeof this.parameters.interval == 'undefined') ? 2000 : this.parameters.interval, //ms interval between plays
  };

  this.speak();
}

Speaker.prototype.audioPlay = function(data) {
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
        address: data.audioAddress
      };
      jx.load(data.path + 'api/speaker.php?parameters=' + encodeURIComponent(JSON.stringify(parameters)), function(response){
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

Speaker.prototype.speak = function(){
  if (!sessionStorage.getItem('audioSession')){
    this.data.parameters.action = 'speak';
    var self = this;
    var path = this.path;
    jx.load(this.path + 'api/speaker.php?parameters=' + encodeURIComponent(JSON.stringify(this.data.parameters)), function(response){
      if (response != null){
        playData = {
          audioAddress: response.address,
          repeat: self.data.repeat,
          interval: self.data.interval,
          uid: response.uid,
          path: path
        };
        self.audioPlay(playData);
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
  } else {
    console.log('Your audio session does not finished yet!');
  }
}

Speaker.prototype.urlExists = function (url){
  return new Promise(function(resolve) {
    var http = new XMLHttpRequest();
    http.open('HEAD', url);
    http.onreadystatechange = function() {
      if (http.readyState === 4) {
        resolve(http.status === 200);
      }
    };
    http.send();
  });
}

