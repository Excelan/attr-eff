function gtrack()
{
	countrefs = true;
	gref = 0; if (document.referrer.indexOf('google') > -1) gref = 1;
	yaref = 0; if (document.referrer.indexOf('yandex') > -1 || document.referrer.indexOf('yandex') > -1) yaref = 1;
	var gcr = document.location;
	var gcurni = document.getElementById('urn');
	var gcurn;
	if (gcurni) gcurn = gcurni.value;
	if (!gcurn && ENV == 'DEVELOPMENT') console.log('NO URN FOR ASYNC VIEW COUNT');
	var gci = document.createElement('script'); 
	gci.type = 'text/javascript'; 
	gci.async = true;
	gci.src = '/ajs?gref='+gref+'&yaref='+yaref;
	if (gcurn) gci.src += '&urn='+gcurn;
	if (countrefs) gci.src += '&u='+encodeURIComponent(gcr);
	gci.src += '&rnd='+ Math.round( Math.random() * 10000 );
	var s = document.getElementsByTagName('script')[0]; 
	s.parentNode.insertBefore(gci, s);
}
gtrack();