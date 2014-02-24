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
