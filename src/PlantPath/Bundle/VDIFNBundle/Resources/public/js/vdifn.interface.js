/**
 * The VDIFN interface.
 *
 * @param  {google.maps.Map} map
 * @param  {crossfilter} db
 */
vdifn.Interface = function(map, db) {
    this.map = map;
    this.db = db;
    this.modelDataPoints = [];
};

/**
 * Wrap the Google Maps controls in manageable divs.
 *
 * @return this
 */
vdifn.Interface.prototype.wrapControls = function() {
    var parent = this.map.getDiv().childNodes[0];
    var mapPanZoomControls = parent.childNodes[7];
    var mapTypeControls = parent.childNodes[8];

    var wrapInDiv = function(element, divId) {
        var parent = element.parentNode;
        var wrapper = document.createElement('div');
        wrapper.id = divId;
        parent.removeChild(element);
        wrapper.appendChild(element);
        parent.appendChild(wrapper);
    };

    wrapInDiv(mapPanZoomControls, 'map-pan-zoom-controls');
    wrapInDiv(mapTypeControls, 'map-type-controls');

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
 * Draw a day of data onto the map.
 *
 * @param  {string} ymd
 *
 * @return this
 */
vdifn.Interface.prototype.drawDay = function(ymd) {
    var self = this;

    this.clearModelDataPoints();

    this.db.find({ time: ymd }, function(results) {
        for (var point in results) {
            self.drawModelDataPoint(new vdifn.map.ModelDataPoint(
                new google.maps.LatLng(results[point].latitude, results[point].longitude),
                results[point].dsv
            ));
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
