function rtInit(domid, rt) {
	//console.log(domid, rt);
	var domc = document.id(domid);

	window.addEvent('domready', function () {

		function ajaxe(text, error) {
			console.log(text, error)
		}

		// UNLINK
		var activeRelationsOnClick = function (e) {
			e.stop()
			var element = e.target;
			var hostUrn = $$('#' + domid + ' .m3')[0].get('data-host');
			var listname = document.id(domid).get('data-listname');
			var urn = element.get('data-urn');
			m = {}
			m.action = 'remove';//'unlink'
			m.urn = urn; //hostUrn
			m.from = hostUrn+':'+listname;
			//entity = $$('#' + domid + ' .myInput')[0].get('data-entity')
			//m[entity] = e.target.get('data-urn')
			//var ns = document.id(domid).get('data-listid')
			//if (ns) m.ns = ns
			//console.log(m);
			new Request.JSON({url:'/goldcut/admin/ajax.php', onError:ajaxe, onSuccess:null}).post(m);
			e.target.dispose()
		}

		// LINK
		var linkRel = function (element) {
			var urn = element.get('data-urn');
			var title = element.get('text');

			var le = $$('#' + domid + ' .m3 a').length
			var ns = document.id(domid).get('data-listid')
			var listname = document.id(domid).get('data-listname')
			var hostUrn = $$('#' + domid + ' .m2')[0].get('data-host')
			var entity = $$('#' + domid + ' .myInput')[0].get('data-entity')

			m = {};
			m.action = 'add';
			m.urn = urn; //hostUrn
			m.to = hostUrn+':'+listname;
			//m.category = urn
			//m.category_meta = le
			//em = entity + '_meta'
			//m[entity] = urn
			//m[em] = le
			//if (ns) m.ns = ns
			//console.log(m);
			//alert(listname);
			new Request.JSON({url:'/goldcut/admin/ajax.php', onError:ajaxe, onSuccess:null}).post(m);

			element.dispose()

			var el2 = new Element('a', {'text':title, 'class':'m2var', 'data-urn':urn});
			$$('#' + domid + ' .m3')[0].grab(el2);
			el2.addEvent('click', activeRelationsOnClick)
		}

		// LINK	 
		var possibleRelationsOnClick = function (event) {
			event.stop()
			linkRel(event.target)
		}


		var vars3 = function (d) {
			$$('#' + domid + ' .m3')[0].empty();
			d.each(function (r) {
				var present;
				if (r.presentation) present = r.presentation;
				if (r.title) present = r.title; else present = r.urn;
				if (r.name) present = r.name;
				var el2 = new Element('a', {'text': present, 'class':'m2var', 'data-urn':r.urn});
				//console.log(el2);
				$$('#' + domid + ' .m3')[0].grab(el2)
				el2.addEvent('click', activeRelationsOnClick)
			})
		}

		var vars = function (d) {
			$$('#' + domid + ' .m2')[0].empty();
			d.each(function (r) {
				var present;
				if (r.presentation) present = r.presentation;
				if (r.title) present = r.title; else present = r.urn
				if (r.name) present = r.name;
				var el = new Element('a', {'text': present, 'class':'m2var', 'data-urn':r.urn});
				$$('#' + domid + ' .m2')[0].grab(el);
				el.addEvent('click', possibleRelationsOnClick);
			})
		}

		var old = null;
		$$('#' + domid + ' .myInput')[0].addEvent('keyup', function () {
			current_value = $$('#' + domid + ' .myInput')[0].value;

			old = this.get('data-old');
			this.set('data-old', current_value);

			if (old != current_value && current_value.length >= 3) {
				m = {}
				m.action = 'load'
				m.urn = 'urn:' + $$('#' + domid + ' .myInput')[0].get('data-entity')
                m.lang = $('editedlang').value;
				m.search = current_value
				m.scope = 'admin';
				m.order = {'created': 'desc' }
				var ns = $$('#' + domid + ' .myInput')[0].get('data-ns')
				if (ns) m.ns = ns
				//console.log(m);
				new Request.JSON({url:'/goldcut/admin/ajax.php', onError:ajaxe, onSuccess:vars}).get(m);
			}

			/**
			 max_chars = 10;
			 current_length = current_value.length;
			 remaining_chars = max_chars - current_length;
			 $('counter_number').innerHTML = remaining_chars;
			 if(remaining_chars<=5) {
			 $('counter_number').setStyle('color', 'red');
			 } else {
			 $('counter_number').setStyle('color', 'green');
			 }
			 */
		});

		$$('#' + domid + ' .myInput')[0].addEvent('keydown', function (e) {

			var elss = domc.getElements('.m2 a.selected')
			var selectedURN = elss.get('data-urn')
			//console.log('selectedURN', selectedURN);
			var els = domc.getElements('.m2 a.m2var')
			var si = -1

			for (var i = 0; i < els.length; i++) {
				if (selectedURN == els[i].get('data-urn')) si = i;
			}

			if (e.key == 'enter') {
				e.stop()
				linkRel(elss[0])
			}
			if (e.key == 'down') {
				e.stop()
				els.removeClass('selected')
				if (si < els.length - 1) si++;
				else si = 0;
				els[si].addClass('selected')
			}

			if (e.key == 'up') {
				e.stop()
				els.removeClass('selected')
				if (si > 0) si--;
				else si = els.length - 1;
				els[si].addClass('selected')

			}
		});


		/**
		 $('myInput').addEvent('change', function() {
		 current_value = $('myInput').value;
		 //console.log(current_value);
		 });
		 */

		//if (window.rt !== undefined)
		if (rt !== undefined) {
			rt.sort(function (a, b) {
				return a.relation > b.relation
			});
			vars3(rt);
		}

	});

}
