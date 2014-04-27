/**
 * The VDIFN interface.
 *
 * @param  {google.maps.Map} map
 * @param  {crossfilter} db
 */
vdifn.Interface = function(map, db) {
    var self = this;
    this.map = map;
    this.db = db;
    this.modelDataPoints = [];
    this.errorOverlay = document.getElementById('error-overlay');
    this.loadingOverlay = document.getElementById('loading-overlay');

    var dsvSquares = document.getElementById('dsv-legend').querySelectorAll('.dsv');

    for (var i = 0; i < dsvSquares.length; ++i) {
        var element = dsvSquares.item(i);
        var dsv = parseInt(element.getAttribute('data-dsv'));
        var color = vdifn.map.ModelDataPoint.getSeverityColor(dsv);
        element.getElementsByTagName('div').item(0).style.backgroundColor = color;
    }

    this.picker = new Pikaday({
        defaultDate: Date.create(),
        setDefaultDate: true,
        field: document.getElementById('datepicker'),
        format: 'MMMM D, YYYY',
        maxDate: Date.create('2 days from today'),
        onSelect: function(date) {
            self.loadingOverlay.style.opacity = 1;
            self.loadingOverlay.style.visibility = "visible";

            self.drawDay(date.format('{yyyy}{MM}{dd}'), function(success) {
                self.loadingOverlay.style.opacity = 0;
                self.loadingOverlay.style.visibility = "hidden";

                if (!success) {
                    self.errorOverlay.style.opacity = 1;
                    self.errorOverlay.style.visibility = "visible";
                    document.getElementById('error-text').innerHTML = "<strong>Error</strong>: Could not load weather data for this day.";
                }
            });
        },
        onClose: function() {
            this.config().field.blur();
        }
    });
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
    this.loadingOverlay.style.opacity = 0;
    this.loadingOverlay.style.visibility = "hidden";
    this.loadingOverlay.style.backgroundColor = "transparent";
    this.loadingOverlay.classList.add('radial');

    return this;
};

/**
 * Callback for closing the error overlay.
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
