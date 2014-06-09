/**
 * The VDIFN interface.
 *
 * @param  {google.maps.Map} map
 * @param  {vdifn.db} db
 * @param  {Pikaday} picker
 */
vdifn.Interface = function(map, db, picker) {
    this.map = map;
    this.db = db;
    this.picker = picker;
    this.modelDataPoints = [];
    this.errorOverlay = document.getElementById('error-overlay');
    this.loadingOverlay = document.getElementById('loading-overlay');
    this.setupDsvLegend();
};

/**
 * Using known severity colors, setup the DSV legend on the sidebar.
 *
 * @return this
 */
vdifn.Interface.prototype.setupDsvLegend = function() {
    var dsvSquares = document.getElementById('dsv-legend').querySelectorAll('.dsv');

    for (var i = 0; i < dsvSquares.length; ++i) {
        var element = dsvSquares.item(i);
        var dsv = parseInt(element.getAttribute('data-dsv'));
        var color = vdifn.map.ModelDataPoint.getSeverityColor(dsv);
        element.getElementsByTagName('div').item(0).style.backgroundColor = color;
    }

    return this;
};

/**
 * Wrap the Google Maps controls in manageable divs.
 *
 * @return this
 */
vdifn.Interface.prototype.wrapControls = function() {
    var parent = this.map.getDiv().childNodes[0];
    var mapGoogleLogo = parent.childNodes[1];
    var mapPanZoomControls = parent.childNodes[7];

    var wrapInDiv = function(element, divId) {
        var parent = element.parentNode;
        var wrapper = document.createElement('div');
        wrapper.id = divId;
        parent.removeChild(element);
        wrapper.appendChild(element);
        parent.appendChild(wrapper);
    };

    wrapInDiv(mapPanZoomControls, 'map-pan-zoom-controls');
    wrapInDiv(mapGoogleLogo, 'map-google-logo');

    return this;
};

/**
 * Callback for a window resize event.
 *
 * @param  {Event} event
 *
 * @return this
 */
vdifn.Interface.prototype.resize = function(event) {
    var size = vdifn.util.calculateWindowSize();
    var width = size[0];
    var height = size[1];
    var div = this.map.getDiv();

    div.style.width = width + 'px';
    div.style.height = height + 'px';

    return this;
};

/**
 * Callback for a Google Maps tilesloaded event.
 *
 * @return this
 */
vdifn.Interface.prototype.tilesloaded = function() {
    this.wrapControls();
    this.closeLoadingOverlay();
    this.loadingOverlay.style.backgroundColor = "transparent";
    this.loadingOverlay.classList.add('radial');

    return this;
};

/**
 * Open the loading overlay.
 *
 * @return this
 */
vdifn.Interface.prototype.openLoadingOverlay = function() {
    this.loadingOverlay.style.opacity = 1;
    this.loadingOverlay.style.visibility = "visible";

    return this;
};

/**
 * Close the loading overlay.
 *
 * @return this
 */
vdifn.Interface.prototype.closeLoadingOverlay = function() {
    this.loadingOverlay.style.opacity = 0;
    this.loadingOverlay.style.visibility = "hidden";

    return this;
};

/**
 * Open the error overlay with a message.
 *
 * @param  {String} message
 *
 * @return this
 */
vdifn.Interface.prototype.openErrorOverlay = function(message) {
    this.errorOverlay.style.display = "block";
    this.errorOverlay.style.opacity = 1;
    this.errorOverlay.style.visibility = "visible";
    document.getElementById('error-text').innerHTML = "<strong>Error</strong>: " + message;

    return this;
};

/**
 * Close the error overlay.
 *
 * @return this
 */
vdifn.Interface.prototype.closeErrorOverlay = function() {
    this.errorOverlay.style.opacity = 0;
    this.errorOverlay.style.visibility = "hidden";

    return this;
};

/**
 * Draw a day of data onto the map.
 *
 * @param  {string} ymd
 * @param  {Function} callback
 *
 * @return this
 */
vdifn.Interface.prototype.drawDay = function(ymd, callback) {
    var self = this;

    this.clearModelDataPoints();

    this.db.find({ time: ymd }, function(results) {
        for (var point in results) {
            self.drawModelDataPoint(new vdifn.map.ModelDataPoint(
                new google.maps.LatLng(results[point].latitude, results[point].longitude),
                results[point].dsv
            ));
        }

        if (typeof callback === 'function') {
            callback.call(this, results && results.length > 0);
        }
    });

    return this;
};

/**
 * Draw a data point onto the map.
 *
 * @param  {vdifn.map.ModelDataPoint} modelDataPoint
 *
 * @return this
 */
vdifn.Interface.prototype.drawModelDataPoint = function(modelDataPoint) {
    this.modelDataPoints.push(modelDataPoint);
    modelDataPoint.plot(this.map);

    return this;
};

/**
 * Clears all the currently plotted data points.
 *
 * @return this
 */
vdifn.Interface.prototype.clearModelDataPoints = function() {
    for (var i in this.modelDataPoints) {
        this.modelDataPoints[i].plot(null);
    }

    this.modelDataPoints.length = 0;

    return this;
};
