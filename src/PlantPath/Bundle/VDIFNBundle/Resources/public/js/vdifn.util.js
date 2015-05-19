vdifn.util = {};

/**
 * Calculate the current size of the window.
 *
 * @return {Array} An array where the first element is the width of the window
 *                 and the second element is the height of the window.
 */
vdifn.util.calculateWindowSize = function() {
    var windowWidth = undefined;
    var windowHeight = undefined;

    if ('offsetWidth' in document.body) {
        windowWidth = document.body.offsetWidth;
        windowHeight = document.body.offsetHeight;
    }

    if (document.compatMode === 'CSS1Compat' && 'documentElement' in document && 'offsetWidth' in document.documentElement) {
        windowWidth = document.documentElement.offsetWidth;
        windowHeight = document.documentElement.offsetHeight;
    }

    if (window.innerWidth && window.innerHeight) {
        windowWidth = window.innerWidth;
        windowHeight = window.innerHeight;
    }

    return [windowWidth, windowHeight];
};

/**
 * Parse a query string into a JSON object.
 *
 * @param queryString
 */
vdifn.util.parseQueryString = function(queryString) {
    var params = {}, temp;

    if (queryString.indexOf('?') === 0) {
        queryString = queryString.substring(1);
    }

    // Split into key/value pairs
    var queries = queryString.split("&");

    // Convert the array of strings into an object
    for (var i = 0, l = queries.length; i < l; i++) {
        temp = queries[i].split('=');
        params[temp[0]] = temp[1];
    }

    return params;
};
