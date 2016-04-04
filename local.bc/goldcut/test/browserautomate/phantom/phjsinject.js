function mouseclick(element)
{
	var event = document.createEvent('MouseEvents');
	event.initMouseEvent( 'click', true, true, window, 1, 0, 0 );
	element.dispatchEvent( event );
}
