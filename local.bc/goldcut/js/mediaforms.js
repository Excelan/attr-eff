window.addEvent('domready',

function() {

    var uploaded_content_text = function(data)
    {
        try
        {
            if (true)
            // RICH_TEXT_EDITOR
            {
                var ms;
                var imgalt = '';
                var mediaserver = '';
                //if (data.alt) imgalt = data.alt.replace(/-/gi, ' ');
                imgalt = data.title;

                if (data.mediaserver)
                {
                    mediaserver = 'http://i' + data.mediaserver + '.' + window.location.hostname;
                    data.image.uri = data.image.uri.replace('media/', '');
                }
				tinyMCE.activeEditor.execCommand('mceInsertContent',false, "\n<p><img src=\""+mediaserver+data.image.uri+"\" alt=\""+imgalt+"\"></p>\n");
            }
            else
            {
                //setCaretPosition(document.id('content_text'), 10);
                //insertAtCaret('content_text', "n/"+data.urn+"/"+data.filename+"/"+data.alt+"/n");
                insertAtCaret(data.target, "n/" + data.urn + "/" + data.alt + "/n");
            }
        }
        catch(Error)
        {
            console.log(Error);
            // Error.message tinymce errors
        }
    };

    var uploaded_illustration = function(data)
    {
        //console.log('illustration', data);
        document.id('illustration').set('src', data.uri);
        document.id('illustration_hidden_input').set('value', data.urn);

        var img = new Element('img', {
            src: data.thumbnail.uri,
            alt: data.thumbnail.alt
        });
        document.id('fileuiillustration_dropbox').grab(img);
        // getElement("div").
    };

    var uploaded_hasmanyphotos = function(data)
    {
        build_photoitem([data]);
    };

    var uploaded_hasonephoto = function(data)
    {
        var entityname = data.urn.split('-')[1];
        build_hasonephoto([data], entityname);
    };

    var uploaded_hasonevideo = function(data)
    {
        build_hasonevideo([data]);
    };

    var uploaded_hasoneaudio = function(data)
    {
        build_hasoneaudio([data]);
    };

    var uploaded_hasoneattach = function(data)
    {
        build_hasoneattach([data]);
    };

    ps.subscribe("uploaded/content_text", uploaded_content_text);
    ps.subscribe("uploaded/illustration", uploaded_illustration);
    ps.subscribe("uploaded/hasmanyphotos", uploaded_hasmanyphotos);
    ps.subscribe("uploaded/hasonephoto", uploaded_hasonephoto);
    ps.subscribe("uploaded/hasonevideo", uploaded_hasonevideo);
    ps.subscribe("uploaded/hasoneaudio", uploaded_hasoneaudio);
    ps.subscribe("uploaded/hasoneattach", uploaded_hasoneattach);


    ps.subscribe("progress",
    function(progress)
    {
        //console.log(progress.progress+'%')
	});


    function flashUploadHere(cnt)
    {
        var uploadpostparams = {};
        if (cnt.get('data-destination')) uploadpostparams.destination = cnt.get('data-destination');
        if (cnt.get('data-target')) uploadpostparams.target = cnt.get('data-target');
        if (cnt.get('data-entity')) uploadpostparams.entity = cnt.get('data-entity');
        if (cnt.get('data-host')) uploadpostparams.hostentity = cnt.get('data-host').split('-')[1];
        if (uploadpostparams.hostentity)
        		uploadpostparams[uploadpostparams.hostentity] = cnt.get('data-host');

        var flashbutid = cnt.get('data-target') + 'flashbutton';
        cnt.grab(new Element('span', {
            id: flashbutid
        }));
        var uploadoptions = {};
        uploadoptions.post_params = uploadpostparams;
        uploadoptions.upload_url = "/goldcut/upload/";
        uploadoptions.button_placeholder_id = flashbutid;
        //uploadoptions.upload_url = "/upload.debug.php";
        //uploadoptions.file_types = "*.jpg; *.png";
        //uploadoptions.file_types_description = "JPG Images; PNG Image";
        //$('initupload').dispose();
        //var contents = cnt.getChildren('img');
        //cnt.empty();
        var dropbox = new Element('div', {
            'id': cnt.get('id') + '_dropbox',
            'text': '',
            'class': 'dropbox'
        });
        //dropbox.adopt(contents);
        cnt.grab(dropbox);
        var progressdiv = new Element('div', {
            'id': cnt.get('id') + '_dropbox_progress',
            'text': '',
            'class': 'dropboxprogress'
        });
        dropbox.grab(progressdiv);

        uploadoptions.progressdiv = progressdiv;
        var upload = new Uploader(uploadoptions);
        upload.activate();

    }
    // upload init
    $$('.dropbox-container').each(function(cnt) {
        if (BrowserDetect.browser == 'Explorer' || BrowserDetect.browser == 'Opera')
        {
            flashUploadHere(cnt);
        }
        else
        {
            new UIFile({
                container: cnt.get('id')
            })
        }

    });


   		/**
		FORM ON SUBMIT e.stop();
		*/
	   if (document.id('entityformset'))
	   {
	       document.id('entityformset').addEvent('submit',
	       function(e) {
				var mu = {};

				var fields = this.getElements('input');
	           fields.each(function(fs) {
	               if (fs.get('name') && fs.get('value') != null) mu[fs.get('name')] = fs.get('value');
	           });

				if (typeof(GCAdminOnSaveCB) == 'function')
				{
					var cbret = GCAdminOnSaveCB.call(document.id('entityformset'), mu);
					if (cbret == false) return false;
				}
				/*
	           var fieldsets = this.getElements('fieldset');
	           fieldsets.each(function(fs) {
	               console.log(fs.getElement('legend').get('text'));
	               var inputs = fs.getElements('input');
	               console.log(inputs);
	           });
				*/
	           return true;
	       });
	   }

    /**
	DELETE
	*/
    if (document.id('urn_delete'))
    {

        document.id('urn_delete').addEvent('click',
        function(e) {
            e.stop();
            if (confirm('Delete?'))
            {
                var urn = this.get('data-urn');
                var user = this.get('data-user');
                new Request.JSON({
                    url: '/goldcut/admin/ajax.php',
                    onSuccess: function(d) {
                        var ename = d.urn.split(':')[1]+':'+d.urn.split(':')[2]+':'+d.urn.split(':')[3];
                        var turn = 'urn:' + ename;
                        var href = '/goldcut/admin/?urn=' + turn + '&action=list&lang=ru';
                        location.href = href;
                    }
                }).post({
                    action: "delete",
                    user: user,
                    urn: urn
                });
            }
        });

    }


    /**
	CLONE
	*/
    if (document.id('urn_clone'))
    {

        document.id('urn_clone').addEvent('click',
        function(e) {
            e.stop();
                var urn = this.get('data-urn');
                var user = this.get('data-user');
                new Request.JSON({
                    url: '/goldcut/admin/ajax.php',
                    onSuccess: function(d) {
						console.log(d);
                        //var ename = d.urn.split('-')[1];
                        //var turn = 'urn-' + ename;
                        var href = '/goldcut/admin/?urn=' + d.urn + '&action=edit&lang=ru';
                        location.href = href;
                    }
                }).post({
                    action: "copy",
                    user: user,
                    urn: urn
                });

        });

    }

    // hidden ts
    $$('.timestamp').each(function(input, i)
    {
        var ts = input.get('value').toInt();
        var dateh = timestampToYYYYMMDDHHMMSS(ts);
        if (ts)
        {
            var fname = input.get('name');
            if (input.disabled)
            {
                //var fnameh = fname+'_h';
                //document.id(fnameh).set('value', dateh);
                var fnamecal = fname + '_cal';
                new Element('div', {
                    'text': dateh,
                    'class': 'disabledInputText'
                }).inject(document.id(fnamecal));
            }
            else
            {
                var rd = YYYYMMDDHHMMtoTimestamp(dateh);
                //console.log(dateh);
                //console.log(ts);
                //console.log(rd, rd-ts, (rd-ts)/60);
                var gcd = timestampToGCDate(ts);
                //console.log(gcd);
                var rrd = GCDateToTimestamp(gcd);
                //console.log(rrd, rrd-ts);
                htmlDateTimeSelect(ts, fname);
            }
        }
        else
        {
            var fname = input.get('name');
            if (input.disabled)
            {
                var fnamecal = fname + '_cal';
                new Element('div', {
                    'html': '&mdash;',
                    'class': 'disabledInputText'
                }).inject(document.id(fnamecal));
            }
            else
            {
                htmlDateTimeSelect(currentTimestamp(), fname);
            }
        }
        /*
	  input.addEvent('changed', function(ev){
	  	console.log('datasource changed');
	  });
	  */
    });
    // _h - is text input
    /*
	$$('.timestamp_h').addEvent('change', function(e){
		var val = e.target.get('value');
		var src = e.target.get('data-source');
		document.id(src).set('value', YYYYMMDDHHMMtoTimestamp(val));
	});
	*/


var logoutBut = id('logout');
if (logoutBut)
{
	logoutBut.addEvent('click', function(e){
		e.stop();
		m = {};
		m.action = 'logout';
		m.urn = 'urn-user-'+this.getAttribute('data-user');
		ajax('/member/logout', function(){ window.location.reload() }, {'onError': function(){window.location.reload()}}, 'POST', m);
	});
}


if ('querySelectorAll' in document)
{

	var images64 = document.querySelectorAll('.image64');
	//console.log(images64);
	for (var i=0;i<images64.length;i++)
	{
		image64 = images64[i];

		if (typeof window.FileReader === 'undefined') {
		  if (image64.classList)
			  image64.classList.add("fail")
		  else
			  image64.className = 'fail';
		} else {
		  //image64.className = 'success';
		  image64.classList.add("success")
		}

		image64.ondragover = function () { this.classList.add("hover"); return false; };
		image64.ondragend = function () { this.classList.remove("hover"); return false; };
		image64.ondrop = function (e) {
		  //this.className = '';
		  this.classList.remove("hover");
		  e.preventDefault();
		  var file = e.dataTransfer.files[0];
		  var reader = new FileReader();
		  reader.onload = function (event) {
			//console.log(event.target); // FileReader loaded
			image64.style.background = 'url(' + event.target.result + ') no-repeat center';
			var field_name = image64.getAttribute('data-fieldname');
			document.querySelectorAll('input.'+field_name)[0].value = event.target.result;
		  };
		  //console.log(file); // File
		  reader.readAsDataURL(file);

		  return false;
		};
	}


	var switchTab = function(clickedTab)
	{
		var activeTab = document.querySelectorAll('a.tab-active')[0]; // same code
		if (clickedTab == activeTab.parentNode) return false;
		// console.log('SWITCH from A active to Li Clicked', activeTab, clickedTab, activeTab.parentNode);
		var targetTabdivClass = clickedTab.getAttribute('data-tab');
		var onselect = clickedTab.getAttribute('data-tabon');
		// console.log(onselect, window[onselect]);
		// console.log(targetTabdivClass);
		if (!targetTabdivClass) console.log('tab-nav has no [data-tab] attrib');
		var targetTabDIV = document.querySelectorAll('.'+targetTabdivClass)[0];
		if (!targetTabDIV) console.log('tab-nav target div class ['+targetTabdivClass+'] by [data-tab] attrib not exists');
		var shownTab = document.querySelectorAll('.tab-content:not(.hide)')[0];
		removeClass(targetTabDIV, 'hide');
		// console.log(shownTab);
		addClass(shownTab, 'hide');
		activeTab = document.querySelectorAll('a.tab-active')[0];
		removeClass(activeTab, 'tab-active');
		addClass(clickedTab.childNodes[0], 'tab-active');
		if (onselect && window[onselect]) window[onselect]();
	}
	// dont use hypens- in class names for data-tab
	var navTabs = document.querySelectorAll('#tab-nav li');
	var activeTab = document.querySelectorAll('a.tab-active')[0]; // same
	// console.log(window.location.hash, activeTab);
	for (var i=0; i<navTabs.length; i++)
	{
		var tab = navTabs[i];
		// console.log(tab.childNodes[0], tab.childNodes[0].getAttribute('href'), tab.getAttribute('data-tab'));
		if (!activeTab && i == 0) addClass(tab.childNodes[0], 'tab-active'); // activate first by def. was && !window.location.hash
		//if (!activeTab && !window.location.hash) window.location.hash = tab.childNodes[0].getAttribute('href');
		if (!activeTab && window.location.hash == '#'+tab.getAttribute('data-tab') && i>0) switchTab(tab);

		var sameLinks = document.querySelectorAll( '.alttabactivate a[href="/account#'+tab.getAttribute('data-tab')+'"]' );
		// console.log();

		var switcher = function (e) {
			switchTab(this);
		}

		for (var l=0; l<sameLinks.length; l++)
		{
			var sl = sameLinks[l];
			// console.log(sl, tab);
			sl.onclick = switcher.bind(tab);
		}

		tab.onclick = function (e) {
			var clickedTab = this;
			switchTab(clickedTab);
		};
	}

	var linkbuttons = document.querySelectorAll('.linkbutton');
	var linkCheckProcessor = function(data){
		//console.log('linkchecked', data, this)
		var linkBut = this[0];
		var m = this[1];
		if (data.error == 'Anonymous')
		{
			if (ENV == 'DEVELOPMENT') console.log('Anonymous cant check list '+data.context);
			var atext = linkBut.getAttribute('data-anonymous-text');
			if (atext) linkBut.innerText = atext;
		}
		else if (data.exists == 1)
		{
			linkBut.innerText = linkBut.getAttribute('data-exists-text');
			linkBut.setAttribute('data-active','yes');
			m.action = 'unlink';
		}
		else
		{
			linkBut.innerText = linkBut.getAttribute('data-absent-text');
			linkBut.setAttribute('data-active','no');
			m.action = 'link';
		}
	};

	var linkedProcessor = function(data){
		var linkBut = this;
		if (data.error == 'Anonymous')
		{
			if (ENV == 'DEVELOPMENT') console.log('Anonymous cant add/remove to/from list '+data.context);
			var atext = linkBut.getAttribute('data-anonymous-text');
			if (atext) linkBut.innerText = atext;
			else linkBut.innerText += ' только для зарегистрированных';
		}
		else if (data.link == 'established')
		{
			linkBut.innerText = linkBut.getAttribute('data-exists-text');
			linkBut.setAttribute('data-active','yes');
		}
		else if (data.link == 'removed')
		{
			linkBut.innerText = linkBut.getAttribute('data-absent-text');
			linkBut.setAttribute('data-active','no');
		}
	};

	var linkError = function(errorCode, errorText) { if (errorCode == 401) alert('Только для зарегистрированных пользователей'); else alert(errorCode+errorText); };



	for (var i=0; i<linkbuttons.length; i++)
	{
		var linkbutton = linkbuttons[i];

		var m = {}
		m.listname = linkbutton.getAttribute('data-listname');
		m.object = linkbutton.getAttribute('data-object');
		m.subject = linkbutton.getAttribute('data-subject');

		var asyncLoadAfterPageLoad = (linkbutton.getAttribute('data-async') == 'yes') ? true : false;
		if (asyncLoadAfterPageLoad)
		{
			//console.log('async')
			//console.log(m)
			ajax('/link/check', linkCheckProcessor.bind([linkbutton,m]), {'onError': linkError}, 'POST', m);
		}
		else
		{
			//console.log('sync')
			m.action = (linkbutton.getAttribute('data-active') == 'no') ? 'link' : 'unlink';
		}

		var userSuccessCallbackName = linkbutton.getAttribute('data-onsuccess');
		if (userSuccessCallbackName && window[userSuccessCallbackName]) linkedProcessor = window[userSuccessCallbackName];

		linkbutton.onmouseover = function () { this.classList.add("hover"); return false; };
		linkbutton.onmouseout = function () { this.classList.remove("hover"); return false; };
		linkbutton.onclick = function (e) {
		  this.classList.remove("hover");
		  e.preventDefault();
			m.action = (linkbutton.getAttribute('data-active') == 'no') ? 'link' : 'unlink';
			//console.log(m)
			ajax('/link', linkedProcessor.bind(linkbutton), {'onError': linkError}, 'POST', m); // 'onStart': xz,
		};
	}




	// <a class='post' data-action='cancel' data-urn='urn-service' data-confirm='user' href='/urn-service/cancel'>Cancel</a>
	// TODO split/use /urn-service/cancel as urn/action
	var allpostButtons = document.querySelectorAll('a.post');
	for (var i=0; i<allpostButtons.length; i++)
	{
		var postButtons = allpostButtons[i];
		var linkProcessor = function(report){
			//console.log('ajax',report)
			if (report.text) postButtons.set('text', report.text);
			if (report.reload) window.location.reload();
			if (report.redirect) window.location = report.redirect;
		};
		var linkError = function(errorCode, errorText) { if (errorCode == 401) alert('Только для зарегистрированных пользователей'); else alert(errorCode+errorText); };
		postButtons.onclick = function (e) {
		    e.preventDefault();
			m = {}
			m.action = this.getAttribute('data-action');
			m.urn = this.getAttribute('data-urn');
			if (this.getAttribute('data-confirm'))
			{
				if (!confirm('Вы уверены?')) return false;
			}
			var targeturi = '/userservice';
			var inplacedefinedtarget = this.getAttribute('data-target');
			if (inplacedefinedtarget) targeturi = inplacedefinedtarget;
			ajax(targeturi, linkProcessor, {'onError': linkError}, 'POST', m);
		};
	}
}

}); // onload
