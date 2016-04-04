function build_hasoneattach(data) {
	if (data.length) {
		//console.log(data[0]);
		var container = document.id('attachContainer');
		container.empty();

		var uuid = data[0].urn.split('-')[2];
		var file = new Element('a', {'text':' Файл ' + data[0].uri + '.' + data[0].ext, 'href':"/original/" + uuid + "." + data[0].ext});
		if (data[0].icon && data[0].icon.uri) {
			var icon = new Element('img', {'src':data[0].icon.uri, 'border':0});
			container.adopt(icon);
		}
		container.adopt(file);
		document.id('hasoneattachurn').set('value', data[0].urn);
	}
}
