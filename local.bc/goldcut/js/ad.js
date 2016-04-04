//console.log('AD pre create frame');

var maraiAll = document.querySelectorAll(".marai"); // placeholder divs all

for (var ai=0; ai < maraiAll.length; ai++)
{
	var iframeWidth, iframeHeight; 
	var marai = maraiAll[ai];
	var format = marai.getAttribute('data-format');
	if (format)
	{
		iframeWidth = format.split('x')[0];
		iframeHeight = format.split('x')[1];
	}
	else
	{
		iframeWidth = 240;
		iframeHeight = 350;	
	}
	var iframe = document.createElement('iframe');
	var random = '?rnd=' + Math.round( Math.random() * 10000 );
	var tag = '&tag=' + marai.getAttribute('data-tag');
	iframe.src = "http://marai.net/serve/" + random + tag + '&format=' + format;
	//iframe.setAttribute("src", "/mf.html");
	iframe.setAttribute("frameborder", 0);
	iframe.className = 'maraiframe';
	//iframe.style.width = iframeWidth+"px"; 
	//iframe.style.height = iframeHeight+"px";
	iframe.setAttribute('width', iframeWidth);
	iframe.setAttribute('height', iframeHeight);
	iframe.setAttribute('vspace', 0);
	iframe.setAttribute('hspace', 0);
	iframe.setAttribute('scrolling', 'no');
	iframe.setAttribute('marginwidth', 0);
	iframe.setAttribute('marginheight', 0);
	
	marai.style.width = iframeWidth+"px";
	marai.style.height = iframeHeight+"px";
	marai.style.marginLeft = "auto";
	marai.style.marginRight = "auto";
	
	marai.appendChild(iframe);
}