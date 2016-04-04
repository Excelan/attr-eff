GCFileUpload = function (el) {
    //console.log('init', el);
    if (el !== undefined)
    {
      //console.log('init concrete', el);
      new FileInput(el);
    }
    else {
      //console.log('init find');
      var uploadForms = document.querySelectorAll('[data-upload]');
      //console.log(uploadForms);
      for (var u=0; u<uploadForms.length;u++)
      {
          //console.log('upload ', u);
          var form = uploadForms[u];
          new FileInput(form);
      }
    }
}

function GlobalUploadQueue()
{
    this.queue = []; // {hashcrc, binary, metadata {filename, destinationEntity, containerURN, facecount, facelist} }
}
GlobalUploadQueue.prototype.addFile = function(f)
{
    // this.queue.push(f) // TODO queue management
    //console.log(uploadQueue.queue);
    this.httpUploadFile(f); // TODO upload after each on Uploaded - 1 after 1, no parallel
}
// will be redifined later
GlobalUploadQueue.prototype.httpUploadFile = function(f)
{
    // Forward declaration
    // on each uploaded >> FI.notifyFileUploaded(hashcrc)
    // on each progress >> FI.notifyProgressOn(hashcrc)
}

// GLOBAL
var uploadQueue = new GlobalUploadQueue();

/*
add cancel upload (in waiting or active upload) > remove from global upload (xhr.abort() +  xhr.addEventListener("abort", uploadCanceled, false))
 */
function FileInput(domcontainer)
{
    this.gate = undefined;
    this.contenttype = undefined; // 'image', 'audio', 'video', 'archive', 'document'
    this.uid = getRandomArbitary(1000000, 9000000);
    // builde from dom or generate input+drop responsive
    this.total = 0; // число известно сразу по длине масисва файлов, а queue будет наполнена только после прочтения их всех
    this.d = 0; // сколько уже закачано. тк queue - async и ее length может быть меньше total к моменту прочтения всех файлов, тк первый уже отправлен, пока другие считывались
    //this.queue = []; // { hashcrc(formUID+filename+size), filename, size, progress, started/uploading/done }
    this.domcontainer = domcontainer;
    this.dominput = null;
    this.domdroparea = null;
    this.uploadQueue = uploadQueue; //new GlobalUploadQueue();
    this.state = 'ready'; // busy, done->ready
    this.onBeginUpload = null;
    this.onDoneUpload = null;
    this.userOnEachUploaded = noop;
    this.userOnAllUploaded = noop;
    this.sharedmetadata = {};
    this.init();
}
//FileInput.prototype.__defineGetter__("done", function() { /*console.log('get', this.d);*/ return this.d });
//FileInput.prototype.__defineSetter__("done", function(d) { this.d = d; /*console.log('set', d)*/ });
FileInput.prototype.init = function()
{
    var form = this.domcontainer;
    var dataDependence = false;
    if (form.getAttribute("data-gate")) this.gate = form.getAttribute("data-gate");
    else this.gate = '/goldcut/upload/stream.debug.php';//"/goldcut/upload/stream.php";
    this.sharedmetadata.destinationentity = form.getAttribute("data-upload");
    if (form.getAttribute("data-container")) this.sharedmetadata.containerurn = form.getAttribute("data-container");
    else this.sharedmetadata.containerurn = id('subjectURN').value;
    if (form.getAttribute("data-eachuploaded")) var oneachuploadedcall = form.getAttribute("data-eachuploaded");
    if (form.getAttribute("data-alluploaded")) var onalluploadedcall = form.getAttribute("data-alluploaded");

    if (window[oneachuploadedcall]) this.userOnEachUploaded = window[oneachuploadedcall];
    if (window[onalluploadedcall]) this.userOnAllUploaded = window[onalluploadedcall];

    var that = this;
    //var x = form.querySelector('input[type="file"]');
    //console.log('file input',x);
    form.querySelector('input[type="file"]').onchange = function(ev){
        //console.log('file selected',this);
        if (form.getAttribute("data-dependence")) dataDependence = form.getAttribute("data-dependence");
        else dataDependence = false;

        that.html5FilesReader(this.files,dataDependence);
    };
    if (form.querySelector('.droparea'))
        new DropArea(form.querySelector('.droparea'), this);
    addClass(this.domcontainer, 'ready');
}
FileInput.prototype.stateBegin = function(){
    removeClass(this.domcontainer, 'ready');
    addClass(this.domcontainer, 'busy');
}
FileInput.prototype.stateDone = function(){
    removeClass(this.domcontainer, 'busy');
    addClass(this.domcontainer, 'ready');
}
FileInput.prototype.onEachUploaded = function(f)
{
    // memory free f.binarydata
    //console.log('FileInput.prototype.onEachUploaded');
    //console.log(f)
    this.userOnEachUploaded(f);
    this.done++;
    if (this.done == this.total) this.onAllUploaded();
    // TODO queue[f.uid]
}
FileInput.prototype.onAllUploaded = function()
{
    //console.log('DONE FileInput.prototype.onAllUploaded');
    this.stateDone();
    this.userOnAllUploaded.call()
}

FileInput.prototype.html5FilesReader = function(files,datadependence)
{
    // on each load >> addReadenFile
    this.stateBegin();
    this.total = files.length;
    this.done = 0;
    this.datadependence = datadependence;
    for (var i = 0; i < files.length; i++) {
        var fr = new FileReader();
        fr.file = files[i];
        fr.onloadend = this.addReadenFile.bind(this);
        fr.readAsDataURL(files[i]);
    }
}
FileInput.prototype.addReadenFile = function(ev)
{
    var file = ev.target.file;
    //var filesize = Math.round(file.size/1024)+"KB";
    //console.log(file.name, file.type, Math.round(file.size/1024)+"KB");
    if (this.contenttype == 'image')
    {
        rFilter = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;
        if (!rFilter.test(file.type)) { throw "FILE TYPE NOT SUPPORTED FOR UPLOAD" }
    }
    f = {};
    f.file = file;
    f.name = file.name;
    f.size = file.size;
    f.uid = crc32(this.uid + f.name + f.size);
    f.manager = this;
    f.destinationentity = this.sharedmetadata.destinationentity;
    f.containerurn = this.sharedmetadata.containerurn;
    f.datadependence = this.datadependence;
    //this.queue.push(f) // TODO
    this.uploadQueue.addFile(f)

}

GlobalUploadQueue.prototype.httpUploadFile = function(f)
{
    var file = f.file;
    //console.log(f.manager)
    //var that = this;
    //var responses = []
    var xhr = new XMLHttpRequest();
    var upload = xhr.upload;
    /**
     * TODO upload progress
    upload.addEventListener("progress", function (ev) {
        if (ev.lengthComputable) {
            this.progress = (ev.loaded / ev.total) * 100 + "%";
            if (this.progress != '100%') console.log(this.progress);
        }
    }, false);
    */
    //upload.addEventListener("load", function (ev) { console.log('uploaded'); }, false);
    upload.addEventListener("error", function (ev) {console.log(ev);}, false); // TODO queue rm
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4)
        {
            if (xhr.status == 200)
            {
                if (xhr.responseText != null)
                {
                    //f.manager.classList.remove('busy');
                    var ok = false;
                    var jsObject;
                    try
                    {
                        jsObject = JSON.parse(xhr.responseText);
                        //responses.push(jsObject);
                        f.response = jsObject
                        ok = true
                    }
                    catch (e) {
                        console.log(e, xhr.responseText);
                    }
                    finally
                    {
                        if (ok === true) f.manager.onEachUploaded(f)
                    }
                }
            }
        }
        else {
          //console.log(xhr)
        }
    }
    var name = transliterate(f.name).replace(' ','-');
    xhr.open("POST", f.manager.gate);
    xhr.setRequestHeader("Cache-Control", "no-cache");
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.setRequestHeader("X-File-Name", name);
    //xhr.setRequestHeader("X-Container", name);
    if (f.destinationentity) xhr.setRequestHeader("X-destination", f.destinationentity);
    if (f.containerurn) xhr.setRequestHeader("X-container", f.containerurn);
    if (f.datadependence) xhr.setRequestHeader("X-dependence", f.datadependence);
    //xhr.setRequestHeader("X-facecount", file.faces);
    //xhr.setRequestHeader("X-facelist", file.facelist);
    //if (f && f.manager) f.manager.classList.add('busy');
    xhr.send(file);
}

DropArea = function(dropareaDomId, manager)
{
    droparea = dropareaDomId;
    this.init = function () {
        droparea.addEventListener("dragenter",  this.stopProp, false);
        droparea.addEventListener("dragleave",  this.dragExit, false);
        droparea.addEventListener("dragover",  this.dragOver, false);
        droparea.addEventListener("drop",  this.onDropFiles, false);
    }
    this.onDropFiles = function (ev) {
        console.log(ev);
        var form = this.parentNode;
        var dataDependence = false;
        if (form.getAttribute("data-dependence"))  dataDependence = form.getAttribute("data-dependence");
        else dataDependence = false;

        ev.stopPropagation();
        ev.preventDefault();
        removeClass(this, 'dragover');
        var files = ev.dataTransfer.files;
        console.log(files);
        manager.html5FilesReader(files,dataDependence);
    }
    this.dragOver = function (ev) {
        ev.stopPropagation();
        ev.preventDefault();
        addClass(this, 'dragover')
    }
    this.dragExit = function (ev) {
        ev.stopPropagation();
        ev.preventDefault();
        removeClass(this, 'dragover')
    }
    this.init();
}
