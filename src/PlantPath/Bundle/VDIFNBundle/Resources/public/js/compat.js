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

if (typeof document.body === 'undefined') {
    document.body = document.getElementsByTagName('body').item(0);
}

/**
 * Minimal classList shim for IE 9
 * By Devon Govett
 * MIT LICENSE
 */
if (!("classList" in document.documentElement) && Object.defineProperty && typeof HTMLElement !== 'undefined') {
    Object.defineProperty(HTMLElement.prototype, 'classList', {
        get: function() {
            var self = this;
            function update(fn) {
                return function(value) {
                    var classes = self.className.split(/\s+/),
                        index = classes.indexOf(value);

                    fn(classes, index, value);
                    self.className = classes.join(" ");
                }
            }

            var ret = {
                add: update(function(classes, index, value) {
                    ~index || classes.push(value);
                }),

                remove: update(function(classes, index) {
                    ~index && classes.splice(index, 1);
                }),

                toggle: update(function(classes, index, value) {
                    ~index ? classes.splice(index, 1) : classes.push(value);
                }),

                contains: function(value) {
                    return !!~self.className.split(/\s+/).indexOf(value);
                },

                item: function(i) {
                    return self.className.split(/\s+/)[i] || null;
                }
            };

            Object.defineProperty(ret, 'length', {
                get: function() {
                    return self.className.split(/\s+/).length;
                }
            });

            return ret;
        }
    });
}
