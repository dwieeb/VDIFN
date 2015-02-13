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

(function(window) {
    Interface.startPicker = vdifn.datepicker.create({
        defaultDate: (2).daysBefore(vdifn.latest_date),
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
        defaultDate: vdifn.latest_date,
        minDate: Interface.startPicker.getDate(),
        field: document.getElementById('datepicker-end')
    });

    // Events
    if (vdifn.parameters.debug) {
        google.maps.event.addListener(Interface.map, 'click', function(event) {
            console.log('new google.maps.LatLng' + event.latLng.toString());
        });
    }

    google.maps.event.addDomListener(document.getElementById('crop-select'), 'change', function(event) {
        var optgroups = document.getElementById('infliction-select').childNodes;
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

            if (optgroup.id.indexOf(this.value) === 0) {
                optgroup.style.display = 'block';

                if (selected === false) {
                    firstOption.selected = "selected";
                    selected = true;
                }
            } else {
                optgroup.style.display = 'none';
            }
        }
    });

    google.maps.event.addDomListener(document.getElementById('select'), 'click', function(event) {
        Interface.drawDateRange({
            start: Interface.startPicker.getDate(),
            end: Interface.endPicker.getDate(),
            crop: document.getElementById('crop-select').value,
            infliction: document.getElementById('infliction-select').value
        }, function(success) {
            Interface.closeLoadingOverlay();

            if (!success) {
                Interface.openErrorOverlay("Could not load weather data for the date range specified.");
            }
        });

        Interface.stations.forEach(function(station) { station.current = false; });
        Interface.modelDataPoints.forEach(function(modelDataPoint) { modelDataPoint.current = false; });
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
    google.maps.event.addListenerOnce(Interface.map, 'idle', Interface.idle.bind(Interface));
    google.maps.event.addDomListener(document.getElementById('error-button'), 'click', Interface.closeErrorOverlay.bind(Interface));

    // Initialization
    google.maps.event.trigger(window, 'resize');
    google.maps.event.trigger(document.getElementById('crop-select'), 'change');
    google.maps.event.trigger(document.getElementById('select'), 'click');
    Interface.drawStations();
})(window, undefined);
