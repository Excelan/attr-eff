// build blank form with inputs, structs, multiple structs (structure in XML)
// build filled form, with multiple structs (data in JSON)
// select fields  gc data requests {urn: urn-department} or gate request (return structs of value/title/selected)
// TODO show changed, new data in inputs
// 			(compare json, list of patches (time, field, old/new(formatByFType)) +group by 1 save)
// TODO add, clone button in multiple structs
// TODO 	reorder ms items
// TODO 	delete ms item
// TODO internal validate
// TODO 	check unique (gate request)
// --
// TODO window find, select LIST
// TODO show diff in text
// TODO changed text get as patch suggestion
// TODO show history list (patches, preview it)

'use strict';

GC.ONLOAD.push(function (e) {

    setTimeout(function(){TMCE();}, 3000);

});

    function TMCE() {
        var tas = document.querySelectorAll('textarea');
        for (var i = 0; i < tas.length; i++) {
            var maxLines = 50;
            var autoGrowTextarea = new Autogrow(tas[i], maxLines);
        }
        //new GCFileUpload();
        //console.log(tinyOptions);
        //tinyMCE.init(tinyOptions);

        tinymce.init({
            mode: "textareas",
            editor_selector: 'richtext',
            width: '400px',
            // inline: true,
            // format: 'html',
            // forced_root_block : true,
            menubar: false,
            statusbar: false,
            autoresize_min_height: 50,
            autoresize_max_height: 800,
            autoresize_bottom_margin: 50,
            autoresize_on_init: true,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor autoresize',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table contextmenu paste'
            ],
            toolbar: 'insertfile undo redo | bullist numlist outdent indent | link | fullscreen'
        });

    }

function createFormContainerDelegateToRecursor(configXML, domContainer, data, controller, saveenabled) {
    try {
        var role = configXML.getElementsByTagName("form")[0].getAttribute('role');
    }
    catch(e) {}

    var form = document.createElement("form");
    form.data = {
        inittime: new Date().getTime()
    };

    //console.log(data)
    //console.log(data['urn'])
    var urnHidden = document.createElement("input");
    //if (data.urn)
        urnHidden.setAttribute("type", "hidden");
    urnHidden.setAttribute("data-selector", "urn");
    urnHidden.setAttribute("id", "URN");
    urnHidden.setAttribute("value", data.urn);
    form.appendChild(urnHidden);

    urnHidden = document.createElement("input");
    urnHidden.setAttribute("type", "hidden");
    urnHidden.setAttribute("data-selector", "upn");
    urnHidden.setAttribute("id", "UPN");
    urnHidden.setAttribute("value", data.upn);
    form.appendChild(urnHidden);

    // @ RECURSIVE
    if (configXML == null) throw "No configXML";
    FormForGeneralEntityStructureRecursor(configXML.getElementsByTagName("structure")[0].childNodes, [], form, 0, data);
    domContainer.appendChild(form);


    //верхння кнопка
    var buttons2 = document.createElement("div");
    buttons2.classList.add("buttons");
    buttons2.classList.add("c");

    if (saveenabled) {
        var formSubmit2 = document.createElement("input");
        formSubmit2.setAttribute("type", "submit");
        formSubmit2.setAttribute("id", "submitInternal");
        if(id('processproto').value == 'DMS:Correction:CAPA' && (id('currentstage').value == 'Delegating' || id('currentstage').value == 'Considering'))
        formSubmit2.setAttribute("value", "Сохранить и отправить");
        else if(id('processproto').value == 'DMS:Correction:CAPA' && id('currentstage').value == 'Doing')
        formSubmit2.setAttribute("value", "Сделано");
        else if(id('currentstage').value == 'Approving')
            formSubmit2.setAttribute("value", "Утвердить");
        else formSubmit2.setAttribute("value", "Сохранить");
        formSubmit2.classList.add("gin");
        formSubmit2.classList.add("c");

        buttons2.appendChild(formSubmit2);
    }
    domContainer.appendChild(buttons2);

    //управление редиректами с форм
    if(formSubmit2) {
        if (formSubmit2.value.toLowerCase() != 'сохранить') {
            id('managedform').setAttribute('data-goto', '/inbox');
        } else id('managedform').removeAttribute('data-goto');
    }
    if (controller != null) {
        //console.log(controller)
        //нижняя кнопка
        var buttons = document.createElement("div");
        var buttonsp = document.createElement("p");
        buttonsp.innerHTML = 'Завершить выполнение этапа процесса';
        buttonsp.classList.add("itext");
        buttons.classList.add("buttons");
        buttons.appendChild(buttonsp);

        var formSubmit = document.createElement("input");
        formSubmit.setAttribute("type", "button");
        formSubmit.setAttribute("id", "nextInternal");
        formSubmit.setAttribute("value", "Отправить");
        formSubmit.classList.add("gin");

        buttons.appendChild(formSubmit);
        domContainer.appendChild(buttons);
    }


}


function FormForGeneralEntityStructureRecursor(structs, ns, containerDom, level, data, insidecontext) {
    // console.log('NS: ', ns, level, insidecontext);
    level++;
    for (var childItem in structs) {
        if (structs[childItem].nodeType != 1) continue;

        // console.log(structs[childItem]);

        if (structs[childItem].tagName == 'section') // and level == 1
        {
            if (level > 1) throw "NO NESTED SECTIONS";
            var nstitle = structs[childItem].getAttribute('title');
            //console.log('SECTION *', nstitle);

            var fieldset;
            var legend;
            var useHtmlFieldSetLegend = false;
            if (useHtmlFieldSetLegend)
            {
                // use <fieldset><legend>
                fieldset = document.createElement("fieldset");
                legend = document.createElement("legend");
            }
            else {
                // use div.fieldset > div.legend
                fieldset = document.createElement("div");
                fieldset.classList.add("fieldset");
                legend = document.createElement("div");
                legend.classList.add("legend");
            }
            var iscontext = (structs[childItem].getAttribute('type') == 'context' || insidecontext);
            if (iscontext) fieldset.classList.add('contextsection');
            legend.appendChild(document.createTextNode(nstitle));
            fieldset.appendChild(legend);
            containerDom.appendChild(fieldset);

            // @ RECURSIVE
            FormForGeneralEntityStructureRecursor(structs[childItem].childNodes, ns, fieldset, level, data, iscontext); // recursive

        }
        else if (structs[childItem].tagName == 'struct')
        {
            var ismultiple = (structs[childItem].getAttribute('multiple') == 'yes');
            var iscontext = (structs[childItem].getAttribute('type') == 'context');
            var nsname = structs[childItem].getAttribute('name');
            var nstitle = structs[childItem].getAttribute('title');
            var nsanon = structs[childItem].getAttribute('anonymous') == 'yes' ? true : false;
            //console.log('STRUCT *', nsname, level, ismultiple);

            ns.push(nsname);
            var selector = ns.join('-');
            ns.pop();

            var wholeStructSection = document.createElement("section");
            wholeStructSection.classList.add("structsection");
            if (nsanon) wholeStructSection.classList.add("anon");
            if (iscontext) wholeStructSection.classList.add('contextstruct');
            //if (ismultiple) wholeStructSection.classList.add("multiple");
            else wholeStructSection.classList.add("nonanon");
            wholeStructSection.setAttribute("data-level", level-1);

            // header
            var enableHeaderFooter = !nsanon; // у не анонимной структуры есть заголовок

            // TODO это заголовок и множественных структур и одной структуры
            // TODO позже в buildStructItem
            var enableHeaderForStructMultAll = (!ismultiple && !nsanon) ? true : false; // TODO OPTION!!!
            if (enableHeaderForStructMultAll) // non anon
            {
                var header = document.createElement("header");
                header.classList.add("ofstruct");
                if (ismultiple) header.classList.add("multiple");
                else header.classList.add("single");
                wholeStructSection.appendChild(header); // <
                header.appendChild(document.createTextNode(nstitle));
            }
            // / header

            // 1 / multiplestructcontainerfor
            var minstructs = structs[childItem].getAttribute('min') ? parseInt(structs[childItem].getAttribute('min')) : 1;
            //console.log(nsname, 'min', minstructs)
            var structcontainerm = document.createElement("main");
            structcontainerm.classList.add('structcontainer');
            if (ismultiple)
                structcontainerm.setAttribute('data-multiplestructcontainerfor', nsname);

            wholeStructSection.appendChild(structcontainerm);

            // footer with +Add in multiple Structs
            if (enableHeaderFooter) // TODO OPTION!!!
            {
                var footer = document.createElement("footer");
                footer.classList.add("ofstruct");
                if (ismultiple) footer.classList.add("multiple");
                else footer.classList.add("single");
                wholeStructSection.appendChild(footer); // <
                if (ismultiple && !insidecontext) //  && (o.editmode == 'allow' || o.editmode == 'lockloaded')
                {
                    var addIcon = document.createElement("a");
                    addIcon.setAttribute("href", "#");
                    addIcon.appendChild(document.createTextNode("Добавить "+nstitle));
                    footer.appendChild(addIcon);
                    //console.log(structs[childItem].childNodes);
                    // TODO FIX ADD CLONE
                    //console.log('Add selector level', selector, level);
                    Event.add(addIcon, "click", function(e)
                    {
                        var blankdata = {};
                        blankdata[this.nsname] = [];
                        var nth = this.structcontainerm.querySelectorAll('section[data-struct="'+this.selector+'"]').length;
                        //console.log(this.xml);
                        //console.log('var', nsname, selector, ns);
                        //console.log('this nsname, selector, ns, level', this.nsname, this.selector, this.ns, this.level);
                        var structcontainer = buildStructItem(this.xml, blankdata, this.ismultiple, this.ns, this.nsname, this.selector, this.nstitle, nth, this.level, this.insidecontext);
                        //structcontainer.setAttribute("data-struct", selector);
                        //console.log(structcontainer);
                        this.structcontainerm.appendChild(structcontainer);
                        TMCE();
                    }.bind({structcontainerm: structcontainerm, xml: structs[childItem].childNodes, insidecontext: insidecontext, ismultiple: ismultiple, ns: ns.slice(), nsname: nsname, nstitle: nstitle, level: level, selector: selector}));
                    //footer.setAttribute("data-addtostack", inputsVerticalStack.id); // TODO
                }
            }
            // / footer

            if (ismultiple) structcontainerm.setAttribute("data-multiplestruct", selector);

            if (data == undefined) data = {};
            if (data[nsname] == undefined) data[nsname] = []; // minstructs at least == 1
            for (var i = 0; i < ((data[nsname].length > minstructs) ? data[nsname].length : minstructs); i++) // TODO data[nsname]
            {
                // !!! FN
                // TODO Заголовок каждой структуры из mult создается здесь, как часть самого элемента структуры
                var structcontainer = buildStructItem(structs[childItem].childNodes, data, ismultiple, ns, nsname, selector, nstitle, i, level, insidecontext);
                structcontainer.setAttribute("data-struct", selector);
                if (ismultiple) structcontainer.setAttribute("data-array", "item");
                structcontainerm.appendChild(structcontainer);
            }
            // add whole struct to container
            containerDom.appendChild(wholeStructSection); // < !
        }
        else // FIELD
        {
            var fieldTag = structs[childItem];
            if (fieldTag.tagName == 'field')
            {
                var fn = fieldTag.getAttribute('name');
                var ft = fieldTag.getAttribute('title');
                var fp = fieldTag.getAttribute('placeholder');
                var ftype = fieldTag.getAttribute('type');
                var multmin = fieldTag.getAttribute('min') ? fieldTag.getAttribute('min') : 0;
                var ismultiple = fieldTag.getAttribute('multiple');
                var editmode = fieldTag.getAttribute('edit') ? fieldTag.getAttribute('edit') : "allow";
                var origeditmode = editmode;
                if (insidecontext)
                    editmode = 'lock'; // !
                if (origeditmode == 'unlock')
                    editmode = 'allow'; // !
                //console.log(editmode, origeditmode);
                // console.log('F', ns, fn);
                ns.push(fn);
                var selector = ns.join('-');
                ns.pop();

                // case type > get value option (default value)
                var options = [];
                // message
                var promise;
                var messageXML = structs[childItem].getElementsByTagName("message");
                if (messageXML && messageXML.length == 1) {
                    var message = getAttributes(messageXML[0]);
                    promise = new Promise(function(resolve, reject) {
                        setTimeout(function() {
                            console.log('RESOLVE M NOW');
                            resolve(message);
                        }, 100);
                    });
                }
				// Gate
                if (ftype == 'select' || ftype == 'radio') {
                    var messageXML = structs[childItem].getElementsByTagName("query");
                    if (messageXML && messageXML.length == 1) {
                        var message = getAttributes(messageXML[0]);
                        if (id('mpe')) message.mpe = id('mpe').value;
                        promise = new Promise(function (resolve, reject) {
                            ajax('/' + message.gate, function (resp) {
                                //console.log('SELECT RES', this);
                                resolve(resp);
                                window['dataFromGate'+this] = resp;
                            }.bind(message.gate), {
                                'onError': function (e, d) {
                                    console.error(e);
                                    console.error(d);
                                    reject(e);
                                },
                                'onStart': noop,
                                'onDone': noop
                            }, 'POST', message);
                        });
                    }
                    // inplace local options
                    var optionsXML = structs[childItem].getElementsByTagName("options");
                    var optionXML = [];
                    if (optionsXML && optionsXML.length == 1) {
                        optionXML = optionsXML[0].getElementsByTagName("option");
                        for (var i = 0; i < optionXML.length; i++) {
                            var optionValue = optionXML[i].getAttribute('value');
                            var optionTitle = optionXML[i].getAttribute('title');
                            options.push({'value': optionValue, 'title': optionTitle});
                        }
                    }
                }
                //if (options.length) console.log('Value opts', options);

                //if (insidecontext) editmode = 'lock';

                var fieldValue = data ? (data[fn] ? data[fn] : undefined) : undefined;
                var inputopts = {
                    name: fn,
                    title: ft,
                    placeholder: fp,
                    type: ftype,
                    selector: selector,
                    value: fieldValue,
                    valueoptions: options,
                    ismultiple: ismultiple,
                    multmin: multmin,
                    editmode: editmode
                }
                if (promise != undefined) inputopts.deferredoptions = promise;
				promise = undefined;
                new FormInput(containerDom, inputopts);
            } // field
        }

    }
}

/**
this.domNode.controller = this; // link to this object. vs .this.formwidget - link to input widget div container
*/
//inherit(FormInput, InteractiveBlock);

// header (help. error place)
// main
    // label
    // input/s container vertical stack
        // input & wrapper controls L/R
            // L controls (- del)
            // pure input
            // R controls (valid)
// footer (+ add)
function FormInput(formDomContainer, o)
{
    this.name = o.name;

    // input only (без controls)
    var pureInputs = [];
    var values = [];
    if (!o.ismultiple) values = [o.value];
    else values = o.value;
    if (values == undefined) values = [];
    var oloc;
    var maxmult = Math.max(o.multmin, values.length);
    for (var i = 0; i < maxmult; i++) {
        oloc = {"value": values[i], "selector": o.selector, "placeholder": o.placeholder, "ismultiple": o.ismultiple, "editmode": o.editmode, "valueoptions": o.valueoptions, "deferredoptions": o.deferredoptions }
        var input = caseBuildInput(o.type, oloc);
        pureInputs.push(input);
        //input.inputwidget = wholeInputSection; // =
    }

    // весь блок с label, input + controls (+,-,lock,reorder)
    var wholeInputSection = document.createElement("section");
    if (o.ismultiple) wholeInputSection.classList.add('multipleinput');
    wholeInputSection.classList.add('inputwidget');

    // hide section with input hidden
    if (o.type == 'hidden') hide(wholeInputSection);

    // label
    var label = document.createElement("label");
    label.appendChild(document.createTextNode(o.title));
    //main.appendChild(label); // <

    var enableHeader = 0;
    if (enableHeader == 1)
    {
        var header = document.createElement("header");
        wholeInputSection.appendChild(header); // <
        header.appendChild(document.createTextNode("Help"));
    }
    var main = document.createElement("main");
    main.classList.add("BLK");
    wholeInputSection.appendChild(main); // <
    // Stack for Field with controls Containers
    var inputsVerticalStack = document.createElement("div");
    inputsVerticalStack.classList.add("stack");
    inputsVerticalStack.id = "stack" + Math.floor((Math.random() * 1000000) + 1);
    inputsVerticalStack.setAttribute("data-containfieldtype", o.type);
    inputsVerticalStack.setAttribute("data-containfieldselector", o.selector);
    main.appendChild(label); // <
    main.appendChild(inputsVerticalStack); // <
    // footer with +Add in multiple fields
    var footer = document.createElement("footer");
    wholeInputSection.appendChild(footer); // <
    if (o.ismultiple && (o.editmode == 'allow' || o.editmode == 'lockloaded'))
    {
        var addIcon = document.createElement("img");
        addIcon.setAttribute("src", "/goldcut/assets/icons/newicons/add.png");
        addIcon.setAttribute("width", "17");
        addIcon.setAttribute("height", "17");
        footer.appendChild(addIcon);
        //footer.appendChild(document.createTextNode("+ Add")); // fieldtype, containerId. TODO create case input, wrap with LFRContainer
        footer.setAttribute("data-addtostack", inputsVerticalStack.id);
        footer.setAttribute("data-forwardismultiple", o.ismultiple);
        if (o.type == 'select')
        {
            footer.setAttribute("data-forwardismultiple", 'XXX');
            footer.defopt = o.deferredoptions;
        }
    }

    for (var i = 0; i < pureInputs.length; i++)
    {
        var pureInputContainer = buildLFRContainer(o.type, pureInputs[i], o.ismultiple, (i+1), o.editmode);
        // apend new Container to Stack
        inputsVerticalStack.appendChild(pureInputContainer); // <
    }

    // !!!
    formDomContainer.appendChild(wholeInputSection); // >
    wholeInputSection.formc = formDomContainer;
    // + on change
    if (input != undefined)
    {
        this.domNode = input;
        this.domNode.controller = this; // link to this object. vs .this.formwidget - link to input widget div container
    }

}
//inherit(FormInput, InteractiveBlock);


function buildStructItem(xml, data, ismultiple, ns, nsname, selector, nstitle, i, level, insidecontext)
{
    // console.log('buildStructItem', ismultiple, ns, nsname, selector, nstitle, i, level);
    var structcontainer = document.createElement("section");
    structcontainer.setAttribute('data-struct', selector);
    structcontainer.classList.add('structitem');
    if (ismultiple) {
        structcontainer.setAttribute('data-array', 'item');
        structcontainer.classList.add("multiplestructitem");
    }
    // every struct item header
    if (ismultiple)
    {
        var header = document.createElement("div");
        //
        if (!insidecontext) {
            var closespan = document.createElement("span");
            var closeimg = document.createElement("img");
            closeimg.setAttribute("src", "/goldcut/assets/icons/newicons/close.png");
            closespan.appendChild(closeimg);
            header.appendChild(closespan);
        }
        //
        header.classList.add("everystructheader");
        var headertext = document.createElement("div");
        headertext.classList.add("everystructheadertext");
        headertext.appendChild(document.createTextNode(nstitle + " " + (i+1)));
        header.appendChild(headertext);
        structcontainer.appendChild(header); // <
    }
    //
    var fieldValue = (ismultiple ? data[nsname][i] : data[nsname]);
    ns.push(nsname);
    // @ RECURSIVE (1/MULT STRUCT)
    FormForGeneralEntityStructureRecursor(xml, ns, structcontainer, level, fieldValue, insidecontext); // recursive
    //FormForGeneralEntityStructureRecursor(structs[childItem].childNodes, ns, structcontainer, level, fieldValue); // recursive
    ns.pop();
    return structcontainer;
}

function buildLFRContainer(oType, pureInputsEl, ismultiple, pos, editmode)
{
    // Container(L+Field+R)
    var pureInputContainer = document.createElement("div");
    pureInputContainer.id = "LFRContainer" + Math.floor((Math.random() * 1000000) + 1);
    pureInputContainer.classList.add("inputWithControls");
    pureInputContainer.classList.add("BLK");
    pureInputContainer.classList.add("item"+pos);
    pureInputContainer.setAttribute("data-fieldtype", oType);
    pureInputContainer.setAttribute("data-editmode", editmode);
    // controlsR
    var controlsR = document.createElement("nav");
    controlsR.classList.add('R');
    controlsR.appendChild(document.createTextNode(""));
    pureInputContainer.appendChild(controlsR); // <
    // FIELD
    var fieldbox = document.createElement("div");
    fieldbox.classList.add("fieldbox");
    if(pureInputsEl.tagName == "SELECT"){
        fieldbox.classList.add("selectbox");
    }
    fieldbox.appendChild(pureInputsEl); // <
    pureInputContainer.appendChild(fieldbox); // <
    // controlsL
    var controlsL = document.createElement("nav");
    controlsL.classList.add('L');
    var controlLIcon = document.createElement("img");
    var spanelement = document.createElement("span");
    if (ismultiple)
    {
        if (editmode == 'allow')
        {
            controlLIcon.setAttribute("src", "/goldcut/assets/icons/newicons/delete.png");
            controlLIcon.setAttribute("width", "17");
            controlLIcon.setAttribute("height", "17");
            controlsL.setAttribute("data-action", "deleteitem");
        }
        else
        {
            controlLIcon.setAttribute("src", "/goldcut/assets/icons/newicons/lock.png");
            controlLIcon.setAttribute("width", "13");
            controlLIcon.setAttribute("height", "19");
        }
        //controlLIcon = document.createElement("span");
    }
    else {
        if (editmode == 'allow')
        {
            //controlLIcon.setAttribute("src", "/goldcut/assets/filetype/default.png");
            controlLIcon = document.createElement("span");
        }
        else
        {
            controlLIcon.setAttribute("src", "/goldcut/assets/icons/newicons/lock.png");
            controlLIcon.setAttribute("width", "13");
            controlLIcon.setAttribute("height", "19");
        }
        //controlLIcon = document.createElement("span");
    }
    if (controlLIcon.tagName != "SPAN")
    {
        controlsL.appendChild(controlLIcon);
        controlsL.setAttribute("data-controlCLFRContainer", pureInputContainer.id);
        pureInputContainer.appendChild(controlsL); // <
    }
    return pureInputContainer;
}

function caseBuildInput(oType, oloc)
{
    var input;
    switch (oType) {
        case 'hidden':
            input = inputBuildText(oloc, 'hidden');
            break;
        case 'string':
            input = inputBuildText(oloc);
            break;
        case 'date':
            input = inputBuildText(oloc, 'date');
            break;
        case 'email':
            input = inputBuildText(oloc, 'email');
            break;
        case 'url':
            input = inputBuildText(oloc, 'url');
            break;
        case 'integer':
            input = inputBuildText(oloc, 'number');
            break;
        case 'float':
            input = inputBuildText(oloc);
            break;
        case 'money':
            input = inputBuildText(oloc);
            break;
        case 'tel':
            input = inputBuildText(oloc, 'tel');
            break;
        case 'text':
            input = inputBuildTextArea(oloc);
            break;
        case 'richtext':
            input = inputBuildRichTextArea(oloc);
            break;
        case 'radio':
            input = inputBuildRadio(oloc);
            break;
        case 'checkbox':
            input = inputBuildRadio(oloc);
            break;
        case 'attachment':
            input = inputBuildAttachment(oloc);
            break;
        case 'select':
            var sel = new InputBuilderSelect(oloc);
            input = sel.getDom();
            break;
        case 'Document':
            var sel = new InputBuilderRichSelect(oloc, oType);
            input = sel.getDom();
            break;
        case 'RiskManagementRiskApproved':
            var sel = new InputBuilderRichSelect(oloc, oType);
            input = sel.getDom();
            break;
        case 'BusinessObject':
            //oloc.value = 'urn:z:z:z:1';
            //oloc.unit = {'urn:z:z:z:1' : 'ZZZ1'};
            var sel = new InputBuilderRichSelect(oloc, oType);
            input = sel.getDom();
            break;
        case 'CompanyLegalEntityCounterparty':
            var sel = new InputBuilderRichSelect(oloc, oType);
            input = sel.getDom();
            break;
        case 'ManagementPostIndividual':
            var sel = new InputBuilderRichSelect(oloc, oType);
            input = sel.getDom();
            break;
        default:
            alert("NO TYPE " + oType);
            console.log("NO TYPE " + oType);
            input = inputBuildText(oloc);
            break;
    }
    return input;
}

function inputBuildText(o, html5type) {
    var input = document.createElement("input");
    if (o.editmode == 'allow') input.setAttribute('data-selector', o.selector);
    if(o.placeholder)input.setAttribute('placeholder', o.placeholder);
    if (!html5type) html5type = 'text';
    input.setAttribute('type', html5type);
    if (o.value != undefined) input.setAttribute('value', o.value);
    if (o.editmode != 'allow') input.setAttribute('disabled', 'disabled');
    if (o.ismultiple) input.setAttribute('data-multiple', 'yes');
    return input;
}

function onEachUploadedLocal1(d)
{
  //console.log(d.manager.domcontainer)
  console.log(d)
  //d.manager.domcontainer.setAttribute('data-selector', o.selector);
  d.manager.domcontainer.querySelector('input[data-selector]').setAttribute('value', d.response.uri);
  d.manager.domcontainer.querySelector('img').setAttribute('src', d.response.uri);
}

function inputBuildAttachment(o) {
    var inputWrapper = document.createElement("div");
    inputWrapper.setAttribute('data-upload', 'urn:Media:UniversalImage:Container');
    inputWrapper.setAttribute('data-eachuploaded', 'onEachUploadedLocal1');
    inputWrapper.setAttribute('data-alluploaded', 'onAllUploadedLocal');
    //
    var preview = document.createElement("img");
    preview.classList.add('fileAttachPreviewMicro');
    if (o.value != undefined && !(o.value[""] == "")) { preview.src = o.value;  } // console.log(o.value)
    var linkPreview = document.createElement("a");
    var datal = 'all';
    if (o.value != undefined && !(o.value[""] == "")) { linkPreview.href = o.value;  datal = o.value; }
    linkPreview.setAttribute('data-lightbox', datal);
    linkPreview.appendChild(preview);
    inputWrapper.appendChild(linkPreview);
    var showInput = true;
    if (o.value == undefined || o.value[""] == "") hide(preview);
    //else showInput = false;
    //
    var inputH = document.createElement("input");
    inputH.setAttribute('type', 'hidden');
    if (o.value != undefined && !(o.value[""] == "")) inputH.setAttribute('value', o.value);
    if (o.editmode != 'allow') inputH.setAttribute('disabled', 'disabled');
    if (o.editmode == 'allow') inputH.setAttribute('data-selector', o.selector);
    if (o.ismultiple) inputH.setAttribute('data-multiple', 'yes');
    inputWrapper.appendChild(inputH);
    //
    if (o.editmode == 'allow' && showInput == true) //  && o.value == undefined
    {
      var input = document.createElement("input");
      input.setAttribute('type', 'file');
      inputWrapper.appendChild(input);
      new GCFileUpload(inputWrapper);
    }
    else {
      show(preview);
    }
    //

    return inputWrapper;
}


function inputBuildTextArea(o) {
    var input = document.createElement("textarea");
    if(o.placeholder)input.setAttribute('placeholder', o.placeholder);
    if (o.editmode == 'allow') input.setAttribute('data-selector', o.selector);
    //input.setAttribute('name', 'xtext');
    if (o.editmode != 'allow') input.setAttribute('disabled', 'disabled');
    if (o.value != undefined) input.appendChild(document.createTextNode(o.value));
    return input;
}

function inputBuildRichTextArea(o) {
    //var input = document.createElement("div");
    var input = document.createElement("textarea");
    //if(o.placeholder)input.setAttribute('placeholder', o.placeholder);
    if (o.editmode == 'allow') input.setAttribute('data-selector', o.selector);
    //input.setAttribute('name', 'xtext');
    if (o.editmode != 'allow') input.setAttribute('disabled', 'disabled');
    input.classList.add('richtext');
    if (o.value != undefined) input.appendChild(document.createTextNode(o.value));
    return input;
}

function inputBuildRadio(o) {
    var container = document.createElement("div");
	container.classList.add('radioContainer');
	var ns = Math.random() * 100000;
    for (var ov in o.valueoptions) {
        if (o.valueoptions.hasOwnProperty(ov)) {
			var valueContainer = document.createElement("div");
			valueContainer.classList.add('radioValueContainer');
            var ovvalue = o.valueoptions[ov].value;
			var ovtitle = o.valueoptions[ov].title;
            var option = document.createElement("input");
			option.setAttribute('name', ns+o.selector);
            option.setAttribute('type', 'radio');
            if (o.editmode == 'allow') option.setAttribute('data-selector', o.selector);
            option.setAttribute('value', ovvalue);
			if (ovvalue == o.value) option.setAttribute('checked', 'checked');
            if (o.editmode != 'allow') option.setAttribute('disabled', 'disabled');
			var titlespan = document.createElement("span");
            titlespan.appendChild(document.createTextNode(ovtitle));
            valueContainer.appendChild(option);
			valueContainer.appendChild(titlespan);
			container.appendChild(valueContainer);
        }
    }
    return container;
}

// this.rebuild(this.data) - MVC with local data VS MVC with external data (in window)
function InputBuilderSelect(o) {
	this.data = o;
    //console.log(o)
    this.sel = document.createElement("select");
    if (o.editmode != 'allow') this.sel.setAttribute('disabled', 'disabled');
    if (o.editmode == 'allow') this.sel.setAttribute('data-selector', this.data.selector);

    var that = this;

	if (this.data.valueoptions != undefined && this.data.valueoptions.length > 0) // inline options
    {
      /*
      var option = document.createElement("option");
      option.setAttribute('value', 'NULL');
      option.appendChild(document.createTextNode('Выберите'));
      this.sel.appendChild(option);
      */
      //
        this.rebuild();
    }
	else if (this.data.deferredoptions)  // loaded options
    {
      // var key = Object.keys(this.data.value)[0];
      // if (key == 0) key = this.data.value;
      var option = document.createElement("option");
      option.setAttribute('value', 'NULL');
      if (this.data.value)
      {
        this.sel.needclean = true;
        option.appendChild(document.createTextNode('Loading..'));
        this.sel.appendChild(option);
      }
      //else
        //option.appendChild(document.createTextNode('Выберите'));

      //
        //console.log('this.data.deferredoptions');
        this.data.deferredoptions.then(function(val) {
            //console.log('this.data.deferredoptions THEN ', val);
            that.data.valueoptions = val.options;
            that.rebuild();
        }).catch(function(error) {
            console.log('InputBuilderSelect deferred resolve error', error);
        });
    }
}
InputBuilderSelect.prototype.rebuild = function()
{
    //console.log('rebuild');
	// TODO this.sel clear
	// urn=title, selected (by json data)
    //console.log(this.data.valueoptions);
    //console.log(this.data.value);
    if (this.sel.needclean == true) this.sel.innerHTML = '';
    var option = document.createElement("option");
    option.setAttribute('value', 'NULL');
    if (this.data.value)
      option.appendChild(document.createTextNode('Не выбран'));
    else
      option.appendChild(document.createTextNode('Выберите'));
    this.sel.appendChild(option);
    var selectedSome = 0;
    var index = 0;
    var i = 1;
    for (var ov in this.data.valueoptions) {
        if (this.data.valueoptions.hasOwnProperty(ov)) {
            var ovtitle = this.data.valueoptions[ov].title;
			var ovvalue = this.data.valueoptions[ov].value;
            var option = document.createElement("option");
            //console.log(ovvalue);
            option.setAttribute('value', ovvalue);
            if (option.getAttribute('value') == '[object Object]') alert(ovtitle + ' value ' + '[object Object] from server');
            option.appendChild(document.createTextNode(ovtitle));
			// TODO value select
            if (this.data.value != null && this.data.value != undefined)
            {

                var keyOld = Object.keys(this.data.value)[0];// OLD
                //console.log(keyOld);
                var key = this.data.value.urn; // NEW
                if (keyOld == 0) key = this.data.value; // select on simple local options
                //var cellData = this.data.value[key];
                //console.log(key, ovvalue, this.data.value);
                if (key == ovvalue) {
                    index = i;
                    selectedSome = true;
                }
            }
            this.sel.appendChild(option);
            i++;
        }
    }
    if (selectedSome) this.sel.selectedIndex = index;
}
InputBuilderSelect.prototype.getDom = function()
{
	return this.sel;
}


/**
icon, value(linked)
empty / withDocument
change (select again)
*/
function InputBuilderRichSelect(o, richtype) {
	this.data = o;
    //this.hasdata = false;
    // create div + icon + value
    this.container = document.createElement("div");
    this.container.classList.add("field_document");
    this.container.classList.add("richfield");
    this.container.classList.add("richfield_"+richtype);
    this.container.classList.add("BLK");
    var icon = document.createElement("div");
    icon.classList.add("icon");
    icon.classList.add("FL");
    icon.setAttribute('data-richtype',richtype);
    icon.setAttribute('data-openwindow','modal_winname');
    if (o.editmode != 'allow') icon.setAttribute('data-openwindowdisabled','disabled'); //
    icon.setAttribute('data-windowcontentrenderer','UIGeneralSelectWindow');
    icon.setAttribute('data-call','UIGeneralSelectWindowReturn');
    icon.bridge = this;
    var valuediv = document.createElement("div");
    valuediv.classList.add("value");
    valuediv.classList.add("FL");
    this.valuediv = valuediv;

    var hiddenvalue = document.createElement("input");
    hiddenvalue.setAttribute("name", Math.floor((Math.random() * 1000000) + 1)+"-"+this.data.selector);
    hiddenvalue.setAttribute("type", "hidden");
    if (o.editmode == 'allow') hiddenvalue.setAttribute("data-selector", this.data.selector);
    // TODO multiple
    if (o.ismultiple) hiddenvalue.setAttribute('data-multiple', 'yes');
    // SET VALUE
    //console.log('this.data', this.data);
    //console.log('this.data.unit', this.data.unit);

    this.hiddenvalue = hiddenvalue;

    this.setValue(this.data.value);

    this.container.appendChild(icon);
    this.container.appendChild(valuediv);
    this.container.appendChild(hiddenvalue);

    //this.sel.setAttribute('data-selector', this.data.selector);
    //if (this.data.valueoptions != undefined && this.data.valueoptions.length > 0) this.rebuild();
    // TODO Icon click open window
    /*
    Event.add(icon, 'click', function(e){
        console.log('InputBuilderRichSelect', this.hasdata);
    }.bind(this))
    */
}
InputBuilderRichSelect.prototype.setValue = function(val)
{
    //console.log('SET VAL', val);
    //console.log(this);
    //this.sel.value = this.data.value;
    if (val != undefined && val[""] == undefined)
    {
        this.data.value = val;

        // var key = Object.keys(val)[0]; // old
        var key = val.urn; // NEW
        //console.log('key', key);
        //console.log('value', this.data.value[key], val[key]);
        //this.hasdata = true;
        var title = null;
        var subtitle = null;
        //var xx = this.data.value[key].split(';'); // OLD
        //console.log(this.data.value)
        //console.log(this.data.value.title)
        var xx = [];
        if (this.data.value.title == null)
          xx = ['No title'];
        else
          xx = this.data.value.title.split(';'); // NEW


        if (xx.length > 1)
        {
          title = xx[0];
          subtitle = xx[1].trim();
        }
        else {
          // title = this.data.value[key]; // OLD
          title = this.data.value.title; // NEW
        }
        //console.log(this.data.value, key, xx, title)
        this.valuediv.innerHTML = '';
        this.valuediv.appendChild(document.createTextNode(title));
        //console.log(this.valuediv);
        //console.log(this.hiddenvalue);
        this.hiddenvalue.value = key;
        //console.log(this.hiddenvalue);
    }
}
InputBuilderRichSelect.prototype.rebuild = function()
{
	//this.sel.value = this.data.value;
}
InputBuilderRichSelect.prototype.getDom = function()
{
	return this.container;
}



// UNUSED
function onChangeInput(input)
{
    // ON CHANGE
    Event.add(input, "blur", function(e) {
        //console.log('blur',this.controller.name, this.inputwidget);
        var fieldname = this.getAttribute('name');
        var oldValue = this.getAttribute('data-oldValue');
        var newValue = this.value;
        //console.log(this.inputwidget.formwidget);
        if (newValue != oldValue) {
            this.setAttribute('data-isValueChanged', 'yes'); //this.inputwidget
            this.newValue = newValue; //inputwidget.
            // on container
            // if (!this.inputwidget.formwc.changedData) this.inputwidget.formc.changedData = {};
            //this.inputwidget.formc
            // input.form.data[fieldname] = newValue; // TODO
            // console.log(input.form.data);
        } else {
            this.setAttribute('data-isValueChanged', 'no'); //th iw
        }
    });
}


// INIT
function onloadmanagedformtakecontrol()
{
    var managedFormElement = id('managedform');
    if (!managedFormElement) return;

    if (!id('subjectURN')) throw 'NO hidden input subjectURN';
    var loadurn = id('subjectURN').value;


    ajax(managedFormElement.getAttribute("data-load"), function(data) {
        // TODO remove debug
        console.log(data);
        if (data == undefined) throw 'Ajax load error';
        ajax(managedFormElement.getAttribute("data-structure"), function(configXML) {
            new createFormContainerDelegateToRecursor(configXML, managedFormElement, data, managedFormElement.getAttribute("data-controller"), (managedFormElement.getAttribute("data-saveenabled") == 'yes' ? true : false ) )
            TMCE();
        }, {'responseType': 'XML'}, 'GET', {});
     }, {'responseType': 'JSON'}, 'POST', {"urn": loadurn});


     Event.add(managedFormElement, "click", function(e)
     {
         var dpath = new DomPath2(e.target);
         //console.log('CLICK')
         if (dpath.testNodesOnPath({'tag':'img'}, {'tag':'nav'}))
         {
             var LFRContainer = id(dpath.getNodeBy({'tag':'nav'}).getAttribute("data-controlCLFRContainer"));
             var deleteItemMode = dpath.getNodeBy({'tag':'nav'}).getAttribute("data-action") == 'deleteitem' ? true : false;
             if (deleteItemMode)
                removeElement(LFRContainer)
         }
         else if (dpath.testNodesOnPath({'tag':'img'},{'tag':'footer'}))
         {
             var stackCntainer = id(dpath.getNodeBy({'tag':'footer'}).getAttribute("data-addtostack"));
             var ismultiple = dpath.getNodeBy({'tag':'footer'}).getAttribute("data-forwardismultiple");
             var gateWithData = dpath.getNodeBy({'tag':'footer'}).getAttribute("data-gatewithdata");
             var defOpt = dpath.getNodeBy({'tag':'footer'}).defopt;
             //console.log(stackCntainer);
             var oType = stackCntainer.getAttribute("data-containfieldtype");
             var oSelector = stackCntainer.getAttribute("data-containfieldselector");
             var iopts = {"value": {'':''}, "selector": oSelector, "editmode": "allow", "ismultiple": ismultiple };
             if (oType == 'select')
             {
                 //pureInputsEl
                 //console.log('GWD', gateWithData);
                 iopts.deferredoptions = defOpt;
                 //"valueoptions": o.valueoptions, "deferredoptions": o.deferredoptions
             }
             var pureInputsEl = caseBuildInput(oType, iopts);
             var nextSerial = stackCntainer.querySelectorAll('div.inputWithControls').length + 1;
             var LFRContainer = buildLFRContainer(oType, pureInputsEl, true, nextSerial, "allow");
             //console.log('added');
             stackCntainer.appendChild(LFRContainer);
         }
         else if (dpath.testNodesOnPath({'tag':'a'},{'tag':'footer'}))
         {
             e.preventDefault();
             //console.log('FOOTER CLICK')
         }
         else if (dpath.testNodesOnPath({'hasdataattrib': 'data-openwindow'}, {'id':'managedform'} ))
         {
             e.preventDefault();
             var x = dpath.getNodeBy({'hasdataattrib': 'data-openwindow'});
             //console.log('hasdataattrib CLICK', x)
             openModalWindow(x)
         }
         else if (dpath.testNodesOnPath({'id':'nextInternal'},{'tag':'div'}))
         {
             e.preventDefault();
             //console.log('NEXT CLICK')

             actionCompleteStage();

         }
         else {
             // click in form not recognized
             // console.log(dpath);
         }

     })

}
GC.ONLOAD.push(onloadmanagedformtakecontrol);

/*
if (ismultiple) {
    var structcontainerm = document.createElement("div");
    structcontainerm.setAttribute('data-multiplestruct', nsname);
    containerDom.appendChild(structcontainerm);
    structcontainerWhich = structcontainerm;
} else {
    structcontainerWhich = containerDom;
}
*/

/*
var useFieldSetForStruct = 0;
if (useFieldSetForStruct == 1)
{
    var fieldset = document.createElement("fieldset");
    var legend = document.createElement("legend");
    legend.appendChild(document.createTextNode(nstitle));
    fieldset.appendChild(legend);
    structcontainerWhich.appendChild(fieldset);
    structcontainerWhich = fieldset;
    //containerDom = fieldset;
    if (iscontext) fieldset.classList.add('contextsection');
}
*/


function actionCompleteStage() {
    if (!id('mpe')) throw "No dom id mpe";
    //var MPE_ID = id('mpe').value.split(':')[4];
    var MPE_ID = id('mpeid').value;
    if (!id('processproto')) throw "No dom id mpe";
    var processproto = id('processproto').value;

    var alertresultReal = function (d) {
        console.log(d);
        fadeScreen.call();
        notification('GOOD', 'Переход во входящие');
        window.location.href = '/inbox';
    };
    var alerterrorReal = function (e,d) {
        //console.log(e);
        //console.log(d);
        notification('BAD', 'Недопустимое действие в процессе');
    };

    var url = 'http://'+document.location.hostname+':8020/completestage/';
    //console.log(url);
    //console.log(id('mpe'));
    //console.log(id('mpeid').value);
    ajax(url, alertresultReal, {
        'onError': function(e, d) {
            //console.log(e, d);
            unFadeScreen.call();
            alerterrorReal.call(this, e, d)
        },
        'onStart': fadeScreen,
        'onDone': function() { unFadeScreen(); }
    }, 'GET', {'upn': 'UPN:'+processproto+':'+MPE_ID});
}

