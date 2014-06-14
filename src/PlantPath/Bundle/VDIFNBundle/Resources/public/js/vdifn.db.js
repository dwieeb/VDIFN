/**
 * VDIFN database interface.
 */
vdifn.db = function() {};

/**
 * Verify that the criteria passed in is valid.
 *
 * @param  {Object} criteria
 * @param  {Object} options
 *         - {string} requiredCriteria A list of required criteria.
 *
 * @throws Error if invalid criteria
 */
vdifn.db.verifyCriteria = function(criteria, options) {
    options = options || {};

    if ('required' in options && Array.isArray(options.required)) {
        options.required.forEach(function(criterion) {
            if (!(criterion in criteria)) {
                throw new Error(criterion + ' is a required criterion.');
            }
        });
    }

    if (Object.isEmpty(criteria)) {
        throw new Error('Must specify criteria.');
    }
};

/**
 * Verify that the callback passed in is valid.
 *
 * @param  {Function} callback
 *
 * @throws Error if invalid callback
 */
vdifn.db.verifyCallback = function(callback) {
    if (typeof callback !== 'function') {
        throw new Error('callback must be a function.')
    }
};

/**
 * Find aggregated daily weather data based upon a set of criteria.
 *
 * @param  {Object} criteria
 * @param  {Function} callback
 */
vdifn.db.prototype.findPredictedWeatherData = function(criteria, callback) {
    vdifn.db.verifyCriteria(criteria, { required: ['start', 'end'] });
    vdifn.db.verifyCallback(callback);

    var db = this;

    superagent.get(
        Routing.generate('weather_daily_date_range', {
            start: criteria.start,
            end: criteria.end
        })
    ).end(function(response) {
        if (response.ok) {
            callback.call(db, response.body);
        } else {
            callback.call(db, []);
        }
    });

    return this;
};

vdifn.db.prototype.findStations = function(criteria, callback) {
    vdifn.db.verifyCriteria(criteria, { required: ['country', 'state'] });
    vdifn.db.verifyCallback(callback);

    var db = this;

    superagent.get(
        Routing.generate('stations_country_state', {
            country: criteria.country,
            state: criteria.state
        })
    ).end(function(response) {
        if (response.ok) {
            callback.call(db, response.body);
        } else {
            callback.call(db, []);
        }
    });

    return this;
};
