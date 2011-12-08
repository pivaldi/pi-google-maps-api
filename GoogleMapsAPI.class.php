<?php

if(isset($_GET['source'])) {
  highlight_file(__FILE__);
  die;
}

/*
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 *
 *  @authors          CERDAN Yohann <cerdanyohann@yahoo.fr>, Philippe Ivaldi http://www.piprime.fr/
 *  @copyright        (c) 2009  CERDAN Yohann, All rights reserved
 *  @version          Last modified: Fri Feb  5 16:48:29 CET 2010 by Philippe Ivaldi
 */


require_once(dirname(__FILE__).'/SimpleTemplate.class.php');

/* Define the googleMapsAPI install directory */
define('GMA_PATH','/var/www/guideregional/typo3conf/ext/pi_annuaire/googlemapsapi/pi-google-maps-api/');
/* define('GMA_REL_PATH', str_replace(array(DIRECTORY_SEPARATOR,'//'), */
/*                                    '/', */
/*                                    str_replace($_SERVER['DOCUMENT_ROOT'], */
/*                                                '', */
/*                                                GMA_PATH_thisScript))); */

/* define('GMA_CUR_PATH', dirname(str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):($_SERVER['ORIG_SCRIPT_FILENAME']?$_SERVER['ORIG_SCRIPT_FILENAME']:$_SERVER['SCRIPT_FILENAME'])))).'/'); */



class GIcon {

  public $icon = array();

  private function _getImageInfo($img) {
    return getimagesize($img);
    /*         $_img = strpos($img,'http') === 0 ? $img : $_SERVER['DOCUMENT_ROOT'].$img; */
    /*         if(!($imgInfo = @getimagesize($_img))) { */
    /*             trigger_error('_getImageInfo: error reading image: '.$_img, E_USER_ERROR); */
    /*         } */
    /*         return $imgInfo; */
  }

  private function _dirToPixel($flag,$n) {
    if(is_numeric($flag)) return $flag;
    $flag = strtolower($flag);
    if($flag === 'x') { // Centered
      return $n/2;
    } elseif($flag === 'w' || $flag == 's') {// West or North
      return $n;
    } elseif($flag === 'e'|| $flag == 'n') {// Est or South
      return 0;
    }
  }


  /**
   * Generate an array of params for a new marker icon image
   * iconShadowImage is optional
   * If anchor coords are not supplied, we use the center point of the image by default.
   * Can be called statically.
   * This is a modified version of createMarkerIcon from:
   * $Id: GoogleMapAPI.class.php,v 1.63 2007/08/03 16:29:40 mohrt Exp $
   * http://www.phpinsider.com/php/code/GoogleMapAPI/
   *
   * @param string $img URI to icon image: 24-bit PNG image with alpha transparency
   * @param string $anchorX X coordinate for icon anchor point
   * @param string $anchorY Y coordinate for icon anchor point
   * @param string $infoWindowAnchorX X coordinate for info window anchor point
   * @param string $infoWindowAnchorY Y coordinate for info window anchor point
   * @param string $printImg URI to an alternate foreground icon image used for printing on browsers incapable of handling the main foreground image. Versions of IE typically require an alternative image in these cases as they cannot print the icons as transparent PNGs. Note that browsers capable of printing the main foreground image ignore this property. Transparent GIF.
   * @param string $mozPrintImg URI to an alternate non-transparent icon image used for printing on browsers incapable of handling either transparent PNGs or transparent GIFs. Older versions of Firefox/Mozilla typically require non-transparent images for printing. Note that browsers capable of printing the main foreground image will ignore this property. Non transparent GIF with a light grey background.
   * @param string $shadowImg URI to shadow image: 24-bit PNG image with alpha transparency.
   * @param string $shadowPrintImg URI to a shadow image used for printed maps. This is a GIF image since most browsers cannot print PNG images. Transparent GIF with a chequered pattern of grey pixels for the shadow area.
   * @param string $transparentImg URI to a virtually transparent version of the foreground icon image used to capture click events in Internet Explorer. This image is a 24-bit PNG version of the main foreground image with 1% opacity, but of the same shape and size.
   * @param $imgMap represents the image map area as defined by an array of x,y pixel coordinates. Used for capturing image clicks in non IE browsers.
   */

  public function GIcon($img,
                        $anchorX = 'x', $anchorY = 's',
                        $infoWindowAnchorX = 'x', $infoWindowAnchorY = 'x',
                        $printImg='', $mozPrintImg='',
                        $shadowImg='', $shadowPrintImg='',
                        $transparentImg='',$imgMap='') {

    $imgInfo = $this->_getImageInfo($img);
    if($shadowImg) {
      $shadowImgInfo = $this->_getImageInfo($shadowImg);
    }

    $anchorX = $this->_dirToPixel($anchorX, $imgInfo[0]);
    $anchorY = $this->_dirToPixel($anchorY, $imgInfo[1]);
    $infoWindowAnchorX = $this->_dirToPixel($infoWindowAnchorX, $imgInfo[0]);
    $infoWindowAnchorY = $this->_dirToPixel($infoWindowAnchorY, $imgInfo[1]);

    $iconInfo = array('img'               => (string) $img,
                      'iconWidth'         => (int) $imgInfo[0],
                      'iconHeight'        => (int) $imgInfo[1],
                      'printImg'          => (string) $printImg,
                      'mozPrintImg'       => (string) $mozPrintImg,
                      'shadowImg'         => (string) $shadowImg,
                      'shadowWidth'       => (int) $shadowImgInfo[0],
                      'shadowHeight'      => (int) $shadowImgInfo[1],
                      'shadowPrintImg'    => (string) $shadowPrintImg,
                      'transparentImg'    => (string) $transparentImg,
                      'anchorX'           => (int) $anchorX,
                      'anchorY'           => (int) $anchorY,
                      'infoWindowAnchorX' => (int) $infoWindowAnchorX,
                      'infoWindowAnchorY' => (int) $infoWindowAnchorY,
                      'imgMap'          => (string) $imgMap);
    $this->icon = $iconInfo;
  }

}

class GoogleMapsAPI
{
  /** GoogleMap key **/
  private $googleMapKey = '';

  /** GoogleMap ID for the HTML DIV  **/
  private $googleMapId = 'googlemapapi';

  /** GoogleMap  Direction ID for the HTML DIV **/
  private $googleMapDirectionId = 'route';

  /** Width of the gmap **/
  private $width = 0;

  /** Height of the gmap **/
  private $height = 0;

  /** Infowindow width of the gmarker **/
  private $infoWindowWidth = 250;

  /** Defautl zoom of the gmap **/
  private $zoom = 9;

  /** Lang of the gmap **/
  private $lang = 'fr';

  /**Center of the gmap **/
  private $center = 'Paris France';
  private $needGeocoding = TRUE;

  /** Content of the HTML generated **/
  private $content = '';

  /** Add the direction button to the infowindow **/
  private $displayDirectionFields = FALSE;

  /** Hide the marker by default **/
  private $defaultHideMarker = FALSE;

  /** Layers of the gmap **/
  /* Structure is {'int id' => {'string url','string name','boolean visible'} */
  private $layers = array();

  /** explicit polygons on the gmap **/
  /* Structure is
     array(array('name'     => string,
     'coords'  => array(),
     'fillStyle'=>string,   See http://code.google.com/intl/fr/apis/maps/documentation/reference.html#GPolyStyleOptions
     'strokeStyle'=>string, See http://code.google.com/intl/fr/apis/maps/documentation/reference.html#GPolyStyleOptions
     'visible'=>boolean),
     'callBack'=>string) */
  /* callBack is some JavaScript code
     in which the word THEPOLYGON (in uper case) will be replaced by the corresponding polygon */
  private $polygons = array();

  /**
     Icons shared by several markers
  **/
  private $sharedIcons = array();

  /** Markers by coords
      array of array('latitude','longitude','html','category','icon','callBack')
      where
      * ['icon'] = array('image','iconWidth','iconHeight','iconAnchorX',
        'iconAnchorY','infoWindowAnchorX','infoWindowAnchorY',
        'shadowWidth','shadowHeight');
        Use the method $this->newIcon(...) to create it

      * callBack is some JavaScript code in which the word THEMARKER (in uper case) will be replaced by
        the corresponding marker
  **/
  public $markersByCoords = array();

  /** Markers by address **/
  // array of array('address','content','category','icon');
  public $markersByAddress = array();

  /** Use clusterer to display a lot of markers on the gmap **/
  private $useClusterer = FALSE;
  private $gridSize = 100;
  private $maxZoom = 9;
  private $clustererLibrary;
  private $contextMenuControlLibrary;


  /** Cache params**/
  public $useCache = FALSE;
  public $cacheParams = array('libs'    => array('name' => 'GMA-LIBS',
                                                 'ttl'  => 3600),
                              'loader' => array('name' => 'GMA-LOADER',
                                                'ttl'  => 3600)
                              );

  /**
   * Class constructor
   *
   * @param string $googleMapKey the googleMapKey
   *
   * @return void
   */

  public function __construct($googleMapKey='')
  {
    $this->googleMapKey = $googleMapKey;
  }

  public function __destruct() {
    // Nothing to do...
  }


  /**
   * Set the key of the gmap
   *
   * @param string $googleMapKey the googleMapKey
   *
   * @return void
   */

  public function setKey($googleMapKey)
  {
    $this->googleMapKey = $googleMapKey;
  }

  /**
   * Enable and set the cache parameter (use APC)
   *
   * @param string $suffixCacheName suffix of cache name
   *
   * @return array of used cache names
   */

  public function useCache($suffixCacheName='', $ttl=3600)
  {
    if($this->useCache == FALSE) {
      $this->suffixCacheName = $suffixCacheName;
      $this->useCache = TRUE;
      $oCacheParams = array();
      foreach($this->cacheParams as $key => $cacheParam) {
        $this->cacheParams[$key]['name'] = $cacheParam['name'].$suffixCacheName;
        $this->cacheParams[$key]['ttl'] = $ttl;
      }
    }
    return $this->cacheParams;
  }

  /**
   * Enable and set the clusterer parameter (optimization to display a lot of markers)
   *
   * @param string $clusterIcon the cluster icon
   * @param int $maxVisibleMarkers max visible markers
   * @param int $gridSize grid size
   * @param int $minMarkersPerClusterer minMarkersPerClusterer
   * @param int $maxLinesPerInfoBox maxLinesPerInfoBox
   *
   * @return void
   */
  public function useClusterer($clustererLibrary,
                               $gridSize=100, $maxZoom=9)
  {
    /* TODO: update the doc */
    if(!@is_readable($clustererLibrary)) {
      trigger_error('useClusterer: you must specifiy a valid and readable JavaScript file',
                    E_USER_ERROR);
    }
    $this->useClusterer = TRUE;
    $this->gridSize = $gridSize;
    $this->maxZoom = $maxZoom;
    $this->clustererLibrary = $clustererLibrary;
  }

  /**
   * Enable and set the context Menu Control Library
   * http://gmaps-utility-library-dev.googlecode.com/svn/tags/contextmenucontrol/1.0/docs/reference.html
   * @param string $contextMenuControlLibrary: the js lib
   *
   * @return void
   */
  public function useContextMenu($contextMenuControlLibrary)
  {
    if(!@is_readable($contextMenuControlLibrary)) {
      trigger_error('useContextMenu: you must specifiy a valid and readable JavaScript file',
                    E_USER_ERROR);
    }
    $this->contextMenuControlLibrary = $contextMenuControlLibrary;
  }

  /**
   * Set the ID of the default gmap DIV
   *
   * @param string $googleMapId the google div ID
   *
   * @return void
   */

  public function setDivId($googleMapId)
  {
    $this->googleMapId = $googleMapId;
  }

  /**
   * Set the ID of the default gmap direction DIV
   *
   * @param string $googleMapDirectionId GoogleMap  Direction ID for the HTML DIV
   *
   * @return void
   */

  public function setDirectionDivId($googleMapDirectionId)
  {
    $this->googleMapDirectionId = $googleMapDirectionId;
  }

  /**
   * Set the size of the gmap
   *
   * @param int $width GoogleMap  width
   * @param int $height GoogleMap  height
   *
   * @return void
   */

  public function setSize($width, $height)
  {
    $this->width = $width;
    $this->height = $height;
  }

  /**
   * Set the with of the gmap infowindow (on marker clik)
   *
   * @param int $infoWindowWidth GoogleMap  info window width
   *
   * @return void
   */

  public function setInfoWindowWidth ($infoWindowWidth)
  {
    $this->infoWindowWidth = $infoWindowWidth;
  }

  /**
   * Set the lang of the gmap
   *
   * @param string $lang GoogleMap  lang : fr,en,..
   *
   * @return void
   */

  public function setLang($lang)
  {
    $this->lang = $lang;
  }

  /**
   * Set the zoom of the gmap
   *
   * @param int $zoom GoogleMap  zoom.
   *
   * @return void
   */

  public function setZoom($zoom)
  {
    $this->zoom = $zoom;
  }

  /**
   * Set the center of the gmap (an address)
   *
   * @param string $center GoogleMap  center (an address)
   *
   * @return void
   */
  public function setCenterByAddress($center)
  {
    $this->center = $center;
    $this->needGeocoding = TRUE;
  }

  /**
   * Set the center of the gmap (an address)
   *
   * @param real $latitude
   * @param real $longitude
   *
   * @return void
   */
  public function setCenterByCoods($latitude, $longitude)
  {
    $this->center = $latitude.','.$longitude;
    $this->needGeocoding = FALSE;
  }

  /**
   * Set the center of the gmap
   *
   * @param boolean $displayDirectionFields display directions or not in the info window
   *
   * @return void
   */

  public function setDisplayDirectionFields($displayDirectionFields)
  {
    $this->displayDirectionFields = $displayDirectionFields;
  }

  /**
   * Set the defaultHideMarker
   *
   * @param boolean $defaultHideMarker hide all the markers on the map by default
   *
   * @return void
   */

  public function setDefaultHideMarker($defaultHideMarker)
  {
    $this->defaultHideMarker = $defaultHideMarker;
  }

  /**
   * Get the google map content
   *
   * @return string the google map html code
   */

  public function getGoogleMap()
  {
    return $this->content;
  }

  /**
   * Get URL content using cURL.
   *
   * @param string $url the url
   *
   * @return string the html code
   *
   * @todo add proxy settings
   */

  private function getContent($url)
  {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_URL, $url);
    $data = curl_exec($curl);
    curl_close ($curl);
    return $data;
  }

  /**
   * Geocoding an address (address -> lat,lng)
   *
   * @param string $address an address
   *
   * @return array array with precision, lat & lng
   */

  public function geocoding($address)
  {
    $encodeAddress = urlencode($address);
    $url = "http://maps.google.com/maps/geo?q=".$encodeAddress."&output=csv&key=".$this->googleMapKey;

    if(function_exists('curl_init')) {
      $data = $this->getContent($url);
    } else {
      $data = file_get_contents($url);
    }

    $csvSplit = split(",", $data);
    $status = $csvSplit[0];

    if (strcmp($status, "200") == 0) {
      $return = $csvSplit; // successful geocode, $precision = $csvSplit[1], $lat = $csvSplit[2], $lng = $csvSplit[3];
    } else {
      $return = null; // failure to geocode
    }

    return $return;
  }

  /**
   * return icons and markers as JavaScript code
   * (no templating here because of code complexity)
   * This is inspired of the method MapJS from:
   * $Id: GoogleMapAPI.class.php,v 1.63 2007/08/03 16:29:40 mohrt Exp $
   * http://www.phpinsider.com/php/code/GoogleMapAPI/
   *
   */
  private function _markersToJS() {
    $_output = 'var icon = [];'."\n";

    // Convert mixed type $icon to usable ressource (array or integer or NULL)
    // Array is real icon, integer is shared icon, NULL is default icon (G_DEFAULT_ICON)
    function _standardizeIcon($icon) {
      if(is_string($icon)) {
        $oIcon=new GIcon($icon);
        return  $oIcon->icon;
      }
      if(is_object($icon)) return $icon->icon;
      if(is_int($icon) || $icon == NULL) return $icon;
      trigger_error('_standardizeIcon: invaldi icon type.', E_USER_ERROR);
    }

    // JS code creating nice icon
    function _getIcon($icon) {
      return "createIcon('{$icon['img']}','{$icon['printImg']}','{$icon['mozPrintImg']}','{$icon['shadowImg']}','{$icon['shadowPrintImg']}','{$icon['transparentImg']}',{$icon['anchorX']},{$icon['anchorY']},{$icon['infoWindowAnchorX']},{$icon['infoWindowAnchorY']},{$icon['iconWidth']},{$icon['iconHeight']},{$icon['shadowWidth']},{$icon['shadowHeight']},'{$icon['imgMap']}');\n";
    }

    $k = 0;
    // Generate the JS icons for shared icons
    for($i = 0, $j = count($this->sharedIcons); $i < $j; $i++) {
      $icon = _standardizeIcon($this->sharedIcons[$i]);
      if(is_int($icon)) {
        trigger_error('_markersToJS: invalid SHARED icon type. Strange error...', E_USER_ERROR);
      }
      // hash the icon data to see further if we've already got this one; if so, save some javascript
      $iconKey = md5(serialize($icon));
      $existIcon[$iconKey] = $i;
      $_output .= "icon[{$i}] = "._getIcon($icon);
      $k++;
    }

    // Generate icons (wich are not shared) and markers
    if(!empty($this->markersByCoords)) {
      for($i = 0, $j = count($this->markersByCoords); $i < $j; $i++) {
        $marker = $this->markersByCoords[$i];
        $icon = _standardizeIcon($marker['icon']);
        if(is_int($icon)) {
          $jsIcon = "icon[{$icon}]";
        } elseif(is_array($icon)) {
          // hash the icon data to see if we've already got this one; if so, save some javascript
          $iconKey = md5(serialize($icon));
          if(!isset($existIcon[$iconKey])) {
            $iconNumber = $i+$k;
            $existIcon[$iconKey] = $iconNumber;
            $_output .= "icon[{$iconNumber}] = "._getIcon($icon);
            $jsIcon = "icon[{$iconNumber}]";
          } else {
            $jsIcon = "icon[{$existIcon[$iconKey]}]";
          }
        } else { // $icon == NULL
          $jsIcon = '\'\'';
        }
        $_output .= "var tmpInstance = createMarker({$marker['latitude']},{$marker['longitude']},'{$marker['html']}','{$marker['category']}',{$jsIcon});\n";
        if($marker['callBack'] != '') {
            /* $_output .= 'alert(gmarkers[\''.$marker['category'].'\'].length-1)'; */
            $_output .= str_replace("THEMARKER",'gmarkers[\''.$marker['category'].'\'][gmarkers[\''.$marker['category'].'\'].length-1]',$marker['callBack'])."\n";
        }
      }
    }
    return $_output;
  }


  /**
   * Add marker by his coords
   *
   * @param string $lat lat
   * @param string $lng lngs
   * @param string $html html code display in the info window
   * @param string $category marker category
   * @param array $icon an icon url
   * @param string $callBack: JS script executed after Initialize the marker object $name
   *        typically used to add events. The string "THEMARKER" in this JS code will be
   *        replaced by the name of the instance of the marker.
   *
   * @return void
   */

  public function addMarkerByCoords($lat, $lng, $html='', $category='', $icon=NULL, $callBack='')
  {
    $this->markersByCoords[] = array('latitude'  => $lat,
                                     'longitude' => $lng,
                                     'html'      => $html,
                                     'category'  => $category,
                                     'icon'      => $icon,
                                     'callBack'  => $callBack);
  }

  /**
   * Add address to the stack of markers by address
   *
   * @param string $address an ddress
   * @param string $content html code display in the info window
   * @param string $category marker category
   * @param array $icon an icon url
   *
   * @return void
   */

  public function addMarkerByAddress($address, $content='', $category='', $icon=NULL)
  {
    $this->markersByAddress[] = array('address'  => $address,
                                      'content'  => $content,
                                      'category' => $category,
                                      'icon'     => $icon);
  }

  /**
   * Add marker by an array of coord
   *
   * @param string $coordtab an array of lat,lng,content
   * @param string $category marker category
   * @param array $icon an icon url
   *
   * @return void
   */

  public function addArrayMarkerByCoords($coordtab, $category='', $icon=NULL)
  {
    $_icon = ($icon != NULL) ? array_push($this->sharedIcons,$icon)-1 : NULL;
    foreach ($coordtab as $coord) {
      $this->addMarkerByCoords($coord[0], $coord[1], $coord[2], $category, $_icon);
    }
  }

  /**
   * Add array of addresses to the stack of markers by address
   *
   * @param array(strings) $addresses an array of addresses
   * @param string $category marker category
   * @param array $icon an icon url
   *
   * @return void
   */

  public function addArrayMarkerByAddress($addresses, $category='', $icon=array())
  {
    $_icon = ($icon != NULL) ? array_push($this->sharedIcons,$icon)-1 : NULL;
    foreach ($addresses as $address) {
      $this->addMarkerByAddress($address[0], $address[1], $category, $_icon);
    }
  }


  /**
   * Add an address to the array markersByCoords. Need geocoding so must be called iff
   * cache is not used or failed.
   *
   * @param  array('address','content','category','icon') $markerByAddress an array of address
   *
   * @return TRUE if it successes else return FALSE
   */
  private function _addMarkerByAddressToMarkersByCoords($markerByAddress)
  {
    $point = $this->geocoding($markerByAddress['address']);
    if($point[0]  == "200") {
      $this->addMarkerByCoords($point[2], $point[3], $markerByAddress['content'],
                               $markerByAddress['category'], $markerByAddress['icon']);
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Add an array of address to the array markersByCoords. Need geocoding.
   * Refer to _addMarkerByAddressToMarkersByCoords
   *
   * @param array(string) &$markersByAddress an array of address
   *
   * @return TRUE if success else return FALSE
   */
  private function _addMarkersByAddressToMarkersByCoords(&$markersByAddress)
  {
    $markerByAddress = array_pop($markersByAddress);
    while($markerByAddress != NULL) {
      $this->_addMarkerByAddressToMarkersByCoords($markerByAddress);
      $markerByAddress = array_pop($markersByAddress);
    }
  }

  /**
   * Set a direction between 2 addresss and set a text panel
   *
   * @param string $from an address
   * @param string $to an address
   * @param string $idpanel id of the div panel
   *
   * @return void
   */

  public function addDirection($from, $to, $idpanel='')
  {
    /* $this->contentMarker .= 'addDirection("'.$from.'","'.$to.'","'.$idpanel.'");'; */
  }

  /**
   * Parse a XML file and add markers to a category
   *
   * @param string $url url of the xml file compatible with gmap and gearth
   * @param string $category marker category
   * @param array $icon an icon url
   *
   * @return void
   */

  public function addXML($url, $category='', $icon='')
  {
    $xml = new SimpleXMLElement($url, null, true);
    foreach ($xml->Document->Folder->Placemark as $item) {
      $coordinates = explode(',', (string) $item->Point->coordinates);
      $name = (string) $item->name;
      $this->addMarkerByCoords($coordinates[1], $coordinates[0], $name, $category, $icon);
    }
  }

  /**
   * Add a layer from an URL
   *
   * @param string $url url of the layer file compatible with gmap and gearth
   * @param string $name name of the layer
   * @param boolean $visible set default visibility
   *
   * @return void
   */

  public function addLayer($url, $name='', $visible=true)
  {
    if($name == '') $name = 'layer'.count($this->layers);
    $this->layers[] = array('url'     => $url,
                            'name'    => $name,
                            'visible' => $visible);
  }

  /**
   * Add a layer from an URL
   *
   * @param string $coords: coords
   * @param string $name: name of the polygon object
   * @param boolean $visible: set default visibility
   * @param string $fillStyle: set the polygon style: '{color:value,opacity:value}'
   * See http://code.google.com/intl/fr/apis/maps/documentation/reference.html#GPolyStyleOptions
   * @param string $strokeStyle: set the polygon style: '{color:value,opacity:value,weight:value}'
   * See http://code.google.com/intl/fr/apis/maps/documentation/reference.html#GPolyStyleOptions
   * @param string $callBack: JS script executed after Initialize the polygon object $name
   *        typically used to add events. The string "THEPOLYGON" in this JS code will be
   *         replaced by the name of the instance of the polygon.
   * @return void
   */
  public function addPolygonByCoords($coords, $name='',
                                     $visible=true,
                                     $fillStyle='{color:"#4d6ecd",opacity:0.2}',
                                     $strokeStyle='{color:"#331111",opacity:0.5,weight:2}',
                                     $callBack='')
  {
    if($name == '') $name = 'polygon'.count($this->polygons);
    $this->polygons[] = array('coords'      => $coords,
                              'name'        => $name,
                              'visible'     => $visible,
                              'fillStyle'   => $fillStyle,
                              'strokeStyle' => $strokeStyle,
                              'callBack'    => $callBack);
  }

  /**
   * Convert kml file like res/departments_fr/1/contour.kml to PHP array of coords.
   *
   * @param string $file: kml file location
   *
   * @return a string: '$coords=array(array(lat,lnt),…)'
   */
  private function kmlToPHPCoordinates($file) {
    $jsOut='';
    $xml = new SimpleXMLElement($file,null,true);
    foreach ($xml->Document->Placemark->Polygon->outerBoundaryIs->LinearRing->coordinates as $item) {
      /* die('PASS'); */
      $coordinates = explode(' ', (string) $item);
      $name   = (string) $item->name;
      $length=count($coordinates)-1;
      foreach ($coordinates as $key=>$coordinate) {
      $ltlg=explode(',',$coordinate);
      $jsOut .= '  array('.$ltlg[1].','.$ltlg[0].')';
      if($key != $length) $jsOut .= ','."\n";
      }
    }
    $jsOut = '$coords=array('."\n".$jsOut.');';
    /* $filePart=pathinfo($file); */
    /* $filename=$filePart['dirname'].'/'.$filePart['filename'].'.php'; */
    /* $handle=fopen($filename, 'w'); */
    /* fwrite($handle, "<?php\n".$jsOut."\n?>"); */
    /* fclose($handle); */
    return $jsOut;
  }

  /**
   * Initialize the javascript code
   *
   * @return void
   */
  public function init()
  {
    // Google map JS
    $this->content .= '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$this->googleMapKey.'" type="text/javascript">';
    $this->content .= '</script>'."\n";

    // Clusterer JS
    if ($this->useClusterer==true) {
      // Source: http://gmaps-utility-library.googlecode.com/svn/trunk/markerclusterer/1.0/src/
      $this->content .= '<script src="'.$this->clustererLibrary.'" type="text/javascript"></script>'."\n";
    }

    if ($this->contextMenuControlLibrary) {
      // Source: http://gmaps-utility-library-dev.googlecode.com/svn/tags/contextmenucontrol/1.0/src/
      $this->content .= '<script src="'.$this->contextMenuControlLibrary.'" type="text/javascript"></script>'."\n";
    }

    $content = FALSE;
    if($this->useCache) {
      $tpl = new SimpleTemplate(GMA_PATH.'res/templates/libs.tpl',
                          $this->cacheParams['libs']['name'],
                          $this->cacheParams['libs']['ttl']);
      $content = $tpl->tryGetCache();
    } else $tpl = new SimpleTemplate(GMA_PATH.'res/templates/libs.tpl');
    $tplParams = array();
    if($content == FALSE) {
      /* $tplParams['iconSize'] = $this->iconWidth.','.$this->iconHeight; */
      // Use clusterer optimisation or not
      $tplParams['useClusterer'] = $this->useClusterer;

      // Display direction inputs in the info window
      $tplParams['displayDirectionFields'] = $this->displayDirectionFields;
      $tplParams['googleMapDirectionId']   = $this->googleMapDirectionId;

      $tplParams['infoWindowWidth'] = $this->infoWindowWidth;

      // Hide marker ?
      $tplParams['HideMarker'] = $this->defaultHideMarker;

      $tplParams['lang'] = $this->lang;
      $tplParams['zoom'] = $this->zoom;

      // Assign $tplParams values to the variable $PARAMS of the template
      $tpl->assign('PARAMS', $tplParams);
      // Get the content
      $content .= $tpl->fetch();
    }

    $this->content .= '<script type="text/javascript">'."\n/* <![CDATA[ */\n".$content."\n/* ]]> */\n".'</script>'."\n";
    // Google map DIV
    $this->content .= '<div id="'.$this->googleMapId.'"';
    if($this->width > 0 && $this->height > 0) {
      $this->content .= ' style="width:'.$this->width.'px;height:'.$this->height.'px"';
    }
    $this->content .= '></div>'."\n";
  }

  /**
   * Generate the gmap
   *
   * @return void
   */

  public function generate()
  {

    $this->init();

    $content = FALSE;
    if($this->useCache) {
      $tpl = new SimpleTemplate(GMA_PATH.'res/templates/loader.tpl',
                          $this->cacheParams['loader']['name'],
                          $this->cacheParams['loader']['ttl']);
      $content=$tpl->tryGetCache();
    } else $tpl = new SimpleTemplate(GMA_PATH.'res/templates/loader.tpl');
    $tplParams = array();
    if($content == FALSE) {

      // The layers
      $layersDef     = 'layers = {';
      $layersVisibilityStr = '';
      $listener      = '';
      $count = count($this->layers)-1;
      foreach($this->layers as $key => $layer) {
        $layersDef .= '"'.$layer['name'].'":{"url":"'.$layer['url'].'"}';
        if($key != $count) {
          $layersDef .= ',';
        }
        if($layer['visible']) {
          $layersVisibilityStr .= 'toggleXML("'.$layer['name'].'");'."\n";
        }

      }
      $layersDef .= '};';

      // The Polygons
      $polygonsDef           = '';
      $polygonsInit          = '';
      $polygonsVisibilityStr = '';
      $polygonCallBack              = '';
      $count                 = count($this->polygons)-1;
      foreach($this->polygons as $key => $polygon) {
        $polygonsDef .= 'polygons["'.$polygon['name'].'"]={"coords":[';
        $coordsCount=count($polygon['coords'])-1;
        foreach($polygon['coords'] as $key => $coord) {
          $polygonsDef .= '['.$coord[0].','.$coord[1].']';
          if($key != $coordsCount) $polygonsDef .= ',';
        }
        $polygonsDef .= '],"fillStyle":'.$polygon['fillStyle'].
          ',"strokeStyle":'.$polygon['strokeStyle'].
          ',"visible":'.($polygon['visible'] ? 'true':'false').'};'."\n";
        $polygonCallBack .= str_replace("THEPOLYGON",'polygons["'.$polygon['name'].'"].polygon',$polygon['callBack'])."\n";
        if($polygon['visible']) {
          $polygonsVisibilityStr .= 'togglePolygon("'.$polygon['name'].'");'."\n";
        }

      }


      // Center of the GMap
      if($this->needGeocoding) {
        $geocodeCentre = $this->geocoding($this->center);

        if ($geocodeCentre[0]=="200") { // success
          $latlngCentre = $geocodeCentre[2].",".$geocodeCentre[3];
        } else { // Paris
          $latlngCentre = "48.8792,2.34778";
        }
      } else  $latlngCentre = $this->center; // No need geocoding.


      $tplParams['afterLoad'] = $layersDef."\n".$layersVisibilityStr."\n";
      $tplParams['afterLoad'] .= $polygonsDef."\n".$polygonsVisibilityStr."\n";
      if($this->contextMenuControlLibrary) {
        $tplParams['afterLoad'] .= 'map.addControl(new ContextMenuControl());'."\n";
      }
      $tplParams['afterLoad'] .= $polygonCallBack; // Le dernier de afterLoad.
      $tplParams['googleMapId'] = $this->googleMapId;
      $tplParams['latlngCentre'] = $latlngCentre;
      $tplParams['zoom'] = $this->zoom;
      $tplParams['useClusterer'] = $this->useClusterer;
      $tplParams['gridSize'] = $this->gridSize;
      $tplParams['maxZoom'] = $this->maxZoom;

      // Push markers by address in the markersByCoords stack
      $this->_addMarkersByAddressToMarkersByCoords($this->markersByAddress);

      // Ready to assign all the markers and params
      /* $tpl->assign('MARKERS',$this->markersByCoords); */
      $tpl->assign('MARKERS',$this->_markersToJS());
      $tpl->assign('PARAMS',$tplParams);

      // Get the content
      $content=$tpl->fetch();
    }

    $this->content .= '<script type="text/javascript">'."\n/* <![CDATA[ */\n";
    $this->content .= $content."\n/* ]]> */\n";
    $this->content .= '</script>'."\n";
  }
}


// Local Variables:   **
// c-basic-offset : 4 **
// End:               **
