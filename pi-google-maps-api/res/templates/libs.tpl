/*
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 *
 *  @authors          CERDAN Yohann, Philippe Ivaldi http://www.piprime.fr/
 *  @copyright        (c) 2009  CERDAN Yohann, All rights reserved
 *  @version          Last modified: Fri Feb  5 16:48:29 CET 2010 by Philippe Ivaldi
 */

var map;
var layers;
var gmarkers = [[]];
var clusterer = null;
var currentLat = 0;
var currentLng = 0;
var fixedLayers = {"com.google.webcams":{},"com.panoramio.all":{},"org.wikipedia.ar":{},"org.wikipedia.bg":{},"org.wikipedia.ca":{},"org.wikipedia.cs":{},"org.wikipedia.da":{},"org.wikipedia.de":{},"org.wikipedia.el":{},"org.wikipedia.en":{},"org.wikipedia.es":{},"org.wikipedia.eu":{},"org.wikipedia.fi":{},"org.wikipedia.fr":{},"org.wikipedia.gl":{},"org.wikipedia.he":{},"org.wikipedia.hr":{},"org.wikipedia.hu":{},"org.wikipedia.id":{},"org.wikipedia.it":{},"org.wikipedia.ja":{},"org.wikipedia.lt":{},"org.wikipedia.lv":{},"org.wikipedia.nl":{},"org.wikipedia.nn":{},"org.wikipedia.no":{},"org.wikipedia.pl":{},"org.wikipedia.pt":{},"org.wikipedia.ru":{},"org.wikipedia.sk":{},"org.wikipedia.sl":{},"org.wikipedia.sv":{},"org.wikipedia.th":{},"org.wikipedia.tr":{},"org.wikipedia.uk":{},"org.wikipedia.vi":{}};
var layerWikipedia = new GLayer("org.wikipedia.fr");
var layerPanoramio = null;
var trafficInfo = null;
var directions = null;
var geocoder = null;
var polygons = [];

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

// marker6 = new GMarker(new GLatLng(47.644053,1.59046),{icon:icon6,title:"Centre"});
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
//    GEvent.addListener(marker,'click',function() {marker.openInfoWindowHtml(html);if(tooltip){tooltip.hide();}});
    // GEvent.addListener(marker,'click',function() {alert('PASS');});
//    GEvent.addListener(marker,'infowindowclose',setCenter);
    if(!gmarkers[category]) gmarkers[category] = new Array();
    gmarkers[category].push(marker);
    <?=$PARAMS['HideMarker'] ? 'marker.hide();' : '';?>
    return marker;
};

function getCurrentLat() {
    return currentLat;
}
function getCurrentLng() {
    return currentLng;
}
function addDirection(from,to,idpanel) {
    directionsPanel = document.getElementById(idpanel);
    if (directions!=null) { directions.clear(); }
    directions = new GDirections(map, directionsPanel);
    directions.load("from: "+from+" to: "+to,"locale: <?=$PARAMS['lang'];?>");
    map.closeInfoWindow();
}
function showCategory(category) {
    for (var i=0; i < gmarkers[category].length; i++) {
	gmarkers[category][i].show();
    }
}
function hideCategory(category) {
    for (var i=0; i < gmarkers[category].length; i++) {
	    gmarkers[category][i].hide();
    }
    map.closeInfoWindow();
}
function hideCategories() {
    for (var cat in gmarkers) {
        for (var i=0; i < gmarkers[cat].length; i++) {
	    gmarkers[cat][i].hide();
        }
    }
    map.closeInfoWindow();
}
function showCategories() {
    for (var cat in gmarkers) {
        for (var i=0; i < gmarkers[cat].length; i++) {
	    gmarkers[cat][i].show();
        }
    }
    map.closeInfoWindow();
}
function toggleCategory(category) {
    for (var i=0; i < gmarkers[category].length; i++) {
	if (gmarkers[category][i].isHidden()) {
            gmarkers[category][i].show();
        } else {
            gmarkers[category][i].hide();
        }
    }
    map.closeInfoWindow();
}

function toggleXML(id) {
    if(!layers[id].geoXml) {
        layers[id].geoXml = new GGeoXml(layers[id]['url']);
        map.addOverlay(layers[id].geoXml);
    } else if (layers[id].geoXml.isHidden()) {
        layers[id].geoXml.show();
    } else {
        layers[id].geoXml.hide();
    }
}

function togglePolygon(id) {
    if(!polygons[id].polygon) {
        addPolygon(id);
    } else if (polygons[id].polygon.isHidden()) {
        polygons[id].polygon.show();
    } else {
        polygons[id].polygon.hide();
    }
}

function togglePolygons() {
    for (var id in polygons) {
        togglePolygon(id);
    }
}

function addPolygon(id) {
    var coordinates = [];
    var poly = polygons[id]["coords"];
    for (var i=0; i < poly.length; i++) {
        coordinates[i]=new GLatLng(poly[i][0],poly[i][1]);
    }
    polygons[id].polygon=new google.maps.Polygon(coordinates);
    // polygons[id].polygon=new google.maps.Polygon(coordinates,"#000000", 0.5, 1, "#4d6ecd", 0.2);
    map.addOverlay(polygons[id].polygon);
    if(polygons[id]["fillStyle"]) polygons[id].polygon.setFillStyle(polygons[id]["fillStyle"]);
    if(polygons[id]["strokeStyle"]) polygons[id].polygon.setStrokeStyle(polygons[id]["strokeStyle"]);
}

function hidePolygons() {
    for (var i in polygons) {
        polygons[i].polygon.hide();
    }
}

function addXML(file) {
    var oXML = new GGeoXml(file);
    map.addOverlay(oXML);
}

function toggleLayer(id) {
    if(!fixedLayers[id].gLayer) {
        fixedLayers[id].gLayer = new GLayer(id);
        map.addOverlay(fixedLayers[id].gLayer);
    } else if (fixedLayers[id].gLayer.isHidden()) {
        fixedLayers[id].gLayer.show();
    } else {
        fixedLayers[id].gLayer.hide();
    }
}

function addLayerWikipedia() {
    map.addOverlay(layerWikipedia);
}
function removeLayerWikipedia() {
    map.removeOverlay(layerWikipedia);
}
function addLayerPanoramio() {
    layerPanoramio = new GLayer("com.panoramio.all");
    map.addOverlay(layerPanoramio);
}
function removeLayerPanoramio() {
    map.removeOverlay(layerPanoramio);
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
