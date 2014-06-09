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
    new vdifn.db(new crossfilter()),
    new Pikaday({
        defaultDate: Date.create(),
        setDefaultDate: true,
        field: document.getElementById('datepicker'),
        format: 'MMMM D, YYYY',
        minDate: Date.create('April 16, 2014'),
        maxDate: Date.create('2 days from today'),
        onSelect: function(date) {
            Interface.openLoadingOverlay();
            Interface.drawDay(date.format('{yyyy}{MM}{dd}'), function(success) {
                Interface.closeLoadingOverlay();

                if (!success) {
                    Interface.openErrorOverlay("Could not load weather data for this day.");
                }
            });
        },
        onClose: function() {
            this.config().field.blur();
        }
    })
);

// Events
if (vdifn.parameters.debug) {
    google.maps.event.addListener(Interface.map, 'click', function(event) {
        console.log('new google.maps.LatLng' + event.latLng.toString());
    });
}

google.maps.event.addDomListener(window, 'resize', Interface.resize.bind(Interface));
google.maps.event.addListenerOnce(Interface.map, 'tilesloaded', Interface.tilesloaded.bind(Interface));
google.maps.event.addDomListener(document.getElementById('error-button'), 'click', Interface.closeErrorOverlay.bind(Interface));
google.maps.event.trigger(window, 'resize');

// Initialization
Interface.drawDay(Interface.picker.getDate().format('{yyyy}{MM}{dd}'));
