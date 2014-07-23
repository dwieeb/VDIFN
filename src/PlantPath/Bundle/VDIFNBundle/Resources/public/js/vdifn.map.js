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
        boxClass: 'infoBox infoBox-station',
        closeBoxURL: vdifn.parameters.static_path + '/img/close.png',
        visible: false,
        pixelOffset: new google.maps.Size(-13, -20)
    }, options));
}

/**
 * Construct the object.
 *
 * @return this
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
    if (!(this.infoBox instanceof InfoBox)) {
        var content = document.createElement('div');
        var loading = document.createElement('ul');
        loading.classList.add('loading-icon');
        loading.appendChild(document.createElement('li'));
        loading.appendChild(document.createElement('li'));
        loading.appendChild(document.createElement('li'));
        content.setAttribute('id', 'station-' + this.usaf + '-' + this.wban);
        content.classList.add('station');
        content.classList.add('loading');
        content.appendChild(loading);

        this.infoBox = vdifn.map.Plottable.createInfoBox({
            content: content,
            boxClass: 'infoBox infoBox-station'
        });

        this.getWeatherDetails();
    }

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
            wban: this.wban
        })
    ).end(function(response) {
        if (response.ok) {
            var station = document.getElementById('station-' + self.usaf + '-' + self.wban);
            station.classList.remove('loading');
            station.innerHTML = response.text;
        } else {
            var content = self.getInfoBox().getContent();
            content.innerHTML = '<p style="text-align: center">Error loading station.</p>';
        }
    });

    return this;
};

/**
 * @see vdifn.map.Plottable.prototype.plot
 */
vdifn.map.Station.prototype.plot = function(map) {
    vdifn.map.Plottable.prototype.plot.call(this, map);

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
 * @param  {number} dsv
 */
vdifn.map.ModelDataPoint = function(latLng, dsv) {
    vdifn.map.DataPoint.call(this, latLng);
    this.dsv = dsv;
    this.size = 12; // km
};

vdifn.map.ModelDataPoint.prototype = Object.create(vdifn.map.DataPoint.prototype);

/**
 * Get the hex color code of a given disease severity value.
 *
 * @param  {number} dsv
 *
 * @return {string}
 */
vdifn.map.ModelDataPoint.getSeverityColor = function(dsv) {
    if (dsv >= 0 && dsv < 5) {
        return '#00c957';
    } else if (dsv >= 5 && dsv < 10) {
        return '#7dff23';
    } else if (dsv >= 10 && dsv < 15) {
        return '#ffd700';
    } else if (dsv >= 15 && dsv < 20) {
        return '#ff8000';
    } else if (dsv >= 20) {
        return '#cc0000';
    }

    return '#ffffff';
};

/**
 * @see vdifn.map.DataPoint.prototype.draw
 */
vdifn.map.ModelDataPoint.prototype.draw = function() {
    if (!this.drawn) {
        var color = vdifn.map.ModelDataPoint.getSeverityColor(this.dsv);
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
            fillColor: color,
            fillOpacity: 0.2
        });

        this.drawn = true;
    }

    return this;
};
