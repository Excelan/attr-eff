function build_hasoneaudio(data) {
	if (data.length) {
		var uuid = data[0].urn.split('-')[2];
		//var dur = ' '+data[0].uri + ', длина '+ Math.floor(data[0].duration/60) +' мин';
		//var img = new Element('img', {'src':"/goldcut/assets/filetype/sound.png"});
		var audio = new Element('audio', {'controls': "controls", 'html':'<source src="/audio/'+uuid+'.mp3" type="audio/mpeg"><source src="/audio/'+uuid+'.ogg" type="audio/ogg">'})
		document.id('audioContainer').empty().adopt([audio]); // img, new Element('span', {'text': dur} )
		document.id('hasoneaudiourn').set('value', data[0].urn);
	}
}
