<!DOCTYPE html>
<?php include_once 'api/speaker.class.php'; ?>
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
	<h2>MEDVOICE EXAMPLE</h2>
	<p>
	    <?php
	    $speaker = new Speaker();
	    $availableTechs = $speaker->getAvailableTechs();
	    echo '<select name="technology" title="Which TTS technology do you want?">';
	    foreach($availableTechs as $key => $tech){
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
 var entryData = document.querySelector('[name=text-to-speak]');
 var speaker = new Speaker();
 
 entryData.addEventListener('change', function(){
     data = {
         text: this.value,
         technology: document.querySelector('[name=technology]').value,
         action: 'speak'
     };
     speaker.speak(data);
 });
</script>
