function moveEnabler() {
	var selected = null;

	$$('.delPhoto').addEvent('click', function (o) {
		o.stop();
		if (confirm('Delete?')) {
			var urn = this.get('data-urn');
			new Request({url:'/goldcut/admin/ajax.php', onSuccess:function () {
				document.id(urn).dispose();
			}}).post({action:"delete", urn:urn});
		}
	});


	$$('.move1').addEvent('click', function (o) {

		if (selected && selected != this) {
			//console.log('swap');

			// MOVE AFTER

			selected.removeClass('move1selected');
			this.removeClass('move1selected');

			// cut & insert
			selected.getParent().getParent().dispose().inject(this.getParent().getParent(), 'before');

			// new ordered urns
			var neworder = [];
			selected.getParent().getParent().getParent().getChildren('div').each(function (o) {
				neworder.push(o.get('id'));
			});
			//console.log(neworder);
			var message = new Hash();
			message.action = 'reorder';

			// TODO !!

			message.urn = this.getParent('form').get('data-hosturn');

			message.order = neworder;
			new Request({method:'post', url:'/goldcut/admin/ajax.php'}).send(message.toQueryString());

			/*
			 // SWAP
			 var temp1 = new Element('div');
			 temp1.set('text', 1);
			 var temp2 = new Element('div');
			 temp2.set('text', 2);

			 var cut = selected.getParent().getParent();
			 var cutC = cut.clone();
			 temp1.replaces(cut);

			 var cur = this.getParent().getParent();
			 var curC = cur.clone();
			 temp2.replaces(cur);

			 cutC.replaces(temp2);
			 curC.replaces(temp1);
			 */


			selected = null;

			moveEnabler();

		}
		else if (selected && selected == this) {
			//console.log('same');
			this.removeClass('move1selected');
			selected = null;

		}
		else {
			//console.log('first click');
			selected = this;
			selected.addClass('move1selected');
		}

	});
}

function build_photoitem(data) {
	
	var changedmon = function (e) {
			var message = new Hash();
			message.action = 'update';
			message.urn = this.get('data-urn');
			var k = this.get('data-key');
			//var v = this.get('value') ? this.get('value') : this.get('text'); // text always returns old data
			var vv = this.get('value');
			if (vv == null) vv = 'NULL';
			if (vv == '') vv = 'NULL';
			message.set(k, vv);
			new Request({method:'post', url:'/goldcut/admin/ajax.php'}).send(message.toQueryString());
		};

	data.each(function (d, index) {

		var div = new Element('div', {'class':'photoitem', 'id':d.urn});

		var divMove = new Element('div', {'class':'photoitem_move'});
		var divImage = new Element('div', {'class':'photoitem_image'});
		var divTitles = new Element('div', {'class':'photoitem_titles'});
		var divSpecial = new Element('div', {'class':'photoitem_special'});

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

		var divMove1 = new Element('div', {'class':'move1', 'text': ordered});
		var divMove2 = new Element('div', {'class':'move2'});

		var delPhoto = new Element('a', { 'class':'delPhoto', 'data-urn':d.urn, href:'#', html: '&mdash;' });
		
		divMove.adopt([divMove1, divMove2]);
		divImage.adopt([img, delPhoto]);

		// SET ALBUM COVER IF TOP = 1
		if (d.top == true) divImage.addClass('selected');

		div.adopt([divMove, divImage, divTitles, divSpecial]);

		document.id('photosContainer').adopt(div);

        if (img)
        {
		img.addEvent('click', function (e) {
			var selected = document.id('photosContainer').getElements('.selected');
			var oldis = [];
			this.getParent('div').toggleClass('selected');
			var selectedUrn = this.getParent('div').getParent('div').get('id');
			selected.each(function (a) {
				a.removeClass('selected');
				oldis.push(a.getParent('.photoitem').get('id'))
			});
			//console.log(oldis);
			var message = new Hash();
			message.action = 'update';
			if (oldis.length > 0) {
				message.urn = oldis[0];
				message.set('top', 0);
				new Request({method:'post', url:'/goldcut/admin/ajax.php'}).send(message.toQueryString());
			}
			if (oldis[0] != selectedUrn) {
				message.urn = selectedUrn;
				message.set('top', 1);
				new Request({method:'post', url:'/goldcut/admin/ajax.php'}).send(message.toQueryString());
			}
			//document.id('photosContainer').getElements('div.photoitem').removeClass('selected');
		});
        }


		

	});

	moveEnabler();


	/*
	 document.id('photosContainer').addEvent('click', function(e){
	 console.log(this);
	 console.log(e.target);
	 });
	 */
}
