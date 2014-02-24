var Interface = new vdifn.Interface(
    new google.maps.Map(document.getElementById('map-canvas'), {
        center: new google.maps.LatLng(44.5278427984555, -89.6484375),
        mapTypeControl: false,
        mapTypeId: google.maps.MapTypeId.HYBRID,
        streetViewControl: false,
        zoom: 7
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
