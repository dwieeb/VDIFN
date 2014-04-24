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
    new vdifn.db(new crossfilter())
);

var picker = new Pikaday({
    defaultDate: Date.create(),
    setDefaultDate: true,
    field: document.getElementById('datepicker'),
    maxDate: Date.create('3 days from today'),
    onSelect: function(date) {
        Interface.drawDay(date.format('{yyyy}{MM}{dd}'))
    }
});

if (vdifn.parameters.debug) {
    google.maps.event.addListener(Interface.map, 'click', function(event) {
        console.log('new google.maps.LatLng' + event.latLng.toString());
    });
}

google.maps.event.addDomListener(window, 'resize', Interface.resize.bind(Interface));
google.maps.event.trigger(window, 'resize');

google.maps.event.addListenerOnce(Interface.map, 'idle', function() {
    Interface.wrapControls();
});

Interface.drawDay(picker.getDate().format('{yyyy}{MM}{dd}'));
