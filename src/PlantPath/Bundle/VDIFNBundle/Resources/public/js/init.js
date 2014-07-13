// Setup
var Interface = new vdifn.Interface(
    new google.maps.Map(document.getElementById('map-canvas'), {
        center: new google.maps.LatLng(45.05024026979463, -90.274658203125),
        mapTypeControl: true,
        mapTypeControlOptions: {
            mapTypeIds: [google.maps.MapTypeId.TERRAIN, google.maps.MapTypeId.HYBRID]
        },
        mapTypeId: google.maps.MapTypeId.TERRAIN,
        maxZoom: 12,
        minZoom: 6,
        streetViewControl: false,
        zoom: 7
    }),
    new vdifn.db()
);

(function() {
    Interface.startPicker = vdifn.datepicker.create({
        defaultDate: Date.create('3 days ago'),
        field: document.getElementById('datepicker-start'),
        onSelect: function() {
            Interface.endPicker.setMinDate(this.getDate());
        }
    });

    Interface.endPicker = vdifn.datepicker.create({
        defaultDate: Date.create(),
        minDate: Interface.startPicker.getDate(),
        field: document.getElementById('datepicker-end')
    });

    var onDateRangeSelect = function(event) {
        Interface.drawDateRange(Interface.startPicker.getDate(), Interface.endPicker.getDate(), onDateRangeDataLoad);
    };

    var onDateRangeDataLoad = function(success) {
        Interface.closeLoadingOverlay();

        if (!success) {
            Interface.openErrorOverlay("Could not load weather data for the date range specified.");
        }
    };

    // Events
    if (vdifn.parameters.debug) {
        google.maps.event.addListener(Interface.map, 'click', function(event) {
            console.log('new google.maps.LatLng' + event.latLng.toString());
        });
    }

    google.maps.event.addDomListener(document.getElementById('datepicker-select'), 'click', onDateRangeSelect);
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
    google.maps.event.trigger(window, 'resize');

    // Initialization
    onDateRangeSelect();
    Interface.drawStations();
})();
