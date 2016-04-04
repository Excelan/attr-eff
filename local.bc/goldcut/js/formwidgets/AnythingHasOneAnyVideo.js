function build_hasonevideo(data) {
	if (data.length) {
		var uuid = data[0].urn.split('-')[2];
		var img = new Element('img', {'src':"/video/" + uuid + ".jpg"});
		document.id('videoContainer').empty().adopt(img);
		document.id('hasonevideourn').set('value', data[0].urn);
	}
}
