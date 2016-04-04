
function loadImage (src) {
	var deferred = when.defer(),
		img = document.createElement('img');
	img.onload = function () { 
		deferred.resolve(img); 
	};
	img.onerror = function () { 
		deferred.reject(new Error('Image not found: ' + src));
	};
	img.src = src;

	// Return only the promise, so that the caller cannot
	// resolve, reject, or otherwise muck with the original deferred.
	return deferred.promise;
}

// example usage:
/**
loadImage('http://google.com/favicon.ico').then(
	function gotIt(img) {
		document.body.appendChild(img);
	},
	function doh(err) {
		document.body.appendChild(document.createTextNode(err));
	}
).then(
	function shout(img) {
		alert('see my new ' + img.src + '?');
	}
);
*/

function doFancyStuffWithImages(imgs)
{
	console.log(imgs);
	for(var i = 0, len = imgs.length; i < len; i++)
		document.body.appendChild(imgs[i]);
}
function handleError(er) {console.log("ERROR",er)}

function loadImages(srcs) {
	var deferreds = [];
	for(var i = 0, len = srcs.length; i < len; i++) {
		deferreds.push(loadImage(srcs[i]));
	}
	return when.all(deferreds);
	//return when.some(deferreds, 1);
}

imageSrcArray = ['http://google.com/favicon.ico', 'https://a248.e.akamai.net/assets.github.com/images/modules/header/logov7@4x-hover.png?1338945075'];

loadImages(imageSrcArray).then(
	function gotEm(imageArray) {
		doFancyStuffWithImages(imageArray);
		return imageArray.length;
	},
	function doh(err) {
		handleError(err);
	}
).then(
    function shout (count) {
        console.log('see my new ' + count + ' images?');
    }
);