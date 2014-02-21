/**
 * Object.create() may not be available for older versions of Javascript.
 * http://javascript.crockford.com/prototypal.html
 */
if (typeof Object.create !== 'function') {
    Object.create = function(o) {
        function F() {}
        F.prototype = o;
        return new F();
    };
}
