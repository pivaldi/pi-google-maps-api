var map;
var layers;
var gmarkers = [];
var gicons = [];
var clusterer = null;
var current_lat = 0;
var current_lng = 0;
var layer_wikipedia = null;
var layer_panoramio = null;
var trafficInfo = null;
var directions = null;
var geocoder = null;
function createIcon(img,printImg,mozPrintImg,
                    shadowImg,shadowPrintImg,
                    transparentImg,anchorX,anchorY,
                    infoWindowAnchorX,infoWindowAnchorY,
                    iconWidth,iconHeight,
                    shadowWidth,shadowHeight,imgMap) {
    var icon;
    if(img) {
        icon  = new GIcon();
        icon.image = img;
    } else icon  = new GIcon(G_DEFAULT_ICON);
    if(iconWidth && iconHeight) icon.iconSize = new GSize(iconWidth,iconHeight);
    icon.shadow = shadowImg;
    if(shadowWidth && shadowHeight) {
        icon.shadowSize = new GSize(shadowWidth,shadowHeight);
    } else {
        if(!shadowImg) icon.shadowSize = new GSize(0,0);
    }
    if(printImg) icon.printImage = printImg;
    if(mozPrintImg) icon.mozPrintImage = mozPrintImg;
    if(shadowPrintImg) icon.printShadow = shadowPrintImg;
    if(transparentImg) icon.transparent = transparentImg;
    if(anchorX && anchorY) icon.iconAnchor = new GPoint(anchorX,anchorY);
    if(infoWindowAnchorX && infoWindowAnchorY) icon.infoWindowAnchor = new GPoint(infoWindowAnchorX,infoWindowAnchorY);
    if(imgMap) icon.imageMap = '['+imgMap+']';
    return icon;
};

function createMarker(lat,lng,html,category,icon) {
    if (!icon) icon = new GIcon(G_DEFAULT_ICON);
    var marker = new GMarker(new GLatLng(lat,lng),icon);
    marker.mycategory = category;
    <?php if(!$PARAMS['useClusterer']) {echo 'map.addOverlay(marker);'."\n";}?>
    <?php if($PARAMS['displayDirectionFields']) {?>
    // Display direction inputs in the info window
    html += '<div style="clear:both;height:20px;"></div>';
    id_name = 'marker_'+gmarkers.length;
    html += '<input type="text" id="'+id_name+'"/>';
    from = lat+","+lng;
    idpanel = '<?=$PARAMS['googleMapDirectionId'];?>';
    html += '<br /><input type="button" onClick="addDirection(from,document.getElementById(\''+id_name+'\').value,idpanel);" value="Arrivée"/>';
    html += '<input type="button" onClick="addDirection(document.getElementById(\''+id_name+'\').value,from,idpanel);" value="Départ"/>';
    <?php } ?>
    html = '<div style="float:left;text-align:left;width:<?=$PARAMS['infoWindowWidth'];?>;">'+html+'</div>';
    GEvent.addListener(marker,"click",function() {marker.openInfoWindowHtml(html);});
    gmarkers.push(marker);
    <?=$PARAMS['HideMarker'] ? 'marker.hide();' : '';?>
};

function getCurrentLat() {
    return current_lat;
}
function getCurrentLng() {
    return current_lng;
}
function addDirection(from,to,idpanel) {
    directionsPanel = document.getElementById(idpanel);
    if (directions!=null) { directions.clear(); }
    directions = new GDirections(map, directionsPanel);
    directions.load("from: "+from+" to: "+to,"locale: <?=$PARAMS['lang'];?>");
    map.closeInfoWindow();
}
function showCategory(category) {
    for (var i=0; i<gmarkers.length; i++) {
	if (gmarkers[i].mycategory == category) {
	    gmarkers[i].show();
	}
    }
}
function hideCategory(category) {
    for (var i=0; i<gmarkers.length; i++) {
	if (gmarkers[i].mycategory == category) {
	    gmarkers[i].hide();
	}
    }
    map.closeInfoWindow();
}
function hideAll() {
    for (var i=0; i<gmarkers.length; i++) {
	gmarkers[i].hide();
    }
    map.closeInfoWindow();
}
function showAll() {
    for (var i=0; i<gmarkers.length; i++) {
	gmarkers[i].show();
    }
    map.closeInfoWindow();
}
function toggleHideShow(category) {
    for (var i=0; i<gmarkers.length; i++) {
	if (gmarkers[i].mycategory == category) {
	    if (gmarkers[i].isHidden()) gmarkers[i].show();
	    else gmarkers[i].hide();
	}
    }
    map.closeInfoWindow();
}

function toggleXML(id) {
    if (!layers[id].geoXml) {
        var geoXml = new GGeoXml(layers[id].url);
        layers[id].geoXml = geoXml;
        map.addOverlay(layers[id].geoXml);
        // document.getElementById("status").innerHTML = "Loading...";
    } else if (layers[id].geoXml.isHidden()) {
        map.addOverlay(layers[id].geoXml);
        layers[id].geoXml.show();
    } else {
        map.removeOverlay(layers[id].geoXml);
        layers[id].geoXml.hide();
    }
}
function addXML(file) {
    var oXML = new GGeoXml(file);
    map.addOverlay(oXML);
}
function addLayerWikipedia() {
    layer_wikipedia = new GLayer("org.wikipedia.fr");
    map.addOverlay(layer_wikipedia);
}
function removeLayerWikipedia() {
    map.removeOverlay(layer_wikipedia);
}
function addLayerPanoramio() {
    layer_panoramio = new GLayer("com.panoramio.all");
    map.addOverlay(layer_panoramio);
}
function removeLayerPanoramio() {
    map.removeOverlay(layer_panoramio);
}
function addTrafficInfo() {
    var trafficOptions = {incidents:true};
    trafficInfo = new GTrafficOverlay(trafficOptions);
    map.addOverlay(trafficInfo);
}
function removeTrafficInfo() {
    map.removeOverlay(trafficInfo);
}
function showAddress(address) {
    if (geocoder) {
	geocoder.getLatLng(
	    address,
	    function(point) {
		if (!point) { alert(address + " not found"); }
		else {
		    map.setCenter(point, <?=$PARAMS['zoom'];?>);
		}
	    }
	);
    }
}
