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
 *  @author           CERDAN Yohann <cerdanyohann@yahoo.fr>, Philippe IVALDI
 *  @copyright        (c) 2009  CERDAN Yohann, All rights reserved
 *  @version          Last modified: Fri Jul 24 18:35:54 CEST 2009
 */

require_once(dirname(__FILE__).'/Template.class.php');

/* Define the googleMapsAPI install directory */
define('GMA_PATH','/var/lib/guideregional/googlemapsapi/branches/');
/* define('GMA_REL_PATH', str_replace(array(DIRECTORY_SEPARATOR,'//'), */
/*                                    '/', */
/*                                    str_replace($_SERVER['DOCUMENT_ROOT'], */
/*                                                '', */
/*                                                GMA_PATH_thisScript))); */

/* define('GMA_CUR_PATH', dirname(str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):($_SERVER['ORIG_SCRIPT_FILENAME']?$_SERVER['ORIG_SCRIPT_FILENAME']:$_SERVER['SCRIPT_FILENAME'])))).'/'); */

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

    /** Icon width of the gmarker **/
    private $iconWidth = 24;

    /** Icon height of the gmarker **/
    private $iconHeight = 24;

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

    /** Markers by coords **/
    // array of array('latitude','longitude','html','category','icon')
    public $markersByCoords = array();

    /** Markers by address **/
    // array of array('address','content','category','icon');
    public $markersByAddress = array();

    /** Use clusterer to display a lot of markers on the gmap **/
    private $useClusterer = FALSE;
    private $gridSize = 100;
    private $maxZoom = 9;
    private $clustererLibrarypath;


    /** Cache params**/
    public $useCache = FALSE;
    public $cacheParams = array('libs'   => array('name' => 'GMA-LIBS',
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

    public function useClusterer($clustererLibraryPath,
                                 $gridSize=100, $maxZoom=9)
    {
        /* TODO: update the doc */
        if(!@is_readable($clustererLibraryPath)) {
            trigger_error('Error --useClusterer--: you must specifiy a valid and readable JavaScript file',
                          E_USER_ERROR);
        }
        $this->useClusterer = TRUE;
        $this->gridSize = $gridSize;
        $this->maxZoom = $maxZoom;
        $this->clustererLibraryPath = $clustererLibraryPath;
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
     * Set the size of the icon markers
     *
     * @param int $iconWidth GoogleMap  marker icon width
     * @param int $iconHeight GoogleMap  marker icon height
     *
     * @return void
     */

    public function setIconSize($iconWidth, $iconHeight)
    {
        $this->iconWidth = $iconWidth;
        $this->iconHeight = $iconHeight;
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
     * Add marker by his coord
     *
     * @param string $lat lat
     * @param string $lng lngs
     * @param string $html html code display in the info window
     * @param string $category marker category
     * @param string $icon an icon url
     *
     * @return void
     */

    public function addMarkerByCoords($lat, $lng, $html='', $category='', $icon='')
    {
        $this->markersByCoords[] = array('latitude'  => $lat,
                                       'longitude' => $lng,
                                       'html'      => $html,
                                       'category'  => $category,
                                       'icon'      => $icon);
    }

    /**
     * Add address to the stack of markers by address
     *
     * @param string $address an ddress
     * @param string $content html code display in the info window
     * @param string $category marker category
     * @param string $icon an icon url
     *
     * @return void
     */

    public function addMarkerByAddress($address, $content='', $category='', $icon='')
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
     * @param string $icon an icon url
     *
     * @return void
     */

    public function addArrayMarkerByCoords($coordtab, $category='', $icon='')
    {
        foreach ($coordtab as $coord) {
            $this->addMarkerByCoords($coord[0], $coord[1], $coord[2], $category, $icon);
        }
    }

    /**
     * Add array of addresses to the stack of markers by address
     *
     * @param array(strings) $addresses an array of addresses
     * @param string $category marker category
     * @param string $icon an icon url
     *
     * @return void
     */

    public function addArrayMarkerByAddress($addresses, $category='', $icon='')
    {
        foreach ($addresses as $address) {
            $this->addMarkerByAddress($address[0], $address[1], $category, $icon);
        }
    }


    /**
     * Add an address to the array markersByCoords. Need geocoding.
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
     * @param string $icon an icon url
     *
     * @return void
     */

    public function addXML ($url, $category='', $icon='')
    {
        $xml = new SimpleXMLElement($url, null, true);
        foreach ($xml->Document->Folder->Placemark as $item) {
            $coordinates = explode(',', (string) $item->Point->coordinates);
            $name = (string) $item->name;
            $this->addMarkerByCoords($coordinates[1], $coordinates[0], $name, $category, $icon);
        }
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
            $this->content .= '<script src="'.$this->clustererLibraryPath.'" type="text/javascript"></script>'."\n";
        }

        $content == FALSE;
        if($this->useCache) {
            $tpl = new Template(GMA_PATH.'res/templates/libs.tpl',
                                $this->cacheParams['libs']['name'],
                                $this->cacheParams['libs']['ttl']);
            $content = $tpl->tryGetCache();
        } else $tpl = new Template(GMA_PATH.'res/templates/libs.tpl');
        $tplParams = array();
        if($content == FALSE) {
            $tplParams['iconSize'] = $this->iconWidth.','.$this->iconHeight;
            // Use clusterer optimisation or not
            $tplParams['useClusterer'] = $this->useClusterer;

            // Display direction inputs in the info window
            $tplParams['displayDirectionFields'] = $this->displayDirectionFields;
            $tplParams['googleMapDirectionId']   = $this->googleMapDirectionId;

            $tplParams['infoWindowWidth'] = $this->infoWindowWidth;

            // Hide marker by default
            if ($this->defaultHideMarker == TRUE) {
                $tplParams['HideMarker'] = 'marker.hide();';
            }

            $tplParams['lang'] = $this->lang;
            $tplParams['zoom'] = $this->zoom;

            // Assign $tplParams values to the variable $PARAMS of the template
            $tpl->assign('PARAMS', $tplParams);
            // Get the content
            $content .= $tpl->fetch();
        }

        $this->content .= '<script type="text/javascript">'."\n".$content."\n".'</script>'."\n";
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
            $tpl = new Template(GMA_PATH.'res/templates/loader.tpl',
                                $this->cacheParams['loader']['name'],
                                $this->cacheParams['loader']['ttl']);
            $content=$tpl->tryGetCache();
        } else $tpl = new Template(GMA_PATH.'res/templates/loader.tpl');
        $tplParams = array();
        if($content == FALSE) {
        // Center of the GMap
        if($this->needGeocoding) {
            $geocodeCentre = $this->geocoding($this->center);

            if ($geocodeCentre[0]=="200") { // success
                $latlngCentre = $geocodeCentre[2].",".$geocodeCentre[3];
            } else { // Paris
                $latlngCentre = "48.8792,2.34778";
            }
        } else  $latlngCentre = $this->center; // No need geocoding.


        $tplParams['googleMapId'] = $this->googleMapId;
        $tplParams['latlngCentre'] = $latlngCentre;
        $tplParams['zoom'] = $this->zoom;
        $tplParams['useClusterer'] = $this->useClusterer;
        $tplParams['gridSize'] = $this->gridSize;
        $tplParams['maxZoom'] = $this->maxZoom;

        // Push markers by address in the markersByCoords stack
        $this->_addMarkersByAddressToMarkersByCoords($this->markersByAddress);

        // Ready to assign all the markers and params
        $tpl->assign('MARKERS',$this->markersByCoords);
        $tpl->assign('PARAMS',$tplParams);

        // Get the content
        $content=$tpl->fetch();
        }

        $this->content .= '<script type="text/javascript">'."\n";
        $this->content .= $content."\n";
        $this->content .= '</script>'."\n";
    }
}

// Local Variables:   **
// c-basic-offset : 4 **
// End:               **
