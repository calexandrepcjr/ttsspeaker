function Speaker() {
    var imported = document.createElement('script');
    imported.src = 'assets/js/jx-3.01a.min.js';
    document.head.appendChild(imported);    
}

Speaker.prototype.audioPlay = function(data){
    var audio = new Audio(data.audioAddress);
    audio.crossOrigin = 'anonymous';
    if (data.repeat && data.repeat > 0) {
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
		    action: 'shutup!',
		    uid: data.uid
		};
		jx.load('api/speaker.php?parameters=' + encodeURIComponent(JSON.stringify(parameters)), function(response){
		    if (response == 1)
			console.log('Audio ended');
		    else
			console.log('Audio remotion routine broked!');
		}, 'json');
	    }
	});
    }
    
    audio.play();
}

Speaker.prototype.speak = function(data){
    jx.load('api/speaker.php?parameters=' + encodeURIComponent(JSON.stringify(data.parameters)), function(response){
	playData = {
	    audioAddress: response.address,
	    repeat: data.repeat,
	    interval: data.interval,
	    uid: response.uid
	}; 
	Speaker.prototype.audioPlay.call(this, playData);
    }, 'json');
}
