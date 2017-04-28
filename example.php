<!DOCTYPE html>
<?php include_once 'api/TTS.class.php'; ?>
<html>
    <head>
	<meta charset="utf-8">
	<title>Example Voice Panel</title>
	<style>
	 html {
	     background: white;
	 }
	 
	 body {
	     width: 400px;
	     height: auto;
	     background: lavender;
	     border: 1px solid black;
	     margin: 0 auto;
	     text-align: center;
	 }
	</style>
    </head>
    <body>
	<h2>EXAMPLE</h2>
	<p>
	    <?php
	    $tts = new TTS();
	    $availableTTS = $tts->getAvailable();
	    echo '<select name="tts" title="Which TTS technology do you want?">';
	    foreach($availableTTS as $key => $tech){
		echo "<option value='{$key}'>{$key}</option>";
	    }
         echo '</select>';
         ?>
         <input type="text" name="text-to-speak" value="" placeholder='Insert any text here' autofocus>
     </p>
   </body>
 </html>
 <script src="speaker.js"></script>
 <script>
 /*
 target = Object or String;
 tts = String with the name of existing techs. You can use the PHP API to retrieve the compatible ones (optional - default lianetts) ;
 binder = You can load other listener types (optional -  - if String will automatically load the audio when the window load, objects will load with change listener attached);
 repeat = How many times do you want play the audio (optional - default 0);
 interval = Interval between the audio loop (default - in ms - 2000ms)
  */
    var parameters = {
     target: document.querySelector('[name=text-to-speak]'),
     tts: {
            name: document.querySelector('[name=tts]')
        }
    };
    var speaker = new Speaker(parameters);
</script>
