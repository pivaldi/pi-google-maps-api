<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
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
       $gmap->setCenterByAddress('Nantes France');
       $gmap->setDisplayDirectionFields(true);
       $gmap->setSize(600,600);
       $gmap->setZoom(11);
       $gmap->setDefaultHideMarker(false);

       // cat1
       $coordtab = array();
       $coordtab []= array('47.29273','-1.49139','<strong>html content</strong>');
       $coordtab []= array('47.16357','-1.47354','<strong>html content</strong>');
       $coordtab []= array('47.1822459','-1.545639','<strong>html content</strong>');
       /* $gmap->setIconSize(20,34); */
       $gmap->addArrayMarkerByCoords($coordtab,'cat1', '../../res/images/markers/yellowMarker.png');

       /* $gmap->addMarkerByCoords('47.213971458','-1.556625','<strong>html content</strong>','cat3', */
       /*                           new GIcon('../../res/images/markers/blasons/nantes/image.png',         // image marker */
       /*                                     // All the others params may be omitted */
       /*                                     'X','X',                                      // coords image anchor (x=centered, E=Est, N=Nord etc) */
       /*                                     'x','x',                                      // infoWindowAnchors (x=centered) */
       /*                                     '../../res/images/markers/blasons/nantes/printImage.gif',    // the print image */
       /*                                     '../../res/images/markers/blasons/nantes/mozPrintImage.gif', // the print image for Mozilla */
       /*                                     '../../res/images/markers/blasons/nantes/shadow.png',        // the shadow image */
       /*                                     '../../res/images/markers/blasons/nantes/printShadow.gif',   // the shadow print image */
       /*                                     '../../res/images/markers/blasons/nantes/transparent.png',   // the transparent image */
       /*                                     '')                                           // the image map (not available currently) */
       /*                           ); */

       $gmap->addMarkerByAddress('Nantes France','<strong>html content</strong>','cat3',
            new GIcon('../../res/images/markers/blasons/nantes/image.png',         // image marker
                      // All the others params may be omitted
                      'X','S',                                      // coords image anchor (x=centered, E=Est, N=Nord etc)
                      'x','x',                                      // coords infoWindowAnchors (x=centered)
                      '../../res/images/markers/blasons/nantes/printImage.gif',    // the print image
                      '../../res/images/markers/blasons/nantes/mozPrintImage.gif', // the print image for Mozilla
                      '../../res/images/markers/blasons/nantes/shadow.png',        // the shadow image
                      '../../res/images/markers/blasons/nantes/printShadow.gif',   // the shadow print image
                      '../../res/images/markers/blasons/nantes/transparent.png',   // the transparent image
                      '')                                           // the image map (not available currently)
                                 );

       // cat2
       $coordtab = array();
       $coordtab []= array('saint-herblain france','<strong>html content</strong>');
       $coordtab []= array('bouguenais france','<strong>html content</strong>');
       $coordtab []= array('orvault france','<strong>html content</strong>');
       $gmap->addArrayMarkerByAddress($coordtab,'cat2');


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
          <input type="button" onclick="removeLayerWikipedia();" value="Cacher wikipedia"/>
          <input type="button" onclick="addLayerWikipedia();" value="Afficher wikipedia"/>
          <br />
          <input type="button" onclick="removeLayerPanoramio();" value="Cacher panoramio"/>
          <input type="button" onclick="addLayerPanoramio();" value="Afficher panoramio"/>
          <br />
          <input type="button" onclick="removeTrafficInfo();" value="Cacher traffic"/>
          <input type="button" onclick="addTrafficInfo();" value="Afficher traffic"/>
        </div>
        <span class="titre">Fichiers KML : </span>
        <div class="panel">
          <input type="text" id="xml" value="http://dev.ycerdan.fr/googlemap/kml/departements/44.kml" />
          <input type="button" onclick="addXML(document.getElementById('xml').value);" value="Load this XML"/>
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
  </body>
</html>
