var UIFile = new Class({

	Implements:[Options, Events],

	options:{

		onQueued:function () {
			//console.log('all handled & queued');
			var droparea = this.container.getChildren('div')[0].getChildren('div.dropboxprogress')[0];
			var queue = this.container.retrieve('queue');
			if (droparea) droparea.set('html', 'Осталось файлов ' + queue.countLeft())
			this.queue.process();
		},

		onDone:function (data) {
			alert('all done');
		},

		onError:function (data) {
			var queue = this.container.retrieve('queue');
			//console.log('LEFT', queue.sizeLeft());
			//console.log('ERROR IN UIFILE', data);
			alert('Файл не принят сервером. ' + data);
		},

		// errors here popped up to nearest try (in html5 upload)
		onEvery:function (data) {
			var droparea = this.container.getChildren('div')[0].getChildren('div.dropboxprogress')[0];
			var queue = this.container.retrieve('queue');

			//console.log('LEFT BYTES', queue.sizeLeft());
			//console.log('LEFT COUNT', queue.countLeft());

			if (droparea) {
				if (queue.countLeft() > 0) {
					droparea.set('html', 'Осталось файлов ' + queue.countLeft());
				}
				else {
					droparea.set('html', '');
					droparea.setStyle('background-color', 'green');
				}
			}

			// CTX dropbox	
			// NEW var e = data.urn.split('-')[1];
			var target = this.container.get('data-target');
			data.target = target;
			data.entity = this.container.get('data-entity');
			// CHECK ENTITY RESULTED (ATTACH vs PHOTO MANAGED)
			ps.publish(["uploaded", target].join('/'), data);
			/**
			 управлять в этом же блоке или отдавать в hidden или по тексту и не вставлять в блок управления (сортировки, удаления, добавления)
			 */
		},

		onBytesuploaded:function (bytes) {
			var queue = this.container.retrieve('queue');
			queue.sizeDecrease(bytes);
			var aw = this.container.getChildren('div')[0].getStyle('width').toInt();
			//console.log('AW', aw);
			//console.log("BYTES PROGRESS MAIN (uifile onBytesuploaded)", bytes);
			var droparea = this.container.getChildren('div')[0].getChildren('div.dropboxprogress')[0];
			if (droparea) {
				var totalbytes = queue.sizeTotal();
				var leftbytes = queue.sizeLeft();
				var donebytes = bytes;
				var pr = leftbytes / totalbytes * 100;
				//console.log(pr.toInt(), totalbytes, leftbytes);
				if (pr == 0) pr = 100;
				pxr = aw / 100 * pr;
				droparea.setStyle('width', pxr + 'px');
				//droparea.setStyle('width', pr+'%');
				if (pr == 100) {
					droparea.setStyle('background-color', 'gold');
					droparea.set('html', 'Обработка.. Дождитесь, пока желтая полоса не станет зеленой');
				}
			}
		},

	},

	initialize:function (options) {
		//console.log('UIFILE BUILT');
		if (!options) options = {};
		if (typeOf(options.container) != 'string')
			throw new Error("No container!");
		else {
			this.container = document.id(options.container);
		}
		this.options.output = this.container.get('id');
		this.setOptions(options);

		/**
		 QUEUE
		 */
		var queue = new Queue(new UploadHTML5({uifile:this})); // HTML5
		this.queue = queue;
		this.container.store('queue', queue);

		/**
		 Build html input and drop area elements
		 */
		this.build();
	},

	toElement:function () {
		return this.container;
	},

	/*
	 getQueue: function()
	 {
	 return this.queue;
	 },
	 */

	build:function () {

		Object.append(Element.NativeEvents, { dragenter:2, dragleave:2, dragover:2, dragend:2, drop:2});

		// CTX dropbox	

		//var cnt = this.options.container;
		var cnt = this.container;
		var contents = cnt.getChildren('img');
		//console.log(contents);
		cnt.empty();


		var input = new Element('input', {type:'file', id:cnt.get('id') + '_file'});
		//var ctrl = new Element('a', {href: '#', id: cnt.get('id')+'_ctrl', html: 'Control'});
		//input.set('accept','image/*');
		input.set('multiple', true);
		this.dropbox = new Element('div', { 'id':cnt.get('id') + '_dropbox', 'text':'', 'class': 'dropbox' });
		this.dropbox.adopt(contents);
		cnt.grab(input);
		//cnt.grab(ctrl);
		cnt.grab(this.dropbox);
		this.progressdiv = new Element('div', { 'id':cnt.get('id') + '_dropbox_progress', 'text':'', 'class': 'dropboxprogress' });
		this.dropbox.grab(this.progressdiv);


		var outer = this;

		input.addEvent('change', function () {
			// !!!!!!!!
			outer.handleFiles(this.files);
		});

		var stopAndPreventEvent = function (e) {
			e.stopPropagation();
			e.preventDefault();
		}
		var dragEnter = function (e) {
			e.stopPropagation();
			e.preventDefault();
			this.classList.add('dragover');
		}
		var dragLeave = function (e) {
			e.stopPropagation();
			e.preventDefault();
			this.classList.remove('dragover');
		}
		this.dropbox.addEvent('dragover', stopAndPreventEvent);
		this.dropbox.addEvent('dragenter', dragEnter);
		this.dropbox.addEvent('dragleave', dragLeave);

		this.dropbox.addEvent('drop', function (e) {
			e.stopPropagation();
			e.preventDefault();
			var dt = e.event.dataTransfer;
			if (dt) {
				var files = dt.files;
				// !!!!!!!!				
				this.handleFiles(files);
			}
			else
				console.log('no e.dataTransfer');
			this.dropbox.classList.remove('dragover');
		}.bind(this));
	},

	handleFiles:function (files) {
		var numFiles = files.length;
		for (var i = 0; i < files.length; i++) {
			var file = files[i];
			this.add(file);
		}
		this.fireEvent('queued');
	},


	add:function (file) {
		this.queue.add(file);
	},
})

