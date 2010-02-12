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
          margin-left: auto;
          margin-right: auto;
          width: 800px;
      }
      #global {
          text-align: center;
          margin-left: auto;
          margin-right: auto;
      }
      #test1 {margin-left:auto;margin-right:auto;}
    </style>
    <title>Example generated with GoogleMapsAPI.class.php</title>
    <script type="text/javascript" src="../../res/js/tooltip.js"></script>
  </head>
  <body onunload="GUnload()">
    <h1>Cliquer sur un département pour faire une recherche sur Wikipédia</h1>
    <div id="global">
      <div id="map">
        <?php

        require_once('../../GoogleMapsAPI.class.php');
       include('../../res/france/info.php');
       $region=$_GET['region'];

       $gmap = new GoogleMapsAPI('ABQIAAAAz7Xbm_WTkGpNU7kyMc1gghS3lcuyex_8Fgp7wndALVTrLQXUHBSpiUS5eUwxq6wOiCz4YtdnlMuOvA');
       /* $gmap->useCache('-simple',900); // ¡¡ NEED APC MODULE !! */
       $gmap->setDivId('test1');
       $gmap->setDirectionDivId('route');
       $gmap->setCenterByAddress($regionsLabel["$region"].' France');
       $gmap->setDisplayDirectionFields(false);
       $gmap->setSize(600,450);
       $gmap->setZoom(7);
       $gmap->setDefaultHideMarker(false);

foreach($regions["$region"] as $departement) {
  $label=$departementsLabel["$departement"];
  include('../../res/france/departements/'.$departement.'/contour.php');
      $gmap->addPolygonByCoords($coords,'polygon'.$departement,TRUE,
                                '{color:\'#FFAA88\',opacity:0.2}',
                                '{color:\'#000000\',opacity:0.5,weight:2}',
                                '
GEvent.addListener(THEPOLYGON,"click",function(){window.open("http://fr.wikipedia.org/w/index.php?title=Sp%C3%A9cial%3ARecherche&search=d%C3%A9partement+'.urlencode($label).'","popwikipedia","menubar=no, status=no, scrollbars=yes, menubar=no, width=800, height=100");});
GEvent.addListener(THEPOLYGON,"mouseover",function(){THEPOLYGON.setFillStyle({color:\'#FF0000\'});tooltip.show("'.$label.'")});
GEvent.addListener(THEPOLYGON,"mouseout",function(){THEPOLYGON.setFillStyle({color:\'#FFAA88\'});tooltip.hide()});');
  }

$gmap->generate();
  echo $gmap->getGoogleMap();

        ?>
      </div>
    </div>
    <div id="tooltip" style="position:absolute;visibility:hidden;background-color:#FFEEC7; border:1px solid black;padding:0.2em;font-size:0.8em;">
    </div>
    <p>Les données définissant les frontières des départements ont été excrètes des fichiers fournis par l'excellent site « <a href="http://www.gitesdegaule.fr/KaraMeLise/">Gites de Gaule</a> ».</p>
    <?php
    include('footer.php');
    ?>
  </body>
</html>
