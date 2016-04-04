(function(exports){


    var someAsyncThing = function() {
        return new Promise(function(resolve, reject) {
            var x = 3;
            resolve(x + 2);
        });
    };

    someAsyncThing().then(() => {
        console.log('PROMISE everything is great');
    }).catch(function(error) {
        console.log('oh no', error);
    });


    // your code goes here
   exports.test = function(){
        return 'hello world'
    };

})(typeof exports === 'undefined' ? GC.fn : exports);

/**
System.import("/js/uni2.js").then(function(m) {
    console.log(m);
    console.log(m.test());
});
// OR DIRECT
console.log(GC.fn.test())
*/
