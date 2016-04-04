function moveEnabler() {
	var selected = null;

	$$('.delPhoto').addEvent('click', function (o) {
		o.stop();
		if (confirm('Удалить?')) {
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
			console.log(neworder);
			var message = new Hash();
			message.action = 'reorder';

			// TODO !!

			message.urn = 'urn-photoalbum'; // this.get('data-urn')

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
	data.each(function (d, index) {
			  console.log(d);
		var div = new Element('div', {'class':'photoitem', 'id':d.urn});

		var divMove = new Element('div', {'class':'photoitem_move'});
		var divImage = new Element('div', {'class':'photoitem_image'});
		var divTitles = new Element('div', {'class':'photoitem_titles'});
		var divSpecial = new Element('div', {'class':'photoitem_special'});

		//var img = new Element('img', {'src':d.thumbnail.uri});

		var inputTitleLabel = new Element('label', {'for':d.urn + 'Title'});
		inputTitleLabel.set('text', 'Подпись');
		var inputTitle = new Element('input', { 'class':'changemon', id:d.urn + 'Title', type:'text', value:d.title, 'data-urn':d.urn, 'data-key':'title', style:'width: 90%' });
		var delPhoto = new Element('a', { 'class':'delPhoto', 'data-urn':d.urn, href:'#', text:'Удалить '+d.uri+'.'+d.ext });

		//var inputAlt = new Element('input', { 'class': 'changemon', type: 'text', value: d.alt, 'data-urn': d.urn, 'data-key': 'alt' });
		/*
		var inputAnonsLabel = new Element('label', {'for':d.urn + 'Anons'});
		inputAnonsLabel.set('text', 'Описание');
		var inputAnons = new Element('textarea', { 'class':'changemon', id:d.urn + 'Anons', 'data-urn':d.urn, 'data-key':'anons', style:'width: 98%; height: 60px;' });
		inputAnons.set('text', d.anons);
		*/
		var ordered = 1;
		if (d.ordered)
			ordered = d.ordered;
		else
			ordered = index + 1;
		ordered = '';

		var divMove1 = new Element('div', {'class':'move1', text: ordered});
		var divMove2 = new Element('div', {'class':'move2'});

		divMove.adopt([divMove1, divMove2]);
		//divImage.adopt([img]);
		divTitles.adopt([inputTitleLabel, inputTitle, delPhoto]);
		//divSpecial.adopt([inputAnonsLabel, inputAnons]);

		// SET ALBUM COVER IF TOP = 1
		if (d.top == true) divImage.addClass('selected');

		div.adopt([divMove, divImage, divTitles, divSpecial]);

		document.id('photosContainer').adopt(div);

		var changedmon = function (e) {
			var message = new Hash();
			message.action = 'update';
			message.urn = this.get('data-urn');
			message.set(this.get('data-key'), this.get('value') ? this.get('value') : this.get('text'));
			new Request({method:'post', url:'/goldcut/admin/ajax.php'}).send(message.toQueryString());
		};

		inputTitle.addEvent('change', changedmon);
		//inputAnons.addEvent('change', changedmon);

	});

	moveEnabler();


	/*
	 document.id('photosContainer').addEvent('click', function(e){
	 console.log(this);
	 console.log(e.target);
	 });
	 */
}
