function doGetCaretPosition (ctrl) {
	var CaretPos = 0;
	// IE Support
	if (document.selection) {
		console.log('IE');
		ctrl.focus ();
		var Sel = document.selection.createRange ();

		Sel.moveStart ('character', -ctrl.value.length);

		CaretPos = Sel.text.length;
	}
	// Firefox support
	else if (ctrl.selectionStart || ctrl.selectionStart == '0')
	{
		console.log('FF');
		CaretPos = ctrl.selectionStart;
	}	
		console.log(CaretPos);
	return (CaretPos);

}



function get_selection(the_id)
{
    var e = document.getElementById(the_id);

    //Mozilla and DOM 3.0
    if('selectionStart' in e)
    {
        var l = e.selectionEnd - e.selectionStart;
        return { start: e.selectionStart, end: e.selectionEnd, length: l, text: e.value.substr(e.selectionStart, l) };
    }
    //IE
    else if(document.selection)
    {
        e.focus();
        var r = document.selection.createRange();
        var tr = e.createTextRange();
        var tr2 = tr.duplicate();
        tr2.moveToBookmark(r.getBookmark());
        tr.setEndPoint('EndToStart',tr2);
        if (r == null || tr == null) return { start: e.value.length, end: e.value.length, length: 0, text: '' };
        var text_part = r.text.replace(/[\r\n]/g,'.'); //for some reason IE doesn't always count the \n and \r in the length
        var text_whole = e.value.replace(/[\r\n]/g,'.');
        var the_start = text_whole.indexOf(text_part,tr.text.length);
        return { start: the_start, end: the_start + text_part.length, length: text_part.length, text: r.text };
    }
    //Browser not supported
    else return { start: e.value.length, end: e.value.length, length: 0, text: '' };
}

function replace_selection(the_id,replace_str)
{
    var e = document.getElementById(the_id);
    selection = get_selection(the_id);
    var start_pos = selection.start;
    var end_pos = start_pos + replace_str.length;
    e.value = e.value.substr(0, start_pos) + replace_str + e.value.substr(selection.end, e.value.length);
    set_selection(the_id,start_pos,end_pos);
    return {start: start_pos, end: end_pos, length: replace_str.length, text: replace_str};
}

function set_selection(the_id,start_pos,end_pos)
{
    var e = document.getElementById(the_id);

    //Mozilla and DOM 3.0
    if('selectionStart' in e)
    {
        e.focus();
        e.selectionStart = start_pos;
        e.selectionEnd = end_pos;
    }
    //IE
    else if(document.selection)
    {
        e.focus();
        var tr = e.createTextRange();

        //Fix IE from counting the newline characters as two seperate characters
        var stop_it = start_pos;
        for (i=0; i < stop_it; i++) if( e.value[i].search(/[\r\n]/) != -1 ) start_pos = start_pos - .5;
        stop_it = end_pos;
        for (i=0; i < stop_it; i++) if( e.value[i].search(/[\r\n]/) != -1 ) end_pos = end_pos - .5;

        tr.moveEnd('textedit',-1);
        tr.moveStart('character',start_pos);
        tr.moveEnd('character',end_pos - start_pos);
        tr.select();
    }
    return get_selection(the_id);
}

function wrap_selection(the_id, left_str, right_str, sel_offset, sel_length)
{
    var the_sel_text = get_selection(the_id).text;
    var selection =  replace_selection(the_id, left_str + the_sel_text + right_str );
    if(sel_offset !== undefined && sel_length !== undefined) selection = set_selection(the_id, selection.start +  sel_offset, selection.start +  sel_offset + sel_length);
    else if(the_sel_text == '') selection = set_selection(the_id, selection.start + left_str.length, selection.start + left_str.length);
    return selection;
}








function setCaretPosition(ctrl, pos)
{

	if(ctrl.setSelectionRange)
	{
		ctrl.focus();
		ctrl.setSelectionRange(pos,pos);
	}
	else if (ctrl.createTextRange) {
		var range = ctrl.createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		range.select();
	}
}



function insertAtCaret(areaId,text) { var txtarea = document.getElementById(areaId); var scrollPos = txtarea.scrollTop; var strPos = 0; var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? "ff" : (document.selection ? "ie" : false ) ); if (br == "ie") { txtarea.focus(); var range = document.selection.createRange(); range.moveStart ('character', -txtarea.value.length); strPos = range.text.length; } else if (br == "ff") strPos = txtarea.selectionStart; var front = (txtarea.value).substring(0,strPos); var back = (txtarea.value).substring(strPos,txtarea.value.length); txtarea.value=front+text+back; strPos = strPos + text.length; if (br == "ie") { txtarea.focus(); var range = document.selection.createRange(); range.moveStart ('character', -txtarea.value.length); range.moveStart ('character', strPos); range.moveEnd ('character', 0); range.select(); } else if (br == "ff") { txtarea.selectionStart = strPos; txtarea.selectionEnd = strPos; txtarea.focus(); } txtarea.scrollTop = scrollPos; }

/*

He had tried using window.getSelection.toString(), but that doesn't work, because window.getSelection() is implemented in terms of DOM Ranges and it doesn't make much sense to have a DOM Range inside a textarea.


IE’s document.selection.createRange().text works for textareas as well as for web page text

IE in textarea 
http://the-stickman.com/web-development/javascript/finding-selection-cursor-position-in-a-textarea-in-internet-explorer/
document.selection.createRange().text = 'Some new text';
I need to know where in a piece of text the selection begins and ends. It’s easy enough in Mozilla: element.selectionStart and element.selectionEnd do the trick.
In IE, you can get just about every other bit of information you could possibly want — the position and size of the selection in pixels, for example — but not the start and end points of the selection. Bonkers.
var element = document.getElementById( 'my_textarea' );
if( document.selection ){
	// The current selection
	var range = document.selection.createRange();
	// We'll use this as a 'dummy'
	var stored_range = range.duplicate();
	// Select all text
	stored_range.moveToElementText( element );
	// Now move 'dummy' end point to end point of original range
	stored_range.setEndPoint( 'EndToEnd', range );
	// Now we can calculate start and end points
	element.selectionStart = stored_range.text.length - range.text.length;
	element.selectionEnd = element.selectionStart + range.text.length;
}
Because this sets the selectionStart and selectionEnd properties for the element, you can use the same methods for getting/setting selection contents as you would for Mozilla.


unction getRangeText() {

    var userSelection;
    if (window.getSelection) {
        userSelection = window.getSelection();
    } else if (document.selection) {
        userSelection = document.selection.createRange();
    }
    var selectedText = userSelection;
    if (userSelection.text) {
        selectedText = userSelection.text;
    }
    return selectedText;
}

I tested this in FF5, Opera 11, Safari on the Mac, as well as IE6 and IE7



Has anyone come up with a way to get the selection offset/length (anchorOffset/focusOffset) in Safari? These work fine in Gecko based browsers. 
Re-reading your question: actually, these two work fine for me in 2.0.2:
<a href="javascript:d=window.getSelection();alert(d.focusOffset);">select and click me</a>



I already have this working in Firefox, Safari and Chrome.
var el = document.getElementById("myTextField");
var pos = 6;
if (document.selection) {
    el.focus();
    var selection = document.selection.createRange();
    selection.moveStart("character", -el.value.length);
    selection.moveStart("character", pos);
    selection.moveEnd("character", 0);
    selection.select();
}
The problem is that when I try to do this the cursor always goes to the end of the value regardless of what position I provide.

The following code is working for me in IE 9
<script type="text/javascript">
    var input = document.getElementById("myInput");
    input.selectionStart = 2;
    input.selectionEnd = 5;
</script>
Here is the code that I'm using for IE 6
      input.select();
        var sel = document.selection.createRange();
        sel.collapse();
        sel.moveStart('character', this.SelectionStart);
        sel.collapse();
        sel.moveEnd('character', this.SelectionEnd - this.SelectionStart);
        sel.select();




http://code.google.com/p/rangy/
A cross-browser JavaScript range and selection library. It provides a simple standards-based API for performing common DOM Range and Selection tasks in all major browsers, abstracting away the wildly different implementations of this functionality between Internet Explorer and DOM-compliant browsers. 


Start with PPK's introduction to ranges. Mozilla developer connection has info on W3C selections. Microsoft have their system documented on MSDN. Some more tricks can be found in the answers here.

http://www.quirksmode.org/dom/range_intro.html
getting the user selection and converting this selection to a W3C Range or Microsoft Text Range object, although we'll treat the programmatic creation of Range objects
Through the Range object you can find the start and end point of this Range, and if you so desire you can copy or delete it, or substitute it by another text, or even a bit of HTML.
browsers adjust the HTML so that the snippet (partly html) becomes valid
Mozilla Selection is Netskape 4 legacy
The Microsoft Text Range object is profoundly different from the other two, because it's string-based. In fact, it is extremely hard to jump from the string contained by Text Range to a DOM node.

var userSelection;
if (window.getSelection) {
	userSelection = window.getSelection();
}
else if (document.selection) { // should come last; Opera! Opera supports both objects
	userSelection = document.selection.createRange();
}
In Mozilla, Safari and Opera userSelection now is a Selection object, while in Internet Explorer it's a Text Range object. This difference will remain valid for the rest of your script: Internet Explorer's Text Ranges are fundamentally different from Mozilla's Selection and W3C's Range objects, and all other code that you write will require a branch for IE and a branch for all other browsers
MZ
var selectedText = userSelection;
MS
var selectedText = userSelection;
if (userSelection.text)
	selectedText = userSelection.text;


	var rangeObject = getRangeObject(userSelection);

	function getRangeObject(selectionObject) {
		if (selectionObject.getRangeAt)
			return selectionObject.getRangeAt(0);
		else { // Safari! Unfortunately Safari (1.3) does not support getRangeAt()
			var range = document.createRange();
			range.setStart(selectionObject.anchorNode,selectionObject.anchorOffset);
			range.setEnd(selectionObject.focusNode,selectionObject.focusOffset);
			// anchorNode/anchorOffset define the start of the Selection, while focusNode/focusOffset define its end
			// (Note that this newly created Range is not visible to the user; it's wholly internal to the browser.)
			return range;
		}
	}

https://developer.mozilla.org/en/DOM/window.getSelection
http://msdn.microsoft.com/en-us/library/ms535869%28VS.85%29.aspx




<div id="mainDiv" onmouseup="alert(getSelectionContainerElementId())">Select some content here. Here's <b id="bold">some bold text</b></div>
function getSelectionContainerElementId() {
    var sel = window.getSelection();
    if (sel.rangeCount > 0) {
        var range = sel.getRangeAt(0);
        var container = range.commonAncestorContainer;

        // container could be a text node, so use its parent if so
        if (container.nodeType == 3) {
            container = container.parentNode;
        }
        return container.id;
    }
    return null;
}


getSelectionHTML = function () {
  var userSelection;
  if (window.getSelection) {
    // W3C Ranges
    userSelection = window.getSelection ();
    // Get the range:
    if (userSelection.getRangeAt)
      var range = userSelection.getRangeAt (0);
    else {
      var range = document.createRange ();
      range.setStart (userSelection.anchorNode, userSelection.anchorOffset);
      range.setEnd (userSelection.focusNode, userSelection.focusOffset);
    }
    // And the HTML:
    var clonedSelection = range.cloneContents ();
    var div = document.createElement ('div');
    div.appendChild (clonedSelection);
    return div.innerHTML;
  } else if (document.selection) {
    // Explorer selection, return the HTML
    userSelection = document.selection.createRange ();
    return userSelection.htmlText;
  } else {
    return '';
  }
};


function highlightSelection(colour) {
    if (document.selection) {
        // IE case
        if (document.selection.type == "Text") {
            document.execCommand("BackColor", false, colour);
            document.selection.empty();
        }
    } else if (window.getSelection) {
        var sel = window.getSelection();
        if (!sel.isCollapsed) {
            // Store selection range
            range = sel.getRangeAt(0);

            // Temporarily put document in designMode to allow
            // document.execCommand() to work
            document.designMode = "on";

            // Restore selection range
            sel.removeAllRanges();
            sel.addRange(range);

            // Highlight the selection
            document.execCommand("HiliteColor", false, colour);

            // Disable designMode
            document.designMode = "off";
            
            // Deselect
            sel.removeAllRanges();
        }
    }
}


vote 5 down vote accepted
	
SELECTION COORDS
In recent non-IE browsers (Firefox 4+, WebKit browsers released since early 2009, Opera 11, maybe earlier), you can use the (currently non-standard) getClientRects() method of Range. In IE, you can use the boundingLeft and boundingTop properties of the TextRange that can be extracted from the selection. Here
function getSelectionCoords() {
    var sel = document.selection, range;
    var x = 0, y = 0;
    if (sel) {
        if (sel.type != "Control") {
            range = sel.createRange();
            range.collapse(true);
            x = range.boundingLeft;
            y = range.boundingTop;
        }
    } else if (window.getSelection) {
        sel = window.getSelection();
        if (sel.rangeCount) {
            range = sel.getRangeAt(0).cloneRange();
            if (range.getClientRects) {
                range.collapse(true);
                var rect = range.getClientRects()[0];
                x = rect.left;
                y = rect.top;
            }
        }
    }
    return { x: x, y: y };
}

document.onmouseup = function() {
    var coords = getSelectionCoords();
    document.getElementById("coords").innerHTML = coords.x + ", " + coords.y;
};



IE

function applySelectedObjectTags(obj){
    var age = txtAge.value;
    // obtain the selection and pass it into a variable.
    var txtSelection = document.selection;
    // to manipulate the selected text you need to create a TextRange object.
    var tRange = txtSelection.createRange();
    var insertionText = tRange.text + " is " + age;
    // You must check whether there is a selection or the formatting blocks will appear outside of the textarea
    // You need to place the insertion at the point of contact and to then insert a placeholder: ie, ##
    if(txtSelection == null)
    {
    alert("selection is nothing");
    // Code to insert placeholder
    return;
    };
    if(tRange != null)
    {
        tRange.text = insertionText;
        return;
    }


	

In addition to incompatible interfaces you'll be happy to know that there is extra bizarreness going on with textarea nodes. If I remember correctly they behave as any other nodes when you select them in IE, but in other browsers they have an independent selection range which is exposed via the .selectionEnd and .selectionStart properties on the node.



function detectBrowser() {

var browser=navigator.appName
var b_version=navigator.appVersion

if ((browser=="Netscape" || browser=="Microsoft Internet Explorer") && b_version >= 4)
{
  
function boldText(textAreaId) 
{
    var str = document.selection.createRange().text;
    document.my_form.myTextarea.focus();
    var sel = document.selection.createRange();
    sel.text = "<b>" + str + "</b>";
    return;
  }
 }
 else
 {
  function boldText(textAreaId) {
  field = document.getElementById(textAreaId);
  startPos = field.selectionStart;
  endPos = field.selectionEnd;
  before = field.value.substr(0, startPos);
  selected = field.value.substr(field.selectionStart, (field.selectionEnd - field.selectionStart));
  after = field.value.substr(field.selectionEnd, (field.value.length - field.selectionEnd));
  field.value = before + "<b>" + selected + "</b>" + after;
  }
 }
}

*/

/*
function insertAtCursor(myField, myValue) {
//IE support
if (document.selection) {
myField.focus();
sel = document.selection.createRange();
sel.text = myValue;
}
//MOZILLA/NETSCAPE support
else if (myField.selectionStart || myField.selectionStart == ‘0′) {
var startPos = myField.selectionStart;
var endPos = myField.selectionEnd;
myField.value = myField.value.substring(0, startPos)
+ myValue
+ myField.value.substring(endPos, myField.value.length);
} else {
myField.value += myValue;
}
}
// calling the function
insertAtCursor(document.formName.fieldName, ‘this value’);
onChange="DisplayEvent('onChange');"
  onKeyDown="DisplayEvent('onKeyDown');"
  onKeyPress="DisplayEvent('onKeyPress');"
  onKeyUp="DisplayEvent('onKeyUp');">
onSelect
onFocus
onChange
onBlur
Methods
Methods 	Description
blur() 	Removes focus away from the textarea.
focus() 	Sets focus on the textarea.
select() 	Highlights the content of the textarea.
*/

