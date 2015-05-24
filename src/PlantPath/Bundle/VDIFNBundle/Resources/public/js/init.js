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
            var maxDate = this.getDate().advance('2 weeks');

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

        Interface.drawDateRange({
            start: Interface.startPicker.getDate(),
            end: Interface.endPicker.getDate(),
            crop: Interface.crop,
            infliction: Interface.infliction
        }, function(success) {
            Interface.closeLoadingOverlay();

            if (!success) {
                Interface.openErrorOverlay("Could not load weather data for the model specified.");
            }
        });

        Interface.drawSeverityLegend();
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

    google.maps.event.addDomListener(document.getElementById('datepicker-start-information'), 'mouseover', function(event) {
        var content = document.createElement('div');
        content.innerHTML = 'Date of Emergence/Last Fungicide Application';
        Interface.openTooltip(document.getElementById('datepicker-start-information'), content);
    });

    google.maps.event.addDomListener(document.getElementById('datepicker-start-information'), 'mouseout', function(event) {
        Interface.closeTooltip();
    });

    google.maps.event.addDomListener(document.getElementById('datepicker-end-information'), 'mouseover', function(event) {
        var content = document.createElement('div');
        content.innerHTML = 'Date through which disease severity values are accumulated';
        Interface.openTooltip(document.getElementById('datepicker-end-information'), content);
    });

    google.maps.event.addDomListener(document.getElementById('datepicker-end-information'), 'mouseout', function(event) {
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
    Interface.drawStations();
})(window);
