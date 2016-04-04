/*
 var URN = new Class({

 initialize: function(urn) {
 this.urn = urn;
 urna = urn.split("-",3);
 console.log(urna);
 this.entity = urna[1];
 this.uuid = urna[2];
 },

 });

 <!-- <div style="width: 100px; height: 100px; border: solid 1px #ccc; background-color: #eee; padding: 2px;"></div> -->

 */
// TODO Error #2032 from flash - upload page 404
//urn1 = new URN('urn-img-256');
//console.log(urn1.entity, urn1.uuid);

var UploadHandle = new Class({

	Implements:[Options, Events],

	options:{

		onStart:function (numFilesQueued) {
			console.log("UH STARTED", numFilesQueued);
		},

		onLoad:function () {
			console.log("UH LOADED");
		},

		onFileUploaded:function (response) {
			//console.log(response);
			// TRY
			console.log('+');
			console.log(response);
			// var object = JSON.decode(response, true); // secure check syntax
			//urn = new URN(object.urn);
			// TODO *
			//console.log(object.urn);
			/**
			 onEntityUploaded() // photo, img, textembed
			 заменять или добавлять и куда именно определяет приемник, запросивший upload. он же сообщает целевый урн и лимиты аплоада
			 upload limit exced?
			 TEXT EMBED HERE?
			 Photoalbum
			 Illustration
			 Screen Attach
			 Doc attach

			 HWINOWD INTERFACE, bottom panes per file?
			 */
		},

		onFileAllUploaded:function () {
			console.log("ALL DONE");
		},

	},

	initialize:function (options) {
		if (!options) options = {};
		this.setOptions(options);
	},

});


var Uploader = new Class({

	Implements:[Options, Events],

	options:{

		upload_url:"/404",

		//post_params: {"extend": "urn-img"}, // "hash": document.getElement

		// File Upload Settings
		file_size_limit:"100 MB",
		file_types:"*.jpg; *.png",
		file_types_description:"JPG Images; PNG Image",
		file_upload_limit:0,

		// Button Settings
		//button_image_url : "/SmallSpyGlassWithTransperancy_17x18.png",
		button_placeholder_id:"spanButtonPlaceholder",
		button_width:100,
		button_height:100,
		button_text:'<span class="button">Вложения<br><span class="buttonSmall">(2 MB Max)</span></span>',
		button_text_style:'.button { font-family: Helvetica, Arial, sans-serif; font-size: 14pt; } .buttonSmall { font-size: 10pt; }',
		button_text_top_padding:0,
		button_text_left_padding:18,
		button_window_mode:SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor:SWFUpload.CURSOR.HAND,

		// Flash Settings
		/*
		 flash_url : "/lib/js/swfupload/swfupload.swf",
		 flash9_url : "/lib/js/swfupload/swfupload_fp9.swf",
		 */
		flash_url:"/lib/js/swfupload_stable/swfupload.swf",

		custom_settings:{
			upload_target:"divFileProgressContainer",
			thumbnail_height:2500,
			thumbnail_width:2500,
			thumbnail_quality:80
		},


		// Debug Settings
		debug:false,

		onLoad:function () {
			console.log("IM READY");
		},

	},

	initialize:function (options) {
		if (!options) options = {};
		this.setOptions(options);
		// !!!!
		//this.handlers = new UploadHandle();
	},

	preLoad:function () {
		if (!this.support.loading) {
			console.log("You need the Flash Player to use SWFUpload.");
			return false;
		} else if (!this.support.imageResize) {
			console.log("You need Flash Player 10 to upload resized images.");
			return false;
		}
		else {
			// !!!!
			handlers = new UploadHandle();
			handlers.fireEvent('load');
		}
	},

	fileDialogComplete:function (numFilesSelected, numFilesQueued) {
		try {
			if (numFilesQueued > 0) {
				console.log(numFilesQueued);

				// !!!!				
				handlers = new UploadHandle();
				handlers.fireEvent('start', numFilesQueued);

				this.startResizedUpload(this.getFile(0).ID, this.customSettings.thumbnail_width, this.customSettings.thumbnail_height, SWFUpload.RESIZE_ENCODING.JPEG, this.customSettings.thumbnail_quality, false);
			}
		} catch (ex) {
			this.debug(ex);
		}
	},

	loadFailed:function () {
		console.log("upload.flash.js ONloadFailed() - Something went wrong while loading SWFUpload");
	},

	fileQueueError:function (file, errorCode, message) {
		try {
			var errorName = "";
			if (errorCode === SWFUpload.errorCode_QUEUE_LIMIT_EXCEEDED) {
				errorName = "You have attempted to queue too many files.";
			}
			if (errorName !== "") {
				console.log(errorName);
				return;
			}
			switch (errorCode) {
				case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
					imageName = "zerobyte.gif";
					break;
				case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
					imageName = "toobig.gif";
					break;
				case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
				case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
				default:
					console.log(message);
					break;
			}
		} catch (ex) {
			this.debug(ex);
		}
	},

	uploadProgress:function (file, bytesLoaded) {
		try {
			var percent = Math.ceil((bytesLoaded / file.size) * 100);
			// TODO upload progress
			// console.log(percent, '%');
		} catch (ex) {
			this.debug(ex);
		}
	},

	uploadSuccess:function (file, serverData) {
		try {

			//console.log(file);
			//console.log(serverData);

			handlers = new UploadHandle();
			handlers.fireEvent('fileUploaded', serverData);

		} catch (ex) {
			this.debug(ex);
		}
	},

	uploadComplete:function (file) {
		try {
			/*  I want the next upload to continue automatically so I'll call startUpload here */
			if (this.getStats().files_queued > 0) {
				this.startResizedUpload(this.getFile(0).ID, this.customSettings.thumbnail_width, this.customSettings.thumbnail_height, SWFUpload.RESIZE_ENCODING.JPEG, this.customSettings.thumbnail_quality, false);
			} else {
				//("All images received.");
				handlers.fireEvent('fileAllUploaded');
			}
		} catch (ex) {
			this.debug(ex);
		}
	},

	uploadError:function (file, errorCode, message) {
		try {
			switch (errorCode) {
				case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
					//file,  this.customSettings.upload_target
					break;
				case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
					break;
				case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
					break;
				default:
					console.log(message);
					break;
			}
		} catch (ex3) {
			this.debug(ex3);
		}

	},

	activate:function () {
		this.options.swfupload_preload_handler = this.preLoad;
		this.options.file_dialog_complete_handler = this.fileDialogComplete;
		this.options.swfupload_load_failed_handler = this.loadFailed;
		this.options.file_queue_error_handler = this.fileQueueError;
		this.options.upload_progress_handler = this.uploadProgress;
		this.options.upload_error_handler = this.uploadError;
		this.options.upload_success_handler = this.uploadSuccess;
		this.options.upload_complete_handler = this.uploadComplete;
		this.swfu = new SWFUpload(this.options);
		this.fireEvent('load');
	},

	deactivate:function () {
		this.swfu = null;
		delete this.swfu;
	},

});


/**
 http://demo.swfupload.org/Documentation/
 void setPostParams(param_object)
 Dynamically modifies the post_params setting. Any previous values are over-written. The param_object should be a simple JavaScript object. All names and values must be strings.
 void setFileTypes(types, description)
 Dynamically updates the file_types and file_types_description settings. Both parameters are required.
 setButtonImageURL(url)
 setButtonText(text)

 uploadStart
 uploadProgress (called over and over again as the file uploads)
 uploadError (called if some kind of error occurs, the file is canceled or stopped)
 uploadSuccess (the upload finished successfully, data returned from the server is available)
 uploadComplete (the upload is complete and SWFUpload is ready to start the next file)

 flash ok | error load = not. сначала проверить что есть и выбрать нужный Uploader.iFrame | Uploader.Html5 | Uploader.Flash
 Files selected for upload, upload started
 File uploaded OK | ERROR of upload (check non 200 codes) +++ File uploaded but json error ret
 All files uploaded
 */


/**


 */
