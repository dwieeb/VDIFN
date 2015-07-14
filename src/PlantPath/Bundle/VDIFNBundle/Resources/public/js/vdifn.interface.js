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
vdifn.Interface.prototype.openTooltip = function(element, content, options) {
    if (typeof options === 'undefined') {
        options = {};
    }

    var defaultOptions = {
        'top': 17,
        'left': -7,
        'right': 17,
        'max-width': '350px',
        'arrow': 'left'
    };

    for (var k in defaultOptions) {
        if (typeof options[k] === 'undefined') {
            options[k] = defaultOptions[k];
        }
    }

    if (typeof this.tooltip === 'undefined') {
        this.tooltip = document.createElement('div');
        this.tooltip.id = 'tooltip';
        document.body.appendChild(this.tooltip);
    }

    var elementBounding = element.getBoundingClientRect();

    this.tooltip.innerHTML = '';
    this.tooltip.appendChild(content);
    this.tooltip.style.display = 'block';
    this.tooltip.style.maxWidth = options['max-width'];
    this.tooltip.style.top = (elementBounding.top - (this.tooltip.clientHeight + options.top)) + 'px';

    if (options['arrow'] === 'left') {
        this.tooltip.style.left = (elementBounding.left + options.left) + 'px';
        this.tooltip.classList.add('left-triangle');
        this.tooltip.classList.remove('right-triangle');
    } else if (options['arrow'] === 'right') {
        this.tooltip.style.left = (elementBounding.left - this.tooltip.clientWidth + options.right) + 'px';
        this.tooltip.classList.add('right-triangle');
        this.tooltip.classList.remove('left-triangle');
    }

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

    if (typeof static.clickListeners === 'undefined') {
        static.clickListeners = {
            pointInfoBoxes: {},
            message: null
        };
    }

    if (typeof static.mouseoverListeners === 'undefined') {
        static.mouseoverListeners = {
            pointInfoBoxes: {}
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

    google.maps.event.removeListener(static.clickListeners.message);
    static.clickListeners.message = listener;

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
            } else if (target.classList.contains('login')) {
                self.openLoginModal(function() {
                    point.getInfoBox();
                });
            } else if (target.classList.contains('unsubscribe')) {
                superagent.del(
                    Routing.generate('subscriptions_delete', {
                        latitude: self.activeModelDataPoint.latLng.lat(),
                        longitude: self.activeModelDataPoint.latLng.lng()
                    })
                ).end(function(response) {
                    if (response.ok) {
                        self.activeModelDataPoint.getInfoBox();
                    }
                });
            }

            event.preventDefault();
        });

        google.maps.event.removeListener(static.clickListeners.pointInfoBoxes[id]);
        static.clickListeners.pointInfoBoxes[id] = listener;

        listener = google.maps.event.addDomListener(pointInfoBox, 'mouseover', function(event) {
            var target = event.target || event.srcElement;

            if (target.classList.contains('weather-details-dsv')) {
                var content = document.createElement('div');
                content.innerHTML = 'Disease Severity Values are calculated by using temperature and leaf wetness (or relative humidity) duration (hours). Values range from 0-4. Accumulation of threshold DSV levels trigger fungicide/pesticide applications.';
                self.openTooltip(target, content);

                google.maps.event.addDomListener(target, 'mouseout', function(event) {
                    self.closeTooltip();
                });
            } else if (target.classList.contains('weather-details-rh')) {
                var content = document.createElement('div');
                content.innerHTML = 'Relative humidity is a measure of how much water vapor (%) is in the air compared to complete saturation (100%) at a given temperature.';
                self.openTooltip(target, content);

                google.maps.event.addDomListener(target, 'mouseout', function(event) {
                    self.closeTooltip();
                });
            }
        });

        google.maps.event.removeListener(static.mouseoverListeners.pointInfoBoxes[id]);
        static.mouseoverListeners.pointInfoBoxes[id] = listener;
    }

    return this;
};

/**
 * The callback for when a model changes.
 *
 * @return this
 */
vdifn.Interface.prototype.modelChangedHandler = function() {
    if (typeof this.crop === 'undefined' || typeof this.infliction === 'undefined') {
        return this;
    }

    var inflictionType = this.infliction.substring(0, this.infliction.indexOf('-'));
    var endPickerField = this.endPicker.config().field;
    var endPickerWrapper = document.getElementById('datepicker-end-wrapper');
    var to = document.getElementById('datepicker-to');
    var legend = document.getElementById('datepicker-legend');

    if (this.crop === 'potato' && this.infliction === 'disease-late-blight') {
        endPickerField.setAttribute("disabled", "disabled");
        this.endPicker.setDate((7).daysAfter(this.startPicker.getDate()));

        if (7 !== this.startPicker.getDate().daysUntil(this.endPicker.getDate())) {
            this.startPicker.setDate((7).daysBefore(this.endPicker.getDate()));
        }
    } else {
        endPickerField.removeAttribute("disabled");
    }

    if (inflictionType === 'pest') {
        endPickerWrapper.style.display = 'none';
        to.style.display = 'none';
        legend.innerHTML = 'Date';
    } else {
        endPickerWrapper.style.display = 'block';
        to.style.display = 'block';
        legend.innerHTML = 'Dates';
    }

    return this;
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
 * Open the login modal.
 *
 * @return this
 */
vdifn.Interface.prototype.openLoginModal = function(callback) {
    var self = this;
    self.openMessageOverlay();

    superagent.get(
        Routing.generate('user_login')
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

                if (typeof callback === 'function') {
                    callback();
                }
            });

            event.preventDefault();
        });
    });

    return this;
};

/**
 * Open the registration form modal.
 *
 * @return this
 */
vdifn.Interface.prototype.openRegistrationModal = function(callback) {
    var self = this;
    self.openMessageOverlay();
    var inner = document.getElementById('message-overlay-inner');

    var attachFormHandlers = function(form) {
        document.getElementById('fos_user_registration_form_email').focus();

        google.maps.event.addDomListener(form, 'submit', function(event) {
            self.openLoadingOverlay();

            superagent.post(
                form.getAttribute('action')
            ).send(
                serialize(form)
            ).end(function(response) {
                self.closeLoadingOverlay();
                inner.innerHTML = response.text;
                var form = document.getElementById('register-form');

                if (form) {
                    attachFormHandlers(form);
                } else {
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            });

            event.preventDefault();
        });
    };

    superagent.get(
        Routing.generate('user_register')
    ).end(function(response) {
        inner.innerHTML = response.text;
        var form = document.getElementById('register-form');
        attachFormHandlers(form);
    });

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
vdifn.Interface.prototype.drawSeverityLegend = function(criteria, callback) {
    if (!this.modelChanged) {
        return this;
    }

    superagent.get(
        Routing.generate('model_severity_legend', {
            crop: Interface.crop,
            infliction: Interface.infliction
        })
    ).end(function(response) {
        if (typeof callback === 'function') {
            callback.call(this, response);
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
 * Draw the user links on the interface.
 *
 * @return this
 */
vdifn.Interface.prototype.drawUserLinks = function() {
    var self = this;
    var content = self.generateLoadingBars();
    var inner = document.getElementById('user-links');
    inner.innerHTML = '';
    inner.appendChild(content);

    superagent.get(
        Routing.generate('user_links')
    ).end(function(response) {
        inner.innerHTML = response.text;

        if (document.getElementById('login')) {
            google.maps.event.addDomListener(document.getElementById('login'), 'click', function(event) {
                self.openLoginModal(function() {
                    self.drawUserLinks();
                });
            });
        }

        if (document.getElementById('logout')) {
            google.maps.event.addDomListener(document.getElementById('logout'), 'click', function(event) {
                superagent.get(
                    Routing.generate('user_logout')
                ).end(function(response) {
                    self.drawUserLinks();
                });
            });
        }

        if (document.getElementById('register')) {
            google.maps.event.addDomListener(document.getElementById('register'), 'click', function(event) {
                self.openRegistrationModal(function() {
                    self.drawUserLinks();
                });
            });
        }
    });

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
vdifn.Interface.prototype.drawDateRange = function(criteria, callback) {
    var self = this;

    this.openLoadingOverlay();
    this.clearModelDataPoints();

    this.db.findPredictedWeatherData(criteria, function(results) {
        if (typeof callback === 'function') {
            callback.call(this, results);
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
