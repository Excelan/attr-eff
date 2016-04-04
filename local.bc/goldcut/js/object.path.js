// window.onbeforeunload

/*
obj = {a:{b:{c:1}}}
objectAccessValueByStringKeyPath(obj,'a.z.d')
c1 = objectSetValueByStringKeyPath(obj,'a.b.c', 789)
*/
function objectAccessValueByStringKeyPath(obj, is, value)
{
    if (typeof is == 'string')
    {
        return objectAccessValueByStringKeyPath(obj, is.split('.'), value);
    }
    else if (is.length == 1 && value !== undefined)
    {   if(isNaN(is[0])){
        return obj[is[0]] = value;
        }else{
        return obj[is[0]-1] = value;
        }
    }
    else if (is.length==0)
    {
        return obj;
    }
    else
    {
        if (obj[is[0]] == undefined) obj[is[0]] = {};
        return objectAccessValueByStringKeyPath(obj[is[0]], is.slice(1), value);
    }
}
okv = objectAccessValueByStringKeyPath;

function objectSetValueByStringKeyPath(obj, is, value)
{
    var path='';
    if (typeof is == 'string'){
        path=is.split(".");
    }
    if (is.length==0){
        return obj;
    }
    if (Array.isArray(is)){path=is}

    var innerKey = path.pop();
    if(!isNaN(innerKey)){
        innerKey = innerKey-1;
    }
    var innerField = obj;
    for (var i in path){
        var key = path[i];
        if(key=="*"){

        }
        if(isNaN(key)){
            innerField = innerField[key];
        }else{
            innerField = innerField[key-1];
        }
    }
    innerField[innerKey] = value;

    return obj
}

/**
 function objectPathSet(o, path, value)
 {
     o[path] = value;
 }
 *
 * function setToValue(obj, value, path) {
    path = path.split('.');
    for (i = 0; i < path.length - 1; i++)
        obj = obj[path[i]];

    obj[path[i]] = value;
}
 *
 function updateObject(object, newValue, path){
  var stack = path.split('>');
  while(stack.length>1){
    object = object[stack.shift()];
  }
  object[stack.shift()] = newValue;

}

 function getKey(key){
		var intKey = parseInt(key);
		if (intKey.toString() === key) {
			return intKey;
		}
		return key;
	}

 function toString(type){
		return Object.prototype.toString.call(type);
	}

 function isNumber(value){
		return typeof value === 'number' || toString(value) === "[object Number]";
	}

 function isString(obj){
		return typeof obj === 'string' || toString(obj) === "[object String]";
	}

 function isObject(obj){
		return typeof obj === 'object' && toString(obj) === "[object Object]";
	}

 function isArray(obj){
		return typeof obj === 'object' && typeof obj.length === 'number' && toString(obj) === '[object Array]';
	}

 function isBoolean(obj){
		return typeof obj === 'boolean' || toString(obj) === '[object Boolean]';
	}

 function assign(obj, prop, value)
 {
     if (typeof prop === "string") prop = prop.split(".").map(getKey);
     if (prop.length > 1)
     {
         var e = prop.shift();
         console.log(e, (typeof e));
         //var cur = Object.prototype.toString.call(obj[e]) === "[object Object]" ? obj[e] : {}
         var cur = isObject(obj[e]) ? obj[e] : (isNumber(e) ? [] : {})
         assign(obj[e], prop, value);
     }
     else
         obj[prop[0]] = value;
 }

 var obj = {}, propName = "top.items.1.el.endf";

 assign(obj, propName, "Value");

 */

var hashSortByValue = function(s){
    var t={};
    Object.keys(s).sort().forEach(function(k){
        t[k]=s[k]
    });
    return t
}
//sorted = hashSortByValue({b:2,a:1,c:3});
//console.log(sorted);

var filterByEqual = function(field, value)
{
    return function(element)
    {
        return element[field] === value;
    }
};

/*
replace obj1 props with obj1 props
obj1 will be overwritten
 */
function merge2(obj1, obj2)
{
    for (var attrname in obj2) { obj1[attrname] = obj2[attrname]; }
}
/**
 * Overwrites obj1's values with obj2's and adds obj2's if non existent in obj1
 * @param obj1
 * @param obj2
 * @returns obj3 a new object based on obj1 and obj2
 */
function merge_options(obj1,obj2){
    var obj3 = {};
    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
    return obj3;
}

// MIT license
// https://github.com/rsms/js-object-merge
Object.merge = function(o, a, b, objOrShallow) {
    var r, k, v, ov, bv, inR,
        isArray = Array.isArray(a),
        hasConflicts, conflicts = {},
        newInA = {}, newInB = {},
        updatedInA = {}, updatedInB = {},
        keyUnion = {},
        deep = true;

    if (typeof objOrShallow !== 'object') {
        r = isArray ? [] : {};
        deep = !objOrShallow;
    } else {
        r = objOrShallow;
    }

    for (k in b) {
        if (isArray && isNaN((k = parseInt(k)))) continue;
        v = b[k];
        r[k] = v;
        if (!(k in o)) {
            newInB[k] = v;
        } else if (v !== o[k]) {
            updatedInB[k] = v;
        }
    }

    for (k in a) {
        if (isArray && isNaN((k = parseInt(k)))) continue;
        v = a[k];
        ov = o[k];
        inR = (k in r);
        if (!inR) {
            r[k] = v;
        } else if (r[k] !== v) {
            bv = b[k];
            if (deep && typeof v === 'object' && typeof bv === 'object') {
                bv = Object.merge((k in o && typeof ov === 'object') ? ov : {}, v, bv);
                r[k] = bv.merged;
                if (bv.conflicts) {
                    conflicts[k] = {conflicts:bv.conflicts};
                    hasConflicts = true;
                }
            } else {
                // if
                if (bv === ov) {
                    // Pick A as B has not changed from O
                    r[k] = v;
                } else if (v !== ov) {
                    // A, O and B are different
                    if (k in o)
                        conflicts[k] = {a:v, o:ov, b:bv};
                    else
                        conflicts[k] = {a:v, b:bv};
                    hasConflicts = true;
                } // else Pick B (already done) as A has not changed from O
            }
        }

        if (k in o) {
            if (v !== ov)
                updatedInA[k] = v;
        } else {
            newInA[k] = v;
        }
    }

    r = {
        merged:r,
        added: {
            a: newInA,
            b: newInB
        },
        updated: {
            a: updatedInA,
            b: updatedInB
        }
    };
    if (hasConflicts)
        r.conflicts = conflicts;
    return r;
}
