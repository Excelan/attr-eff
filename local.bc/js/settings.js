//var ENV = 'DEVELOPMENT';

var MCE_SELECTOR = "richtext";
var RICH_TEXT_EDITOR = true;

var siteSettings = {};
siteSettings.name = 'bc';
siteSettings.tinymce = {};
// siteSettings.tinymce.mode = "async";
siteSettings.tinymce.Lang = "en";
siteSettings.tinymce.Selector = "richtext";
siteSettings.tinymce.ValidElements = "p[class], span[class],br,a[href|target=_blank],strong/b,em/i,u,ul,ol,li,img[src|title|border|alt],h1,h2,h3,h4,h5,blockquote,cite";
siteSettings.tinymce.Blockformats = "p,blockquote";
siteSettings.tinymce.Plugins = "fullscreen,table,paste,spellchecker,searchreplace";
siteSettings.tinymce.Buttons = "bold,italic,link,unlink,bullist,numlist,indent,outdent,blockquote,undo,redo,cut,copy,paste,search,code,fullscreen";
