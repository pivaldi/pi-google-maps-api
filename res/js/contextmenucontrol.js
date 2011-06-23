/**
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * @name ContextMenuControl
 * @version 1.0
 * @copyright 2009 Wolfgang Pichler
 * @author Wolfgang Pichler (Pil), www.wolfpil.de
 * @fileoverview
 * <p>This class lets you add a control to the map which mimics
 * the context menu of Google Maps. The control supports all of the six
 * items supported by Google Maps: finding directions to/from a location,
 * zooming in/out, centering the map, and finding the address at the clicked
 * location. Any of these items may be suppressed by passing options into
 * the constructor. This control extends the 
 * <a href="http://www.google.com/apis/maps/documentation/reference.html#GControl">
 * GControl</a> interface.</p>
 * <p>Note: ContextMenuControl doesn't work in Opera because Opera doesn't 
 * support the oncontextmenu event and doesn't give access to right mouse clicks.
 * </p>
 */

/**
 * @name ContextMenuControlOptions
 * @class This class represents optional arguments to {@link ContextMenuControl}.
 * @property {Boolean} [dirsFrom = true] Shows "Directions from here" item.
 * @property {Boolean} [dirsTo = true] Shows "Directions to here" item.
 * @property {Boolean} [zoomIn = true] Shows "Zoom in" item.
 * @property {Boolean} [zoomOut = true] Shows "Zoom out" item.
 * @property {Boolean} [centerMap = true] "Shows "Center map here" item.
 * @property {Boolean} [whatsHere = true] "Shows "What's here?" item.
 */

/**
 * @desc Creates a control with options specified in {@link ContextMenuControlOptions}.
 * @param {ContextMenuControlOptions} [opt_opts] Named optional arguments.
 * @constructor
 */
function ContextMenuControl(opt_opts) {
  if (opt_opts) {
    this.options = {
      dirsFrom: opt_opts.dirsFrom,
      dirsTo: opt_opts.dirsTo,
      zoomIn: opt_opts.zoomIn,
      zoomOut: opt_opts.zoomOut,
      centerMap: opt_opts.centerMap,
      whatsHere: opt_opts.whatsHere
    };
  } else {
    this.options = {};
  }
  this.dirmarks_ = [];
  this.heremarks_ = [];
  this.letterindex_ = -1;
}

/**
 * Extends GOverlay class from the Google Maps API.
 *  Second param (selectable) should be set to true
 * @private
 */
ContextMenuControl.prototype = new GControl(false, true);

/**
 * @desc Initialize the control on the map.
 * @param {GMap2} map The map that has had this control added to
 * @return {Element} mapdiv Div that holds the map
 * @private
 */
ContextMenuControl.prototype.initialize = function (map) {
  var me = this;
  me.map_ = map;
  var mapdiv = map.getContainer();

  // Prevents the browser's own context menu to appear.
  if (mapdiv.addEventListener) {
    mapdiv.addEventListener("contextmenu", function (e) {
      e.stopPropagation();
      e.preventDefault();
      }, false);
  } else if (mapdiv.attachEvent) {
    mapdiv.attachEvent("oncontextmenu", function () {
      var e = window.event;
      e.cancelBubble = true;
      e.returnValue = false;
    });
  }
  me.createContextMenu_();

  // Displays our context menu on single right mouse click
  GEvent.addListener(map, "singlerightclick", function (pixelPoint, src, ov) {
   var d = me.dirmarks_;
   me.rej_ = null;
   if (d.length > 0) {
     // Right click on a marker
     if (ov instanceof GMarker) {
      for (var i = 0; i < d.length; i++) {
        // If it's a dir marker it should be removable
        if (ov.getLatLng().equals(d[i].getLatLng())) {
          me.rej_ = ov.getLatLng();
          break;
        }
      }
      if (me.rej_) {
        me.rebuildMenu_("remove");
      } else {
       me.rebuildMenu_("add");
      }
     } else  {
      me.rebuildMenu_("add");
     }
   } else {
     me.rebuildMenu_();
   }

    me.clickedPoint_ = map.fromContainerPixelToLatLng(pixelPoint);

    // Correction of IE bug
    var posX = document.all ? (pixelPoint.x - 40) : pixelPoint.x;
    var posY = document.all ? (pixelPoint.y + 10) : pixelPoint.y;

    var mapwidth = map.getSize().width;
    var mapheight = map.getSize().height;
    var menuwidth = me.menuList.offsetWidth;
    var menuheight = me.menuList.offsetHeight;

    // Adjusts the position of the context menu
    if (mapwidth - menuwidth < posX) {
      posX = posX - menuwidth;
    }
    if (mapheight - menuheight < posY) {
      posY = posY - menuheight - 20;
    }
    me.menuList.style.visibility = "visible";
    me.menuList.visible = true;
    var pos = new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(posX, posY));
    pos.apply(me.menuList);
  });

  // Closes context menu when the cursor is being moved out of the map.
  // This DomListener is a workaround for Internet Explorer because
  //  the 'normal' GEvent Listener doesn't work correctly in IE.
  GEvent.addDomListener(mapdiv, "mouseout", function (e) {
    if (me.menuList.visible) {
      if (!e) {
        var e = window.event;
      }
      if (me.checkMouseLeave_(mapdiv, e)) {
        me.hideMenu_();
      }
    }
  });

  // Closes context menu in case of a left click on the map
  GEvent.addListener(map, "click", function () {
    me.hideMenu_();
  });

  // Closes context menu after dragging the map
  GEvent.addListener(map, "dragend", function () {
    me.hideMenu_();
  });
  // Return a dummy element to keep the API happy
  return document.createElement("b");
};

/**
 * Creates a draggable marker for searching directions.
 * @param {String} letter Used to decide which icon to display.
 * @private
 */
ContextMenuControl.prototype.searchDirs_ = function (letter) {
  var me = this;
  me.setChosen_(letter);
  var point = me.clickedPoint_;
  var html;

  if (me.actual) {
    var waypoints = [point, me.actual.getLatLng()];
    me.getDirs_(waypoints);
  } else {
    var image = "marker" + letter + ".png";
    var icon = me.createIcon_(image);
    html = me.makeForm_(letter);
    var marker = new GMarker(point, {icon: icon, draggable: true, title: 'Drag', bouncy: false, dragCrossMove: true});
    me.actual = marker;
    me.map_.addOverlay(marker);
    marker.openInfoWindowHtml(html);

    GEvent.addListener(marker, "click", function () {
      html = me.makeForm_(letter);
      marker.openInfoWindowHtml(html);
    });

    GEvent.addListener(marker, "dragstart", function () {
      me.map_.closeInfoWindow();
    });
  }
};

/**
 * Creates an initially hidden unordered menu list.
 * @param {String} change Used to decide which item to replace.
 * @return {Element} ul that holds the list entries of the context menu.
 * @private
 */
ContextMenuControl.prototype.createContextMenu_ = function (change) {
  var me = this;
  me.menuList = document.createElement("ul");
  me.menuList.style.font = "small Arial";
  me.menuList.style.whiteSpace = "nowrap";
  me.menuList.style.color = "#0000cd";
  me.menuList.style.backgroundColor = "#fff";
  me.menuList.style.listStyle = "none";
  me.menuList.style.padding = "0px";
  me.menuList.style.width = "21ex";
  me.menuList.style.border = "1px solid #666";
  me.menuList.style.position = "absolute";

  if (me.options.dirsFrom !== false && !change) {
    me.menuList.appendChild(me.createListItem_("Directions from here", "from"));
  }
  if (me.options.dirsTo !== false && !change) {
    me.menuList.appendChild(me.createListItem_("Directions to here", "to"));
  }
  if (change == "add") {
    me.menuList.appendChild(me.createListItem_("Add a destination", "add"));
  }
  if (change == "remove") {
    me.menuList.appendChild(me.createListItem_("Remove this point", "rem"));
  }
  me.menuList.appendChild(me.createRuler_());
  if (me.options.zoomIn !== false) {
    me.menuList.appendChild(me.createListItem_("Zoom In", "in"));
  }
  if (me.options.zoomOut !== false) {
    me.menuList.appendChild(me.createListItem_("Zoom Out", "out"));
  }
  if (me.options.centerMap !== false) {
    me.menuList.appendChild(me.createListItem_("Center Map here", "center"));
  }
  if (me.options.whatsHere !== false) {
    me.menuList.appendChild(me.createListItem_("What\'s here?", "here"));
  }
  me.hideMenu_();
  // Adds context menu to the map container
  me.map_.getContainer().appendChild(me.menuList);
  return me.menuList;
};

/**
 * Avoids firing a mouseout event when the mouse moves over a child element.
 * This will be caused by event bubbling.
 * Borrowed from: http://www.faqts.com/knowledge_base/view.phtml/aid/1606/fid/145
 * @param {Element} element Parent div
 * @param {Event} evt The passed mouse event
 * @return {Boolean}
 * @private
 */
ContextMenuControl.prototype.checkMouseLeave_ = function (element, evt) {
  if (element.contains && evt.toElement) {
    return !element.contains(evt.toElement);
  } else if (evt.relatedTarget) {
    return !this.containsDOM_(element, evt.relatedTarget);
  }
};

/**
 * Checks if the mouse leaves the parent element.
 * @param {Element} container Parent div
 * @param {Event} containee Event of node that the mouse entered when leaving the target
 * @return {Boolean}
 * @private
 */
ContextMenuControl.prototype.containsDOM_ = function (container, containee) {
  var isParent = false;
  do {
    if ((isParent = container == containee)) {
      break;
    }
    containee = containee.parentNode;
  } while(containee != null);
  return isParent;
};

/**
 * Creates clickable context menu list items.
 * @param {String} text Text to display in list item.
 * @param {String} arg Used to identify the clicked entry.
 * @return {Element} List item that holds the entry.
 * @private
 */
ContextMenuControl.prototype.createListItem_ = function (text, arg) {
  var me = this;
  var entry = document.createElement("li");
  entry.style.padding = "0px 6px";
  entry.style.lineHeight = "1.6em";
  entry.appendChild(document.createTextNode(text));

  GEvent.addDomListener(entry, "mouseover", function () {
    entry.style.cursor = "pointer";
    entry.style.backgroundColor = "#00ddff";
  });

  GEvent.addDomListener(entry, "mouseout", function () {
    entry.style.cursor = "default";
    entry.style.backgroundColor = "#fff";
  });

  GEvent.addDomListener(entry, "click", function () {
    if (arg == "from") {
      me.searchDirs_("A");
    } else if (arg == "to") {
      me.searchDirs_("B");
    } else if (arg == "add") {
      me.addDest_();
    } else if (arg == "rem") {
      me.removeDest_();
    } else if (arg == "in") {
      me.map_.zoomIn();
    } else if (arg == "out") {
      me.map_.zoomOut();
    } else if (arg == "center") {
      var point = me.clickedPoint_;
      me.map_.panTo(point);
    } else if (arg == "here") {
      me.getReverseGeocode_(); 
    }
    // Hides the menu after it's been used
    me.hideMenu_();
  });
  return entry;
};

/**
 * Removes direction markers on contextual click.
 * Creates a new route when there are still more than
 * two markers on the map after removing a marker.
 * @private
 */
ContextMenuControl.prototype.removeDest_ = function () {
  var me = this;
  var d = me.dirmarks_;
  var waypoints = [];

  for (var i = 0; i < d.length; i++) {
    if (me.rej_.equals(d[i].getLatLng())) {
      me.map_.removeOverlay(d[i]);
      d.splice(i, 1); break;
    }
  }
  if (d.length == 0) {
    me.removeOld_();
  } else if (d.length == 1) {
    me.map_.closeInfoWindow();
    me.map_.removeOverlay(me.poly_);
  } else if (d.length > 1) {
    for (var j = 0; j < d.length; j++) {
      waypoints[j] = d[j].getLatLng();
    }
    me.getDirs_(waypoints);
  }
};

/**
 * Adds previously removed and further destinations.
 * @private
 */
ContextMenuControl.prototype.addDest_ = function () {
  var me = this;
  var d = me.dirmarks_;
  var waypoints = [];

  // Re-adds removed A or B when only A and B are shown
  if (d.length == 1) {
    if (d[0].letter == "A") {
      me.setChosen_("B");
    } else {
      me.setChosen_("A");
    }
    waypoints[0] = me.clickedPoint_;
    waypoints[1] = d[0].getLatLng();
  } else if (d.length > 1) {
    // Adds further destinations
    me.setChosen_();
    for (var i = 0; i < d.length; i++) {
      waypoints[i] = d[i].getLatLng();
    }
    waypoints.push(me.clickedPoint_);
  }
  me.getDirs_(waypoints);
};

/**
 * Creates a styled horizontal ruler between the list entries.
 * @return {Element} hr as separator.
 * @private
 */
ContextMenuControl.prototype.createRuler_ = function () {
  var hr = document.createElement("hr");
  hr.style.height = "1px";
  hr.style.border = "1px";
  hr.style.color = "#e2e2e2";
  hr.style.backgroundColor = "#e2e2e2";
  // Further IE bug
  if (document.all) {
    hr.style.display = "block";
    hr.style.margin = "-6px";
  } else {
    hr.style.margin = "0px";
  }
  return hr;
};

/**
 * Hides the context menu and sets its property visible to false.
 * @private
 */
ContextMenuControl.prototype.hideMenu_ = function () {
  this.menuList.style.visibility = "hidden";
  this.menuList.visible = false;
};

/**
 * Removes the context menu from the map container and adds a changed one.
 * @param {String} arg Used to decide which item to replace.
 * @private
 */
ContextMenuControl.prototype.rebuildMenu_ = function (arg) {
  this.map_.getContainer().removeChild(this.menuList);
  this.createContextMenu_(arg);
};

/**
 * Checks the finally touched marker to request the appropriate route.
 * @param {String} opt_letter
 * @private
 */
ContextMenuControl.prototype.setChosen_ = function (letter) {
  if (letter == "A") {
    this.chosen = {A: true, B: false};
  } else if (letter == "B") {
    this.chosen = {A: false, B: true};
  } else {
    this.chosen = {A: false, B: false};
  }
};

/**
 * Creates alphabetically arranged capital letters for direction markers.
 * @return {String} letter
 * @private
 */
ContextMenuControl.prototype.makeLetter_ = function () {
  this.letterindex_++;
  return String.fromCharCode(this.letterindex_ + 65);
};

/**
 * Creates required properties for icons.
 * @param {GIcon} icon
 * @private
 */
ContextMenuControl.prototype.createIcon_ = function (image) {
  var icon = new GIcon();
  var url = "http://maps.google.com/mapfiles/";
  if (image == "arrow") {
    icon.image = url + "arrow.png";
    icon.shadow = url + "arrowshadow.png";
    icon.iconSize = new GSize(39, 34);
    icon.shadowSize = new GSize(39, 34);
    icon.iconAnchor = new GPoint(20, 34);
    icon.infoWindowAnchor = new GPoint(20, 0);
  } else {
    icon.image = url + image;
    icon.shadow = url + "shadow50.png";
    icon.iconSize = new GSize(20, 34);
    icon.shadowSize = new GSize(37, 34);
    icon.iconAnchor = new GPoint(9, 34);
    icon.infoWindowAnchor = new GPoint(19, 2);
  }
  return icon;
};


/**
 * Creates and adds direction markers to the map.
 * @param {GLatLng) point The GLatLng for the marker.
 * @param {String) letter The markers letter.
 * @private
 */
ContextMenuControl.prototype.makeDirMarker_ = function (point, letter) {
  var me = this;
  var d = me.dirmarks_;
  var r = me.routes_;
  var iw;
  var waypoints = [];
  var image = "marker_green" + letter + ".png";
  var icon = me.createIcon_(image);
  var marker = new GMarker(point, {icon: icon, draggable: true, title: 'Drag to change route', bouncy: false, dragCrossMove: true});

  marker.letter = letter;
  me.map_.addOverlay(marker);
  d.push(marker);

  if (letter == "A") {
    iw = r.iws[0];
  } else {
    iw = r.iws[(r.iws.length - 1)];
  }

  GEvent.addListener(marker, "click", function () {
    if (d.length > 1) {
      me.map_.openInfoWindowHtml(point, iw);
    }
  });

  // Finds out dragged marker and closes infowindow
  GEvent.addListener(marker, "dragstart", function () {
    me.map_.closeInfoWindow();
    me.actual = marker;
    if (d.length <= 2) {
      me.setChosen_(letter);
    }
  });

  // Creates a new route when two or more markers are shown
  GEvent.addListener(marker, "dragend", function () {
    if (d.length == 2) {
      var sticky = me.chosen.A ? d[d.length - 1].getLatLng() : d[0].getLatLng();
      waypoints.splice(0, 0, me.actual.getLatLng(), sticky);
    } else if (d.length > 2) {
      d.splice((letter.charCodeAt() - 65), 1, me.actual);
      for (var i = 0; i < d.length; i++) {
        waypoints[i] = d[i].getLatLng();
      }
    }
    if (d.length >= 2) {
      me.getDirs_(waypoints);
    }
  });
};

/**
 * Error alerts for failed direction queries.
 * @private
 */
ContextMenuControl.prototype.handleErrors_ = function () {
  var status = this.gdir.getStatus().code;
  var reason = [];
  // 400
  reason[G_GEO_BAD_REQUEST] = "A directions request could not be successfully parsed.";
  // 500
  reason[G_GEO_SERVER_ERROR] = "A geocoding, directions or maximum zoom level request could not be successfully processed.";
  // 601
  reason[G_GEO_MISSING_QUERY] = "No query was specified in the input.";
  // 602
  reason[G_GEO_UNKNOWN_ADDRESS] = "No corresponding geographic location could be found for one of the specified addresses.";
  // 603
  reason[G_GEO_UNAVAILABLE_ADDRESS] = "The geocode for the given address or the route for the given directions query cannot be returned due to legal or contractual reasons.";
  // 604
  reason[G_GEO_UNKNOWN_DIRECTIONS] = "The GDirections object could not compute directions between the points mentioned in the query.";
  // 610
  reason[G_GEO_BAD_KEY] = "The given key is either invalid or does not match the domain for which it was given.";
  // 620
  reason[G_GEO_TOO_MANY_QUERIES] = "The given key has gone over the daily requests limit or too many requests were submitted too fast.";

  if (reason[status]) {
    alert(reason[status] + "\nError code: " + status);
  } else {
    alert("An unknown error occurred.");
  }
};

/**
 * Callback function for direction queries.
 * @private
 */
ContextMenuControl.prototype.dirsLoad_ = function () {
  var me = this;
  me.routes_ = {sections: [], iws: [], num: 0};
  var r = me.routes_;

  // Removes possible 'What's here' marker
  if (me.heremarks_.length > 0) {
    me.removeOld_("here");
  }
  // Removes existing search marker
  if (me.actual) {
    me.map_.removeOverlay(me.actual);
    me.actual = null;
  }
  // Removes previous
  if (me.dirmarks_.length > 0) {
    me.removeOld_();
  }
  // Draws polyline for all routes
  me.poly_ = me.gdir.getPolyline();
  me.map_.addOverlay(me.poly_);
  var numRoutes = me.gdir.getNumRoutes();
  r.num = numRoutes;

  // Stores items of every route
  for (var m = 0; m < numRoutes; m++) {
    var route = me.gdir.getRoute(m);
    var numSteps = route.getNumSteps();

    if (m == 0) {
      var spoint = route.getStep(0).getLatLng();
      var saddr = route.getStartGeocode().address;
      r.sections.push(spoint);
      r.iws.push(me.createRouteInfo(saddr, 0));
      me.makeDirMarker_(spoint, me.makeLetter_());
    }

    for (var n = 0; n < numSteps; n++) {
      var count = r.sections.length;
      var step = route.getStep(n);
      r.sections.push(step.getLatLng());
      r.iws.push(me.createRouteInfo(step.getDescriptionHtml(), count));
    }
    var epoint = route.getEndLatLng();
    var eaddr = route.getEndGeocode().address;
    r.sections.push(epoint);
    r.iws.push(me.createRouteInfo(eaddr, (count + 1), (m + 1)));
    me.makeDirMarker_(epoint, me.makeLetter_());
  }
};

/**
 * Creates info windows for direction steps.
 * @param {String} info The returned description for each step.
 * @param {Number} nr counter to identify the appropriate info.
 * @param {Number} i counter to identify the last route.
 * @return {Element} The styled info window.
 * @private
 */
ContextMenuControl.prototype.createRouteInfo = function (info, nr, i) {
  var disabled = function (io) {
    io.style.color = "#a5a5a5";
    io.style.textDecoration = "none";
    io.removeAttribute("href");
    io.style.cursor = "default";
  };
  var me = this;
  var r = me.routes_;
  var iw = document.createElement("div");
  iw.style.width = "240px";
  iw.innerHTML = info;
  var p = document.createElement("p");
  p.style.fontSize = "small";
  p.style.textAlign = "center";
  p.style.marginTop = "20px";

  var zlink = document.createElement("a");
  zlink.setAttribute("href", "javascript:void(0)");
  zlink.innerHTML = "Zoom In";
  zlink.style.marginRight = "20px";

  var blink = document.createElement("a");
  blink.setAttribute("href", "javascript:void(0)");
  blink.innerHTML = "&laquo; Previous";
  blink.style.marginRight = "20px";
  if (nr == 0) {
    disabled(blink);
  }

  var flink = document.createElement("a");
  flink.setAttribute("href", "javascript:void(0)");
  flink.innerHTML = "Next &raquo;";
  if (i == r.num) {
    disabled(flink);
  }

  p.appendChild(blink);
  p.appendChild(zlink);
  p.appendChild(flink);
  iw.appendChild(p);

  // Due to an API bug we do not use addDomListeners here
  // Zoom in link
  zlink.onclick = function () {
    me.map_.zoomIn(r.sections[nr], {doCenter: true});
  };
  // Back link
  blink.onclick = function () {
    if (nr > 0) {
      me.map_.openInfoWindowHtml(r.sections[(nr - 1)], r.iws[(nr - 1)]);
    }
  };
  // Forward link
  flink.onclick = function () {
    if (nr < (r.sections.length - 1)) {
      me.map_.openInfoWindowHtml(r.sections[(nr + 1)], r.iws[(nr + 1)]);
    }
  };
  return iw;
};

/**
 * Handles direction queries.
 * Either first or second param must be passed in.
 * @param {GLatLng[]} [points] Array of GLatLngs to load direction from waypoints.
 * @param {String} [addr] The form input value.
 * @private
 */
ContextMenuControl.prototype.getDirs_ = function (points, addr) {
  var me = this;
  me.gdir = new GDirections();
  GEvent.bind(me.gdir, "error", me, me.handleErrors_);
  GEvent.bind(me.gdir, "load", me, me.dirsLoad_);
  var opts = {getPolyline: true, getSteps: true };
  if (me.opts_) {
    opts.avoidHighways = me.opts_.avoidHighways;
  }
  if (points) {
    if (me.chosen.B) {
      points = points.reverse();
    }
    me.gdir.loadFromWaypoints(points, opts);
  } else {
    var point = me.actual.getLatLng();
    var latlng = point.lat() + "," + point.lng();

    if (me.chosen.A) {
      me.gdir.load("from: " + latlng + " to: " + addr, opts);
    } else {
      me.gdir.load("from: " + addr + " to: " + latlng, opts);
    }
  }
};

/**
 * Creates the form for searching directions displayed in infowindow.
 * @param {String} letter Used to decide which form to load.
 * @return {Element} Nested elements in outer form.
 * @private
 */
ContextMenuControl.prototype.makeForm_ = function (letter) {
  var me = this;
  var text = (letter == "A") ?["From here", "End address:"]: ["To here", "Start address:"];
  var html = document.createElement("div");

  // Header
  html.appendChild(document.createTextNode("Direction: "));
  var bold = document.createElement("b");
  bold.appendChild(document.createTextNode(text[0]));
  html.appendChild(bold);
  html.appendChild(document.createElement("br"));
  var small = document.createElement("small");
  small.appendChild(document.createTextNode(text[1]));
  html.appendChild(small);
  html.appendChild(document.createElement("br"));

  // Input field
  var input = document.createElement("input");
  input.type = "text";
  input.value = "";
  input.style.width = "32ex";
  html.appendChild(input);

  // Dropdown box and submit button
  var p1 = document.createElement("p");
  var select = document.createElement("select");
  select.size = "1";
  select.style.width = "15ex";
  var opt1 = document.createElement("option");
  opt1.appendChild(document.createTextNode("By car"));
  opt1.setAttribute("value", "");
  select.appendChild(opt1);
  var opt2 = document.createElement("option");
  opt2.appendChild(document.createTextNode("Avoid highways"));
  opt2.setAttribute("value", "nohighways");
  select.appendChild(opt2);
  var button = document.createElement("input");
  button.type = "submit";
  button.value = "Get Direction";
  button.style.width = "15ex";
  button.style.marginLeft = "16px";
  p1.appendChild(select);
  p1.appendChild(button);
  html.appendChild(p1);

  // Link for removing the marker
  var p2 = document.createElement("p");
  var small2 = document.createElement("small");
  small2.appendChild(document.createTextNode("Drag or "));
  var rlink = document.createElement("a");
  rlink.setAttribute("href", "javascript:void(0)"); 
  rlink.appendChild(document.createTextNode("remove this point"));
  small2.appendChild(rlink);
  p2.appendChild(small2);
  html.appendChild(p2);

  var form = document.createElement("form");
  form.appendChild(html);

  setTimeout(function () {
    var inputfield = html.childNodes[5];
    if (inputfield.nodeName == "INPUT") {
      inputfield.focus();
    }
  }, 1000);

  GEvent.addDomListener(rlink, "click", function () {
    if (me.actual) {
      me.map_.removeOverlay(me.actual);
      me.actual = null;
    }
  });

  me.opts_ = {avoidHighways: false};
  GEvent.addDomListener(select, "change", function () {
    // This option should be 'session consistent'.
    // So it won't be passed to the function.
    if (select.options[select.selectedIndex].value == "nohighways") {
      me.opts_.avoidHighways = true;
    }
  });

  GEvent.addDomListener(form, "submit", function (e) {
    me.getDirs_(null, input.value);
    if (window.event) {
      event.returnValue = false;
    } else if (e) {
      e.stopPropagation();
      e.preventDefault();
    }
  });
  return form;
};

/**
 * Removes markers and polylines from the map and resets globals.
 * @param {String} what Used to decide what to remove
 * @private
 */
ContextMenuControl.prototype.removeOld_ = function (what) {
  if (what == "here") {
    for (var i = 0; i < this.heremarks_.length; i++) {
      this.map_.removeOverlay(this.heremarks_[i]);
    }
    this.heremarks_.length = 0;
  } else {
    this.map_.closeInfoWindow();
    this.map_.removeOverlay(this.poly_);
    for (var j = 0; j < this.dirmarks_.length; j++) {
      this.map_.removeOverlay(this.dirmarks_[j]);
    }
    this.dirmarks_.length = 0;
    this.letterindex_ = -1;
    if (this.actual) {
      this.map_.removeOverlay(this.actual);
      this.actual = null;
    }
  }
};

/**
 * Tries to reverse geocode the clicked point and
 * creates two markers to show 'What's here'.
 * @private
 */
ContextMenuControl.prototype.getReverseGeocode_ = function () {
  var dec2deg = function (dec) {
    var sign = (dec > 0) ? "+" : "";
    var deg = parseInt(dec, 10);
    dec = Math.abs(dec - deg);
    var min = parseInt((dec * 60), 10);
    dec = (dec * 60) % 1;
    var sec = Math.round(Math.abs(dec * 60) * 100) / 100;
    return sign + deg + "&deg; " + min + "&prime; " + sec + "&Prime;";
  };
  var me = this;
  if (me.heremarks_.length > 0) {
    me.removeOld_("here");
  }
  var point = me.clickedPoint_;
  var p_string = point.toUrlValue();
  var coords = "<div style='width:240px;'>" +
   "<big>" + p_string + "<\/big>" +
   "<p style='margin-top:7px;font-size:small;'>" +
   dec2deg(point.lat()) + ", " + dec2deg(point.lng()) + "<\/p><\/div>";
  var icon = me.createIcon_("arrow");
  var arrow_mark = new GMarker(point, {icon: icon, title: p_string });
  me.heremarks_.push(arrow_mark);
  me.map_.addOverlay(arrow_mark);

  GEvent.addListener(arrow_mark, "click", function () {
    arrow_mark.openInfoWindowHtml(coords);
  });

  var geo = new GClientGeocoder();
  geo.getLocations(point, function (response) {
    if (response.Status.code == 200) {
      var place = response.Placemark[0];
      var latlng = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
      var red_mark = new GMarker(latlng);
      me.heremarks_.push(red_mark);
      me.map_.addOverlay(red_mark);
      var addr = "<div style='width:240px;'>" +
        "<big>Address:<\/big><p style='margin-top:7px;font-size:small;'>" +
      place.address + "<\/p><\/div>";

      GEvent.addListener(red_mark, "click", function () {
        red_mark.openInfoWindowHtml(addr);
      });
    } else {
      // No more error handling needed since the coords are already shown.
      return;
    }
  });
};
