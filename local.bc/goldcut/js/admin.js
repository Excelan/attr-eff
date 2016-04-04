window.addEvent('domready',

function() {

    tinymce.init(tinyOptions);

    /**
	statuses change with cookie save
	*/
    var unsetStatus = function(entity, status)
    {
        var HashCookie = new Hash.Cookie(entity + '-statuses');
        HashCookie.erase(status);
        //console.log(status, HashCookie.get(status));
    }
    var deactivateStatus = function(entity, status)
    {
        var HashCookie = new Hash.Cookie(entity + '-statuses');
        HashCookie.set(status, -1);
        //console.log(status, HashCookie.get(status));
    }
    var activateStatus = function(entity, status)
    {
        var HashCookie = new Hash.Cookie(entity + '-statuses');
        HashCookie.set(status, 1);
        //console.log(status, HashCookie.get(status));
    };
    var chstatusE = function(e) {
        e.stop();
        var entity = e.target.get('data-entity');
        var statusname = e.target.get('data-statusname');
        e.target.toggleClass('statusactive');
        $$('.handler-status-' + statusname).removeClass('statusselected');
        e.target.addClass('statusselected');
        activateStatus(entity, statusname);
        setTimeout(reload, 50);
    };
    var chstatusD = function(e) {
        e.stop();
        var entity = e.target.get('data-entity');
        var statusname = e.target.get('data-statusname');
        $$('.handler-status-' + statusname).removeClass('statusselected');
        e.target.addClass('statusselected');
        deactivateStatus(entity, statusname);
        setTimeout(reload, 50);
    };
    var chstatusA = function(e) {
        e.stop();
        var entity = e.target.get('data-entity');
        var statusname = e.target.get('data-statusname');
        $$('.handler-status-' + statusname).removeClass('statusselected');
        e.target.addClass('statusselected');
        unsetStatus(entity, statusname);
        setTimeout(reload, 50);
    };
    var reload = function()
    {
        window.location.reload(true);
    }
    $$('.chstatusE').addEvent('click', chstatusE);
    $$('.chstatusD').addEvent('click', chstatusD);
    $$('.chstatusA').addEvent('click', chstatusA);



	/**
	Спешает ли часы клиента определяется ебз учет временной зоны только по разнице unixtime
	*/

    // client TS
    var clientts = Math.round(new Date().getTime() / 1000);
    // server TS
    var serverTs = document.id('serverts').get('value').toInt();
    // difference in seconds
    var diffTsTimeClientServer = clientts - serverTs;
    //console.log(clientts, serverTs);

    // warn diff time client - server > 1 min
    if (Math.abs(diffTsTimeClientServer) >= 60)
    {
        document.id('timeCorr').set('html', '&Delta;' + Math.round(diffTsTimeClientServer / 60) );
    }

    // client GMT offset
    var clientGMTOffset = -(new Date().getTimezoneOffset() / 60);
    if (clientGMTOffset > 0) clientGMTOffsetTEXT = 'GMT +' + clientGMTOffset;
    else clientGMTOffsetTEXT = 'GMT ' + clientGMTOffset;
    document.id('clientTimeZone').set('text', clientGMTOffsetTEXT);
    // server GMT offset
    var serverGMTOffset = document.id('serverTimeZone').get('text').toInt();
    // compare client & server TZ
    if (serverGMTOffset != clientGMTOffset)
    {
        document.id('serverTimeZone').set('text', serverGMTOffset);
        document.id('GMTinfo').setStyle('color', 'yellow');
    }
    // every second
    var Site = {
        counter: 0
    };
    var addCount = function()
    {
        this.counter++;
        var ts = Math.round(new Date().getTime() / 1000);
        document.id('clientTime').set('text', timestampToYYYYMMDDHHMMSS(ts));
    };
    addCount.periodical(1000, Site);


    var moveClick = function (o) {

		if (selected && selected != this) {
			// MOVE AFTER
			/*
			console.log(selected);
			console.log(this);
			console.log(selected.getParent());
			console.log(selected.getParent().getParent());
			console.log();
			*/
			selected.removeClass('move1selected');
			this.removeClass('move1selected');

			var selectedTR = selected.getParent().getParent().getParent();
			var clickedTR = this.getParent().getParent().getParent();
			// cut & insert
			selectedTR.dispose().inject(clickedTR, 'before');
			// new ordered urns
			var neworder = [];

			var neightsTR = selectedTR.getParent().getChildren('tr');
			var i=0;
			neightsTR.each(function (o) {
				var urn = o.get('id');
				if (urn)
				{
					i++;
					try {
						o.getElements('.moveData')[0].set('text',i);
						o.getElements('.moveDataS')[0].set('text',i);
					}
					catch (e) {}
					neworder.push(urn);
				}

			});
			// console.log(neworder);
			var message = new Hash();
			message.action = 'reorder';
			message.urn = this.getParent('table').get('data-hosturn');
			message.order = neworder;
			// console.log(message);
			new Request({method:'post', url:'/goldcut/admin/ajax.php'}).send(message.toQueryString());
			selected = null;
			//moveEnabler();
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

	}


	var selected = null;
	$$('.moveData').addEvent('click', moveClick);
	$$('.moveDataS').addEvent('click', moveClick);

});
