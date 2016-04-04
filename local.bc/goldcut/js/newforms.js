//requiredFieldsInEntity = [];
/**
RootFormControl(configXML, domContainer) -> GeneralFormControl(configXML, form) & ^onSubmit get changed data
	GeneralFormControl -> FormForGeneralEntityStructureRecursor(structure.childNodes, null, domContainer)
		FormForGeneralEntityStructureRecursor -> new FormInput(dom, {name: fn, title: ft, type: ftype})
		|
		FormForGeneralEntityStructureRecursor -> new FormWidgetFactory(dom, fieldTag)

inherit(FormInput, InteractiveBlock);
inherit(FormInputString, FormInput);
*/

function GeneralFormControl(configXML, formDomContainer)
{
    var structure = configXML.getElementsByTagName("structure")[0];
    FormForGeneralEntityStructureRecursor(structure.childNodes, null, formDomContainer);
}

function FormForGeneralEntityStructureRecursor(structs, ns, containerDom)
{
	for (var childItem in structs) {
		if (structs[childItem].nodeType == 1)
		{
			if (structs[childItem].tagName == 'group')
			{
				ns = structs[childItem].getAttribute('name');
				nstitle = (nstitle = structs[childItem].getAttribute('title')) ? nstitle : ns;
//                console.log(nstitle, structs[childItem]);
				if (ns) console.log('GROUP *', ns);
				var fieldset = document.createElement("fieldset");
				var legend = document.createElement("legend");
				legend.appendChild(document.createTextNode(nstitle));
				fieldset.appendChild(legend);
				containerDom.appendChild(fieldset);
				// recursive @
				FormForGeneralEntityStructureRecursor(structs[childItem].childNodes, ns, fieldset);
			}
			else
			{
				fieldTag = structs[childItem];
//				console.log(fieldTag);
				if (fieldTag.tagName == 'field' || fieldTag.tagName == 'option')
				{
                    var fn = fieldTag.getAttribute('name');
					var ft = fieldTag.getAttribute('title');
					var ftype = fieldTag.getAttribute('type');
					//requiredFieldsInEntity.push(fn);
					// INPUT
					new FormInput(containerDom, {name: fn, title: ft, type: ftype, ns: ns});
				}
				if (fieldTag.tagName == 'hasone' || fieldTag.tagName == 'useone')
				{
					// FORM WIDGET
                    var ns = fieldTag.getAttribute('entity');
                    console.log(ns);
					new FormWidgetFactory(containerDom, fieldTag, ns);
				}
				if (fieldTag.tagName == 'hasmany' || fieldTag.tagName == 'usemany')
				{
					// FORM WIDGET
                    alert('Not impl');
					//new FormWidgetFactory(containerDom, fieldTag, ns);
				}
			}
				
		}
	}
}


/**
RootFormControl(c, domc) delegate form creation to -> GeneralFormControl(configXML, form
^onSubmit -> get changed data
	direct children get css(form > div.inputwidget[data-isValueChanged="yes"])
	??? inline form widgets - has, use
*/
function RootFormControl(configXML, domContainer)
{
	//console.log(configXML, domContainer);

	var entityManager = configXML.getElementsByTagName("entity")[0].getAttribute('manager');
	var entityName = configXML.getElementsByTagName("entity")[0].getAttribute('name');
	console.log('RootFormControl for ', entityName, entityManager);

    var form = document.createElement("form");

    form.data = {inittime: new Date().getTime()};

	// build form from xml
	new GeneralFormControl(configXML, form);
	domContainer.appendChild(form);

    // create Submit
	var submit = document.createElement("input");
	submit.setAttribute('type', 'button');
	submit.setAttribute('name', 'ok');
	submit.setAttribute('value', 'FORM SUBMIT');
    submit.setAttribute('style', 'margin: 2em;');
	form.appendChild(submit);

    // ON Submit
	Event.add(submit, "click", function(e) { 
		changedData = {};
		var inputs = form.querySelectorAll('form > div.inputwidget[data-isValueChanged="yes"]');
		for (var i = 0; i < inputs.length; i++)
		{
            console.log(inputs[i]);
//            console.log(inputs[i].firstChild);

//			changedData[inputs[i].firstChild().getAttribute('data-name')] = inputs[i].firstChild.newValue;

			//console.log(inputs[i].getAttribute('name'), inputs[i].getAttribute('value'));
		}
		var formwidgets = form.querySelectorAll('form > div.formwidget > div.formwidgetmanaged');
		//console.log(formwidgets);
		for (var i = 0; i < formwidgets.length; i++)
		{
            console.log(formwidgets[i]);

//			changedData[formwidgets[i].getAttribute('data-name')] = formwidgets[i].changedData;

			//console.log(inputs[i].getAttribute('name'), inputs[i].getAttribute('value'));
		}
		console.log('CHANGED DATA', changedData);
		return false; 
	});
}





/**
this.domNode.controller = this;
domNode.appendChild(div);
InteractiveBlock.prototype.root = f()
*/
// BASE CLASS. ROLE?
function InteractiveBlock(domNode)
{
	this.domNode = domNode;
	this.domNode.controller = this;
	var div = document.createElement("div");
    addClass(div,'interactiveBlock');
	domNode.appendChild(div); // + WRAP not include
    console.log("!InteractiveBlock", this.domNode);
}
InteractiveBlock.prototype.root = function(){
	console.log('InteractiveBlock.prototype.root = f()', this.domNode);
}

/**
this.domNode.controller = this; // link to this object. vs .this.formwidget - link to input widget div container
*/
//inherit(FormInput, InteractiveBlock);

function FormInput(formDomContainer, o) {
	// is_dirty, oldValue, value, 
	this.name = o.name;// + '123';
	var div = document.createElement("div");
	div.classList.add('inputwidget');
	var input = document.createElement("input");
	div.setAttribute('data-name', o.name);
    if (o.ns)
    {
        div.setAttribute('data-ns', o.ns);
        input.setAttribute('data-ns', o.ns);
    }
	input.setAttribute('name', o.name);
	input.setAttribute('placeholder', o.title);
//	input.setAttribute('value', o.name + '_val');
//	input.setAttribute('data-oldValue', o.name + '_val');
	input.inputwidget = div;
	//div.input = input;
	div.appendChild(input);
	formDomContainer.appendChild(div);

    // !!! console.log(formDomContainer);

	div.formc = formDomContainer;

	Event.add(input, "blur", function(e) { 
		//console.log('blur',this.controller.name, this.inputwidget);
		var fieldname = this.getAttribute('name'); 
		var oldValue = this.getAttribute('data-oldValue'); 
		var newValue = this.value;
		//console.log(newValue, oldValue);
		//console.log(domContainer);
		//console.log(this.inputwidget.formwidget);
		if (newValue != oldValue)
		{
			this.setAttribute('data-isValueChanged', 'yes'); //this.inputwidget
			this.newValue = newValue; //inputwidget.
            // on container
//			if (!this.inputwidget.formwc.changedData) this.inputwidget.formc.changedData = {};
			//this.inputwidget.formc
            input.form.data[fieldname] = newValue;
            console.log(input.form.data);
		}
		else
        {
			this.setAttribute('data-isValueChanged', 'no'); //th iw
        }
	});
	//Event.add(div, "click", function(e) { console.log(this) });
//	InteractiveBlock.call(this, div);
	this.domNode = input;
	this.domNode.controller = this; // link to this object. vs .this.formwidget - link to input widget div container
}
//inherit(FormInput, InteractiveBlock);








/**
FormWidgetFactory(domc, c) -> FormWidgetFactoryHasOne(domc, c) create div.formwidget -> FormForPhoto(div, configXML)
	FormForPhoto(div, configXML) create div.formwidgetmanaged -> use general FormForGeneralEntityStructureRecursor()
*/
function FormWidgetFactory(formDomContainer, configXML) {
//	console.log(configXML);
	fw = new FormWidgetFactoryHasOne(formDomContainer, configXML);
	//fw.root();
}
//inherit(FormWidgetFactory, InteractiveBlock);

function FormWidgetAbstract() {}

function FormWidgetFactoryHasOne(formDomContainer, configXML, ns) {
	var div = document.createElement("div");
	div.classList.add('formwidget');
    var ns = configXML.getAttribute('entity');
    if (ns)
    {
        div.setAttribute('data-ns', ns);
    }
	new FormForPhoto(div, configXML);
	formDomContainer.appendChild(div);
}
inherit(FormWidgetFactoryHasOne, FormWidgetAbstract);


function FormForPhoto(formDomContainer, configXML, ns) {
	var div = document.createElement("div");
	div.classList.add('formwidgetmanaged');
	var ename = configXML.getAttribute('entity');
	var asname = configXML.getAttribute('as');	
	div.setAttribute('data-name', (asname ? asname : ename));
	console.log('FormForPhoto() ename, asname:', ename, asname);

	// GENERAL INCLUDED ENTITY (NO SPECIAL WIDGET)
	FormForGeneralEntityStructureRecursor(configXML.getElementsByTagName("structure")[0].childNodes, ns, div);

	formDomContainer.appendChild(div);
	
}
inherit(FormForPhoto, FormWidgetAbstract); // FormWidgetFactoryHasOne

/*
function FormInputString() {}
inherit(FormInputString, FormInput);

function FormInputText() {}
inherit(FormInputText, FormInput);
*/