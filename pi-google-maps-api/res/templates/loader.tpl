function setCenter() {map.setCenter(new GLatLng(<?=$PARAMS['latlngCentre'];?>));}
function load() {
    if (GBrowserIsCompatible()) {
	map = new GMap2(document.getElementById("<?=$PARAMS['googleMapId'];?>"));
	geocoder = new GClientGeocoder();
	map.setCenter(new GLatLng(<?=$PARAMS['latlngCentre'];?>),<?=$PARAMS['zoom'];?>);
	map.setUIToDefault();
	GEvent.addListener(map,"click",function(overlay,latlng) { if (latlng) { current_lat=latlng.lat();current_lng=latlng.lng(); }});
            <?php
        echo $MARKERS;
        if($PARAMS['useClusterer']) {
            echo 'var markerCluster = new MarkerClusterer(map, gmarkers,{gridSize: '.$PARAMS['gridSize'].', maxZoom: '.$PARAMS['maxZoom'].'});';
        }
            ?>
        <?=$PARAMS['afterLoad'];?>
    }
}
load();
