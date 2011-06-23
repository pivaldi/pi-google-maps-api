function setCenter() {map.setCenter(new GLatLng(<?=$PARAMS['latlngCentre'];?>));}
function load() {
    if (GBrowserIsCompatible()) {
	map = new GMap2(document.getElementById("<?=$PARAMS['googleMapId'];?>"));
	geocoder = new GClientGeocoder();
	map.setCenter(new GLatLng(<?=$PARAMS['latlngCentre'];?>),<?=$PARAMS['zoom'];?>);
	map.setUIToDefault();
        // map.addControl(new GSmallMapControl());
	GEvent.addListener(map,"click",function(overlay,latlng) { if (latlng) { current_lat=latlng.lat();current_lng=latlng.lng(); }});
            <?php
        echo $MARKERS;
        if($PARAMS['useClusterer']) {
            echo 'for (cat in gmarkers) {';
            echo 'var markerCluster = new MarkerClusterer(map, gmarkers[cat],{gridSize: '.$PARAMS['gridSize'].', maxZoom: '.$PARAMS['maxZoom'].'});';
            echo '}';
        }
            ?>

            <?=$PARAMS['afterLoad'];?>

        var copyright = new GCopyright(1,
                                       new GLatLngBounds(new GLatLng(42.032974332441405, -3.9111328125),
                                                         new GLatLng(51.17934297928927, 10.283203125)),
                                       0,
                                       "©2010 piprime.fr");
        var copyrightCollection = new GCopyrightCollection('piprime.fr');
        copyrightCollection.addCopyright(copyright);
        var tilelayers = [new GTileLayer(copyrightCollection , 3, 11)];
        var custommap = new GMapType(tilelayers, new GMercatorProjection(<?=$PARAMS['zoom'];?>), "piprime.fr", {errorMessage:"No data available"});
        map.addMapType(custommap);
    }
}
load();
