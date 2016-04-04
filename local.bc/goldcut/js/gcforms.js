/*
TODO on button click - when action be ready > close current window or change it someway or close  but then show some notification about complete action
2 types of buttons - actions over main window & action over list element / sublist (like photo comment inside list of photos)
 */
var GOLDCUT = GOLDCUT || {};
GOLDCUT.MODEL = {};
GOLDCUT.CONFIG = {};
GOLDCUT.CONFIG.ENTITY = {};

GOLDCUT.FORM = {}
GOLDCUT.FORM.urn = null;

// entity model object for attach to GOLDCUT.MODEL[urn] = new model(urn)
var model = function(o) {
    this.o = o;
    // check this.o.urn exists
    // merge {updatedonserverts: null, lastserversync: null, localupdatedts: 'now'}
    this.cbs = {};
}
// intent change of key F in model
model.prototype.change = function(f, nv){
    okv(this.o, f, nv); // set by string key (model[urn-some-1].change('illustration.title', 'newvalue'))
//    this.o[f] = nv;
//    if (!okv(this.o, f)) return;
    if (!this.cbs[f]) {
		console.log("NO CALLBACK FOR MODEL.ONCHANGE");
		return;
	}
    for (var i=0; i < this.cbs[f].length; i++)
    {
        this.cbs[f][i].notify(nv);
    }
    console.log(this.o.urn, f, nv);
    updateObjectInStore(this.o.urn, f, nv).then(function(obj1){ console.log('UPDATED updateObjectInStore', obj1) }, MYDB.onerror);
}
// add callback for on field named F changed
model.prototype.onChange = function(f, cb){
    if (!this.cbs[f]) this.cbs[f] = [];
    // check is cb has .notify func?
    this.cbs[f].push(cb);
}



function RootFormControl(configXML, urn, domContainer)
{
    //console.log(configXML, domContainer);

    var entityManager = configXML.getElementsByTagName("entity")[0].getAttribute('manager');
    var entityName = configXML.getElementsByTagName("entity")[0].getAttribute('name');
    //console.log('RootFormControl for ', entityName, entityManager);

    var form = document.createElement("form");
    form.setAttribute('data-urn', urn);
	GOLDCUT.FORM.urn = urn;


    var div = document.createElement("div");
    div.classList.add('urndisplay');
    //div.innerHTML = 'Номер';
    var input = document.createElement("p");
    //input.setAttribute('disabled', 'disabled');
    //input.setAttribute('value', new URN(urn).uuid);
    input.innerHTML = new URN(urn).uuid;
    div.appendChild(input);
    domContainer.appendChild(div);

    //form.data = {inittime: new Date().getTime()};

    // build form from xml
    new GeneralFormControl(configXML, form);
    domContainer.appendChild(form);

    var submitWF = document.createElement("input");
    submitWF.setAttribute('type', 'button');
    submitWF.setAttribute('name', 'sendwf');
    submitWF.setAttribute('value', 'Отправить на сервер как готовую позицию');
    submitWF.setAttribute('style', 'margin: 2em 1em;');
    submitWF.classList.add('subm');
    form.appendChild(submitWF);

    Event.add(submitWF, "click", function(e)
    {
        console.log("SEND WF");
        updateByPK(GC.database, new URN(urn).entity, urn, 'needsync', 1).then(function(){
            window.location.hash = 'list/product';
        });
        return false;
    });

}

function GeneralFormControl(configXML, formDomContainer)
{
    var structure = configXML.getElementsByTagName("structure")[0];
    //console.log(structure);
    try {
        FormForGeneralEntityStructureRecursor(structure.childNodes, formDomContainer);
    }
    catch (e) {console.log(e)}
}

function FormForGeneralEntityStructureRecursor(structs, containerDom)
{
    for (var childItem in structs) {
        if (structs[childItem].nodeType == 1)
        {
            var currentTag = structs[childItem];
            if (currentTag.tagName == 'group')
            {
                var groupname = currentTag.getAttribute('name');
                //console.log('G', groupname);
                var nstitle = currentTag.getAttribute('title');
                var xfieldset = document.createElement("fieldset");
                var xlegend = document.createElement("legend");
                xlegend.appendChild(document.createTextNode(nstitle));
                xfieldset.appendChild(xlegend);
                containerDom.appendChild(xfieldset);
                FormForGeneralEntityStructureRecursor(currentTag.childNodes, xfieldset);
            }
            else
            {
//				console.log(fieldTag);
                if (currentTag.tagName == 'field')
                {
                    var fname = currentTag.getAttribute('name');
                    var ftitle = currentTag.getAttribute('title');
                    var ftype = currentTag.getAttribute('type');
                    var fro = currentTag.getAttribute('readonly');
                    var frequired = currentTag.getAttribute('required');
                    var fautofocus = currentTag.getAttribute('autofocus');
                    var fmin = currentTag.getAttribute('min');
                    var fmax = currentTag.getAttribute('max');
                    var ftip = currentTag.getAttribute('tip');
                    var foptimal = currentTag.getAttribute('optimal');
                    new FormInputWidget({name: fname, type: ftype, title: ftitle, ro: fro, required: frequired, min: fmin, max: fmax, tip: ftip, autofocus: fautofocus}, containerDom);
                }
                else if (currentTag.tagName == 'struct' && currentTag.getAttribute('type') == 'image')
                {
					console.log("Struct/Image")
                    var sname = currentTag.getAttribute('name');
                    var stitle = currentTag.getAttribute('title');
                    var stype = currentTag.getAttribute('type');
                    new FormStructImageWidget({name: sname, type: stype, title: stitle}, containerDom);
                }
                else if (currentTag.tagName == 'useone')
                {
                    var entityname = currentTag.getAttribute('entity');
                    var asname = currentTag.getAttribute('as'); // use as name
                    asname = asname ? asname : entityname;
//                    console.log('useone', entityname, asname);
					if (entityname == 'category')
                    	new FormUseOneWidget({relation: 'useone', entity: entityname, name: asname}, containerDom);
                }
                else if (currentTag.tagName == 'usedby') {
                    var entityname = currentTag.getAttribute('entity');
                    new FormUsedByManyWidget({entity: entityname}, containerDom);
                }
				else
				{
					console.log("UNKNOWN TAG",currentTag.tagName, currentTag.getAttribute('type'), currentTag);
				}
            }

        }
    }
}


function FormUseOneWidget(config, formDomContainer)
{
    try
    {
        //var data = [{_parent:null,id:1,title:"Первый"},{_parent:null,id:2,title:"Второй"},{_parent:null,id:3,title:"Третий"},{_parent:1,id:4,title:"Вложенный 11"},{_parent:1,id:5,title:"Вдлж 12"},{_parent:2,id:6,title:"Влож 21"},{_parent:1,id:7,title:"Влож 13"}];
        var data = [{"id":1952911715,"_parent":null,"title":"\u041e\u0434\u0435\u0436\u0434\u0430"},{"id":381997420,"_parent":1952911715,"title":"\u0411\u0435\u043b\u044c\u0435"},{"id":1456136359,"_parent":1952911715,"title":"\u0411\u043b\u0443\u0437\u043a\u0438"},{"id":1973702874,"_parent":1952911715,"title":"\u0411\u043e\u043b\u0435\u0440\u043e, \u0448\u0440\u0430\u0433"},{"id":1561859959,"_parent":1952911715,"title":"\u0411\u043e\u043b\u044c\u0448\u0438\u0435 \u0440\u0430\u0437\u043c\u0435\u0440\u044b"},{"id":626822072,"_parent":1952911715,"title":"\u0411\u0440\u044e\u043a\u0438, \u0448\u043e\u0440\u0442\u044b"},{"id":1008757752,"_parent":1952911715,"title":"\u0412\u0435\u0440\u0445\u043d\u044f\u044f \u043e\u0434\u0435\u0436\u0434\u0430"},{"id":737209493,"_parent":1952911715,"title":"\u0414\u043b\u044f \u0431\u0443\u0434\u0443\u0449\u0438\u0445 \u043c\u0430\u043c"},{"id":574829100,"_parent":1952911715,"title":"\u0414\u043b\u044f \u043c\u0443\u0436\u0447\u0438\u043d"},{"id":2100233965,"_parent":1952911715,"title":"\u0414\u043b\u044f \u043f\u043e\u0434\u0440\u043e\u0441\u0442\u043a\u043e\u0432"},{"id":729387927,"_parent":1952911715,"title":"\u0416\u0438\u043b\u0435\u0442\u044b"},{"id":1284876212,"_parent":1952911715,"title":"\u041a\u0430\u0440\u043d\u0430\u0432\u0430\u043b\u044c\u043d\u044b\u0435 \u043a\u043e\u0441\u0442\u044e\u043c\u044b"},{"id":1044512493,"_parent":1952911715,"title":"\u041a\u043e\u043c\u0431\u0438\u043d\u0435\u0437\u043e\u043d\u044b"},{"id":999278015,"_parent":1952911715,"title":"\u041a\u043e\u0440\u0441\u0435\u0442\u044b"},{"id":392971480,"_parent":1952911715,"title":"\u041a\u043e\u0441\u0442\u044e\u043c\u044b"},{"id":103294350,"_parent":1952911715,"title":"\u041a\u043e\u0444\u0442\u044b \u0438 \u0441\u0432\u0438\u0442\u0435\u0440\u0430"},{"id":517753414,"_parent":1952911715,"title":"\u041a\u0443\u043f\u0430\u043b\u044c\u043d\u0438\u043a\u0438 \u0438 \u043f\u0430\u0440\u0435\u043e"},{"id":453003721,"_parent":1952911715,"title":"\u041f\u0438\u0434\u0436\u0430\u043a\u0438, \u0436\u0430\u043a\u0435\u0442\u044b"},{"id":2075525574,"_parent":1952911715,"title":"\u041f\u043b\u0430\u0442\u044c\u044f"},{"id":1365942177,"_parent":1952911715,"title":"\u041f\u043e\u043d\u0447\u043e"},{"id":1648143439,"_parent":1952911715,"title":"\u0421\u043f\u043e\u0440\u0442\u0438\u0432\u043d\u0430\u044f \u043e\u0434\u0435\u0436\u0434\u0430"},{"id":810979554,"_parent":1952911715,"title":"\u0422\u0430\u043d\u0446\u0435\u0432\u0430\u043b\u044c\u043d\u044b\u0435 \u043a\u043e\u0441\u0442\u044e\u043c\u044b"},{"id":412409772,"_parent":1952911715,"title":"\u0422\u043e\u043f\u044b"},{"id":1770263933,"_parent":1952911715,"title":"\u0424\u0443\u0442\u0431\u043e\u043b\u043a\u0438, \u043c\u0430\u0439\u043a\u0438"},{"id":1647298913,"_parent":1952911715,"title":"\u0425\u0430\u043b\u0430\u0442\u044b"},{"id":1261522948,"_parent":1952911715,"title":"\u042d\u0442\u043d\u0438\u0447\u0435\u0441\u043a\u0430\u044f \u043e\u0434\u0435\u0436\u0434\u0430"},{"id":487145956,"_parent":1952911715,"title":"\u042e\u0431\u043a\u0438"},{"id":861637863,"_parent":null,"title":"\u041e\u0431\u0443\u0432\u044c \u0440\u0443\u0447\u043d\u043e\u0439 \u0440\u0430\u0431\u043e\u0442\u044b"},{"id":56123648,"_parent":861637863,"title":"\u0414\u0435\u043c\u0438\u0441\u0435\u0437\u043e\u043d\u043d\u0430\u044f \u043e\u0431\u0443\u0432\u044c"},{"id":540191524,"_parent":861637863,"title":"\u0414\u043e\u043c\u0430\u0448\u043d\u0438\u0435 \u0442\u0430\u043f\u043e\u0447\u043a\u0438"},{"id":1961413722,"_parent":861637863,"title":"\u0417\u0438\u043c\u043d\u044f\u044f \u043e\u0431\u0443\u0432\u044c"},{"id":616089679,"_parent":861637863,"title":"\u041b\u0435\u0442\u043d\u044f\u044f \u043e\u0431\u0443\u0432\u044c"},{"id":1278371493,"_parent":null,"title":"\u0410\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u044b"},{"id":570685742,"_parent":1278371493,"title":"\u0411\u0440\u0435\u043b\u043e\u043a\u0438"},{"id":853855174,"_parent":1278371493,"title":"\u0412\u0430\u0440\u0435\u0436\u043a\u0438, \u043c\u0438\u0442\u0435\u043d\u043a\u0438, \u043f\u0435\u0440\u0447\u0430\u0442\u043a\u0438"},{"id":1671910508,"_parent":1278371493,"title":"\u0412\u0435\u0435\u0440\u0430"},{"id":802948116,"_parent":1278371493,"title":"\u0412\u043e\u0440\u043e\u0442\u043d\u0438\u0447\u043a\u0438"},{"id":635532917,"_parent":1278371493,"title":"\u0413\u0430\u043b\u0441\u0442\u0443\u043a\u0438, \u0431\u0430\u0431\u043e\u0447\u043a\u0438"},{"id":1962772972,"_parent":1278371493,"title":"\u0413\u043e\u043b\u043e\u0432\u043d\u044b\u0435 \u0443\u0431\u043e\u0440\u044b"},{"id":676784194,"_parent":1278371493,"title":"\u0417\u043e\u043d\u0442\u044b"},{"id":1382647943,"_parent":1278371493,"title":"\u041a\u043e\u043c\u043f\u043b\u0435\u043a\u0442\u044b \u0430\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u043e\u0432"},{"id":1476683973,"_parent":1278371493,"title":"\u041d\u043e\u0441\u043a\u0438, \u0427\u0443\u043b\u043a\u0438"},{"id":1993085877,"_parent":1278371493,"title":"\u041d\u043e\u0441\u043e\u0432\u044b\u0435 \u043f\u043b\u0430\u0442\u043e\u0447\u043a\u0438"},{"id":1313061626,"_parent":1278371493,"title":"\u041e\u0447\u043a\u0438"},{"id":2036247826,"_parent":1278371493,"title":"\u041f\u043e\u044f\u0441\u0430, \u0440\u0435\u043c\u043d\u0438"},{"id":105166256,"_parent":1278371493,"title":"\u041f\u043e\u044f\u0441\u0430, \u0440\u0435\u043c\u043d\u0438"},{"id":1251909596,"_parent":1278371493,"title":"\u0428\u0430\u043b\u0438, \u043f\u0430\u043b\u0430\u043d\u0442\u0438\u043d\u044b"},{"id":122966069,"_parent":1278371493,"title":"\u0428\u0430\u0440\u0444\u044b \u0438 \u0448\u0430\u0440\u0444\u0438\u043a\u0438"},{"id":1777901949,"_parent":null,"title":"\u0420\u0430\u0431\u043e\u0442\u044b \u0434\u043b\u044f \u0434\u0435\u0442\u0435\u0439"},{"id":1107047191,"_parent":1777901949,"title":"\u0414\u0435\u0442\u0441\u043a\u0430\u044f \u0431\u0438\u0436\u0443\u0442\u0435\u0440\u0438\u044f"},{"id":22243085,"_parent":1777901949,"title":"\u0414\u0435\u0442\u0441\u043a\u0430\u044f \u043e\u0431\u0443\u0432\u044c"},{"id":1792980455,"_parent":1777901949,"title":"\u0414\u0435\u0442\u0441\u043a\u0438\u0435 \u0430\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u044b"},{"id":1034274146,"_parent":1777901949,"title":"\u0414\u0435\u0442\u0441\u043a\u0438\u0435 \u043a\u0430\u0440\u043d\u0430\u0432\u0430\u043b\u044c\u043d\u044b\u0435 \u043a\u043e\u0441\u0442\u044e\u043c\u044b"},{"id":927571651,"_parent":1777901949,"title":"\u0414\u0435\u0442\u0441\u043a\u0438\u0435 \u0442\u0430\u043d\u0446\u0435\u0432\u0430\u043b\u044c\u043d\u044b\u0435 \u043a\u043e\u0441\u0442\u044e\u043c\u044b"},{"id":499666433,"_parent":1777901949,"title":"\u0414\u043b\u044f \u043d\u043e\u0432\u043e\u0440\u043e\u0436\u0434\u0435\u043d\u043d\u044b\u0445"},{"id":1650213025,"_parent":1777901949,"title":"\u041a\u0440\u0435\u0441\u0442\u0438\u043b\u044c\u043d\u044b\u0435 \u043f\u0440\u0438\u043d\u0430\u0434\u043b\u0435\u0436\u043d\u043e\u0441\u0442\u0438"},{"id":1409696844,"_parent":1777901949,"title":"\u041e\u0434\u0435\u0436\u0434\u0430 \u0434\u043b\u044f \u0434\u0435\u0432\u043e\u0447\u0435\u043a"},{"id":532927065,"_parent":1777901949,"title":"\u041e\u0434\u0435\u0436\u0434\u0430 \u0434\u043b\u044f \u043c\u0430\u043b\u044c\u0447\u0438\u043a\u043e\u0432"},{"id":76305031,"_parent":1777901949,"title":"\u041e\u0434\u0435\u0436\u0434\u0430 \u0443\u043d\u0438\u0441\u0435\u043a\u0441"},{"id":483531296,"_parent":1777901949,"title":"\u041f\u043b\u0435\u0434\u044b \u0438 \u043e\u0434\u0435\u044f\u043b\u0430"},{"id":112501406,"_parent":null,"title":"\u0421\u0443\u043c\u043a\u0438 \u0438 \u0430\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u044b"},{"id":620778443,"_parent":112501406,"title":"\u0414\u043b\u044f \u0442\u0435\u043b\u0435\u0444\u043e\u043d\u043e\u0432"},{"id":1119771349,"_parent":112501406,"title":"\u0416\u0435\u043d\u0441\u043a\u0438\u0435 \u0441\u0443\u043c\u043a\u0438"},{"id":239682938,"_parent":112501406,"title":"\u041a\u043e\u0448\u0435\u043b\u044c\u043a\u0438 \u0438 \u0432\u0438\u0437\u0438\u0442\u043d\u0438\u0446\u044b"},{"id":665739981,"_parent":112501406,"title":"\u041c\u0443\u0436\u0441\u043a\u0438\u0435 \u0441\u0443\u043c\u043a\u0438"},{"id":187181826,"_parent":112501406,"title":"\u041e\u0440\u0433\u0430\u043d\u0430\u0439\u0437\u0435\u0440\u044b \u0434\u043b\u044f \u0441\u0443\u043c\u043e\u043a"},{"id":1914166520,"_parent":112501406,"title":"\u0420\u044e\u043a\u0437\u0430\u043a\u0438"},{"id":270064455,"_parent":112501406,"title":"\u0421\u043f\u043e\u0440\u0442\u0438\u0432\u043d\u044b\u0435 \u0441\u0443\u043c\u043a\u0438"},{"id":667011160,"_parent":112501406,"title":"\u0421\u0443\u043c\u043a\u0438 \u0434\u043b\u044f \u043d\u043e\u0443\u0442\u0431\u0443\u043a\u043e\u0432"},{"id":578913262,"_parent":112501406,"title":"\u0423\u043a\u0440\u0430\u0448\u0435\u043d\u0438\u044f \u0434\u043b\u044f \u0441\u0443\u043c\u043e\u043a"},{"id":1721037019,"_parent":112501406,"title":"\u0424\u0443\u0442\u043b\u044f\u0440\u044b, \u043e\u0447\u0435\u0447\u043d\u0438\u043a\u0438"},{"id":1910434921,"_parent":112501406,"title":"\u0427\u0435\u043c\u043e\u0434\u0430\u043d\u044b"},{"id":732482782,"_parent":112501406,"title":"\u042d\u043a\u043e\u0441\u0443\u043c\u043a\u0438"},{"id":1455680078,"_parent":null,"title":"\u0421\u0432\u0430\u0434\u0435\u0431\u043d\u044b\u0439 \u0441\u0430\u043b\u043e\u043d"},{"id":359432798,"_parent":1455680078,"title":"\u041e\u0434\u0435\u0436\u0434\u0430 \u0438 \u0430\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u044b"},{"id":1695315397,"_parent":1455680078,"title":"\u041f\u043e\u0434\u0430\u0440\u043a\u0438 \u043d\u0430 \u0441\u0432\u0430\u0434\u044c\u0431\u0443"},{"id":634362496,"_parent":1455680078,"title":"\u041f\u043e\u0434\u0430\u0440\u043a\u0438 \u043d\u0430 \u0441\u0432\u0430\u0434\u044c\u0431\u0443"},{"id":1739584701,"_parent":1455680078,"title":"\u0421\u0432\u0430\u0434\u0435\u0431\u043d\u044b\u0435 \u0430\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u044b"},{"id":39598012,"_parent":1455680078,"title":"\u0421\u0432\u0430\u0434\u0435\u0431\u043d\u044b\u0435 \u043e\u0442\u043a\u0440\u044b\u0442\u043a\u0438"},{"id":721607149,"_parent":1455680078,"title":"\u0421\u0432\u0430\u0434\u0435\u0431\u043d\u044b\u0435 \u0443\u043a\u0440\u0430\u0448\u0435\u043d\u0438\u044f"},{"id":136033655,"_parent":1455680078,"title":"\u0421\u0432\u0430\u0434\u0435\u0431\u043d\u044b\u0435 \u0444\u043e\u0442\u043e\u0430\u043b\u044c\u0431\u043e\u043c\u044b"},{"id":843052268,"_parent":1455680078,"title":"\u0421\u0432\u0430\u0434\u0435\u0431\u043d\u044b\u0435 \u0446\u0432\u0435\u0442\u044b"},{"id":345523971,"_parent":null,"title":"\u0414\u043b\u044f \u0434\u043e\u043c\u0430\u0448\u043d\u0438\u0445 \u0436\u0438\u0432\u043e\u0442\u043d\u044b\u0445"},{"id":1732496837,"_parent":345523971,"title":"\u0410\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u044b \u0434\u043b\u044f \u043a\u043e\u0448\u0435\u043a"},{"id":1641396782,"_parent":345523971,"title":"\u0410\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u044b \u0434\u043b\u044f \u0441\u043e\u0431\u0430\u043a"},{"id":896464773,"_parent":345523971,"title":"\u041e\u0431\u0443\u0432\u044c \u0434\u043b\u044f \u0436\u0438\u0432\u043e\u0442\u043d\u044b\u0445"},{"id":626391976,"_parent":345523971,"title":"\u041e\u0434\u0435\u0436\u0434\u0430 \u0434\u043b\u044f \u043a\u043e\u0448\u0435\u043a"},{"id":2102964913,"_parent":345523971,"title":"\u041e\u0434\u0435\u0436\u0434\u0430 \u0434\u043b\u044f \u0441\u043e\u0431\u0430\u043a"},{"id":1068585251,"_parent":345523971,"title":"\u0414\u043b\u044f \u0434\u0440\u0443\u0433\u0438\u0445 \u0436\u0438\u0432\u043e\u0442\u043d\u044b\u0445"},{"id":1308473614,"_parent":null,"title":"\u0423\u043a\u0440\u0430\u0448\u0435\u043d\u0438\u044f"},{"id":1677577217,"_parent":1308473614,"title":"\u0411\u0440\u0430\u0441\u043b\u0435\u0442\u044b"},{"id":1221937199,"_parent":1308473614,"title":"\u0411\u0440\u043e\u0448\u0438"},{"id":1901684484,"_parent":1308473614,"title":"\u0414\u0438\u0430\u0434\u0435\u043c\u044b, \u043e\u0431\u0440\u0443\u0447\u0438"},{"id":2050152671,"_parent":1308473614,"title":"\u0417\u0430\u043a\u043e\u043b\u043a\u0438"},{"id":1101216696,"_parent":1308473614,"title":"\u0417\u0430\u043f\u043e\u043d\u043a\u0438"},{"id":1675941645,"_parent":1308473614,"title":"\u041a\u0430\u0444\u0444\u044b"},{"id":55041235,"_parent":1308473614,"title":"\u041a\u043e\u043b\u044c\u0435, \u0431\u0443\u0441\u044b"},{"id":2047001857,"_parent":1308473614,"title":"\u041a\u043e\u043b\u044c\u0446\u0430"},{"id":1783231412,"_parent":1308473614,"title":"\u041a\u043e\u043c\u043f\u043b\u0435\u043a\u0442\u044b \u0443\u043a\u0440\u0430\u0448\u0435\u043d\u0438\u0439"},{"id":1292727448,"_parent":1308473614,"title":"\u041a\u0443\u043b\u043e\u043d\u044b, \u043f\u043e\u0434\u0432\u0435\u0441\u043a\u0438"},{"id":642937756,"_parent":1308473614,"title":"\u041b\u0430\u0440\u0438\u0430\u0442\u044b"},{"id":1121467339,"_parent":1308473614,"title":"\u0421\u0435\u0440\u044c\u0433\u0438"},{"id":1079683330,"_parent":1308473614,"title":"\u0423\u043a\u0440\u0430\u0448\u0435\u043d\u0438\u044f \u0434\u043b\u044f \u043c\u0443\u0436\u0447\u0438\u043d"},{"id":858543636,"_parent":1308473614,"title":"\u0423\u043a\u0440\u0430\u0448\u0435\u043d\u0438\u044f \u0434\u043b\u044f \u043d\u043e\u0436\u0435\u043a"},{"id":102541438,"_parent":1308473614,"title":"\u0423\u043a\u0440\u0430\u0448\u0435\u043d\u0438\u044f \u0434\u043b\u044f \u043f\u0438\u0440\u0441\u0438\u043d\u0433\u0430"},{"id":1218253831,"_parent":1308473614,"title":"\u0427\u0430\u0441\u044b"},{"id":954003585,"_parent":null,"title":"\u0414\u043b\u044f \u0434\u043e\u043c\u0430 \u0438 \u0438\u043d\u0442\u0435\u0440\u044c\u0435\u0440\u0430"},{"id":1254049722,"_parent":954003585,"title":"\u0411\u0430\u043d\u043d\u044b\u0435 \u043f\u0440\u0438\u043d\u0430\u0434\u043b\u0435\u0436\u043d\u043e\u0441\u0442\u0438"},{"id":1786028796,"_parent":954003585,"title":"\u0412\u0430\u0437\u044b"},{"id":467541179,"_parent":954003585,"title":"\u0412\u0430\u043d\u043d\u0430\u044f \u043a\u043e\u043c\u043d\u0430\u0442\u0430"},{"id":1105791543,"_parent":954003585,"title":"\u0414\u0435\u0442\u0441\u043a\u0430\u044f"},{"id":402451226,"_parent":954003585,"title":"\u0417\u0435\u0440\u043a\u0430\u043b\u0430"},{"id":1459341514,"_parent":954003585,"title":"\u0418\u043d\u0442\u0435\u0440\u044c\u0435\u0440\u043d\u044b\u0435 \u043c\u0430\u0441\u043a\u0438"},{"id":1180109838,"_parent":954003585,"title":"\u041a\u043e\u043c\u043f\u043b\u0435\u043a\u0442\u044b \u0430\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u043e\u0432"},{"id":1885084742,"_parent":954003585,"title":"\u041a\u043e\u0440\u0437\u0438\u043d\u044b, \u043a\u043e\u0440\u043e\u0431\u044b"},{"id":1187849926,"_parent":954003585,"title":"\u041a\u0443\u0445\u043d\u044f"},{"id":711927857,"_parent":954003585,"title":"\u041c\u0435\u0431\u0435\u043b\u044c"},{"id":528792466,"_parent":954003585,"title":"\u041c\u0438\u043d\u0438-\u043a\u043e\u043c\u043e\u0434\u044b"},{"id":620166982,"_parent":954003585,"title":"\u041e\u0441\u0432\u0435\u0449\u0435\u043d\u0438\u0435"},{"id":809446747,"_parent":954003585,"title":"\u041f\u0435\u043f\u0435\u043b\u044c\u043d\u0438\u0446\u044b"},{"id":755941876,"_parent":954003585,"title":"\u041f\u043e\u0434\u0432\u0435\u0441\u043a\u0438"},{"id":733582469,"_parent":954003585,"title":"\u041f\u043e\u0434\u0441\u0432\u0435\u0447\u043d\u0438\u043a\u0438"},{"id":601424154,"_parent":954003585,"title":"\u041f\u0440\u0438\u0445\u043e\u0436\u0430\u044f"},{"id":1608547188,"_parent":954003585,"title":"\u0421\u0442\u0430\u0442\u0443\u044d\u0442\u043a\u0438"},{"id":2100545107,"_parent":954003585,"title":"\u0422\u0435\u043a\u0441\u0442\u0438\u043b\u044c, \u043a\u043e\u0432\u0440\u044b"},{"id":1565819014,"_parent":954003585,"title":"\u0427\u0430\u0441\u044b \u0434\u043b\u044f \u0434\u043e\u043c\u0430"},{"id":2147319587,"_parent":954003585,"title":"\u0428\u043a\u0430\u0442\u0443\u043b\u043a\u0438"},{"id":747655756,"_parent":954003585,"title":"\u042d\u043a\u0441\u0442\u0435\u0440\u044c\u0435\u0440 \u0438 \u0434\u0430\u0447\u0430"},{"id":187976515,"_parent":954003585,"title":"\u042d\u043b\u0435\u043c\u0435\u043d\u0442\u044b \u0438\u043d\u0442\u0435\u0440\u044c\u0435\u0440\u0430"},{"id":1660410643,"_parent":null,"title":"\u041f\u043e\u0441\u0443\u0434\u0430"},{"id":1825801280,"_parent":1660410643,"title":"\u0411\u043e\u043a\u0430\u043b\u044b, \u0441\u0442\u0430\u043a\u0430\u043d\u044b"},{"id":506648822,"_parent":1660410643,"title":"\u0413\u0440\u0430\u0444\u0438\u043d\u044b, \u043a\u0443\u0432\u0448\u0438\u043d\u044b"},{"id":1476149676,"_parent":1660410643,"title":"\u0414\u0435\u043a\u043e\u0440\u0430\u0442\u0438\u0432\u043d\u0430\u044f \u043f\u043e\u0441\u0443\u0434\u0430"},{"id":1062800640,"_parent":1660410643,"title":"\u041a\u0430\u043b\u0430\u0431\u0430\u0441\u044b \u0438 \u0431\u043e\u043c\u0431\u0438\u043b\u044c\u0438"},{"id":1352968584,"_parent":1660410643,"title":"\u041a\u043e\u043d\u0444\u0435\u0442\u043d\u0438\u0446\u044b, \u0441\u0430\u0445\u0430\u0440\u043d\u0438\u0446\u044b"},{"id":1296527084,"_parent":1660410643,"title":"\u041a\u0440\u0443\u0436\u043a\u0438 \u0438 \u0447\u0430\u0448\u043a\u0438"},{"id":438660934,"_parent":1660410643,"title":"\u041b\u043e\u0436\u043a\u0438"},{"id":1365163194,"_parent":1660410643,"title":"\u041f\u0438\u0430\u043b\u044b"},{"id":224325117,"_parent":1660410643,"title":"\u0420\u044e\u043c\u043a\u0438"},{"id":2047525478,"_parent":1660410643,"title":"\u0421\u0430\u043b\u0430\u0442\u043d\u0438\u043a\u0438"},{"id":341570345,"_parent":1660410643,"title":"\u0421\u0435\u0440\u0432\u0438\u0437\u044b, \u0447\u0430\u0439\u043d\u044b\u0435 \u043f\u0430\u0440\u044b"},{"id":1235534020,"_parent":1660410643,"title":"\u0422\u0430\u0440\u0435\u043b\u043a\u0438"},{"id":263254062,"_parent":1660410643,"title":"\u0427\u0430\u0439\u043d\u0438\u043a\u0438, \u043a\u043e\u0444\u0435\u0439\u043d\u0438\u043a\u0438"},{"id":2103780278,"_parent":null,"title":"\u041f\u043e\u0434\u0430\u0440\u043a\u0438 \u043a \u043f\u0440\u0430\u0437\u0434\u043d\u0438\u043a\u0430\u043c"},{"id":446208508,"_parent":2103780278,"title":"\u041d\u043e\u0432\u044b\u0439 \u0433\u043e\u0434 2014"},{"id":1459798508,"_parent":2103780278,"title":"\u041f\u0435\u0440\u0441\u043e\u043d\u0430\u043b\u044c\u043d\u044b\u0435 \u043f\u043e\u0434\u0430\u0440\u043a\u0438"},{"id":1374924822,"_parent":2103780278,"title":"\u041f\u043e\u0434\u0430\u0440\u043a\u0438 \u0434\u043b\u044f \u0432\u043b\u044e\u0431\u043b\u0435\u043d\u043d\u044b\u0445"},{"id":323109230,"_parent":2103780278,"title":"\u041f\u043e\u0434\u0430\u0440\u043a\u0438 \u0434\u043b\u044f \u043d\u043e\u0432\u043e\u0440\u043e\u0436\u0434\u0435\u043d\u043d\u044b\u0445"},{"id":1934822498,"_parent":2103780278,"title":"\u041f\u043e\u0434\u0430\u0440\u043a\u0438 \u043d\u0430 \u041f\u0430\u0441\u0445\u0443"},{"id":597835039,"_parent":2103780278,"title":"\u041f\u043e\u0434\u0430\u0440\u043a\u0438 \u043d\u0430 \u0425\u044d\u043b\u043b\u043e\u0443\u0438\u043d"},{"id":541140098,"_parent":2103780278,"title":"\u041f\u043e\u0434\u0430\u0440\u043a\u0438 \u043f\u043e \u0437\u043d\u0430\u043a\u0430\u043c \u0417\u043e\u0434\u0438\u0430\u043a\u0430"},{"id":2050614816,"_parent":2103780278,"title":"\u041f\u0440\u0430\u0437\u0434\u043d\u0438\u0447\u043d\u0430\u044f \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0438\u043a\u0430"},{"id":704495779,"_parent":null,"title":"\u0414\u0438\u0437\u0430\u0439\u043d \u0438 \u0440\u0435\u043a\u043b\u0430\u043c\u0430"},{"id":1049482759,"_parent":704495779,"title":"\u0411\u0430\u043d\u043d\u0435\u0440\u044b \u0434\u043b\u044f \u041c\u0430\u0433\u0430\u0437\u0438\u043d\u043e\u0432 \u043c\u0430\u0441\u0442\u0435\u0440\u043e\u0432"},{"id":1930058038,"_parent":704495779,"title":"\u0412\u0438\u0437\u0438\u0442\u043a\u0438 \u0440\u0443\u0447\u043d\u043e\u0439 \u0440\u0430\u0431\u043e\u0442\u044b"},{"id":1589084745,"_parent":704495779,"title":"\u0414\u0435\u043a\u043e\u0440 \u0430\u0432\u0442\u043e\u043c\u043e\u0431\u0438\u043b\u0435\u0439"},{"id":1554348230,"_parent":704495779,"title":"\u0414\u0435\u043a\u043e\u0440 \u043f\u043e\u0432\u0435\u0440\u0445\u043d\u043e\u0441\u0442\u0435\u0439"},{"id":269107098,"_parent":704495779,"title":"\u0414\u0435\u043a\u043e\u0440 \u0442\u0435\u0445\u043d\u0438\u043a\u0438"},{"id":1089217051,"_parent":704495779,"title":"\u0414\u0438\u0437\u0430\u0439\u043d \u0438\u043d\u0442\u0435\u0440\u044c\u0435\u0440\u043e\u0432"},{"id":839029892,"_parent":704495779,"title":"\u0414\u0438\u0437\u0430\u0439\u043d \u044d\u043a\u0441\u0442\u0435\u0440\u044c\u0435\u0440\u0430"},{"id":1978816188,"_parent":704495779,"title":"\u0418\u043b\u043b\u044e\u0441\u0442\u0440\u0430\u0446\u0438\u0438"},{"id":1824215619,"_parent":704495779,"title":"\u041b\u0430\u043d\u0434\u0448\u0430\u0444\u0442\u043d\u044b\u0439 \u0434\u0438\u0437\u0430\u0439\u043d"},{"id":1174914036,"_parent":704495779,"title":"\u041d\u0430\u0433\u0440\u0430\u0434\u043d\u0430\u044f \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0438\u043a\u0430"},{"id":1894877115,"_parent":704495779,"title":"\u0420\u0435\u043a\u043b\u0430\u043c\u043d\u044b\u0435 \u0432\u044b\u0432\u0435\u0441\u043a\u0438"},{"id":559734199,"_parent":704495779,"title":"\u0420\u0435\u043a\u043b\u0430\u043c\u043d\u044b\u0435 \u0441\u0442\u0435\u043d\u0434\u044b"},{"id":606155831,"_parent":704495779,"title":"\u0420\u0435\u0441\u0442\u0430\u0432\u0440\u0430\u0446\u0438\u044f"},{"id":871837622,"_parent":704495779,"title":"\u0420\u043e\u0441\u0442\u043e\u0432\u044b\u0435 \u043a\u0443\u043a\u043b\u044b"},{"id":317812125,"_parent":704495779,"title":"\u0424\u0438\u0442\u043e\u0434\u0438\u0437\u0430\u0439\u043d \u043f\u043e\u043c\u0435\u0449\u0435\u043d\u0438\u0439"},{"id":1070816902,"_parent":704495779,"title":"\u0424\u043e\u0442\u043e \u0438 \u0432\u0438\u0434\u0435\u043e \u0443\u0441\u043b\u0443\u0433\u0438"},{"id":1703862848,"_parent":704495779,"title":"\u0424\u043e\u0442\u043e-\u0440\u0430\u0431\u043e\u0442\u044b"},{"id":864123102,"_parent":null,"title":"\u041a\u0430\u0440\u0442\u0438\u043d\u044b \u0438 \u043f\u0430\u043d\u043d\u043e"},{"id":652966688,"_parent":864123102,"title":"\u0410\u0431\u0441\u0442\u0440\u0430\u043a\u0446\u0438\u044f"},{"id":1420995855,"_parent":864123102,"title":"\u0413\u043e\u0440\u043e\u0434"},{"id":246227044,"_parent":864123102,"title":"\u0416\u0438\u0432\u043e\u0442\u043d\u044b\u0435"},{"id":425433849,"_parent":864123102,"title":"\u0418\u043a\u043e\u043d\u044b"},{"id":554727054,"_parent":864123102,"title":"\u041a\u0430\u0440\u0442\u0438\u043d\u044b \u0446\u0432\u0435\u0442\u043e\u0432"},{"id":115315378,"_parent":864123102,"title":"\u041b\u044e\u0434\u0438"},{"id":2116818429,"_parent":864123102,"title":"\u041d\u0430\u0442\u044e\u0440\u043c\u043e\u0440\u0442"},{"id":1470854689,"_parent":864123102,"title":"\u041d\u044e"},{"id":288902127,"_parent":864123102,"title":"\u041f\u0435\u0439\u0437\u0430\u0436"},{"id":426841251,"_parent":864123102,"title":"\u0420\u0435\u043f\u0440\u043e\u0434\u0443\u043a\u0446\u0438\u0438"},{"id":637477547,"_parent":864123102,"title":"\u0421\u0438\u043c\u0432\u043e\u043b\u0438\u0437\u043c"},{"id":1551037349,"_parent":864123102,"title":"\u0424\u0430\u043d\u0442\u0430\u0437\u0438\u0439\u043d\u044b\u0435 \u0441\u044e\u0436\u0435\u0442\u044b"},{"id":866849305,"_parent":864123102,"title":"\u0424\u043e\u0442\u043e\u043a\u0430\u0440\u0442\u0438\u043d\u044b"},{"id":238156475,"_parent":864123102,"title":"\u0424\u044d\u043d\u0442\u0435\u0437\u0438"},{"id":1463493477,"_parent":864123102,"title":"\u042d\u0442\u043d\u043e"},{"id":1500747326,"_parent":864123102,"title":"\u042e\u043c\u043e\u0440"},{"id":793045128,"_parent":null,"title":"\u0421\u0443\u0432\u0435\u043d\u0438\u0440\u044b \u0438 \u043f\u043e\u0434\u0430\u0440\u043a\u0438"},{"id":584334578,"_parent":793045128,"title":"\u0410\u0432\u0442\u043e\u043c\u043e\u0431\u0438\u043b\u044c\u043d\u044b\u0435"},{"id":1260298336,"_parent":793045128,"title":"\u0410\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u044b \u0434\u043b\u044f \u0444\u043e\u0442\u043e\u0441\u0435\u0441\u0441\u0438\u0439"},{"id":680877204,"_parent":793045128,"title":"\u0413\u0440\u0435\u0431\u043d\u0438, \u0440\u0430\u0441\u0447\u0435\u0441\u043a\u0438"},{"id":1164907453,"_parent":793045128,"title":"\u0414\u0435\u043a\u043e\u0440\u0438\u0440\u043e\u0432\u0430\u043d\u043d\u044b\u0435 \u0437\u0435\u0440\u043a\u0430\u043b\u044c\u0446\u0430"},{"id":574819339,"_parent":793045128,"title":"\u041a\u0430\u043b\u0435\u0439\u0434\u043e\u0441\u043a\u043e\u043f\u044b"},{"id":70267067,"_parent":793045128,"title":"\u041a\u043e\u043b\u043e\u043a\u043e\u043b\u044c\u0447\u0438\u043a\u0438"},{"id":56408507,"_parent":793045128,"title":"\u041a\u043e\u043c\u043f\u044c\u044e\u0442\u0435\u0440\u043d\u044b\u0435"},{"id":961154892,"_parent":793045128,"title":"\u041a\u043e\u043f\u0438\u043b\u043a\u0438"},{"id":1418317178,"_parent":793045128,"title":"\u041a\u0443\u043b\u0438\u043d\u0430\u0440\u043d\u044b\u0435 \u0441\u0443\u0432\u0435\u043d\u0438\u0440\u044b"},{"id":360160121,"_parent":793045128,"title":"\u041a\u0443\u0441\u0443\u0434\u0430\u043c\u044b"},{"id":1956338633,"_parent":793045128,"title":"\u041c\u0430\u0433\u043d\u0438\u0442\u044b"},{"id":1375859197,"_parent":793045128,"title":"\u041c\u0438\u043d\u0438\u0430\u0442\u044e\u0440\u043d\u044b\u0435 \u043c\u043e\u0434\u0435\u043b\u0438"},{"id":773405899,"_parent":793045128,"title":"\u041c\u0443\u0437\u044b\u043a\u0430\u043b\u044c\u043d\u044b\u0435 \u0448\u0430\u0440\u0438\u043a\u0438"},{"id":73182517,"_parent":793045128,"title":"\u041d\u0430\u0441\u0442\u043e\u043b\u044c\u043d\u044b\u0435 \u0438\u0433\u0440\u044b"},{"id":2036951410,"_parent":793045128,"title":"\u041e\u0440\u0443\u0436\u0438\u0435"},{"id":1543799904,"_parent":793045128,"title":"\u041f\u043e\u0434\u0430\u0440\u043a\u0438 \u0434\u043b\u044f \u043c\u0443\u0436\u0447\u0438\u043d"},{"id":812579689,"_parent":793045128,"title":"\u041f\u043e\u0434\u0430\u0440\u043e\u0447\u043d\u0430\u044f \u0443\u043f\u0430\u043a\u043e\u0432\u043a\u0430"},{"id":1998086555,"_parent":793045128,"title":"\u041f\u043e\u0434\u0430\u0440\u043e\u0447\u043d\u043e\u0435 \u043e\u0444\u043e\u0440\u043c\u043b\u0435\u043d\u0438\u0435 \u0431\u0443\u0442\u044b\u043b\u043e\u043a"},{"id":1709873669,"_parent":793045128,"title":"\u041f\u043e\u0434\u0430\u0440\u043e\u0447\u043d\u044b\u0435 \u043d\u0430\u0431\u043e\u0440\u044b"},{"id":68232390,"_parent":793045128,"title":"\u041f\u0440\u0438\u043a\u043e\u043b\u044b"},{"id":990996243,"_parent":793045128,"title":"\u0420\u0435\u043b\u0430\u043a\u0441\u0430\u0446\u0438\u044f, \u0430\u0440\u043e\u043c\u0430\u0442\u0435\u0440\u0430\u043f\u0438\u044f"},{"id":1604307090,"_parent":793045128,"title":"\u0420\u043e\u0441\u043f\u0438\u0441\u044c \u043f\u043e \u043a\u0430\u043c\u043d\u044e"},{"id":1180407324,"_parent":793045128,"title":"\u0421\u0432\u0435\u0447\u0438 \u0440\u0443\u0447\u043d\u043e\u0439 \u0440\u0430\u0431\u043e\u0442\u044b"},{"id":156083069,"_parent":793045128,"title":"\u0422\u0435\u043c\u0430\u0440\u0438"},{"id":16153527,"_parent":793045128,"title":"\u0424\u043e\u0442\u043e\u0440\u0430\u043c\u043a\u0438"},{"id":419555086,"_parent":793045128,"title":"\u042f\u0439\u0446\u0430"},{"id":1680066853,"_parent":null,"title":"\u041e\u0442\u043a\u0440\u044b\u0442\u043a\u0438"},{"id":1450322463,"_parent":1680066853,"title":"\u0412\u0430\u043b\u0435\u043d\u0442\u0438\u043d\u043a\u0438"},{"id":321585279,"_parent":1680066853,"title":"\u0414\u0435\u0442\u0441\u043a\u0438\u0435 \u043e\u0442\u043a\u0440\u044b\u0442\u043a\u0438"},{"id":2008239849,"_parent":1680066853,"title":"\u041a\u043e\u043d\u0432\u0435\u0440\u0442\u044b \u0434\u043b\u044f \u0434\u0435\u043d\u0435\u0433"},{"id":540956537,"_parent":1680066853,"title":"\u041e\u0442\u043a\u0440\u044b\u0442\u043a\u0438 \u0434\u043b\u044f \u0436\u0435\u043d\u0449\u0438\u043d"},{"id":364042461,"_parent":1680066853,"title":"\u041e\u0442\u043a\u0440\u044b\u0442\u043a\u0438 \u0434\u043b\u044f \u043c\u0443\u0436\u0447\u0438\u043d"},{"id":1816098649,"_parent":1680066853,"title":"\u041e\u0442\u043a\u0440\u044b\u0442\u043a\u0438 \u043a \u0434\u0440\u0443\u0433\u0438\u043c \u043f\u0440\u0430\u0437\u0434\u043d\u0438\u043a\u0430\u043c"},{"id":1813657729,"_parent":1680066853,"title":"\u041e\u0442\u043a\u0440\u044b\u0442\u043a\u0438 \u043a \u043d\u043e\u0432\u043e\u043c\u0443 \u0433\u043e\u0434\u0443"},{"id":500452975,"_parent":1680066853,"title":"\u041e\u0442\u043a\u0440\u044b\u0442\u043a\u0438 \u043a \u041f\u0430\u0441\u0445\u0435"},{"id":1393416581,"_parent":1680066853,"title":"\u041e\u0442\u043a\u0440\u044b\u0442\u043a\u0438 \u043a \u0420\u043e\u0436\u0434\u0435\u0441\u0442\u0432\u0443"},{"id":1077683583,"_parent":1680066853,"title":"\u041e\u0442\u043a\u0440\u044b\u0442\u043a\u0438 \u043a \u044e\u0431\u0438\u043b\u0435\u044e"},{"id":1336876327,"_parent":1680066853,"title":"\u041e\u0442\u043a\u0440\u044b\u0442\u043a\u0438 \u043d\u0430 \u0432\u0441\u0435 \u0441\u043b\u0443\u0447\u0430\u0438 \u0436\u0438\u0437\u043d\u0438"},{"id":1262687167,"_parent":1680066853,"title":"\u041e\u0442\u043a\u0440\u044b\u0442\u043a\u0438 \u043d\u0430 \u0434\u0435\u043d\u044c \u0440\u043e\u0436\u0434\u0435\u043d\u0438\u044f"},{"id":18142976,"_parent":1680066853,"title":"\u041f\u043e\u0437\u0434\u0440\u0430\u0432\u0438\u0442\u0435\u043b\u044c\u043d\u044b\u0435 \u043a\u0430\u0440\u0442\u0438\u043d\u043a\u0438, \u0442\u0430\u0440\u0435\u043b\u043e\u0447\u043a\u0438"},{"id":553614387,"_parent":1680066853,"title":"\u041f\u0440\u0438\u0433\u043b\u0430\u0441\u0438\u0442\u0435\u043b\u044c\u043d\u044b\u0435"},{"id":726543737,"_parent":null,"title":"\u0426\u0432\u0435\u0442\u044b \u0438 \u0444\u043b\u043e\u0440\u0438\u0441\u0442\u0438\u043a\u0430"},{"id":338873331,"_parent":726543737,"title":"\u0411\u043e\u043d\u0441\u0430\u0439"},{"id":1401705495,"_parent":726543737,"title":"\u0411\u0443\u043a\u0435\u0442\u044b"},{"id":1969854245,"_parent":726543737,"title":"\u0414\u0435\u0440\u0435\u0432\u044c\u044f"},{"id":1343647526,"_parent":726543737,"title":"\u0418\u043d\u0442\u0435\u0440\u044c\u0435\u0440\u043d\u044b\u0435 \u043a\u043e\u043c\u043f\u043e\u0437\u0438\u0446\u0438\u0438"},{"id":1803127209,"_parent":726543737,"title":"\u0418\u0441\u043a\u0443\u0441\u0441\u0442\u0432\u0435\u043d\u043d\u044b\u0435 \u0440\u0430\u0441\u0442\u0435\u043d\u0438\u044f"},{"id":1914460229,"_parent":726543737,"title":"\u041a\u0430\u0448\u043f\u043e"},{"id":1601605673,"_parent":726543737,"title":"\u041b\u0435\u0439\u043a\u0438"},{"id":78635319,"_parent":726543737,"title":"\u041f\u043e\u0434\u0441\u0442\u0430\u0432\u043a\u0438 \u043f\u043e\u0434 \u0446\u0432\u0435\u0442\u044b"},{"id":283056845,"_parent":726543737,"title":"\u0422\u043e\u043f\u0438\u0430\u0440\u0438\u0438"},{"id":1917897968,"_parent":726543737,"title":"\u0423\u043a\u0440\u0430\u0448\u0435\u043d\u0438\u044f \u0434\u043b\u044f \u0446\u0432\u0435\u0442\u043e\u0432"},{"id":1042855216,"_parent":726543737,"title":"\u0426\u0432\u0435\u0442\u043e\u0447\u043d\u044b\u0435 \u0433\u043e\u0440\u0448\u043a\u0438"},{"id":1287465168,"_parent":726543737,"title":"\u0426\u0432\u0435\u0442\u044b"},{"id":2002950581,"_parent":null,"title":"\u0421\u0443\u0431\u043a\u0443\u043b\u044c\u0442\u0443\u0440\u044b"},{"id":965496384,"_parent":2002950581,"title":"\u0410\u043d\u0438\u043c\u0435"},{"id":1106798280,"_parent":2002950581,"title":"\u0413\u043e\u0442\u0438\u043a\u0430"},{"id":839771441,"_parent":2002950581,"title":"\u0420\u043e\u043b\u0435\u0432\u044b\u0435 \u0438\u0433\u0440\u044b, \u0420\u0435\u043a\u043e\u043d\u0441\u0442\u0440\u0443\u043a\u0446\u0438\u044f"},{"id":1055187183,"_parent":2002950581,"title":"\u0425\u0438\u043f\u043f\u0438, \u0440\u0430\u0441\u0442\u0430"},{"id":1317206040,"_parent":null,"title":"\u0424\u0435\u043d-\u0448\u0443\u0439 \u0438 \u044d\u0437\u043e\u0442\u0435\u0440\u0438\u043a\u0430"},{"id":1411744889,"_parent":1317206040,"title":"\u0413\u0430\u0434\u0430\u043d\u0438\u044f"},{"id":235156720,"_parent":1317206040,"title":"\u041b\u043e\u0432\u0446\u044b \u0441\u043d\u043e\u0432"},{"id":1356632974,"_parent":1317206040,"title":"\u041c\u0435\u0434\u0438\u0442\u0430\u0446\u0438\u044f"},{"id":1105720722,"_parent":1317206040,"title":"\u041e\u0431\u0435\u0440\u0435\u0433\u0438, \u0442\u0430\u043b\u0438\u0441\u043c\u0430\u043d\u044b, \u0430\u043c\u0443\u043b\u0435\u0442\u044b"},{"id":830096124,"_parent":1317206040,"title":"\u0424\u0435\u043d-\u0448\u0443\u0439"},{"id":13636113,"_parent":1317206040,"title":"\u0427\u0435\u0442\u043a\u0438"},{"id":564491154,"_parent":1317206040,"title":"\u042d\u0437\u043e\u0442\u0435\u0440\u0438\u0447\u0435\u0441\u043a\u0438\u0435 \u0430\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u044b"},{"id":1598744949,"_parent":null,"title":"\u041a\u0443\u043a\u043b\u044b \u0438 \u0438\u0433\u0440\u0443\u0448\u043a\u0438"},{"id":203339991,"_parent":1598744949,"title":"\u0410\u0440\u043e\u043c\u0430\u0442\u0438\u0437\u0438\u0440\u043e\u0432\u0430\u043d\u043d\u044b\u0435 \u043a\u0443\u043a\u043b\u044b"},{"id":470498008,"_parent":1598744949,"title":"\u0412\u0430\u043b\u044c\u0434\u043e\u0440\u0444\u0441\u043a\u0430\u044f \u0438\u0433\u0440\u0443\u0448\u043a\u0430"},{"id":1372705816,"_parent":1598744949,"title":"\u0415\u0434\u0430"},{"id":1228149032,"_parent":1598744949,"title":"\u0418\u0433\u0440\u0443\u0448\u043a\u0438 \u0436\u0438\u0432\u043e\u0442\u043d\u044b\u0435"},{"id":652249959,"_parent":1598744949,"title":"\u041a\u043e\u043b\u043b\u0435\u043a\u0446\u0438\u043e\u043d\u043d\u044b\u0435 \u043a\u0443\u043a\u043b\u044b"},{"id":126507535,"_parent":1598744949,"title":"\u041a\u0443\u043a\u043b\u044b \u0422\u0438\u043b\u044c\u0434\u044b"},{"id":2022027264,"_parent":1598744949,"title":"\u041a\u0443\u043a\u043b\u044b \u0442\u044b\u043a\u0432\u043e\u0433\u043e\u043b\u043e\u0432\u043a\u0438"},{"id":929568067,"_parent":1598744949,"title":"\u041a\u0443\u043a\u043b\u044b-\u043c\u043b\u0430\u0434\u0435\u043d\u0446\u044b \u0438 reborn"},{"id":47869385,"_parent":1598744949,"title":"\u041a\u0443\u043a\u043e\u043b\u044c\u043d\u044b\u0439 \u0434\u043e\u043c"},{"id":385878545,"_parent":1598744949,"title":"\u041a\u0443\u043a\u043e\u043b\u044c\u043d\u044b\u0439 \u0442\u0435\u0430\u0442\u0440"},{"id":1109647497,"_parent":1598744949,"title":"\u041c\u0438\u043d\u0438\u0430\u0442\u044e\u0440\u0430"},{"id":97090457,"_parent":1598744949,"title":"\u041c\u0438\u0448\u043a\u0438 \u0422\u0435\u0434\u0434\u0438"},{"id":825476481,"_parent":1598744949,"title":"\u041d\u0430\u0440\u043e\u0434\u043d\u044b\u0435 \u043a\u0443\u043a\u043b\u044b"},{"id":2002736387,"_parent":1598744949,"title":"\u041e\u0434\u0435\u0436\u0434\u0430 \u0434\u043b\u044f \u043a\u0443\u043a\u043e\u043b"},{"id":445570700,"_parent":1598744949,"title":"\u041f\u043e\u0440\u0442\u0440\u0435\u0442\u043d\u044b\u0435 \u043a\u0443\u043a\u043b\u044b"},{"id":844428883,"_parent":1598744949,"title":"\u0420\u0430\u0437\u0432\u0438\u0432\u0430\u044e\u0449\u0438\u0435 \u0438\u0433\u0440\u0443\u0448\u043a\u0438"},{"id":1605537043,"_parent":1598744949,"title":"\u0421\u043a\u0430\u0437\u043e\u0447\u043d\u044b\u0435 \u043f\u0435\u0440\u0441\u043e\u043d\u0430\u0436\u0438"},{"id":1939794960,"_parent":1598744949,"title":"\u0427\u0435\u043b\u043e\u0432\u0435\u0447\u043a\u0438"},{"id":823045677,"_parent":1598744949,"title":"\u0422\u0435\u0445\u043d\u0438\u043a\u0430"},{"id":1907027827,"_parent":1598744949,"title":"\u041a\u0443\u043a\u043b\u044b \u0432\u0443\u0434\u0443"},{"id":1771455377,"_parent":null,"title":"\u041a\u0430\u043d\u0446\u0435\u043b\u044f\u0440\u0441\u043a\u0438\u0435 \u0442\u043e\u0432\u0430\u0440\u044b"},{"id":1347446989,"_parent":1771455377,"title":"\u0411\u043b\u043e\u043a\u043d\u043e\u0442\u044b"},{"id":1905294750,"_parent":1771455377,"title":"\u0415\u0436\u0435\u0434\u043d\u0435\u0432\u043d\u0438\u043a\u0438"},{"id":716572366,"_parent":1771455377,"title":"\u0416\u0443\u0440\u043d\u0430\u043b\u044c\u043d\u0438\u0446\u044b"},{"id":2008174108,"_parent":1771455377,"title":"\u0417\u0430\u043a\u043b\u0430\u0434\u043a\u0438 \u0434\u043b\u044f \u043a\u043d\u0438\u0433"},{"id":733698819,"_parent":1771455377,"title":"\u0417\u0430\u043f\u0438\u0441\u043d\u044b\u0435 \u043a\u043d\u0438\u0436\u043a\u0438"},{"id":932821043,"_parent":1771455377,"title":"\u041a\u0430\u043b\u0435\u043d\u0434\u0430\u0440\u0438 \u0440\u0443\u0447\u043d\u043e\u0439 \u0440\u0430\u0431\u043e\u0442\u044b"},{"id":1954478391,"_parent":1771455377,"title":"\u041a\u0430\u0440\u0430\u043d\u0434\u0430\u0448\u0438, \u0440\u0443\u0447\u043a\u0438"},{"id":1571496912,"_parent":1771455377,"title":"\u041a\u0430\u0440\u0430\u043d\u0434\u0430\u0448\u043d\u0438\u0446\u044b"},{"id":1216515823,"_parent":1771455377,"title":"\u041a\u0443\u043b\u0438\u043d\u0430\u0440\u043d\u044b\u0435 \u043a\u043d\u0438\u0433\u0438"},{"id":1003741701,"_parent":1771455377,"title":"\u041d\u0430\u0441\u0442\u043e\u043b\u044c\u043d\u044b\u0435 \u0432\u0438\u0437\u0438\u0442\u043d\u0438\u0446\u044b"},{"id":140591568,"_parent":1771455377,"title":"\u041e\u0431\u043b\u043e\u0436\u043a\u0438"},{"id":1998055949,"_parent":1771455377,"title":"\u041f\u0430\u043f\u043a\u0438 \u0434\u043b\u044f \u0431\u0443\u043c\u0430\u0433"},{"id":286899434,"_parent":1771455377,"title":"\u041f\u0435\u043d\u0430\u043b\u044b"},{"id":1128413270,"_parent":1771455377,"title":"\u041f\u0438\u0441\u044c\u043c\u0435\u043d\u043d\u044b\u0435 \u043f\u0440\u0438\u0431\u043e\u0440\u044b"},{"id":1235213552,"_parent":1771455377,"title":"\u0424\u043e\u0442\u043e\u0430\u043b\u044c\u0431\u043e\u043c\u044b"},{"id":1161718788,"_parent":null,"title":"\u041c\u0443\u0437\u044b\u043a\u0430\u043b\u044c\u043d\u044b\u0435 \u0438\u043d\u0441\u0442\u0440\u0443\u043c\u0435\u043d\u0442\u044b"},{"id":1032508355,"_parent":1161718788,"title":"\u0414\u0443\u0445\u043e\u0432\u044b\u0435 \u0438\u043d\u0441\u0442\u0440\u0443\u043c\u0435\u043d\u0442\u044b"},{"id":13710354,"_parent":1161718788,"title":"\u0421\u0442\u0440\u0443\u043d\u043d\u044b\u0435 \u0438\u043d\u0441\u0442\u0440\u0443\u043c\u0435\u043d\u0442\u044b"},{"id":1249032940,"_parent":1161718788,"title":"\u0423\u0434\u0430\u0440\u043d\u044b\u0435 \u0438\u043d\u0441\u0442\u0440\u0443\u043c\u0435\u043d\u0442\u044b"},{"id":1856109139,"_parent":1161718788,"title":"\u0427\u0435\u0445\u043b\u044b \u0434\u043b\u044f \u0438\u043d\u0441\u0442\u0440\u0443\u043c\u0435\u043d\u0442\u043e\u0432"},{"id":212504946,"_parent":null,"title":"\u041a\u043e\u0441\u043c\u0435\u0442\u0438\u043a\u0430 \u0440\u0443\u0447\u043d\u043e\u0439 \u0440\u0430\u0431\u043e\u0442\u044b"},{"id":316620706,"_parent":212504946,"title":"\u0411\u0430\u043b\u044c\u0437\u0430\u043c \u0434\u043b\u044f \u0432\u043e\u043b\u043e\u0441"},{"id":1003765786,"_parent":212504946,"title":"\u0411\u0430\u043b\u044c\u0437\u0430\u043c \u0434\u043b\u044f \u0433\u0443\u0431"},{"id":1932981438,"_parent":212504946,"title":"\u0411\u043e\u043c\u0431\u044b \u0434\u043b\u044f \u0432\u0430\u043d\u043d\u044b"},{"id":397393702,"_parent":212504946,"title":"\u0414\u0435\u0437\u043e\u0434\u043e\u0440\u0430\u043d\u0442\u044b"},{"id":483833683,"_parent":212504946,"title":"\u0414\u043b\u044f \u0441\u043d\u044f\u0442\u0438\u044f \u043c\u0430\u043a\u0438\u044f\u0436\u0430"},{"id":1531567225,"_parent":212504946,"title":"\u041a\u0440\u0435\u043c, \u0433\u0435\u043b\u044c, \u0441\u044b\u0432\u043e\u0440\u043e\u0442\u043a\u0430"},{"id":1260677368,"_parent":212504946,"title":"\u041c\u0430\u0441\u043a\u0438 \u0434\u043b\u044f \u0432\u043e\u043b\u043e\u0441"},{"id":451828817,"_parent":212504946,"title":"\u041c\u0430\u0441\u043a\u0438 \u0434\u043b\u044f \u043b\u0438\u0446\u0430"},{"id":1200527408,"_parent":212504946,"title":"\u041c\u0430\u0441\u043b\u0430 \u0438 \u0441\u043c\u0435\u0441\u0438"},{"id":1713101987,"_parent":212504946,"title":"\u041c\u0430\u0441\u0441\u0430\u0436\u043d\u044b\u0435 \u043f\u043b\u0438\u0442\u043a\u0438"},{"id":1475473960,"_parent":212504946,"title":"\u041c\u043e\u043b\u043e\u0447\u043a\u043e \u0434\u043b\u044f \u0442\u0435\u043b\u0430"},{"id":1838769014,"_parent":212504946,"title":"\u041c\u044b\u043b\u043e"},{"id":1706338858,"_parent":212504946,"title":"\u041c\u044b\u043b\u043e-\u043c\u043e\u0447\u0430\u043b\u043a\u0430"},{"id":1307323230,"_parent":212504946,"title":"\u041c\u044b\u043b\u043e-\u0448\u0430\u043c\u043f\u0443\u043d\u044c"},{"id":1812379411,"_parent":212504946,"title":"\u041d\u0430\u0442\u0443\u0440\u0430\u043b\u044c\u043d\u044b\u0435 \u0434\u0443\u0445\u0438"},{"id":277798555,"_parent":212504946,"title":"\u041f\u0435\u043d\u0430, \u043c\u043e\u043b\u043e\u0447\u043a\u043e \u0434\u043b\u044f \u0432\u0430\u043d\u043d\u044b"},{"id":1618556734,"_parent":212504946,"title":"\u041f\u043e\u0434\u0430\u0440\u043e\u0447\u043d\u044b\u0435 \u043d\u0430\u0431\u043e\u0440\u044b \u043a\u043e\u0441\u043c\u0435\u0442\u0438\u043a\u0438"},{"id":1159065572,"_parent":212504946,"title":"\u0421\u043a\u0440\u0430\u0431"},{"id":413334053,"_parent":212504946,"title":"\u0421\u043e\u043b\u044c \u0434\u043b\u044f \u0432\u0430\u043d\u043d\u044b"},{"id":1205334855,"_parent":212504946,"title":"\u0422\u043e\u043d\u0438\u043a\u0438"},{"id":359319195,"_parent":212504946,"title":"\u0428\u0430\u043c\u043f\u0443\u043d\u044c"},{"id":1926300139,"_parent":null,"title":"\u0420\u0443\u0441\u0441\u043a\u0438\u0439 \u0441\u0442\u0438\u043b\u044c"},{"id":1528300205,"_parent":1926300139,"title":"\u0411\u043e\u0433\u0438 \u0414\u0440\u0435\u0432\u043d\u0435\u0439 \u0420\u0443\u0441\u0438"},{"id":2144123961,"_parent":1926300139,"title":"\u0411\u044b\u0442"},{"id":1676024881,"_parent":1926300139,"title":"\u041c\u0430\u0442\u0440\u0435\u0448\u043a\u0438"},{"id":1336821237,"_parent":1926300139,"title":"\u041e\u0434\u0435\u0436\u0434\u0430"},{"id":1733668729,"_parent":1926300139,"title":"\u041f\u043e\u0441\u0443\u0434\u0430"},{"id":1051464066,"_parent":1926300139,"title":"\u0420\u0443\u0448\u043d\u0438\u043a\u0438"},{"id":346535926,"_parent":1926300139,"title":"\u0422\u043a\u0430\u0447\u0435\u0441\u0442\u0432\u043e"},{"id":1130708867,"_parent":1926300139,"title":"\u0423\u043a\u0440\u0430\u0448\u0435\u043d\u0438\u044f \u0438 \u0430\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u044b"},{"id":432320515,"_parent":1926300139,"title":"\u0421\u0443\u0432\u0435\u043d\u0438\u0440\u044b"},{"id":340079717,"_parent":null,"title":"\u041c\u0430\u0442\u0435\u0440\u0438\u0430\u043b\u044b \u0434\u043b\u044f \u0442\u0432\u043e\u0440\u0447\u0435\u0441\u0442\u0432\u0430"},{"id":879819203,"_parent":340079717,"title":"\u0410\u043f\u043f\u043b\u0438\u043a\u0430\u0446\u0438\u0438, \u0432\u0441\u0442\u0430\u0432\u043a\u0438, \u043e\u0442\u0434\u0435\u043b\u043a\u0430"},{"id":1367252252,"_parent":340079717,"title":"\u0412\u0430\u043b\u044f\u043d\u0438\u0435"},{"id":240918378,"_parent":340079717,"title":"\u0412\u044b\u0448\u0438\u0432\u043a\u0430"},{"id":2032213940,"_parent":340079717,"title":"\u0412\u044f\u0437\u0430\u043d\u0438\u0435"},{"id":1313738092,"_parent":340079717,"title":"\u0414\u0435\u043a\u0443\u043f\u0430\u0436 \u0438 \u0440\u043e\u0441\u043f\u0438\u0441\u044c"},{"id":2084271212,"_parent":340079717,"title":"\u0414\u043b\u044f \u0443\u043a\u0440\u0430\u0448\u0435\u043d\u0438\u0439"},{"id":1812743146,"_parent":340079717,"title":"\u0414\u0440\u0443\u0433\u0438\u0435 \u0432\u0438\u0434\u044b \u0440\u0443\u043a\u043e\u0434\u0435\u043b\u0438\u044f"},{"id":1397191267,"_parent":340079717,"title":"\u041a\u0443\u043a\u043b\u044b \u0438 \u0438\u0433\u0440\u0443\u0448\u043a\u0438"},{"id":1650247191,"_parent":340079717,"title":"\u041c\u0430\u043d\u0435\u043a\u0435\u043d\u044b"},{"id":263876542,"_parent":340079717,"title":"\u041e\u0431\u0443\u0447\u0430\u044e\u0449\u0438\u0435 \u043c\u0430\u0442\u0435\u0440\u0438\u0430\u043b\u044b"},{"id":849323370,"_parent":340079717,"title":"\u041e\u0440\u0433\u0430\u043d\u0430\u0439\u0437\u0435\u0440\u044b \u0434\u043b\u044f \u0440\u0443\u043a\u043e\u0434\u0435\u043b\u0438\u044f"},{"id":478674559,"_parent":340079717,"title":"\u041e\u0442\u043a\u0440\u044b\u0442\u043a\u0438 \u0438 \u0441\u043a\u0440\u0430\u043f\u0431\u0443\u043a\u0438\u043d\u0433"},{"id":1236073409,"_parent":340079717,"title":"\u0423\u043f\u0430\u043a\u043e\u0432\u043a\u0430"},{"id":1055614214,"_parent":340079717,"title":"\u0428\u0438\u0442\u044c\u0435"}];
        //console.log('useone TBD', config.entity);


        var input = document.createElement("input");
        input.setAttribute('type', 'hidden');
        formDomContainer.appendChild(input);
        var urn = input.form.getAttribute('data-urn');
        //console.log(urn);

        GOLDCUT.MODEL[urn].onChange('category', this);

        var label = document.createElement("label");
        label.setAttribute('for', config.name);
        label.innerHTML = 'Категория';
        formDomContainer.appendChild(label);

        var cdiv = document.createElement("div");
        cdiv.classList.add('BLK');
        cdiv.classList.add('selectwidget');

        var div1 = document.createElement("div");
        div1.classList.add('selectcolumn');
        div1.classList.add('g4');
        div1.classList.add('RPM');
        cdiv.appendChild(div1);

        var div2 = document.createElement("div");
        div2.classList.add('selectcolumn');
        div2.classList.add('g4');
        div2.classList.add('RPM');
        cdiv.appendChild(div2);

        data.filter( filterByEqual('_parent',null) ).forEach(function(el) {
            var divin = document.createElement("div");
            divin.classList.add('selectelement');
            divin.setAttribute('data-id',el.id);
            divin.innerHTML = el.title;
            div1.appendChild(divin);
        } );

        var select1 = function(hostID)
        {
            try { bycss('.selected',1,div1).classList.remove('selected'); } catch(e){} // deselect current
            var selectedDomEl = bycss('[data-id="'+hostID+'"]',1,div1); // selected dom element find by data-id
            if (selectedDomEl) selectedDomEl.classList.add('selected');

            //console.log(hostID);
            localStorage.setItem("lastSelection1", hostID);

            div2.innerHTML='';
            data.filter( filterByEqual('_parent', hostID) ).forEach(function(el) {
                var divin = document.createElement("div");
                divin.classList.add('selectelement');
                divin.setAttribute('data-id',el.id);
                divin.innerHTML = el.title;
                div2.appendChild(divin);
            } );
        }

        var sel1 = localStorage.getItem("lastSelection1");
        if (sel1 != null) select1(parseInt(sel1));

        Event.add(div1, "click", function(e) {
            var dpath = new DomPath2(e.target);
            //console.log(dpath.dompath[0], dpath.dompath[0].dom.getAttribute('data-urn'));
            if (dpath.dompath[0].tag == 'DIV' || dpath.dompath[0].tag == 'div')
            {
                var hostID = parseInt(dpath.dompath[0].dom.getAttribute('data-id'));
                select1(hostID);
            }
        });


        var select2 = function(targetID, save)
        {
            try { bycss('.selected',1,div2).classList.remove('selected'); } catch(e){} // deselect current
            var selectedDomEl = bycss('[data-id="'+targetID+'"]',1,div2);
            if (selectedDomEl) selectedDomEl.classList.add('selected');

            localStorage.setItem("lastSelection2", targetID);
            //console.log('@cat sel',targetID);

            //input.setAttribute('value', GOLDCUT.MODEL[urn].o[o.name] ? GOLDCUT.MODEL[urn].o[o.name] : '');
            if (save === true) GOLDCUT.MODEL[urn].change('category', targetID);
        }

        var modelValue = GOLDCUT.MODEL[urn].o['category'];
        //console.log('modelValue',modelValue);
        var sel2 = localStorage.getItem("lastSelection2");
        //console.log('lastSelectedValue', sel2);
        if (sel2 != null) parseInt(sel2);
        var sel2MODELORLAST = null;
        if (modelValue) sel2MODELORLAST = modelValue;
        else if (sel2)  sel2MODELORLAST = sel2;
        select2(sel2MODELORLAST);


        Event.add(div2, "click", function(e) {
            var dpath = new DomPath2(e.target);
            //console.log(dpath.dompath[0], dpath.dompath[0].dom.getAttribute('data-urn'));
            if (dpath.dompath[0].tag == 'DIV' || dpath.dompath[0].tag == 'div')
            {
                var targetID = parseInt(dpath.dompath[0].dom.getAttribute('data-id'));
                select2(targetID, true); // select & SAVE IN DB
            }
        });



        formDomContainer.appendChild(cdiv);
        //console.log(data.filter( filterByEqual('_parent',1) ));

    }
    catch (e) {console.log(e)}
}
FormUseOneWidget.prototype.notify = function(nv){
    console.log('some USEONE controller gets notified, new val', nv);
}



function FormInputWidget(o, domContainer)
{

//    console.log(formDomContainer);
//    console.log(o);
    this.name = o.name;
    var div = document.createElement("div");
    div.classList.add('inputwidget');

    var label = document.createElement("label");
    label.setAttribute('for', o.name);
    label.innerHTML = o.title;
    div.appendChild(label);


    var urn;

    // type range (min,max, step)
    if (['string', 'password', 'email', 'url', 'number', 'integer', 'money', 'option'].indexOf(o.type) !== -1)
    {
        var input = document.createElement("input");
        input.setAttribute('name', o.name);
        input.setAttribute('type', (o.type == 'string' ? 'text' : o.type));
        // mozactionhint (replace Return but text in FF mobile) <input type="text" mozactionhint="next" name="sometext" />
        // inputmode = latin-name for Names
        if (o.min && o.type == 'number') input.setAttribute('min', o.min);
        if (o.max && o.type == 'number') input.setAttribute('max', o.max);
        if (o.max && o.type == 'string') input.setAttribute('maxlength', o.max);
        if (o.tip) input.setAttribute('placeholder', o.tip);
        if (o.required && o.required != 'no') input.setAttribute('required', 'yes');
        if (o.autofocus && o.autofocus != 'no') input.setAttribute('autofocus', '');
        input.setAttribute('autocomplete', 'off');
        div.appendChild(input);
        domContainer.appendChild(div);
        urn = input.form.getAttribute('data-urn');

        input.setAttribute('value', GOLDCUT.MODEL[urn].o[o.name] ? GOLDCUT.MODEL[urn].o[o.name] : '');

    }
    else if (o.type == 'text')
    {
        var input = document.createElement("textarea");
        input.setAttribute('name', o.name);
        div.appendChild(input);
        domContainer.appendChild(div);
        urn = input.form.getAttribute('data-urn');
        input.value = GOLDCUT.MODEL[urn].o[o.name] ? GOLDCUT.MODEL[urn].o[o.name] : '';
    }
    else
    {
        console.log("INPUT WIDGET NOT REALIZED", o.type);
        return;
    }

    var urn = input.form.getAttribute('data-urn');
    //console.log('w init',urn, o.name, GOLDCUT.MODEL[urn].o);
    input.setAttribute('value', GOLDCUT.MODEL[urn].o[o.name] ? GOLDCUT.MODEL[urn].o[o.name] : '');
    GOLDCUT.MODEL[urn].onChange(o.name, this);

    Event.add(input, "blur", function(e) {
        var fieldname = this.getAttribute('name');
        this.setAttribute('data-isValueChanged', 'yes');
        //console.log('blur',urn);
        GOLDCUT.MODEL[urn].change(fieldname, input.value);
    });
}
FormInputWidget.prototype.notify = function(nv){
    console.log('some Unput controller gets notified, new val', nv);
}

function FormStructImageWidget(o, formDomContainer)
{
	var urn = GOLDCUT.FORM.urn; //input.form //.getAttribute('data-urn');

	GOLDCUT.MODEL[urn].onChange('illustration_image', this);
	
    console.log('Struct', o.name, o.type, o.title);
    this.name = o.name;
    var div = document.createElement("div");
    div.classList.add('structwidget');
	var input = document.createElement("input");
	input.setAttribute('type', "file");
	input.setAttribute('multiple', "multiple");
    input.setAttribute('name', "fileField");
	input.setAttribute('id', "fileField");
	input.classList.add('fileField');
    div.appendChild(input);
	var filedrop = document.createElement("div");
	filedrop.setAttribute('id', "filedrop");
	filedrop.classList.add('filedrop');
	
	var images = GOLDCUT.MODEL[urn].o["illustration_image"];
	
	divimages = document.createElement('div');
	divimages.innerHTML = images
	filedrop.appendChild(divimages);
	
	filedrop.appendChild(document.createTextNode('DROP FILES HERE'));
    div.appendChild(filedrop);
	/*
	div.innerHTML = '<input type="hidden" class="onalluploadedcall" value="onAllUploadedLocal"> \
	<input type="file" id="fileField" class="fileField" name="fileField" multiple /> \
    <input type="hidden" class="upload_destination" value="urn-photoitem"> \
    <div id="fileDrop" class="fileDrop"> \
        <p>Drop files here</p> \
    </div> \
    <canvas id="src"></canvas>';
	*/
	formDomContainer.appendChild(div);
	
	destination = 'urn-productphoto';
    container = 'urn-product-234';
	
	onAllUploaded = function(data)
	{
		console.log('<<< all uploaded (onAllUploaded)');
		console.log(data.length);
		filedrop.innerHTML = '';
		data.forEach(function(d) {
			console.log(d)
			filedrop.innerHTML += "<img width='300' src='"+d.image.uri+"'>";	
		} )
		GOLDCUT.MODEL[urn].change('illustration', '1234567');
		GOLDCUT.MODEL[urn].change('illustration_image', filedrop.innerHTML);
		console.log("DONE")
	}
    
	//new DropArea(bycss('.fileDrop',1));
	//console.log( div.querySelectorAll(".fileField") ) // WORKING (by css from document - not working)
	new DropArea(filedrop);
	input.onchange = function(ev)
    {
        addFileListItemsWithFileReader(this.files);
    };
}
FormStructImageWidget.prototype.notify = function(nv){
    console.log('some STRUCT controller gets notified, new val', nv);
}

/*
function FormStructImageWidgetByManyWidget(config, formDomContainer) {
//    console.log(config);
    console.log('usedby', config.entity);

    new DropArea(bycss('.fileDrop',1));

    // tagret urn for inplace photo extend
    //destination = bycss(".upload_destination",1).value;
    // target photoalbum, news etc urn of container
    //container = bycss(".upload_container",1).value;

    destination = 'urn-productphoto';
    container = 'urn-product-234';

    var onalluploadedcall = bycss(".onalluploadedcall",1).value;

    if (onalluploadedcall && window[onalluploadedcall])
        onAllUploaded = window[onalluploadedcall];
    else
        onAllUploaded = onAllUploadedDefault;

    // FILE SELECT
    console.log(bycss(".fileField"));
    bycss(".fileField",1).onchange = function(ev)
    {
//        console.log(123);
        addFileListItemsWithFileReader(this.files);
    };

    // BUTTON ACTION UPLOAD
    var uploadbut = document.getElementById("upload");
    if (uploadbut)
    {
        uploadbut.onclick = function()
        {
            new fileQueueUploader(fileQueue, onAllUploaded);
        };
    }

}
*/