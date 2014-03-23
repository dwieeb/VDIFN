/**
 * @namespace vdifn
 */
var vdifn = {};

/**
 * Base exception.
 *
 * @param {string} message
 */
vdifn.Exception = function(message) {
    this.name = '';
    this.message = message;
};

vdifn.Exception.prototype = Object.create(Error.prototype);

/**
 * Form a string of relevant information.
 *
 * @return {string}
 */
vdifn.Exception.prototype.getMessage = function() {
    var name = this.name || 'unknown';
    var message = this.message || 'no description';

    return '[' + name + ']: ' + message;
};

/**
 * Thrown when a child class fails to implement an abstract method of a parent
 * class.
 *
 * @param {string} message Information regarding the thrown exception.
 */
vdifn.UnimplementedAbstractException = function(message) {
    vdifn.Exception.call(this, message ? message : 'Must implement inherited abstract method.');
    this.name = 'UnimplementedAbstractException';
};

vdifn.UnimplementedAbstractException.prototype = Object.create(vdifn.Exception.prototype);
