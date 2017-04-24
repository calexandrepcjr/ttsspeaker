<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Example Voice Panel</title>
  </head>
  <body>
      <input type="text" name="text-to-speak" value="" placeholder='Insert any text here'>
  </body>
</html>
<script src="speaker.js"></script>
<script>
 var entryData = document.querySelector('[name=text-to-speak]');
 var speaker = new Speaker();
 
 entryData.addEventListener('change', function(){
     data = {
	 text: this.value,
	 action: 'speak'
     };
     speaker.speak(data);
 });
</script>
