/**
 * VDIFN client-side database. Utilizes crossfilter.
 *
 * @param  {crossfilter} cf A crossfilter instance.
 */
vdifn.db = function(cf) {
    this.cf = cf;
    this.dimension = {};

    this.addDimension('latitude', function(record) {
        return record.latitude;
    });

    this.addDimension('longitude', function(record) {
        return record.longitude;
    });

    this.addDimension('time', function(record) {
        return record.time;
    });
};

/**
 * Adds a dimension to the crossfilter database.
 *
 * @param {String} name
 * @param {Function} dimension
 *
 * @return this
 */
vdifn.db.prototype.addDimension = function(name, dimension) {
    if (name in this.dimension) {
        throw new Error('A dimension already exists by name: ' + name);
    }

    this.dimension[name] = this.cf.dimension(dimension);

    return this;
};

/**
 * Gets the crossfilter dimension.
 *
 * @param  {String} name
 *
 * @return {Object}
 */
vdifn.db.prototype.getDimension = function(name) {
    if (!(name in this.dimension)) {
        throw new Error('No dimensions exist by name: ' + name);
    }

    return this.dimension[name];
};

/**
 * Calls filterAll() on each known dimension, resetting the filter.
 *
 * @return this
 */
vdifn.db.prototype.resetDimensions = function() {
    for (var i in this.dimension) {
        this.dimension[i].filterAll();
    }

    return this;
};

/**
 * Insert a record or multiple records into the database.
 *
 * @param  {Object|Array} records
 *
 * @return this
 */
vdifn.db.prototype.insert = function(records) {
    this.cf.add(records);

    return this;
};

/**
 * Use crossfilter dimensions to filter the return values. The keys are the
 * names of the dimensions and the values are the filter to apply to each
 * corresponding dimension.
 *
 * @param  {Object} criteria
 * @param  {Function} callback
 */
vdifn.db.prototype.find = function(criteria, callback) {
    var db = this;

    if (Object.isEmpty(criteria)) {
        throw new Error('Must specify criteria.');
    }

    if (typeof callback !== 'function') {
        throw new Error('callback must be a function.')
    }

    if (!('time' in criteria)) {
        throw new Error('time is a required criterion.')
    }

    if (0 === this.getDimension('time').filter(criteria.time).top(Infinity).length) {
        superagent.get(
            Routing.generate('weather_daily_bounding_box', {
                day: criteria.time,
                nwLat: vdifn.parameters.bounding_box.n,
                nwLong: vdifn.parameters.bounding_box.w,
                seLat: vdifn.parameters.bounding_box.s,
                seLong: vdifn.parameters.bounding_box.e
            })
        ).end(function(response) {
            if (response.ok) {
                db.insert(response.body);
                callback.call(db, db._find(criteria));
            } else {
                console.log('ajax error', response);
            }
        });
    } else {
        callback.call(this, this._find(criteria));
    }

    return this;
};

/**
 * Find data from client-side only.
 *
 * @param  {Object} criteria
 *
 * @return Array
 */
vdifn.db.prototype._find = function(criteria) {
    this.resetDimensions();

    for (var i in criteria) {
        this.getDimension(i).filter(criteria[i]);
    }

    return this.getDimension(i).top(Infinity);
}
