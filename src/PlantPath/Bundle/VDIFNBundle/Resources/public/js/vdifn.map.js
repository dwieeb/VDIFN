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
 * Constructor.
 *
 * @param  {google.maps.LatLng} latLng
 */
vdifn.map.DataPoint = function(latLng) {
    this.latLng = latLng;
    this.object = undefined;
    this.drawn = false;
};

/**
 * Construct the Google Maps object.
 *
 * @return this
 */
vdifn.map.DataPoint.prototype.draw = function() {
    throw new vdifn.UnimplementedAbstractException();
};

/**
 * Plot the data point Google Maps object onto the map.
 *
 * @param  {google.maps.Map} map
 *
 * @return this
 */
vdifn.map.DataPoint.prototype.plot = function(map) {
    if (!this.drawn) {
        this.draw();
    }

    this.object.setMap(map);

    return this;
};

/**
 * Constructor.
 *
 * @param  {google.maps.LatLng} latLng
 * @param  {number} dsv
 */
vdifn.map.ModelDataPoint = function(latLng, dsv) {
    vdifn.map.DataPoint.call(this, latLng);
    this.dsv = dsv;
    this.size = 12;
};

vdifn.map.ModelDataPoint.prototype = Object.create(vdifn.map.DataPoint.prototype);

/**
 * Get the hex color code of a given disease severity value.
 *
 * @param  {number} dsv
 *
 * @return {string}
 */
vdifn.map.ModelDataPoint.prototype.getSeverityColor = function(dsv) {
    var color = '#ff0000';

    switch (dsv) {
        case 0:
            color = '#ffffff';
            break;
        case 1:
            break;
        // etc
    }

    return color;
};

/**
 * @see vdifn.map.DataPoint.prototype.draw
 */
vdifn.map.ModelDataPoint.prototype.draw = function() {
    if (!this.drawn) {
        var color = this.getSeverityColor(this.dsv);
        var latitude = this.latLng.lat();
        var longitude = this.latLng.lng();
        var latitudeOffset = vdifn.map.kmToLatitude(this.size) / 2;
        var longitudeOffset = vdifn.map.kmToLongitude(this.size, latitude) / 2;

        this.object = new google.maps.Rectangle({
            map: this.map,
            bounds: new google.maps.LatLngBounds(
                new google.maps.LatLng(latitude - latitudeOffset, longitude - longitudeOffset),
                new google.maps.LatLng(latitude + latitudeOffset, longitude + longitudeOffset)
            ),
            strokeWeight: 0,
            fillColor: color,
            fillOpacity: 0.35
        });
    }

    return this;
};
