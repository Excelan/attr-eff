// CONFIG DOC http://webhamster.ru/site/page/index/articles/comp/15
var tmceValidElements;
if (siteSettings.tinymce.ValidElements)
{
	if (window['ENV'] && ENV == 'DEVELOPMENT') console.log('SITE local tinymce settings used');
	tmceValidElements = siteSettings.tinymce.ValidElements;
}
else
{
	if (window['ENV'] && ENV == 'DEVELOPMENT') console.log('SYS global tinymce settings used');
	tmceValidElements = "p[align|class],span[class],br,a[href|target=_blank],strong/b,em/i,ul,ol,li,img[src|title|border|alt],h2/h1,h3,h4,blockquote,cite,table,tr,th,td[colspan,rowspan]";
}

if (window['ENV'] && ENV == 'DEVELOPMENT') console.log('non filtered html:',tmceValidElements);

var tmceBlockformats;
if (siteSettings.tinymce.Blockformats)
	tmceBlockformats = siteSettings.tinymce.Blockformats;
else
	tmceBlockformats = "p,h2,h3,h4,blockquote";

var tmcePlugins;
if (siteSettings.tinymce.Plugins)
	tmcePlugins = siteSettings.tinymce.Plugins;
else
	tmcePlugins = "fullscreen,table,paste,spellchecker,searchreplace";
	//tmcePlugins = "advlink,advimage,fullscreen,inlinepopups,table,paste,iespell,spellchecker,searchreplace";

var tmceButtons;
if (siteSettings.tinymce.Buttons)
	tmceButtons = siteSettings.tinymce.Buttons;
else
	tmceButtons = "bold,italic,link,unlink,formatselect,bullist,numlist,tablecontrols,blockquote,undo,redo,cut,copy,paste,search,code,fullscreen"; // spellchecker

var tmceLang;
if (siteSettings.tinymce.Lang)
	tmceLang = siteSettings.tinymce.Lang;
else
	tmceLang = "ru";

var tmceSelector;
if (siteSettings.tinymce.Selector)
	tmceSelector = siteSettings.tinymce.Selector;
else
	tmceSelector = "richtext";


var tinyOptions = {
	language:tmceLang,
	mode:"textareas",
	//theme:"advanced",
	//skin:"o2k7",

	//valid_elements: tmceValidElements,
	theme_advanced_blockformats: tmceBlockformats,

	handle_event_callback:'myHandleEvent',

	force_root_block:true,
	force_br_newlines:false,
	force_p_newlines:true,
	cleanup:true,
	cleanup_on_startup:true,
	verify_html:true,

	elements:'absurls',
	relative_urls:false,
	/* convert_urls: true, */

	auto_reset_designmode:true,

	gecko_spellcheck:true,
	spellchecker_languages:"+Русский=ru,English=en",
	plugins:tmcePlugins,
	theme_advanced_buttons1:tmceButtons,

	theme_advanced_buttons2:"",
	theme_advanced_buttons3:"",

	theme_advanced_toolbar_location:"external",
	theme_advanced_toolbar_align:"left",
	theme_advanced_toolbar_location:"top",
	theme_advanced_toolbar_align:"center",
	theme_advanced_statusbar_location:"bottom",
	theme_advanced_resize_horizontal:true,
	theme_advanced_resizing:true,
	apply_source_formatting:true,
	textarea_trigger:"specific_textareas",
	editor_selector:tmceSelector,
	editor_deselector:"mceNoEditor",
	height:"200px",
	width: "100%",

	theme_advanced_font_sizes:"10px,12px,13px,14px,16px,18px,20px",
	font_size_style_values:"10px,12px,13px,14px,16px,18px,20px",
	content_css:"/css/editor.css"
};

if (tmceValidElements)
	tinyOptions.valid_elements = tmceValidElements;

console.log(siteSettings.tinymce.mode)
if (siteSettings.tinymce.mode != "async") {
    console.log('TINYMCE INIT',tinyOptions);
    tinymce.init(tinyOptions);
		/*
		tinymce.init({
				selector: '.richtext',
				inline: true,
				menubar: false,
				plugins: [
						'advlist autolink lists link image charmap print preview anchor',
						'searchreplace visualblocks code fullscreen',
						'insertdatetime media table contextmenu paste'
				],
				toolbar: 'insertfile undo redo | bullist numlist outdent indent | link | fullscreen'
		});
		*/
}
else {
    if (window['ENV'] && ENV == 'DEVELOPMENT')
        console.log('ASYNC TINYMCE. DO MANUAL tinyMCE.init(tinyOptions)');
		else
			console.log('ASYNC TINYMCE! NON ENV DEV)');
}

function rmm() {
	setTimeout('tinyMCE.activeEditor.execCommand(\'mceCleanup\')', 15);
}
function myHandleEvent(e) {
	if (e.type == 'paste') rmm();
	return true;
}
