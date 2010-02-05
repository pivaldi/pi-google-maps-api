<?php

if(isset($_GET['source'])) {
  highlight_file(__FILE__);
  die;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <style type="text/css">
      body {
      margin: 10px; /* pour eviter les marges */
      text-align: center; /* pour corriger le bug de centrage IE */
      width: 1000px;
      }
      #global {
      text-align: center;
      margin-left: auto;
      margin-right: auto;
      }
      #route {
      height: 130px;
      overflow-y: auto;
      }
      #map {
      float: left;
      }
      #options {
      width: 350px;
      float: left;
      padding: 0 10px 10px 10px;
      text-align: left;
      }
      .panel {
      background-color: #E8ECF9;
      border: 1px dashed black;
      padding: 5px;
      margin: 10px 0 10px 0;
      }
      .titre {
      text-align: left;
      font-weight: bold;
      margin: 0 0 5px 0;
      }

      .inputTxt {
      width: 100px;
      }
    </style>
    <title>Example generated with GoogleMapsAPI.class.php</title>
  </head>
  <body onunload="GUnload()">
    <div id="global">
      <div id="map" onclick="document.getElementById('lat').value=getCurrentLat();document.getElementById('lng').value=getCurrentLng();">
        <?php

       require_once('../../GoogleMapsAPI.class.php');

       $gmap = new GoogleMapsAPI('ABQIAAAAz7Xbm_WTkGpNU7kyMc1gghS3lcuyex_8Fgp7wndALVTrLQXUHBSpiUS5eUwxq6wOiCz4YtdnlMuOvA');
       /* $gmap->useCache('-simple',900); */
       $gmap->setDivId('test1');
       $gmap->setDirectionDivId('route');
       $gmap->setCenterByAddress('Carcassonne France');
       $gmap->setDisplayDirectionFields(true);
       $gmap->setSize(600,600);
       $gmap->setZoom(9);
       $gmap->setDefaultHideMarker(false);

       // cat1
       $coordtab = array();
       $coordtab []= array('43.1986689','2.206192','<strong>html content</strong>');
       $coordtab []= array('43.241201214','2.111434936','<strong>html content</strong>');
       $coordtab []= array('43.13406333787913','2.245330810546875','<strong>html content</strong>');
       /* $gmap->setIconSize(20,34); */
       $gmap->addArrayMarkerByCoords($coordtab,'cat1', '../../res/images/markers/yellowMarker.png');

       /* $gmap->addMarkerByCoords('43.2126828', '2.3572540','<strong>html content</strong>','cat3', */
       /*                           new GIcon('../../res/images/carcassonne/markers/image.png',         // image marker */
       /*                                     // All the others params may be omitted */
       /*                                     'X','X',                                      // coords image anchor (x=centered, E=Est, N=Nord etc) */
       /*                                     'x','x',                                      // infoWindowAnchors (x=centered) */
       /*                                     '../../res/images/carcassonne/markers/printImage.gif',    // the print image */
       /*                                     '../../res/images/carcassonne/markers/mozPrintImage.gif', // the print image for Mozilla */
       /*                                     '../../res/images/carcassonne/markers/shadow.png',        // the shadow image */
       /*                                     '../../res/images/carcassonne/markers/printShadow.gif',   // the shadow print image */
       /*                                     '../../res/images/carcassonne/markers/transparent.png',   // the transparent image */
       /*                                     '')                                           // the image map (not available currently) */
       /*                           ); */

       $gmap->addMarkerByAddress('Carcassonne France','<strong>html content</strong>','cat3',
            new GIcon('../../res/images/carcassonne/markers/image.png',         // image marker
                      // All the others params may be omitted
                      'X','S',                                      // coords image anchor (x=centered, E=Est, N=Nord etc)
                      'x','x',                                      // coords infoWindowAnchors (x=centered)
                      '../../res/images/carcassonne/markers/printImage.gif',    // the print image
                      '../../res/images/carcassonne/markers/mozPrintImage.gif', // the print image for Mozilla
                      '../../res/images/carcassonne/markers/shadow.png',        // the shadow image
                      '../../res/images/carcassonne/markers/printShadow.gif',   // the shadow print image
                      '../../res/images/carcassonne/markers/transparent.png',   // the transparent image
                      '')                                           // the image map (not available currently)
                                 );

       // cat2
       $coordtab = array();
       $coordtab []= array('Limoux france','<strong>html content</strong>');
       $coordtab []= array('Narbonne france','<strong>html content</strong>');
       $coordtab []= array('Castelnaudary france','<strong>html content</strong>');
       $gmap->addArrayMarkerByAddress($coordtab,'cat2');

        // Adding kml layer by url
        $gmap->addLayer("http://piprim.tuxfamily.org/temp/departements/11/contour.kml",
                        'dep44',true); // true = visible, false=not visible


                        $gmap->generate();
                        echo $gmap->getGoogleMap();

        ?>
      </div>
      <div id="options">
        <span class="titre">Informations : </span>
        <div class="panel">
          Lat : <input type="text" id="lat" class="inputTxt" onclick="" value=""/>
          Lng : <input type="text" id="lng" class="inputTxt" onclick="" value=""/>
        </div>
        <span class="titre">Gestion des marqueurs : </span>
        <div class="panel">
          <input type="button" onclick="showCategory('cat1');" value="Afficher catégories 1"/>
          <input type="button" onclick="hideCategory('cat1');" value="Cacher catégories 1"/>
          <br />
          <input type="button" onclick="showCategory('cat2');" value="Afficher catégories 2"/>
          <input type="button" onclick="hideCategory('cat2');" value="Cacher catégories 2"/>
        </div>
        <span class="titre">Extras : </span>
        <div class="panel">
          <input type="button" onclick="toggleLayer('org.wikipedia.fr');" value="Basculer wikipedia"/>
          <br />
          <input type="button" onclick="toggleLayer('com.google.webcams');" value="Basculer les WebCams"/>
          <br />
          <input type="button" onclick="toggleLayer('com.panoramio.all');" value="Basculer les Panoramio"/>
          <br />
          <input type="button" onclick="addTrafficInfo();" value="Afficher traffic"/>
        </div>
        <span class="titre">Fichiers KML : </span>
        <div class="panel">
          <input type="button" onclick="toggleXML('dep44');" value="Basculer le KML"/>
        </div>
        <span class="titre">Itinéraires : </span>
        <div class="panel">
          <input type="text" id="from" value="nantes" class="inputTxt" /> à <input type="text" id="to" value="paris" class="inputTxt" />
          <input type="button" onclick="addDirection(document.getElementById('from').value,document.getElementById('to').value,'route');" value="Rechercher"/>
        </div>
        <span class="titre">Résultat de l'itinéraire : </span>
        <div id="route" class="panel" style="padding: 5px;"></div>
        <div style="clear:both;"></div>
      </div>
    </div>
    <p style="clear:both">©2010 <a href="http://www.piprime.fr/">PIPRIME.FR</a></p>
    <p>Carte Généré avec <a href="http://svn.piprime.fr/listing.php?repname=pi-google-maps-api&path=%2Ftrunk%2F">pi-google-maps-api</a></p>
    <p>Voir <a href="?source">le code source PHP</a></p>
     <p>
        <a href="http://validator.w3.org/check?uri=referer"><img
        src="http://www.w3.org/Icons/valid-xhtml10-blue"
        alt="Valid XHTML 1.0 Strict" height="31" width="88" /></a>
    </p>
  </body>
</html>
