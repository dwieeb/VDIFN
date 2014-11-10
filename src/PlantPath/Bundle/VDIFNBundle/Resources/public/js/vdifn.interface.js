/**
 * The VDIFN interface.
 *
 * @param  {google.maps.Map} map
 * @param  {vdifn.db} db
 */
vdifn.Interface = function(map, db) {
    this.map = map;
    this.db = db;
    this.modelDataPoints = [];
    this.stations = [];
    this.tooltip = undefined;
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
 * Open the VDIFN informational tooltip.
 *
 * @param  {DOMElement} element
 * @param  {DOMElement|string} content
 *
 * @return this
 */
vdifn.Interface.prototype.openTooltip = function(element, content) {
    if (typeof this.tooltip === 'undefined') {
        this.tooltip = document.createElement('div');
        this.tooltip.id = 'tooltip';
        document.body.appendChild(this.tooltip);
    }

    var elementBounding = element.getBoundingClientRect();

    this.tooltip.innerHTML = '';
    this.tooltip.appendChild(content);
    this.tooltip.style.display = 'block';
    this.tooltip.style.top = (elementBounding.top - (this.tooltip.clientHeight + 7)) + 'px';
    this.tooltip.style.left = (elementBounding.left + 1) + 'px';

    return this;
};

/**
 * Close the VDIFN informational tooltip.
 *
 *
 * @return this
 */
vdifn.Interface.prototype.closeTooltip = function() {
    this.tooltip.style.display = 'none';

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
 * Draw a date range of aggregated data onto the map.
 *
 * @param  {Date} startDate
 * @param  {Date} endDate
 * @param  {Function} callback
 *
 * @return this
 */
vdifn.Interface.prototype.drawDateRange = function(startDate, endDate, callback) {
    var self = this;
    var startYmd = startDate.format('{yyyy}{MM}{dd}');
    var endYmd = endDate.format('{yyyy}{MM}{dd}');

    this.openLoadingOverlay();
    this.clearModelDataPoints();

    this.db.findPredictedWeatherData({ start: startYmd, end: endYmd }, function(results) {
        for (var point in results) {
            self.drawModelDataPoint(new vdifn.map.ModelDataPoint(
                new google.maps.LatLng(results[point].latitude, results[point].longitude),
                results[point].dsv
            ));
        }

        if (typeof callback === 'function') {
            callback.call(this, !results.isEmpty());
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
    if (typeof vdifn.Interface.prototype.drawModelDataPoint.id === 'undefined') {
        vdifn.Interface.prototype.drawModelDataPoint.id = 0;
    }

    this.modelDataPoints.push(modelDataPoint);
    modelDataPoint.plot(this.map);
    modelDataPoint.id = vdifn.Interface.prototype.drawModelDataPoint.id++;

    google.maps.event.addListener(modelDataPoint.object, 'click', modelDataPoint.onclick.bind(modelDataPoint));

    return this;
};

/**
 * Draw the stations onto the map.
 *
 * @return this
 */
vdifn.Interface.prototype.drawStations = function() {
    var self = this;

    this.db.findStations({ country: "US", state: "WI" }, function(results) {
        results.forEach(function(result) {
            var station = new vdifn.map.Station(new google.maps.LatLng(result.latitude, result.longitude), result);
            self.drawStation(station);
        });
    });

    return this;
};

/**
 * Draw a station onto the map.
 *
 * @param  {vdifn.map.Station} station
 *
 * @return this
 */
vdifn.Interface.prototype.drawStation = function(station) {
    this.stations.push(station);
    station.plot(this.map);

    google.maps.event.addListener(station.object, 'click', station.onclick.bind(station));

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
