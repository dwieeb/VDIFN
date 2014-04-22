var Interface = new vdifn.Interface(
    new google.maps.Map(document.getElementById('map-canvas'), {
        center: new google.maps.LatLng(44.5278427984555, -89.6484375),
        mapTypeControl: true,
        mapTypeControlOptions: {
            mapTypeIds: [google.maps.MapTypeId.TERRAIN, google.maps.MapTypeId.HYBRID]
        },
        mapTypeId: google.maps.MapTypeId.TERRAIN,
        maxZoom: 12,
        minZoom: 6,
        panControlOptions: {
            position: google.maps.ControlPosition.RIGHT_TOP
        },
        streetViewControl: false,
        zoom: 7,
        zoomControlOptions: {
            position: google.maps.ControlPosition.RIGHT_TOP
        }
    }),
    new vdifn.db(new crossfilter())
);

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

Interface.drawDay(Date.create().format('{yyyy}{MM}{dd}'));
