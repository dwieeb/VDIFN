/**
 * The VDIFN interface.
 *
 * @param  {google.maps.Map} map
 * @param  {vdifn.db} db
 */
vdifn.Interface = function(map, db) {
    this.map = map;
    this.db = db;
    this.modelDataPoints = {};
    this.activeModelDataPoint = undefined;
    this.stations = {};
    this.tooltip = undefined;
    this.loadingOverlay = document.getElementById('loading-overlay');
    this.errorOverlay = document.getElementById('error-overlay');
    this.messageOverlay = document.getElementById('message-overlay');
    this.crop = undefined;
    this.infliction = undefined;
    this.modelChanged = true;
    this.severities = {};
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
    this.attachListeners();

    return this;
};

/**
 * (Re)attach the various event listeners.
 *
 * @return this
 */
vdifn.Interface.prototype.attachListeners = function() {
    var self = this;
    var static = vdifn.Interface.prototype.attachListeners;
    var div = this.map.getDiv();
    var pointInfoBoxes = div.querySelectorAll('.infoBox-point');
    var listener;

    if (typeof static.listeners === 'undefined') {
        static.listeners = {
            pointInfoBoxes: {},
            message: null
        };
    }

    listener = google.maps.event.addDomListener(document.getElementById('message'), 'click', function(event) {
        var target = event.target || event.srcElement;

        if (target.id == "subscription_save") {
            var form = document.getElementById('subscription-form');
            var latitude = document.getElementById('subscription_latitude');
            var longitude = document.getElementById('subscription_longitude');
            latitude.value = self.activeModelDataPoint.latLng.lat();
            longitude.value = self.activeModelDataPoint.latLng.lng();

            superagent.post(
                Routing.generate('subscriptions_form')
            ).send(
                serialize(form)
            ).end(function(response) {
                if (response.ok) {
                    self.closeMessageOverlay();
                    self.activeModelDataPoint.getInfoBox();
                }
            });

            event.preventDefault();
        }
    });

    google.maps.event.removeListener(static.listeners.message);
    static.listeners.message = listener;

    for (var i = 0; i < pointInfoBoxes.length; i++) {
        var pointInfoBox = pointInfoBoxes[i];
        var id = pointInfoBox.querySelector('.point').getAttribute('data-id');
        var point = self.modelDataPoints[id];

        listener = google.maps.event.addDomListener(pointInfoBox, 'click', function(event) {
            var target = event.target || event.srcElement;
            var id = this.querySelector('.point').getAttribute('data-id');
            var point = self.modelDataPoints[id];

            self.activeModelDataPoint = point;

            if (target.classList.contains('actions-subscribe')) {
                self.openMessageOverlay();

                superagent.get(
                    Routing.generate('subscriptions_form')
                ).end(function(response) {
                    var inner = document.getElementById('message-overlay-inner');
                    inner.innerHTML = response.text;

                    var d = new Date(document.getElementById('datepicker-start').value);
                    document.getElementById('subscription_emergenceDate_month').value = d.getMonth() + 1;
                    document.getElementById('subscription_emergenceDate_day').value = d.getDate();
                    document.getElementById('subscription_emergenceDate_year').value = d.getFullYear();
                    self.registerInflictionSelectHandler(
                        document.getElementById('subscription_crop'),
                        document.getElementById('subscription_infliction')
                    );
                });

                event.preventDefault();
            } else if (target.classList.contains('login')) {
                self.openMessageOverlay();

                superagent.get(
                    Routing.generate('fos_user_login')
                ).end(function(response) {
                    var inner = document.getElementById('message-overlay-inner');
                    inner.innerHTML = response.text;
                    document.getElementById('username').focus();
                    var form = document.getElementById('login-form');

                    google.maps.event.addDomListener(form, 'submit', function(event) {
                        self.openLoadingOverlay();

                        superagent.post(
                            form.getAttribute('action')
                        ).send(
                            serialize(form)
                        ).end(function(response) {
                            self.closeLoadingOverlay();
                            self.closeMessageOverlay();
                            point.getInfoBox();
                        });

                        event.preventDefault();
                    });
                });

                event.preventDefault();
            } else if (target.classList.contains('unsubscribe')) {
                superagent.del(
                    Routing.generate('subscriptions_delete')
                ).query({
                    'latitude': self.activeModelDataPoint.latLng.lat(),
                    'longitude': self.activeModelDataPoint.latLng.lng()
                }).end(function(response) {
                    if (response.ok) {
                        self.activeModelDataPoint.getInfoBox();
                    }
                });
            }
        });

        google.maps.event.removeListener(static.listeners.pointInfoBoxes[id]);
        static.listeners.pointInfoBoxes[id] = listener;
    }

    return this;
};

/**
 * The callback for when a model changes.
 *
 * @return this
 */
vdifn.Interface.prototype.modelChangedHandler = function() {
    if (this.crop === 'potato' && this.infliction === 'disease-late-blight') {
        this.endPicker.config().field.setAttribute("disabled", "disabled");
        this.endPicker.setDate((7).daysAfter(this.startPicker.getDate()));

        if (7 !== this.startPicker.getDate().daysUntil(this.endPicker.getDate())) {
            this.startPicker.setDate((7).daysBefore(this.endPicker.getDate()));
        }
    } else {
        this.endPicker.config().field.removeAttribute("disabled");
    }
};

/**
 * Register events for the pest/disease select box.
 *
 * @param  {DOMElement} cropSelect The select box that contains the crops.
 * @param  {DOMElement} inflictionSelect The select box that contains the inflictions.
 *
 * @return this
 */
vdifn.Interface.prototype.registerInflictionSelectHandler = function(cropSelect, inflictionSelect) {
    var static = vdifn.Interface.prototype.registerInflictionSelectHandler;

    if (typeof static.listeners === 'undefined') {
        static.listeners = {};
    }

    if (cropSelect in static.listeners) {
        google.maps.event.removeListener(static.listeners[cropSelect]);
    }

    static.listeners[cropSelect] = google.maps.event.addDomListener(cropSelect, 'change', function(event) {
        var optgroups = inflictionSelect.childNodes;
        var selected = false;

        for (var i = 0; i < optgroups.length; i++) {
            if (optgroups[i].nodeType !== Node.ELEMENT_NODE) {
                continue;
            }

            var optgroup = optgroups[i];
            var options = optgroup.childNodes;
            var firstOption = false;

            for (var j = 0; j < options.length; j++) {
                if (options[j].nodeType !== Node.ELEMENT_NODE) {
                    continue;
                }

                options[j].removeAttribute('selected');

                if (firstOption === false) {
                    firstOption = options[j];
                }
            }

            if (optgroup.label.indexOf(this.selectedOptions[0].innerHTML) === 0) {
                optgroup.style.display = 'block';

                if (selected === false) {
                    firstOption.selected = "selected";
                    selected = true;
                }
            } else {
                optgroup.style.display = 'none';
            }
        }

        google.maps.event.trigger(inflictionSelect, 'change');
    });

    google.maps.event.trigger(cropSelect, 'change');

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
 * Open the message overlay with a message.
 *
 * @return this
 */
vdifn.Interface.prototype.openMessageOverlay = function() {
    this.messageOverlay.style.display = "block";
    this.messageOverlay.style.opacity = 1;
    this.messageOverlay.style.visibility = "visible";

    var content = this.generateLoadingBars();
    var inner = document.getElementById('message-overlay-inner');
    inner.innerHTML = '';
    inner.appendChild(content);

    return this;
};

/**
 * Close the error overlay.
 *
 * @return this
 */
vdifn.Interface.prototype.closeMessageOverlay = function() {
    this.messageOverlay.style.opacity = 0;
    this.messageOverlay.style.visibility = "hidden";

    return this;
};

/**
 * Load and display the severity legend given the current model.
 *
 * @return this
 */
vdifn.Interface.prototype.drawSeverityLegend = function() {
    if (!this.modelChanged) {
        return this;
    }

    var self = this;
    var content = this.generateLoadingBars();
    var inner = document.getElementById('severity-legend');
    inner.innerHTML = '';
    inner.appendChild(content);

    superagent.get(
        Routing.generate('model_severity_legend')
    ).query({
        'crop': Interface.crop,
        'infliction': Interface.infliction
    }).end(function(response) {
        inner.innerHTML = response.text;
        var elements = inner.querySelectorAll('.dsv');
        var severity, color;
        self.severities = {};

        for (var i = 0; i < elements.length; i++) {
            severity = elements[i].getAttribute('data-severity');
            color = elements[i].getAttribute('data-color');
            self.severities[severity] = color;

            google.maps.event.addDomListener(elements[i], 'mouseover', function(event) {
                var content = document.createElement('div');
                content.innerHTML = this.getAttribute('data-description');
                Interface.openTooltip(this, content);
            });

            google.maps.event.addDomListener(elements[i], 'mouseout', function(event) {
                Interface.closeTooltip();
            });
        }
    });

    return this;
};

/**
 * Redraw the open InfoBoxes according to the current model.
 *
 * @return this
 */
vdifn.Interface.prototype.closeInfoBoxes = function() {
    for (var key in this.modelDataPoints) {
        if (this.modelDataPoints.hasOwnProperty(key)) {
            this.modelDataPoints[key].closeInfoBox();
        }
    }

    return this;
};

/**
 * Generate DOM elements that represent loading.
 *
 * @return DOMElement
 */
vdifn.Interface.prototype.generateLoadingBars = function() {
    var content = document.createElement('div');
    var loading = document.createElement('ul');
    loading.classList.add('loading-icon');
    loading.appendChild(document.createElement('li'));
    loading.appendChild(document.createElement('li'));
    loading.appendChild(document.createElement('li'));
    content.classList.add('loading');
    content.appendChild(loading);

    return content;
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
vdifn.Interface.prototype.drawDateRange = function(criteria, callback) {
    var self = this;

    this.openLoadingOverlay();
    this.clearModelDataPoints();

    this.db.findPredictedWeatherData(criteria, function(results) {
        for (var point in results) {
            self.drawModelDataPoint(new vdifn.map.ModelDataPoint(
                point,
                new google.maps.LatLng(results[point].latitude, results[point].longitude),
                results[point].severity
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
    var self = this;

    if (typeof vdifn.Interface.prototype.drawModelDataPoint.id === 'undefined') {
        vdifn.Interface.prototype.drawModelDataPoint.id = 0;
    }

    this.modelDataPoints[modelDataPoint.id] = modelDataPoint;
    modelDataPoint.plot(this.map);
    modelDataPoint.id = vdifn.Interface.prototype.drawModelDataPoint.id++;

    google.maps.event.addListener(modelDataPoint.object, 'click', function(event) {
        modelDataPoint.onclick(event);
        self.activeModelDataPoint = modelDataPoint;
        self.attachListeners();
    });

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
    this.stations[station.usaf + '-' + station.wban] = station;
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

    this.modelDataPoints = {};

    return this;
};
