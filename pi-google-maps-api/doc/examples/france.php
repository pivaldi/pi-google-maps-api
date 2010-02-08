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
      }
      #global {
          text-align: center;
          margin-left: auto;
          margin-right: auto;
      }
    </style>
    <title>Example generated with GoogleMapsAPI.class.php</title>
    <script type="text/javascript" src="../../res/js/tooltip.js"></script>
  </head>
  <body onunload="GUnload()">
    <h1>Cliquer sur une région</h1>
    <div id="global">
      <div id="map">
        <?php

       require_once('../../GoogleMapsAPI.class.php');
       include('../../res/france/info.php');
       $gmap = new GoogleMapsAPI('ABQIAAAAz7Xbm_WTkGpNU7kyMc1gghS3lcuyex_8Fgp7wndALVTrLQXUHBSpiUS5eUwxq6wOiCz4YtdnlMuOvA');
       /* $gmap->useCache('-simple',900); // ¡¡ NEED APC MODULE !! */
       $gmap->setDivId('test1');
       $gmap->setDirectionDivId('route');
       $gmap->setCenterByAddress('France');
       $gmap->setDisplayDirectionFields(false);
       $gmap->setSize(750,650);
       $gmap->setZoom(6);
       $gmap->setDefaultHideMarker(false);


foreach ($regionsLabel as $region => $label ) {
      include('../../res/france/regions/'.$region.'/contour-simple.php');
      $gmap->addPolygonByCoords($coords,'polygon'.$region,TRUE,
                                '{color:\'#FFAA88\',opacity:0.2}',
                                '{color:\'#000000\',opacity:0.5,weight:2}',
                                '
GEvent.addListener(THEPOLYGON,"click",function(){window.location ="france-departements.php?region='.$region.'";});
GEvent.addListener(THEPOLYGON,"mouseover",function(){THEPOLYGON.setFillStyle({color:\'#FF0000\'});tooltip.show("'.$label.'")});
GEvent.addListener(THEPOLYGON,"mouseout",function(){THEPOLYGON.setFillStyle({color:\'#FFAA88\'});tooltip.hide()});');
  }

  $gmap->generate();
  echo $gmap->getGoogleMap();

      ?>
      </div>
    </div>
    <p style="clear:both">©2010 <a href="http://www.piprime.fr/">PIPRIME.FR</a></p>
    <p>Carte Généré avec <a href="http://svn.piprime.fr/listing.php?repname=pi-google-maps-api&path=%2Ftrunk%2F">pi-google-maps-api</a></p>
    <p>Voir <a href="?source">le code source PHP</a></p>
    <p><a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10-blue" alt="Valid XHTML 1.0 Strict" height="31" width="88" /></a></p>
    <div id="tooltip" style="position:absolute;visibility:hidden;background-color:#FFEEC7; border:1px solid black;padding:0.2em;font-size:0.8em;">
    </div>
  </body>
</html>
