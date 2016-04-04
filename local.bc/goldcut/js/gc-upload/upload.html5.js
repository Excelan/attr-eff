// http://www.opera.com/docs/specs/presto28/file/
// http://www.thebuzzmedia.com/html5-drag-and-drop-and-file-api-tutorial/
/**
 xml upload > on status 200 > parse response JSON > fire this onUploaded > it fire uifile.onEvery
 on error in json parse it file this.onErrorInUpload
 TODO non 200 is not handled
 */
var UploadHTML5 = new Class({

	Implements:[Options, Events],

	options:{

		onUploaded:function (data) {
			//console.log('onUploaded');
			this.options.uifile.container.retrieve('queue').countDecrease(1);
			this.options.uifile.fireEvent("every", data);
		},
		onErrorInUpload:function (data) {
			//console.log('ERROR IN UPLOADHTML5: ', data);
			this.options.uifile.container.retrieve('queue').countDecrease(1);
			this.options.uifile.fireEvent("error", data);
		},

	},

	initialize:function (options) {
		if (!options) options = {};
		if (!options.uifile) throw new Error("MUST HAVE PARENT uifile");
		this.setOptions(options);
	},

	process:function (task, cb) {
		//console.log('html5 upload task', task);
		this.upload(task, cb);
	},

	upload:function (file, cb) {
		if (!(file.size > 0)) {
			throw new Error('cant upload empty file');
		}

		thisuifile = this.options.uifile;

		function updateProgress(e) {
			if (e.lengthComputable) {
				var percentComplete = e.loaded / e.total * 100;
				ps.publish("progress", { "progress":percentComplete.toFixed(2) });
				thisuifile.fireEvent("bytesuploaded", e.loaded);
			}
			else {
				/**
				 no progress (in FF)
				 */
				//console.log('no progress (in FF)');
				//console.log(e);
			}
		}

		var x = new XMLHttpRequest();

		x.upload.onprogress = updateProgress.bind(this);
		x.addEventListener("progress", updateProgress, false);

		x.onreadystatechange = function () {
			// 1, POST, 2.status200, 3.parsedResponceData, 4.all?
			if (x.readyState == 4) {
				if (x.status == 200) {
					try {
						var data = JSON.decode(x.responseText);
					}
					catch (Error) {
						this.fireEvent("errorInUpload", x.responseText + "\n JSON PARSE ERRRO: " + Error);
					}
					if (data.error > 0) {
						// SOFT ERROR
						this.fireEvent("errorInUpload", data);
					}
					else {
						// OK
						this.fireEvent("uploaded", data);
					}
				}
				else {
					this.fireEvent("errorInUpload", x.status + " HTTP NOT 200 IN UPLOAD " + x.responseText);
				}
			}
		}.bind(this);

		x.open('POST', '/goldcut/upload/', true);
		var d = new FormData();
		var destination = this.options.uifile.container.get('data-destination');
		if (destination) d.append('destination', destination);
		var host = this.options.uifile.container.get('data-host');
		if (host) {
			var hostentity = host.split('-')[1];
			d.append(hostentity, host);
			//console.log(hostentity, host);
		}
		/**
		 GROUP SMALL FILES IN ONE REQUEST
		 нет статуса - данные отправлены и идет обработка.
		 Open sgnal connect and data connect with data plan. send files in data with immediate return (no process on return) & wait for complete in lonh signal connection.
		 */
		d.append('Filedata', file);
		x.send(d);
		return true;
	}

});
