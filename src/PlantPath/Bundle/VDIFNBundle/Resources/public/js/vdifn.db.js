/**
 * VDIFN database interface.
 */
vdifn.db = function() {};

/**
 * Find aggregated daily weather data based upon a set of criteria.
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

    if (!('start' in criteria) || !('end' in criteria)) {
        throw new Error('start and end are required criteria.')
    }

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
