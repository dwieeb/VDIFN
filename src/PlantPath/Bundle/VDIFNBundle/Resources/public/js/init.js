var qs = vdifn.util.parseQueryString(window.location.search);

var initialLatitude = 45.05024026979463;
var initialLongitude = -90.274658203125;
var initialZoom = 7;

if ('latitude' in qs && 'longitude' in qs) {
    initialLatitude = qs['latitude'];
    initialLongitude = qs['longitude'];
    initialZoom = 10;
}

// Setup
var Interface = new vdifn.Interface(
    new google.maps.Map(document.getElementById('map-canvas'), {
        center: new google.maps.LatLng(initialLatitude, initialLongitude),
        mapTypeControl: true,
        mapTypeControlOptions: {
            mapTypeIds: [google.maps.MapTypeId.TERRAIN, google.maps.MapTypeId.HYBRID]
        },
        mapTypeId: google.maps.MapTypeId.TERRAIN,
        maxZoom: 12,
        minZoom: 6,
        streetViewControl: false,
        zoom: initialZoom
    }),
    new vdifn.db()
);

(function(window, undefined) {
    var endDate = Date.utc.create();

    Interface.startPicker = vdifn.datepicker.create({
        defaultDate: (1).weeksBefore(endDate),
        field: document.getElementById('datepicker-start'),
        onSelect: function() {
            var ultimateMaxDate = vdifn.latest_date;
            var minDate = this.getDate();
            var maxDate = this.getDate().advance('8 weeks');

            if (maxDate > ultimateMaxDate) {
                maxDate = ultimateMaxDate;
            }

            Interface.endPicker.setMinDate(minDate);
            Interface.endPicker.setMaxDate(maxDate);

            if (Interface.endPicker.getDate() < minDate) {
                Interface.endPicker.setDate(minDate);
                Interface.endPicker.gotoDate(minDate);
            }

            if (Interface.endPicker.getDate() > maxDate) {
                Interface.endPicker.setDate(maxDate);
                Interface.endPicker.gotoDate(maxDate);
            }

            Interface.modelChangedHandler();
        }
    });

    Interface.endPicker = vdifn.datepicker.create({
        defaultDate: endDate,
        minDate: Interface.startPicker.getDate(),
        field: document.getElementById('datepicker-end')
    });

    // Events
    if (vdifn.parameters.debug) {
        google.maps.event.addListener(Interface.map, 'click', function(event) {
            console.log('new google.maps.LatLng' + event.latLng.toString());
        });
    }

    google.maps.event.addDomListener(document.getElementById('select'), 'click', function(event) {
        if (Interface.modelChanged) {
            Interface.closeInfoBoxes();
        }

        async.parallel({
            date_range: function(callback) {
                Interface.drawDateRange({
                    start: Interface.startPicker.getDate(),
                    end: Interface.endPicker.getDate(),
                    crop: Interface.crop,
                    infliction: Interface.infliction
                }, function(results) {
                    Interface.closeLoadingOverlay();

                    if (results.isEmpty()) {
                        Interface.openErrorOverlay("Could not load weather data for the model specified.");
                    }

                    callback(null, {'results': results});
                });
            },
            severity_legend: function(callback) {
                if (Interface.modelChanged) {
                    var content = Interface.generateLoadingBars();
                    var inner = document.getElementById('severity-legend');
                    inner.innerHTML = '';
                    inner.appendChild(content);

                    Interface.drawSeverityLegend({
                        crop: Interface.crop,
                        infliction: Interface.infliction
                    }, function(response) {
                        callback(null, {'response': response});
                    });
                } else {
                    callback(null, null);
                }
            }
        }, function(err, results) {
            if (results.severity_legend) {
                var inner = document.getElementById('severity-legend');
                inner.innerHTML = results.severity_legend.response.text;
                var elements = inner.querySelectorAll('.dsv');
                var severity, color;
                Interface.severities = {};

                for (var i = 0; i < elements.length; i++) {
                    severity = elements[i].getAttribute('data-severity');
                    color = elements[i].getAttribute('data-color');
                    Interface.severities[severity] = color;

                    google.maps.event.addDomListener(elements[i].querySelector('.more-information'), 'mouseenter', function(event) {
                        var content = document.createElement('div');
                        content.innerHTML = this.parentNode.parentNode.getAttribute('data-description');
                        Interface.openTooltip(this, content, { 'arrow': 'right' });
                    });

                    google.maps.event.addDomListener(elements[i].querySelector('.more-information'), 'mouseleave', function(event) {
                        Interface.tooltipOpen = false;
                        Interface.closeTooltip();
                    });
                }
            }

            var date_range_results = results.date_range.results;

            for (var point in date_range_results) {
                Interface.drawModelDataPoint(new vdifn.map.ModelDataPoint(
                    point,
                    new google.maps.LatLng(date_range_results[point].latitude, date_range_results[point].longitude),
                    date_range_results[point].severity
                ));
            }
        });

        Interface.modelChanged = false;
    });

    google.maps.event.addDomListener(document.getElementById('crop-select'), 'change', function(event) {
        if (Interface.crop !== this.value) {
            Interface.crop = this.value;
            Interface.modelChanged = true;
            Interface.modelChangedHandler();
        }
    });

    google.maps.event.addDomListener(document.getElementById('infliction-select'), 'change', function(event) {
        if (Interface.infliction !== this.value) {
            Interface.infliction = this.value;
            Interface.modelChanged = true;
            Interface.modelChangedHandler();
        }
    });

    google.maps.event.addDomListener(document.getElementById('infliction-select-information'), 'mouseenter', function(event) {
        var content = document.createElement('div');
        content.innerHTML = vdifn.infliction_descriptions[document.getElementById('infliction-select').value];
        Interface.openTooltip(document.getElementById('infliction-select-information'), content, { 'top': 7, 'left': 1, 'max-width': '600px' });
    });

    google.maps.event.addDomListener(document.getElementById('infliction-select-information'), 'mouseleave', function(event) {
        Interface.tooltipOpen = false;
        Interface.closeTooltip();
    });

    google.maps.event.addDomListener(document.getElementById('datepicker-start-information'), 'mouseenter', function(event) {
        var content = document.createElement('div');
        content.innerHTML = 'Date of Emergence/Last Fungicide Application';
        Interface.openTooltip(document.getElementById('datepicker-start-information'), content, { 'top': 7, 'left': 1, 'max-width': '200px' });
    });

    google.maps.event.addDomListener(document.getElementById('datepicker-start-information'), 'mouseleave', function(event) {
        Interface.tooltipOpen = false;
        Interface.closeTooltip();
    });

    google.maps.event.addDomListener(document.getElementById('datepicker-end-information'), 'mouseenter', function(event) {
        var content = document.createElement('div');
        content.innerHTML = 'Date through which disease severity values are accumulated';
        Interface.openTooltip(document.getElementById('datepicker-end-information'), content, { 'top': 7, 'left': 1, 'max-width': '200px' });
    });

    google.maps.event.addDomListener(document.getElementById('datepicker-end-information'), 'mouseleave', function(event) {
        Interface.tooltipOpen = false;
        Interface.closeTooltip();
    });

    google.maps.event.addDomListener(document.getElementById('tomcast-information'), 'mouseenter', function(event) {
        var content = document.createElement('div');
        content.innerHTML = 'Disease forecasting model (adapted from a tomato disease model) used to predict the development of carrot foliar blights caused by <em>Alternaria</em> and <em>Cercospora</em> fungi, based on an accumulation of DSVs from past temperature and leaf wetness data combined with forecasted weather conditions.';
        Interface.openTooltip(document.getElementById('tomcast-information'), content, { 'arrow': 'right' });
    });

    google.maps.event.addDomListener(document.getElementById('tomcast-information'), 'mouseleave', function(event) {
        Interface.tooltipOpen = false;
        Interface.closeTooltip();
    });

    google.maps.event.addDomListener(document.getElementById('blitecast-information'), 'mouseenter', function(event) {
        var content = document.createElement('div');
        content.innerHTML = 'Disease forecasting model used to predict the development of late blight of potato/tomato caused by <em>Phytophthora infestans</em>, based on an accumulation of DSVs, which are generated from air temperature and relative humidity data.';
        Interface.openTooltip(document.getElementById('blitecast-information'), content, { 'arrow': 'right' });
    });

    google.maps.event.addDomListener(document.getElementById('blitecast-information'), 'mouseleave', function(event) {
        Interface.tooltipOpen = false;
        Interface.closeTooltip();
    });

    google.maps.event.addDomListener(document.getElementById('disease-forecasting-information'), 'mouseenter', function(event) {
        var content = document.createElement('div');
        content.innerHTML = 'A plant disease management system that uses computer-based models to collect field weather data and predict the onset and potential severity of crop diseases. Current and forecasted weather conditions determine the risk for disease, and prompts disease management decisions (preventative pesticide applications).';
        Interface.openTooltip(document.getElementById('disease-forecasting-information'), content, { 'arrow': 'right' });
    });

    google.maps.event.addDomListener(document.getElementById('disease-forecasting-information'), 'mouseleave', function(event) {
        Interface.tooltipOpen = false;
        Interface.closeTooltip();
    });

    google.maps.event.addDomListener(window, 'resize', Interface.resize.bind(Interface));
    google.maps.event.addListenerOnce(Interface.map, 'tilesloaded', Interface.tilesloaded.bind(Interface));
    google.maps.event.addDomListener(document.getElementById('error-button'), 'click', Interface.closeErrorOverlay.bind(Interface));
    google.maps.event.addDomListener(document.getElementById('message-overlay-close'), 'click', Interface.closeMessageOverlay.bind(Interface));

    // Initialization
    google.maps.event.trigger(window, 'resize');
    Interface.registerInflictionSelectHandler(
        document.getElementById('crop-select'),
        document.getElementById('infliction-select')
    );

    google.maps.event.trigger(document.getElementById('crop-select'), 'change');
    google.maps.event.trigger(document.getElementById('infliction-select'), 'change');
    google.maps.event.trigger(document.getElementById('select'), 'click');
    // Interface.drawUserLinks();
    Interface.drawStations();
})(window);
