function build_hasonephoto(data, hid) 
{
	if (!data[0]) return;
	
	var changedmon = function(e){
		 var message = new Hash();
		 message.action = 'update';
		 message.urn = this.get('data-urn');
		 message.set(this.get('data-key'), this.get('value') ? this.get('value') : this.get('text'));
		 new Request({method: 'post', url: '/goldcut/admin/ajax.php'}).send(message.toQueryString());
	 };
	
	var editableFields = window["editableFields_"+hid];
	var firstText = window["firstText_"+hid];
//	console.log(editableFields, hid);

	d = data[0];
	index = 0;

	var div = new Element('div', {'class':'photoitem', 'id':d.urn});

	var divMove = new Element('div', {'class':'photoitem_move'});
	var divImage = new Element('div', {'class':'photoitem_image'});
	var divTitles = new Element('div', {'class':'photoitem_titles'});
	var divSpecial = new Element('div', {'class':'photoitem_special'});

//	console.log(d);
	var img;
	if (d.thumbnail.uri)
		img	= new Element('img', {'src':d.thumbnail.uri});
	if (d.thumbnail.data)
		img	= new Element('img', {'src':d.thumbnail.data, 'max-width': '100'});

	var inputs = [];
	for(var v in editableFields)
	{
		if (!editableFields.hasOwnProperty(v)) continue;
		var inputLabel = new Element('label', {'for':d.urn + 'Title', 'style': 'margin-bottom: 0; padding-bottom:0; margin-top: 3px;'});
		inputLabel.set('text', editableFields[v]);
		inputs.push(inputLabel);
		var input = new Element('input', { 'class':'changemon', id: d.urn + 'Title', type: 'text', value: d[v], 'data-urn': d.urn, 'data-key': v, style: 'width: 100%' });
		input.addEvent('change', changedmon);
		inputs.push(input);
	};
	divTitles.adopt(inputs);
	
	var Tinputs = [];
	for(var v in firstText)
	{
		if (!firstText.hasOwnProperty(v)) continue;
		var inputLabel = new Element('label', {'for':d.urn + 'Title', 'style': 'margin-bottom: 0; padding-bottom:0; margin-top: 3px;'});
		inputLabel.set('text', firstText[v]);
		Tinputs.push(inputLabel);
		var input = new Element('textarea', { 'class':'changemon', id: d.urn + 'Anons', 'data-urn': d.urn, 'data-key':'anons', style:'width: 98%; height: 60px;' });
		if (d[v]) input.set('text', d[v]);
		input.addEvent('change', changedmon);
		Tinputs.push(input);
	};
	divSpecial.adopt(Tinputs);
	
	var ordered = 1;
	if (d.ordered)
		ordered = d.ordered;
	else
		ordered = index + 1;

	var divMove1 = new Element('div', {'class':'move1', text:ordered});
	var divMove2 = new Element('div', {'class':'move2'});

	divMove.adopt([divMove1, divMove2]);
	divImage.adopt([img]);

	div.adopt([divMove, divImage, divTitles, divSpecial]);
	document.id('onePhoto_' + hid).empty().adopt(div);
	document.id('hasonephotourn_' + hid).set('value', d.urn);

}
