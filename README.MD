A PHP/JS lightweight API to use TTS(text-to-speech) solutions on your website/system

Currently with compatibility with two TTS: lianetts (a SERPRO open solution to pt-br - https://www.serpro.gov.br/menu/suporte1/servicos/downloads-e-softwares/lianetts
or http://intervox.nce.ufrj.br/lianetts/) and espeak natively. It's important to emphatize: the main language oriented is to pt-br systems,
i'll add later full compatibility with all other voices available on espeak.

The default TTS of this version is the lianetts, if you want to load the espeak, will need load the respective technology by parameters.

I'll need to install one of two supported technologies, create the mediapool path with full permissions and enjoy.
### TO DO
- [ ] SSH support (remote connections with TTS)
- [ ] Stronger manipulation of TTS technologies (pitch, volume, etc)
- [ ] Wider TTS options
- [X] Wider Languages options
- [ ] List available languages when espeak is available
