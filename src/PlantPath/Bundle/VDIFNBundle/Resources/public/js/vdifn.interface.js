/**
 * The VDIFN interface.
 */
vdifn.Interface = function(map, db) {
    this.map = map;
    this.db = db;
};

/**
 * Callback for a window resize event.
 *
 * @this   {vdifn.Interface}
 * @param  {Event} event
 */
vdifn.Interface.prototype.resize = function(event) {
    var size = vdifn.util.calculateWindowSize();
    var width = size[0];
    var height = size[1];
    var div = this.map.getDiv();

    div.style.width = width + 'px';
    div.style.height = height + 'px';
};
