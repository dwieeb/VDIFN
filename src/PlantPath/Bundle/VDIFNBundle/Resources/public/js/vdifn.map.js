vdifn.map = {};

/**
 * Convert kilometers to latitudinal degrees.
 *
 * @param  {number} km
 *
 * @return {number}
 */
vdifn.map.kmToLatitude = function(km) {
    return 1 / (110.54 / km);
};

/**
 * Convert kilometers to longitudinal degrees.
 *
 * @param  {number} km
 * @param  {number} latitude
 *
 * @return {number}
 */
vdifn.map.kmToLongitude = function(km, latitude) {
    return 1 / ((111.32 * Math.cos(latitude * Math.PI / 180)) / km);
};

/**
 * An item that can be plotted on Google Maps.
 *
 * @param {google.maps.LatLng} latLng
 */
vdifn.map.Plottable = function(latLng) {
    this.latLng = latLng;
    this.object = undefined;
    this.map = undefined;
    this.infoBox = undefined;
    this.drawn = false;
};

/**
 * Create an InfoBox with extra options.
 *
 * @param  {Object} options
 *
 * @return InfoBox
 */
vdifn.map.Plottable.createInfoBox = function(options) {
    options = options || {};

    return new InfoBox(Object.merge({
        alignBottom: true,
        closeBoxURL: vdifn.parameters.static_path + '/img/close.png',
        visible: false,
        pixelOffset: new google.maps.Size(-13, -20)
    }, options));
};

/**
 * Determines whether the InfoBox for this Plottable is visible.
 *
 * @return boolean
 */
vdifn.map.Plottable.prototype.isInfoBoxVisible = function() {
    return typeof this.infoBox !== 'undefined' && this.infoBox.getVisible();
};

/**
 * Close the Plottable's InfoBox if it has one and is open.
 *
 * @return this
 */
vdifn.map.Plottable.prototype.closeInfoBox = function() {
    if (this.isInfoBoxVisible()) {
        this.infoBox.setMap(null);
    }

    return this;
};

/**
 * Construct the object.
 */
vdifn.map.Plottable.prototype.draw = function() {
    throw new vdifn.UnimplementedAbstractException();
};

/**
 * Plot the data point Google Maps object onto the map.
 *
 * @param  {google.maps.Map} map
 *
 * @return this
 */
vdifn.map.Plottable.prototype.plot = function(map) {
    if (!this.drawn) {
        this.draw();
    }

    this.object.setMap(map);
    this.map = map;

    return this;
};

/**
 * Constructor.
 *
 * @param  {google.maps.LatLng} latLng
 * @param  {object} data
 */
vdifn.map.Station = function(latLng, data) {
    data = data || {};
    vdifn.map.Plottable.call(this, latLng);

    this.usaf = data.usaf;
    this.wban = data.wban;
};

vdifn.map.Station.prototype = Object.create(vdifn.map.Plottable.prototype);

/**
 * @see vdifn.map.Plottable.prototype.draw
 */
vdifn.map.Station.prototype.draw = function() {
    if (!this.drawn) {
        this.object = new google.maps.Marker({
            icon: vdifn.parameters.static_path + '/img/station-open.png',
            position: this.latLng
        });

        this.drawn = true;
    }

    return this;
};

/**
 * Get the InfoBox for this station which displays station information and
 * recent weather details for this station.
 *
 * @return {InfoBox}
 */
vdifn.map.Station.prototype.getInfoBox = function() {
    var content = Interface.generateLoadingBars();

    content.setAttribute('id', 'station-' + this.usaf + '-' + this.wban);
    content.classList.add('station');

    this.infoBox = vdifn.map.Plottable.createInfoBox({
        content: content,
        boxClass: 'infoBox infoBox-station'
    });

    this.getWeatherDetails();

    return this.infoBox;
}

/**
 * Get the weather details of this station and then draw the result.
 *
 * @return this
 */
vdifn.map.Station.prototype.getWeatherDetails = function() {
    var self = this;

    superagent.get(
        Routing.generate('stations_get', {
            usaf: this.usaf,
            wban: this.wban,
            start: Interface.startPicker.getDate().format('{yyyy}{MM}{dd}'),
            end: Interface.endPicker.getDate().format('{yyyy}{MM}{dd}'),
            crop: Interface.crop,
            infliction: Interface.infliction
        })
    ).end(function(response) {
        if (response.ok) {
            var station = document.getElementById('station-' + self.usaf + '-' + self.wban);
            station.classList.remove('loading');
            station.innerHTML = response.text;
            Interface.attachStationListeners();
        } else {
            var content = self.getInfoBox().getContent();
            content.innerHTML = '<p style="text-align: center">Error loading station.</p>';
        }
    });

    return this;
};

/**
 * The event to run during a click event.
 */
vdifn.map.Station.prototype.onclick = function(event) {
    var infoBox = this.getInfoBox();
    infoBox.open(this.map, this.object);
    infoBox.setVisible(true);
};

/**
 * Constructor.
 *
 * @param  {google.maps.LatLng} latLng
 */
vdifn.map.DataPoint = function(latLng) {
    vdifn.map.Plottable.call(this, latLng);
};

vdifn.map.DataPoint.prototype = Object.create(vdifn.map.Plottable.prototype);

/**
 * Constructor.
 *
 * @param  {google.maps.LatLng} latLng
 * @param  {string} severity
 */
vdifn.map.ModelDataPoint = function(id, latLng, severity) {
    vdifn.map.DataPoint.call(this, latLng);
    this.id = id;
    this.severity = severity;
    this.size = 12; // km
};

vdifn.map.ModelDataPoint.prototype = Object.create(vdifn.map.DataPoint.prototype);

/**
 * @see vdifn.map.DataPoint.prototype.draw
 */
vdifn.map.ModelDataPoint.prototype.draw = function() {
    var self = this;

    if (!this.drawn) {
        var latitude = this.latLng.lat();
        var longitude = this.latLng.lng();
        var latitudeOffset = vdifn.map.kmToLatitude(this.size) / 2;
        var longitudeOffset = vdifn.map.kmToLongitude(this.size, latitude) / 2;
        var cornerOffset = 0.0025;

        this.object = new google.maps.Polygon({
            paths: [
                new google.maps.LatLng(latitude - latitudeOffset + cornerOffset, longitude - longitudeOffset - cornerOffset), // SW
                new google.maps.LatLng(latitude + latitudeOffset + cornerOffset, longitude - longitudeOffset + cornerOffset), // NW
                new google.maps.LatLng(latitude + latitudeOffset - cornerOffset, longitude + longitudeOffset + cornerOffset), // NE
                new google.maps.LatLng(latitude - latitudeOffset - cornerOffset, longitude + longitudeOffset - cornerOffset)  // SE
            ],
            strokeWeight: 0,
            fillColor: Interface.severities[self.severity],
            fillOpacity: 0.2
        });

        // Sort of a hack to give a data point an anchor in InfoBox.
        this.object.getPosition = function() {
            return self.latLng;
        };

        this.drawn = true;
    }

    return this;
};

/**
 * The event to run during a click event.
 */
vdifn.map.ModelDataPoint.prototype.onclick = function(event) {
    var infoBox = this.getInfoBox();
    infoBox.open(this.map, this.object);
    infoBox.setVisible(true);
};

/**
 * Get the InfoBox for this data point which displays information and
 * recent weather details for this data point.
 *
 * @return {InfoBox}
 */
vdifn.map.ModelDataPoint.prototype.getInfoBox = function() {
    var content = Interface.generateLoadingBars();

    content.setAttribute('id', 'point-' + this.id);
    content.setAttribute('data-id', this.id);
    content.classList.add('point');

    this.infoBox = vdifn.map.Plottable.createInfoBox({
        content: content,
        boxClass: 'infoBox infoBox-point'
    });

    this.getWeatherDetails();

    return this.infoBox;
}

/**
 * Get the weather details of this data point and then draw the result.
 *
 * @return this
 */
vdifn.map.ModelDataPoint.prototype.getWeatherDetails = function() {
    var self = this;

    superagent.get(
        Routing.generate('weather_daily_point', {
            latitude: this.latLng.lat(),
            longitude: this.latLng.lng(),
            start: Interface.startPicker.getDate().format('{yyyy}{MM}{dd}'),
            end: Interface.endPicker.getDate().format('{yyyy}{MM}{dd}'),
            crop: Interface.crop,
            infliction: Interface.infliction
        })
    ).end(function(response) {
        if (response.ok) {
            var point = document.getElementById('point-' + self.id);
            point.classList.remove('loading');
            point.innerHTML = response.text;
            Interface.attachListeners();
        } else {
            var content = self.getInfoBox().getContent();
            content.innerHTML = '<p style="text-align: center">Error loading data point.</p>';
        }
    });

    return this;
};
