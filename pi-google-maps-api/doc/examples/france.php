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
    <h1>Cliquer sur un département pour faire une recherche sur Wikipedia</h1>
    <div id="global">
      <div id="map">
        <?php

          $deps=array("1"=>"Ain","2"=>"Aisne","3"=>"Allier","4"=>"Alpes-de-Haute-Provence","5"=>"Hautes-Alpes","6"=>"Alpes-Maritimes","7"=>"Ardèche","8"=>"Ardennes","9"=>"Ariège","10"=>"Aube","11"=>"Aude","12"=>"Aveyron","13"=>"Bouches-du-Rhône","14"=>"Calvados","15"=>"Cantal","16"=>"Charente","17"=>"Charente-Maritime","18"=>"Cher","19"=>"Corrèze","21"=>"Côte-d'Or","22"=>"Côtes-d'Armor","23"=>"Creuse","24"=>"Dordogne","25"=>"Doubs","26"=>"Drôme","27"=>"Eure","28"=>"Eure-et-Loir","29"=>"Finistère","30"=>"Gard","31"=>"Haute-Garonne","32"=>"Gers","33"=>"Gironde","34"=>"Hérault","35"=>"Ille-et-Vilaine","36"=>"Indre","37"=>"Indre-et-Loire","38"=>"Isère","39"=>"Jura","40"=>"Landes","41"=>"Loir-et-Cher","42"=>"Loire","43"=>"Haute-Loire","44"=>"Loire-Atlantique","45"=>"Loiret","46"=>"Lot","47"=>"Lot-et-Garonne","48"=>"Lozère","49"=>"Maine-et-Loire","50"=>"Manche","51"=>"Marne","52"=>"Haute-Marne","53"=>"Mayenne","54"=>"Meurthe-et-Moselle","55"=>"Meuse","56"=>"Morbihan","57"=>"Moselle","58"=>"Nièvre","59"=>"Nord","60"=>"Oise","61"=>"Orne","62"=>"Pas-de-Calais","63"=>"Puy-de-Dôme","64"=>"Pyrénées-Atlantiques","65"=>"Hautes-Pyrénées","66"=>"Pyrénées-Orientales","67"=>"Bas-Rhin","68"=>"Haut-Rhin","69"=>"Rhône","70"=>"Haute-Saône","71"=>"Saône-et-Loire","72"=>"Sarthe","73"=>"Savoie","74"=>"Haute-Savoie","75"=>"Paris","76"=>"Seine-Maritime","77"=>"Seine-et-Marne","78"=>"Yvelines","79"=>"Deux-Sèvres","80"=>"Somme","81"=>"Tarn","82"=>"Tarn-et-Garonne","83"=>"Var","84"=>"Vaucluse","85"=>"Vendée","86"=>"Vienne","87"=>"Haute-Vienne","88"=>"Vosges","89"=>"Yonne","90"=>"Territoire de Belfort","91"=>"Essonne","92"=>"Hauts-de-Seine","93"=>"Seine-Saint-Denis","94"=>"Val-de-Marne","95"=>"Val-d'Oise","971" => "Guadeloupe","972" => "Martinique","973" => "Guyane","974" => "La Réunion","2A"=>"Corse-du-Sud","2B"=>"Haute-Corse");
          require_once('../../GoogleMapsAPI.class.php');

       $gmap = new GoogleMapsAPI('ABQIAAAAz7Xbm_WTkGpNU7kyMc1gghS3lcuyex_8Fgp7wndALVTrLQXUHBSpiUS5eUwxq6wOiCz4YtdnlMuOvA');
       /* $gmap->useCache('-simple',900); // ¡¡ NEED APC MODULE !! */
       $gmap->setDivId('test1');
       $gmap->setDirectionDivId('route');
       $gmap->setCenterByAddress('France');
       $gmap->setDisplayDirectionFields(false);
       $gmap->setSize(600,600);
       $gmap->setZoom(6);
       $gmap->setDefaultHideMarker(false);

for ($i = 1; $i < 95; $i++) {
  if($i != 20)  {
    include('../../res/departments_fr/'.$i.'/contour.php');
    $gmap->addPolygonByCoords($coords,'polygon'.$i,TRUE,
        '{color:\'#FFAA88\',opacity:0.2}',
        '{color:\'#000000\',opacity:0.5,weight:2}',
        'GEvent.addListener(THEPOLYGON,"click",function(){THEPOLYGON.setFillStyle({color:\'#FF0000\'});window.open("http://fr.wikipedia.org/w/index.php?title=Sp%C3%A9cial%3ARecherche&search=d%C3%A9partement+'.urlencode($deps["$i"]).'","popwikipedia","menubar=no, status=no, scrollbars=yes, menubar=no, width=800, height=100");});');
  }
}

  $gmap->generate();
  echo $gmap->getGoogleMap();

        ?>
      </div>
    </div>
    <p style="clear:both">©2010 <a href="http://www.piprime.fr/">PIPRIME.FR</a></p>
    <p>Carte Généré avec <a href="http://svn.piprime.fr/listing.php?repname=pi-google-maps-api&path=%2Ftrunk%2F">pi-google-maps-api</a></p>
    <p>Voir <a href="?source">le code source PHP</a></p>
    <p>Les données définissant les frontières des départements ont été excrètes des fichiers fournis par l'excellent site « <a href="http://www.gitesdegaule.fr/KaraMeLise/">Gites de Gaule</a> ».</p>
    <p>
        <a href="http://validator.w3.org/check?uri=referer"><img
        src="http://www.w3.org/Icons/valid-xhtml10-blue"
        alt="Valid XHTML 1.0 Strict" height="31" width="88" /></a>
    </p>
  </body>
</html>
