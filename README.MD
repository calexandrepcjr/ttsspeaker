### MEDVOICE
## A PHP/JS lightweight API to use TTS(text-to-speech) solutions on your website/webystem

Software made for Medlynx Systems (www.medlynx.com.br)

Currently with compatibility with two TTS: lianetts (a SERPRO open solution to pt-br - https://www.serpro.gov.br/menu/suporte1/servicos/downloads-e-softwares/lianetts
or http://intervox.nce.ufrj.br/lianetts/) and espeak natively. It's important to emphatize: the main language oriented is to pt-br systems,
you'll need to call in the parameters about all other voices available on espeak.

The default TTS of this version is the lianetts, if you want to load the espeak, will need load the respective technology by parameters. Other TTS can be add
into the config.ini file, with the name and their verbosis, if the TTS added into config exists into server (/bin normal access, finded with which feature),
will be available to use,

### Parameters available to speak.js (* necessary):
- target = Object or String (The text-to-speech always will come from this object)*;
- secondaryTarget = Object or String (a seconde reference to trigger the event, like a button);
- tts = String with the name of existing techs. You can use the PHP API to retrieve the compatible ones (optional - default lianetts) ;
- binder = You can load other listener types (optional -  - if String will automatically load the audio when the window load, objects will load with change listener attached, but you can change the logic too, its just the default behavior. The binder speak activates the 'free' mode, without listeners: just take the string/object and plays when the js dependancies are loaded (good feature to panels));
- repeat = How many times do you want play the audio (optional - default 0);
- interval = Interval between the audio loop (default - in ms - 2000ms);
- path = An object with two attributes, name and link, referencing respectively to path to voice files and link (URI) to the files;

I'll need to install one of listed technologies into conf.ini or natively code added (lianetts or espeak) and enjoy.

### TO DO
- [ ] SSH support (remote connections with TTS)
- [ ] Stronger manipulation of TTS technologies (pitch, volume, etc)
- [X] Wider TTS options
- [X] Wider Languages options
- [ ] List available languages when espeak is available
