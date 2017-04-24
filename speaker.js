//AJAX by JS
//Author: https://www.quirksmode.org/js/xmlhttp.html
var imported = document.createElement('script');
imported.src = 'assets/js/jx-3.01a.min.js';
document.head.appendChild(imported);

function Speaker() {
    this.speak = function(data){
	jx.load('api/speaker.php?parameters=' + encodeURIComponent(JSON.stringify(data)), function(response){
	    console.log(response);
	}, 'json');
    }
}

