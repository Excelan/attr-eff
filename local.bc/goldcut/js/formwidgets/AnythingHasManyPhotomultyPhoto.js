function moveEnabler() {
	var selected = null;

	$$('.tc_switch').addEvent('click', function (o) {
		o.stop();
		var all = this.getParent().getChildren('.tc_switch');
		var loact = this.getParent().getChildren('.tc_switch.active')[0];
		var lact = loact.get('data-lang');
		var lang = this.get('data-lang');
		var hideLang = lact;
		this.getParent('.tabbed_control').getChildren('.tabbed_content.'+lang).show();
		this.getParent('.tabbed_control').getChildren('.tabbed_content.'+hideLang).hide();
		loact.removeClass('active');
		this.addClass('active');
	});
	
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



function build_photoitem_international(datal, langs) {
	var data;
	var d;
	//console.log(datal,langs);
	//console.log(typeof(datal), datal.length, 'langs:',langs);
	if (!langs)
	{
		//console.log('NO LANGS');
		o = datal;
		datal = {};
		datal['ru'] = o;
		datal['en'] = [];
		langs = ['ru','en'];
	}
	else
		data = datal[langs[0]]; // first lang as default
	
	//console.log('data.length:',data.length);
	//console.log('iterate main lang');
	data.each(function (d, index) {
		
		//console.log('each main',d);	  
			  
		var div = new Element('div', {'class':'photoitem', 'id':d.urn});
		
		var divMove = new Element('div', {'class':'photoitem_move'});
		var divImage = new Element('div', {'class':'photoitem_image'});
		
		var divIconControls = new Element('div', {'class':'icon_controls', 'html': '<a href="#" class="delPhoto" data-urn="'+d.urn+'"><img src="/goldcut/assets/icons/delete.png"></a>'});
		
		var divTitles = [];
		var divSpecial = [];
		
		langs.each(function (lang) {
			divTitles[lang] = new Element('div', {'class':'photoitem_titles'});
			divSpecial[lang] = new Element('div', {'class':'photoitem_special'});	   
		});
		
		var divContentControl = [];
		langs.each(function (lang, li) {
		    divContentControl[lang] = new Element('div', {'class':'tabbed_content '+lang});
		    divContentControl[lang].adopt([divTitles[lang], divSpecial[lang]]);
		    if (li>0) divContentControl[lang].hide();
		});
		
		var divTabbedControl = new Element('div', {'class':'tabbed_control'});
		
		var langbuts = [];
		langs.each(function (lang, li) {
		    var addclass = '';
			if (li == 0) addclass = 'active';	   
		    langbuts.push('<a href="#" data-lang="'+lang+'" class="tc_switch '+addclass+'">'+lang+'</a>');
		});
		var divTabbedControlTabsSwitcher = new Element('div', {'class':'tabbed_control_switcher', 'html': langbuts.join(' ')});
		
		divTabbedControl.adopt([divTabbedControlTabsSwitcher]);
		langs.each(function (lang) {
			divTabbedControl.adopt([divContentControl[lang]]);
		});

		var img = new Element('img', {'src':d.thumbnail.uri});
		
		var inputTitleLabel = [];
		var inputTitle = [];
		var inputAnonsLabel = [];
		var inputAnons = [];
		var dol = d; // original curr lang data item
		langs.each(function (lang, li) { // all langs data items on same index
			//try {  } catch (Error) { console.log(Error); }
		    	d = datal[langs[li]][index];
		    	if (typeof d === 'undefined')
			{
				//console.log('NO CURR LANG');
				d = {};
				d.urn = dol.urn;
				d.lang = lang;
				d.title = 'ENt';
				d.anons = 'ENa';
			}
			//console.log('each all',lang,d);
			inputTitleLabel[lang] = new Element('label', {'for': d.urn + 'Title'});
			inputTitleLabel[lang].set('text', 'Подпись');
			//inputTitle[lang] = new Element('input', { 'class':'changemon', id:d.urn + 'Title', type:'text', value:d.title, 'data-urn':d.urn, 'data-lang':lang, 'data-key':'title', style:'width: 90%' });
			inputTitle[lang] = new Element('textarea', { 'class':'changemon ft', id:d.urn + 'Title', text:d.title, 'data-urn':d.urn, 'data-lang':lang, 'data-key':'title', style:'' });
			inputAnonsLabel[lang] = new Element('label', {'for':d.urn + 'Anons'});
			inputAnonsLabel[lang].set('text', 'Описание');
			inputAnons[lang] = new Element('textarea', { 'class':'changemon fd', id:d.urn + 'Anons', 'data-urn':d.urn, 'data-key':'anons', 'data-lang':lang, style:'' });
			if (d.anons) inputAnons[lang].set('text', d.anons);
			
			
		});
		d = dol;
		langs.each(function (lang) {
		    divTitles[lang].adopt([inputTitleLabel[lang], inputTitle[lang]]);
		    	divSpecial[lang].adopt([inputAnonsLabel[lang], inputAnons[lang]]);  
		});
		
		var ordered = 1;
		if (d.ordered)
			ordered = d.ordered;
		else
			ordered = index + 1;

		var divMove1 = new Element('div', {'class':'move1', 'text': ordered});
		var divMove2 = new Element('div', {'class':'move2'});

		divMove.adopt([divMove1, divMove2]);
		divImage.adopt([img]);

		// SET ALBUM COVER IF TOP = 1
		if (d.top == true) divImage.addClass('selected');

		// MAIN BUILD
		div.adopt([divMove, divImage, divIconControls, divTabbedControl]);

		document.id('photosContainer').adopt(div);

		img.addEvent('click', function (e) {
			var selected = document.id('photosContainer').getElements('.selected');
			var oldis = [];
			this.getParent('div').toggleClass('selected');
			var selectedUrn = this.getParent('div').getParent('div').get('id');
			selected.each(function (a) {
				a.removeClass('selected');
				oldis.push(a.getParent('.photoitem').get('id'))
			});
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
		});


		var changedmon = function (e) {
			var message = new Hash();
			message.action = 'update';
			message.urn = this.get('data-urn');
			var k = this.get('data-key');
			var clang = this.get('data-lang');
			//var v = this.get('value') ? this.get('value') : this.get('text'); // text always returns old data
			var vv = this.get('value');
			if (vv == null) vv = 'NULL';
			if (vv == '') vv = 'NULL';
			message.set(k, vv);
			message.set('lang', clang);
			new Request({method:'post', url:'/goldcut/admin/ajax.php'}).send(message.toQueryString());
		};

		langs.each(function (lang) {
		    inputTitle[lang].addEvent('change', changedmon);
		    inputAnons[lang].addEvent('change', changedmon);
		});

	});

	moveEnabler();


	/*
	 document.id('photosContainer').addEvent('click', function(e){
	 console.log(this);
	 console.log(e.target);
	 });
	 */
}



function build_photoitem(data) {
	data.each(function (d, index) {

		var div = new Element('div', {'class':'photoitem', 'id':d.urn});

		var divMove = new Element('div', {'class':'photoitem_move'});
		var divImage = new Element('div', {'class':'photoitem_image'});

		var img = new Element('img', {'src':d.thumbnail.uri});

		var ordered = 1;
		if (d.ordered)
			ordered = d.ordered;
		else
			ordered = index + 1;

		var divMove1 = new Element('div', {'class':'move1', 'text': ordered});
		var divMove2 = new Element('div', {'class':'move2'});

		divMove.adopt([divMove1, divMove2]);
		divImage.adopt([img]);

		div.adopt([divMove, divImage]);

		document.id('photosContainer').adopt(div);

	});

}