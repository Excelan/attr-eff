if (typeof FileReader == "undefined") alert ("Sorry your browser does not support the File API");

/*
Face detect on all selected photos
Upload queue
each uploaded callback
all uploaded callback
 */


/*
var h = new Hurray;
h.push('a','b','c');
alert(h.length); // 3
var last = h.pop();
alert(last); // 'c'
alert(h.length); // 2
*/

var onAllUploaded;

var fileQueue = []; 
var destination = null;
var container = null;

// TODO progress callback
function uploadFile(file, destination, container)
{
	total = this[0];
	processed = this[1];
	responses = this[2];
	dataProcessor = this[3];
    eachUploadedProcessor = this[4];
	var xhr = new XMLHttpRequest();
	var upload = xhr.upload;
	upload.addEventListener("progress", function (ev) {
		if (ev.lengthComputable) {
			this.progress = (ev.loaded / ev.total) * 100 + "%";
			if (this.progress != '100%') console.log(this.progress);
		}
	}, false);
	//upload.addEventListener("load", function (ev) { console.log('uploaded'); }, false);
	upload.addEventListener("error", function (ev) {console.log(ev);}, false);
	//xhr = upload;
	xhr.onreadystatechange = function () {
	if (xhr.readyState == 4) 
	{
		if (xhr.status == 200) 
		{
			//console.log(200);
			if (xhr.responseText != null) 
			{
				//console.log(xhr.responseText);	// console.log(xhr.responseXML);
                var xx = false;
                var jsObject;
				try 
				{
					jsObject = JSON.parse(xhr.responseText);
					//console.log(jsObject);
					responses.push(jsObject);
					processed++;
                    xx = true
                }
                catch (e) {
                    console.log(e, xhr.responseText);
                }
                finally
                {
                    if (xx === true) eachUploadedProcessor.call([processed, total], jsObject);
                }

                if (processed == total)
                {
                    //console.log('DONE');
                    if (window['hideUploadOverlay']) hideUploadOverlay();
                    dataProcessor(responses)
                }

			}
		}
	}
	}
	xhr.open("POST", "/goldcut/upload/stream.php");
	xhr.setRequestHeader("Cache-Control", "no-cache");
	xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	xhr.setRequestHeader("X-File-Name", file.name);
	if (destination) xhr.setRequestHeader("X-destination", destination);
	if (container) xhr.setRequestHeader("X-container", container);
	xhr.setRequestHeader("X-facecount", file.faces);
	xhr.setRequestHeader("X-facelist", file.facelist);
	xhr.send(file);
}

function fileQueueUploader(fileQueue, dataProcessor, onEachUploaded)
{
	this.progress = 0;
	//console.log(fileQueue.length);
	var total = fileQueue.length;
	var processed = 0;
	var responses = [];
	while (fileQueue.length > 0) 
	{
		var file = fileQueue.pop();
		//console.log('try to upload', file.name);
		uploadFile.call([total, processed, responses, dataProcessor, onEachUploaded], file, destination, container);
    }
}

function DropArea(dropareaDomId)
{
    droparea = dropareaDomId;
	this.init = function () {
        droparea.addEventListener("dragenter",  this.stopProp, false);
        droparea.addEventListener("dragleave",  this.dragExit, false);
        droparea.addEventListener("dragover",  this.dragOver, false);
        droparea.addEventListener("drop",  this.onDropFiles, false);
    }
    this.onDropFiles = function (ev) {
    	ev.stopPropagation();
        ev.preventDefault();
        var files = ev.dataTransfer.files;
        addFileListItemsWithFileReader(files);
    }
    this.dragOver = function (ev) {
        ev.stopPropagation();
        ev.preventDefault();
        this.style["backgroundColor"] = "#F0FCF0";
    }
    this.dragExit = function (ev) {
        ev.stopPropagation();
        ev.preventDefault();
        this.style["backgroundColor"] = "#FEFEFE";
    }
	
    this.init();
}


var preprocessL = null;
// async
var addFileListItemsWithFileReader = function (files) { // from input & drag
	preprocessL = files.length;
	if (window['showUploadOverlay']) showUploadOverlay();
	for (var i = 0; i < files.length; i++) {
		var fr = new FileReader();
		fr.file = files[i];
		fr.onloadend = addFileToQueue; // then AUTOSTART UPLOAD AFTER LAST ADDED
		fr.readAsDataURL(files[i]);
	}
}

// sync
function resizeCanvas(image, canvas) {
    document.body.appendChild(image);
    canvas.width = image.offsetWidth;
    canvas.style.width = image.offsetWidth.toString() + "px";
    canvas.height = image.offsetHeight;
    canvas.style.height = image.offsetHeight.toString() + "px";
    document.body.removeChild(image);
}

// async
var addFileToQueue = function (ev) {
	var file = ev.target.file;
    var filesize = Math.round(file.size/1024)+"KB";
	// console.log(file.name, file.type, Math.round(file.size/1024)+"KB");
	rFilter = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;
	if (!rFilter.test(file.type)) { return; }

    if (DETECT_FACES != true)
    {
        fileQueue.push(file);
        preprocessL--;
        //console.log('added to Q ', fileQueue.length);
        //console.log('Q to Preprocess', fileQueue.length, preprocessL);
        // AUTO UPLOAD QUEUE
        if (preprocessL == 0) new fileQueueUploader(fileQueue, onAllUploaded, onEachUploaded); // TODO EXPORT TO CONFIG!
    }
    else
    {
        //document.getElementById("uploadPreview").
        var img = new Image();
        img.src = ev.target.result;
        img.onload = function()
        {
            img = this;
            document.body.appendChild(img);
            imgW = img.offsetWidth;
            imgH = img.offsetHeight;
            //var canvas = document.getElementById('src');
            //var ctx = canvas.getContext('2d');
            //canvas.width = img.offsetWidth;
            //canvas.style.width = img.offsetWidth.toString() + "px";
            //canvas.height = img.offsetHeight;
            //canvas.style.height = img.offsetHeight.toString() + "px";
            document.body.removeChild(img);
            var s = (new Date()).getTime();
            //showMsg("Detecting ...");
            var comp = [];
            if (window['DETECT_FACES'] && DETECT_FACES == true)
            {
                comp = ccv.detect_objects({
                    "canvas": ccv.grayscale(ccv.pre(img)),
                    "cascade": cascade,
                    "interval": 5,
                    "min_neighbors": 1
                });
                // console.log(comp)
            }
            //showMsg("Elapsed time : " + ((new Date()).getTime() - s).toString() + "ms");
            //ctx.drawImage(img, 0, 0);
            //ctx.lineWidth = 3;
            //ctx.strokeStyle = "#f00";
            //console.log('img w,h', imgW, imgH);
            var faces = [];
            for (var i = 0; i < comp.length; i++) {
                //ctx.strokeRect(comp[i].x, comp[i].y, comp[i].width, comp[i].height);
                //console.log(comp[i].x, comp[i].y, comp[i].width, comp[i].height);
                var ox = ((comp[i].x + comp[i].width/2)/imgW); // pos to CENTER, not to left corner!
                var oy = ((comp[i].y + comp[i].height/2)/imgH);
                var fr = (comp[i].height/2)/imgW;
                faces.push([parseFloat(ox.toFixed(3)), parseFloat(oy.toFixed(3)), parseFloat(fr.toFixed(2))]); // offsetleft, offsettop, radius
            }
            file.faces = comp.length;
            file.facelist = JSON.stringify(faces);
            fileQueue.push(file);
            preprocessL--;
            //console.log('added to Q ', fileQueue.length, 'faces', comp.length);
            //console.log(comp) // - comp - raw faces data
            //console.log(faces); // - faces - % offset x,y, face radius
            // console.log('Queue to preprocess & upload', fileQueue.length, preprocessL);

            // AUTO UPLOAD QUEUE
            if (preprocessL == 0) new fileQueueUploader(fileQueue, onAllUploaded, onEachUploaded);

        };
    }
	//fileQueue.push(file);
	//console.log('added to Q ', fileQueue.length);
}



var onAllUploadedDefault = function(data)
{
    var total = data.length;
	console.log('All uploaded (onAllUploadedDefault)', total);
	console.log(data);
}

var onEachUploadedDefault = function(data)
{
    var processed = this[0], total = this[1];
    console.log('file uploaded', processed, total);
    console.log(data);
}

// MAIN
// WOKR IF:
    // exists: #upload_destination
    // optional: #upload_container (targer album etc), #onAllUploadedLocal (callback)
    // input type file #fileField, drop area div #fileDrop, optional submit but #upload
    // UI: showUploadOverlay, hideUploadOverlay callbacks
window.onload = function () {

    var uploadForms = document.querySelectorAll('form.gcupload');
    for (var u=0; u<uploadForms.length;u++)
    {
        form = uploadForms[u];

        // tagret urn for inplace photo extend
        var uploaddest = form.querySelector(".upload_destination");
        //console.log(uploaddest);
        if (!uploaddest) return;

        destination = uploaddest.value;
        // target photoalbum, news etc urn of container
        if (form.querySelector('.upload_container')) container = form.querySelector(".upload_container").value;
        if (form.querySelector('.onalluploadedcall')) onalluploadedcall = form.querySelector(".onalluploadedcall").value;
        if (form.querySelector('.oneachuploadedcall')) oneachuploadedcall = form.querySelector(".oneachuploadedcall").value;

        // ON UPLOAD ALL
        if (window[onalluploadedcall])
            onAllUploaded = window[onalluploadedcall]
        else if (window['onAllUploadedDefault'])
            onAllUploaded = window['onAllUploadedDefault']
        else
        {
            console.log('! No onalluploadedcall & no onAllUploadedDefault')
            onAllUploaded = noop
        }
        // ON UPLOAD EACH
        console.log(form, oneachuploadedcall)
        if (window[oneachuploadedcall])
            onEachUploaded = window[oneachuploadedcall].bind(form)
        else if (window['onEachUploadedDefault'])
            onEachUploaded = window['onEachUploadedDefault']
        else
            onEachUploaded = noop

        // DROPBOX
        if (form.querySelector('.fileDrop')) new DropArea(form.querySelector('.fileDrop'));

        // FILE SELECT
        form.querySelector(".fileField").onchange = function(ev){
            addFileListItemsWithFileReader(this.files);
        };

        // BUTTON ACTION UPLOAD
        var uploadbut = form.querySelector(".upload");
        if (uploadbut)
        {
            uploadbut.onclick = function()
            {
                new fileQueueUploader(fileQueue, onAllUploaded, onEachUploaded);
            };
        }
    }

	
}


/*
used by fapi.js
*/
function showUploadOverlay()
{
    document.getElementById('light').style.display = 'block';
    document.getElementById('fade').style.display = 'block'
}
function hideUploadOverlay()
{
    document.getElementById('light').style.display = 'none';
    document.getElementById('fade').style.display = 'none'
}