// TODO Error #2032 from flash - upload page 404

var UploadHandle = new Class({

	Implements:[Options, Events],

	options:{

		onStart:function (progressdiv) {
			//console.log("UPLOAD STARTED", numFilesQueued);
		},

		onFileUploaded:function (response, file) {
			//console.log('fileup', response);
			// TRY
			try {
				var data = JSON.decode(response, true); // secure check syntax
				ps.publish(["uploaded", data.target].join('/'), data);
			}
			catch (ex) {
				console.log(response, ex);
			}
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

		onLoad:function () {
			//console.log("SWF LOADED");
		},

		onFileAllUploaded:function (progressdiv) // numFilesUploaded
		{
			//console.log("ALL DONE", numFilesUploaded);
			if ($('upload_wrapper')) $('upload_wrapper').empty();
			//console.log('here',progressdiv);
			progressdiv.setStyle('background-color', 'green');
			//this.deactivate();
			//$('upload_wrapper').retrieve('swfuploader');

			try {
				var data = {};
				var qname = ["uploaded", "all"].join('/');
				ps.publish(qname, data);
			}
			catch (ex) {
				alert(ex);
			}


		},

	},

	initialize:function (options) {
		if (!options) options = {};
		this.setOptions(options);
	},

});

// TODO display max upload size
var uploadlimit = '10 MB'; // default
//console.log(siteSettings);
if (siteSettings.uploadmax > 0) uploadlimit = siteSettings.uploadmax + ' MB';
var buttonimage = "/lib/js/swfupload_stable/XPButtonUploadText_61x22.png";
var button_width = 62;
var button_height = 22;
if (siteSettings.buttonimage) 
{
	buttonimage = siteSettings.buttonimage;
	button_width = siteSettings.uploadbuttonw;
	button_height = siteSettings.uploadbuttonh;
}

var Uploader = new Class({

	Implements:[Options, Events],

	options:{
		flash_url:"/lib/js/swfupload_stable/swfupload.swf",
		upload_url:"/404",
		file_size_limit:uploadlimit,
		file_types:"*.*",
		file_types_description:"All Files",
		file_upload_limit:100,
		file_queue_limit:0,

		custom_settings:{
			progressTarget:"fsUploadProgress",
			cancelButtonId:"btnCancel"
		},

		debug:false,

		// Button Settings
		button_image_url:buttonimage,
		//button_placeholder_id : "spanButtonPlaceholder",
		button_width:button_width,
		button_height:button_height,
	},

	initialize:function (options) {
		if (!options) options = {};
		this.setOptions(options);
		// !!!!
		//this.handlers = new UploadHandle();
	},

	preLoad:function () {
		handlers = new UploadHandle();
		handlers.fireEvent('load');
	},

	fileDialogComplete:function (numFilesSelected, numFilesQueued) {
		try {
			if (numFilesSelected > 0) {
				//cancelButtonId.disabled = false;
				//console.log('numFilesQueued',numFilesQueued);
			}
			this.settings.progressdiv.setStyle('width', '0%');
			this.settings.progressdiv.setStyle('background-color', 'yellow');
			this.startUpload();
			handlers = new UploadHandle();
			handlers.fireEvent('start', numFilesQueued);
		}
		catch (ex) {
			alert(ex);
		}
	},

	fileQueued:function (file) {
		try {
		} catch (ex) {
			alert(ex);
		}
	},

	loadFailed:function () {
		console.log("upload.flash.js ONloadFailed() - Something went wrong while loading SWFUpload");
	},

	fileQueueError:function (file, errorCode, message) {
		try {
			if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
				alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
				return;
			}

			// this.customSettings.progressTarget

			switch (errorCode) {
				case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
					//progress.setStatus("File is too big.");
					alert("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
					//progress.setStatus("Cannot upload Zero Byte files.");
					alert("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
					//progress.setStatus("Invalid File Type.");
					alert("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				default:
					if (file !== null) {
						progress.setStatus("Unhandled Error");
					}
					alert("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
			}
		} catch (ex) {
			alert(ex);
		}
	},

	uploadProgress:function (file, bytesLoaded, bytesTotal) {
		try {
			//var percent = Math.ceil((bytesLoaded / file.size) * 100);
			var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
			// TODO upload progress
			//console.log(percent, '%');
			var progressdiv = this.options.progressdiv;
			progressdiv.setStyle('width', percent + '%');
		} catch (ex) {
			alert(ex);
		}
	},

	uploadSuccess:function (file, serverData) {
		try {
			handlers = new UploadHandle();
			handlers.fireEvent('fileUploaded', serverData, file);
		} catch (ex) {
			alert(ex);
		}
	},


	uploadComplete:function (file) {
		if (this.getStats().files_queued === 0) {
			//cancelButtonId).disabled = true;
		}
	},

	queueComplete:function (numFilesUploaded) {
		//var status = document.getElementById("divStatus");
		//status.innerHTML = numFilesUploaded + " file" + (numFilesUploaded === 1 ? "" : "s") + " uploaded.";
		handlers.fireEvent('fileAllUploaded', this.options.progressdiv);
	},

	uploadError:function (file, errorCode, message) {
		alert(file.name + ' ошибка на сервере. http status: ' + message);
		try {
			switch (errorCode) {
				case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
					progress.setStatus("Upload Error: " + message);
					alert("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
					break;
				case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
					progress.setStatus("Upload Failed.");
					alert("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				case SWFUpload.UPLOAD_ERROR.IO_ERROR:
					progress.setStatus("Server (IO) Error");
					alert("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
					break;
				case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
					progress.setStatus("Security Error");
					alert("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
					break;
				case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
					progress.setStatus("Upload limit exceeded.");
					alert("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
					progress.setStatus("Failed Validation.  Upload skipped.");
					alert("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
					// If there aren't any files left (they were all cancelled) disable the cancel button
					if (this.getStats().files_queued === 0) {
						document.getElementById(this.customSettings.cancelButtonId).disabled = true;
					}
					progress.setStatus("Cancelled");
					progress.setCancelled();
					break;
				case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
					progress.setStatus("Stopped");
					break;
				default:
					progress.setStatus("Unhandled Error: " + errorCode);
					alert("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
			}
		} catch (ex) {
			alert(ex);
		}
	},

	activate:function () {

		this.options.swfupload_loaded_handler = this.preLoad;
		this.options.file_dialog_complete_handler = this.fileDialogComplete;
		this.options.file_queued_handler = this.fileQueued;
		this.options.swfupload_load_failed_handler = this.loadFailed;
		this.options.file_queue_error_handler = this.fileQueueError;
		this.options.upload_progress_handler = this.uploadProgress.bind(this);
		this.options.upload_error_handler = this.uploadError;
		this.options.upload_success_handler = this.uploadSuccess;
		this.options.upload_complete_handler = this.uploadComplete;
		this.options.queue_complete_handler = this.queueComplete.bind(this); // we can use this.settings of swfupload object instead

		//this.swfu = new SWFUpload(this.options);
		this.fireEvent('load');

		this.swfu = new SWFUpload(this.options);

		if ($('upload_wrapper')) $('upload_wrapper').store('swfuploader', this.swfu);

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