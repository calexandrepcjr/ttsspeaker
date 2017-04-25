//AJAX by JS
//Author: https://www.quirksmode.org/js/xmlhttp.html

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
	    this.currentTime = 0;
	    this.play();
	});
    }
    
    audio.play();
}

Speaker.prototype.speak = function(data){
    jx.load('api/speaker.php?parameters=' + encodeURIComponent(JSON.stringify(data)), function(response){
	playData = {
	    audioAddress: response,
	    repeat: 3
	}; 
	Speaker.prototype.audioPlay.call(this, playData);
    }, 'json');
}
