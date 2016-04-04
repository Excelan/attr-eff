
// After that, both arrays will have the same contents. Changing one array will not change the other.
//var destinationArray = sourceArray.concat(); for new
//destinationArray.push.apply(destinationArray, sourceArray); // for exists dest

// -- OBJECT

/*
 function A()
 {
 this.x = 1;
 }
 A.prototype.DoIt = function()
 {
 this.x += 1;
 }
 function B()
 {
 A.call(this);
 this.y = 1;
 }
 //B.prototype = new A;
 //B.prototype.constructor = B;
 inherit(B, A);
 B.prototype.DoIt = function()
 {
 A.prototype.DoIt.call(this);
 this.y += 1;
 }
 b = new B;
 document.write((b instanceof A) + ', ' + (b instanceof B) + '<BR/>');
 b.DoIt();
 b.DoIt();
 document.write(b.x + ', ' + b.y);
 */
function extend() {
    function ext(destination, source) {
        var prop;
        for (prop in source) {
            if (source.hasOwnProperty(prop)) {
                destination[prop] = source[prop];
            }
        }
    }

    ext(arguments["0"], arguments["1"]);
}

function noop() {
}

function debuginput(i, e) {
  console.log(i)
  if (e) console.log(e)
}

function inherit(ctor, superCtor) {
    if (Object.create) {
        ctor.prototype = Object.create(superCtor.prototype, {
            constructor: {value: ctor, enumerable: false}
        });
    } else {
        noop.prototype = superCtor.prototype;
        ctor.prototype = new noop();
        ctor.prototype.constructor = superCtor;
    }
}
//var o1 = {a:1, b:2};
//var o2 = copy(o1);
function copy(o) {
    var copy = Object.create(Object.getPrototypeOf(o));
    var propNames = Object.getOwnPropertyNames(o);
    propNames.forEach(function (name) {
        var desc = Object.getOwnPropertyDescriptor(o, name);
        Object.defineProperty(copy, name, desc);
    });
    return copy;
}
