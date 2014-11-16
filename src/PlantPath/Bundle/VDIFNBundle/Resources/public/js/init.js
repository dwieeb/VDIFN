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
        defaultDate: Date.create(),
        field: document.getElementById('datepicker-start'),
        onSelect: function() {
            var ultimateMaxDate = Interface.endPicker.config().defaultDate;
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
        }
    });

    Interface.endPicker = vdifn.datepicker.create({
        defaultDate: Date.create('2 days from now'),
        minDate: Interface.startPicker.getDate(),
        field: document.getElementById('datepicker-end')
    });

    // Events
    if (vdifn.parameters.debug) {
        google.maps.event.addListener(Interface.map, 'click', function(event) {
            console.log('new google.maps.LatLng' + event.latLng.toString());
        });
    }

    google.maps.event.addDomListener(document.getElementById('datepicker-select'), 'click', function(event) {
        Interface.drawDateRange(Interface.startPicker.getDate(), Interface.endPicker.getDate(), function(success) {
            Interface.closeLoadingOverlay();

            if (!success) {
                Interface.openErrorOverlay("Could not load weather data for the date range specified.");
            }
        });
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

    [
        {
            element: document.getElementById('dsv-very-high').getElementsByTagName('div')[0],
            tooltip: 'Very high likelihood of disease<br />(accumulated DSVs &ge; 20)'
        },
        {
            element: document.getElementById('dsv-high').getElementsByTagName('div')[0],
            tooltip: 'High likelihood of disease<br />(15 &le; accumulated DSVs &lt; 20)'
        },
        {
            element: document.getElementById('dsv-medium').getElementsByTagName('div')[0],
            tooltip: 'Medium likelihood of disease<br />(10 &le; accumulated DSVs &lt; 15)'
        },
        {
            element: document.getElementById('dsv-low').getElementsByTagName('div')[0],
            tooltip: 'Low likelihood of disease<br />(5 &le; accumulated DSVs &lt; 10)'
        },
        {
            element: document.getElementById('dsv-very-low').getElementsByTagName('div')[0],
            tooltip: 'Very low likelihood of disease<br />(0 &le; accumulated DSVs &lt; 5)'
        }
    ].forEach(function(severity) {
        google.maps.event.addDomListener(severity.element, 'mouseover', function(event) {
            var content = document.createElement('div');
            content.innerHTML = severity.tooltip;
            Interface.openTooltip(severity.element, content);
        });

        google.maps.event.addDomListener(severity.element, 'mouseout', function(event) {
            Interface.closeTooltip();
        });
    });

    google.maps.event.addDomListener(window, 'resize', Interface.resize.bind(Interface));
    google.maps.event.addListenerOnce(Interface.map, 'tilesloaded', Interface.tilesloaded.bind(Interface));
    google.maps.event.addDomListener(document.getElementById('error-button'), 'click', Interface.closeErrorOverlay.bind(Interface));

    // Initialization
    google.maps.event.trigger(window, 'resize');
    google.maps.event.trigger(document.getElementById('datepicker-select'), 'click');
    Interface.drawStations();
})();
