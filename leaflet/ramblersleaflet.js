var L, ramblersMap, OsGridRef, Element, OpenLocationCode;
function RamblersLeafletMap(base) {
    this.base = base;
    this.map = null;
    this.mapLayers = null;
    this.currentLayer = null;
    this.bingkey = null;
    this.gridsquare10 = null;
    this.gridsquare100 = null;
    this.postcodes = null;
    this.markerList = null;
    this.markersCG = null;
    this.progress = null;
    this.progressBar = null;
    this.startingplaces = false;
    this.drawnItems = null;
    this.displayMouseGridSquare = true;
    this.elevationcontrol = null;
    this.processPopups = "on";
    this.mapControl = null;
    this.maphelppage = '';
    this.options = {cluster: false,
        fullscreen: false,
        google: false,
        bing: false,
        search: false,
        locationsearch: false,
        osgrid: false,
        mouseposition: false,
        postcodes: false,
        fitbounds: false,
        draw: false,
        print: false,
        displayElevation: false,
        ramblersPlaces: false,
        topoMapDefault: false
    };
}

function raLoadLeaflet() {
    if (ramblersMap.options.fullscreen) {
        ramblersMap.map = new L.Map("leafletmap", {
            center: new L.LatLng(54.221592, -3.355007),
            zoom: 5,
            zoomSnap: 0.25,
            maxZoom: 18,
            fullscreenControl: true
        });
    } else {
        ramblersMap.map = new L.Map("leafletmap", {
            center: new L.LatLng(54.221592, -3.355007),
            zoom: 5,
            zoomSnap: 0.25,
            maxZoom: 18});
    }

    ramblersMap.mapLayers = new Object();
// map types
    ramblersMap.mapLayers["Open Street Map"] = new L.TileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "Map data &copy; <a href=\"https://openstreetmap.org\">OpenStreetMap</a>"}).addTo(ramblersMap.map);
    ramblersMap.mapLayers["Open Topo Map"] = new L.TileLayer("https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png", {
        maxNativeZoom: 16,
        attribution: 'Kartendaten: &copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>-Mitwirkende, <a href="http://viewfinderpanoramas.org">SRTM</a> | Kartendarstellung: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'});
    if (ramblersMap.options.bing) {
//  var bingkey = 'AjtUzWJBHlI3Ma_Ke6Qv2fGRXEs0ua5hUQi54ECwfXTiWsitll4AkETZDihjcfeI';
        ramblersMap.mapLayers["Bing Aerial"] = new L.BingLayer(ramblersMap.bingkey, {type: 'Aerial'});
        ramblersMap.mapLayers["Bing Aerial (Labels)"] = new L.BingLayer(ramblersMap.bingkey, {type: 'AerialWithLabels'});
        ramblersMap.mapLayers["Ordnance Survey"] = new L.BingLayer(ramblersMap.bingkey, {type: 'ordnanceSurvey',
            attribution: 'Bing/OS Crown Copyright'});
    }
//    if (ramblersMap.options.google) {
//        ramblersMap.mapLayers["Google"] = L.gridLayer.googleMutant({
//            type: "roadmap" // valid values are 'roadmap', 'satellite', 'terrain' and 'hybrid'
//        });
//        ramblersMap.mapLayers["Google Satellite"] = L.gridLayer.googleMutant({
//            type: "satellite" // valid values are 'roadmap', 'satellite', 'terrain' and 'hybrid'
//        });
//        ramblersMap.mapLayers["Google Hybrid"] = L.gridLayer.googleMutant({
//            type: "hybrid" // valid values are 'roadmap', 'satellite', 'terrain' and 'hybrid'
//        });
//        ramblersMap.mapLayers["Google Terrain"] = L.gridLayer.googleMutant({
//            type: "terrain" // valid values are 'roadmap', 'satellite', 'terrain' and 'hybrid'
//        });
//    }
    createMouseMarkers();
    createPlaceMarkers();
    createWalkMarkers();
    if (ramblersMap.options.cluster) {
// progress bar for cluster
        ramblersMap.progressBar = document.getElementById("ra-cluster-progress-bar");
        ramblersMap.markersCG = L.markerClusterGroup({chunkedLoading: true, chunkProgress: updateClusterProgressBar, disableClusteringAtZoom: 12, maxClusterRadius: 50});
        ramblersMap.markerList = [];
    }

    addContent(ramblersMap);
    if (ramblersMap.options.cluster) {
        ramblersMap.markersCG.addLayers(ramblersMap.markerList);
        ramblersMap.map.addLayer(ramblersMap.markersCG);
    }

    if (ramblersMap.options.fitbounds) {
// [FitBounds]   
        if (ramblersMap.options.cluster) {
// calc bounds from marker as cluster still loading
            if (ramblersMap.markerList.length !== 0) {
                var bounds = getBounds(ramblersMap.markerList);
                ramblersMap.map.fitBounds(bounds, {padding: [150, 150]});
            }
        } else {

        }
    }

    if (ramblersMap.options.osgrid) {
        var osgrid = L.layerGroup([]);
        osMapGrid(osgrid);
        var overlayGraphics = {
            "OS 100km Grid": osgrid
        };
        osgrid.addTo(ramblersMap.map);
    }

    ramblersMap.mapControl = L.control.layers(ramblersMap.mapLayers, overlayGraphics, {collapsed: true}).addTo(ramblersMap.map);
    if (ramblersMap.options.topoMapDefault) {
        ramblersMap.map.addLayer(ramblersMap.mapLayers["Open Topo Map"]);
    } else {
        ramblersMap.map.addLayer(ramblersMap.mapLayers["Open Street Map"]);
        //ramblersMap.mapLayers["Open Street Map"].addTo(ramblersMap.map.addLayer);
    }
    if (ramblersMap.options.search) {
        try {
            L.Control.geocoder({
                defaultMarkGeocode: true,
                collapsed: true,
                geocoder: L.Control.Geocoder.nominatim({
                    geocodingQueryParams: {countrycodes: 'gb'}
                })
            }).addTo(ramblersMap.map);
        } catch (err) {
            document.getElementById("ra-error-text").innerHTML = "ERROR: " + err.message;
        }
// must be after layers so is second control in top right

    }

    if (ramblersMap.options.locationsearch) {
        L.control.locationsearch({
            defaultMarkLocationsearch: true,
            collapsed: true
        }).addTo(ramblersMap.map);
    }

    if (ramblersMap.options.startingplaces) {
        L.control.usageAgreement().addTo(ramblersMap.map);
    }
    if (ramblersMap.options.print) {
        L.control.browserPrint({
            title: 'The Ramblers - working for walkers',
            documentTitle: 'The Ramblers - working for walkers',
            printModes: ["Portrait", "Landscape"],
            closePopupsOnPrint: false
        }).addTo(ramblersMap.map);
        if (ramblersMap.options.bing) {
            L.Control.BrowserPrint.Utils.registerLayer(
                    L.BingLayer,
                    "L.BingLayer",
                    function (layer) {
                        var bing = L.bingLayer(layer.key, layer.options);
                        // fix as above object fails to set url
                        bing._url = ramblersMap.currentLayer._url;
                        return bing;
                    }
            );
        }
    }
    ramblersMap.map.on('baselayerchange', function (e) {
        ramblersMap.currentLayer = e.layer;
        //alert('Changed to ' + e.name);
    });
    if (ramblersMap.options.ramblersPlaces) {
        createPlaceMarkers();
    }

    if (ramblersMap.options.postcodes) {
        try {
//   ramblersMap.postcodelayer = L.featureGroup([]);
//   ramblersMap.postcodelayer.addTo(ramblersMap.map);
            ramblersMap.postcodes = L.control.postcodeStatus().addTo(ramblersMap.map);
        } catch (err) {
            document.getElementById("ra-error-text").innerHTML = "ERROR: " + err.message;
        }
    }

    if (ramblersMap.options.mouseposition && !L.Browser.mobile) {
        try {
            L.control.mouse().addTo(ramblersMap.map);
        } catch (err) {
            document.getElementById("ra-error-text").innerHTML = "ERROR: " + err.message;
        }

    }
// Zoom control
    L.control.scale().addTo(ramblersMap.map);
    ramblersMap.map.on('LayersControlEvent', function (ev) {
        alert(ev.latlng); // ev is an event object (MouseEvent in this case)
    });
    // help button
    if (ramblersMap.maphelppage !== "") {
        var helpbutton = new L.Control.DisplayHelp();
        ramblersMap.map.addControl(helpbutton);
    }
}

function updateClusterProgressBar(processed, total, elapsed) {
    if (elapsed > 1000) {
// if it takes more than a second to load, display the progress bar:
        ramblersMap.progressBar.innerHTML = "Loading: " + Math.round(processed / total * 100) + "%";
    }

    if (processed === total) {
// all markers processed - hide the progress bar:
        ramblersMap.progressBar.style.display = "none";
    }
}
function removeClusterMarkers() {
    ramblersMap.markersCG.removeLayers(ramblersMap.markerList);
    ramblersMap.markerList = [];
    //     ramblersMap.markersCG.addLayers(ramblersMap.markerList);
}
function addClusterMarkers() {
    ramblersMap.markersCG.addLayers(ramblersMap.markerList);
    var bounds = getBounds(ramblersMap.markerList);
    ramblersMap.map.fitBounds(bounds);
}

function createWalkMarkers() {
    ramblersMap.markerRoute = L.icon({
        iconUrl: ramblersMap.base + "libraries/ramblers/images/marker-route.png",
        iconSize: [33, 50],
        iconAnchor: [16, 45]
    });
    ramblersMap.markerStart = L.icon({
        iconUrl: ramblersMap.base + "libraries/ramblers/images/marker-start.png",
        iconSize: [35, 35]
    });
    ramblersMap.markerArea = L.icon({
        iconUrl: ramblersMap.base + "libraries/ramblers/images/marker-area.png",
        iconSize: [35, 35]
    });
    ramblersMap.markerCancelled = L.icon({
        iconUrl: ramblersMap.base + "libraries/ramblers/images/marker-cancelled.png",
        iconSize: [35, 35]
    });
    ramblersMap.walkingarea = L.icon({
        iconUrl: ramblersMap.base + "libraries/ramblers/images/area.png",
        iconSize: [40, 35]
    });
    ramblersMap.walkinggroup = L.icon({
        iconUrl: ramblersMap.base + "libraries/ramblers/images/group.png",
        iconSize: [40, 35]
    });
    ramblersMap.walkingspecial = L.icon({
        iconUrl: ramblersMap.base + "libraries/ramblers/images/specialgroup.png",
        iconSize: [40, 35]
    });
}
function createMouseMarkers() {
// add marker and layer group to contain postcode markers
    ramblersMap.postcodeIcon = L.icon({
        iconUrl: ramblersMap.base + 'libraries/ramblers/leaflet/images/postcode-icon.png',
        iconSize: [24, 18], // size of the icon
        shadowSize: [26, 20], // size of the shadow
        iconAnchor: [12, 9], // point of the icon which will correspond to marker's location
        shadowAnchor: [0, 0], // the same for the shadow
        popupAnchor: [0, -12] // point from which the popup should open relative to the iconAnchor
    });
    ramblersMap.postcodeIconClosest = L.icon({
        iconUrl: ramblersMap.base + 'libraries/ramblers/leaflet/images/postcode-icon-closest.png',
        iconSize: [24, 18], // size of the icon
        shadowSize: [26, 20], // size of the shadow
        iconAnchor: [12, 9], // point of the icon which will correspond to marker's location
        shadowAnchor: [0, 0], // the same for the shadow
        popupAnchor: [0, -12] // point from which the popup should open relative to the iconAnchor
    });
    ramblersMap.redmarkericon = L.icon({
        iconUrl: ramblersMap.base + 'libraries/ramblers/leaflet/images/redmarker.png',
        iconSize: [32, 32], // size of the icon
        shadowSize: [26, 20], // size of the shadow
        iconAnchor: [16, 32], // point of the icon which will correspond to marker's location
        shadowAnchor: [0, 0], // the same for the shadow
        popupAnchor: [0, -12] // point from which the popup should open relative to the iconAnchor
    });
    // create an orange rectangle
    ramblersMap.gridsquare100 = L.rectangle([[84, -89], [84.00001, -89.000001]],
            {color: "#ff7800", weight: 1}).addTo(ramblersMap.map);
    ramblersMap.gridsquare10 = L.rectangle([[84, -89], [84.00001, -89.000001]],
            {color: "#884000", weight: 1}).addTo(ramblersMap.map);
}
function createPlaceMarkers() {
    ramblersMap.s0 = L.icon({
        iconUrl: ramblersMap.base + 'libraries/ramblers/leaflet/images/rejected.png',
        iconSize: [15, 15]
    });
    ramblersMap.s1 = L.icon({
        iconUrl: ramblersMap.base + 'libraries/ramblers/leaflet/images/1star.png',
        iconSize: [19, 19]
    });
    ramblersMap.s2 = L.icon({
        iconUrl: ramblersMap.base + 'libraries/ramblers/leaflet/images/2star.png',
        iconSize: [21, 21]
    });
    ramblersMap.s3 = L.icon({
        iconUrl: ramblersMap.base + 'libraries/ramblers/leaflet/images/3star.png',
        iconSize: [23, 23]
    });
    ramblersMap.s4 = L.icon({
        iconUrl: ramblersMap.base + 'libraries/ramblers/leaflet/images/4star.png',
        iconSize: [25, 25]
    });
    ramblersMap.s5 = L.icon({
        iconUrl: ramblersMap.base + 'libraries/ramblers/leaflet/images/5star.png',
        iconSize: [27, 27]
    });
}

function addMarker($popup, $lat, $long, $icon) {
    var marker = L.marker([$lat, $long], {icon: $icon});
    var $pop = $popup.replace(/&quot;/g, '"'); // replace quots in popup text
    marker.bindPopup($pop);
    ramblersMap.markerList.push(marker);
}

function addMarkerToLayer($layer, $popup, $lat, $long, $icon) {
    var marker = L.marker([$lat, $long], {icon: $icon});
    var $pop = $popup.replace(/&quot;/g, '"'); // replace quots in popup text 
    marker.bindPopup($pop);
    $layer.add.push(marker);
}
function addPlace($gr, $no, $lat, $long, $icon) {
    var $grdisp;
    var marker = L.marker([$lat, $long], {icon: $icon, gridref: $gr, no: $no, lat: $lat, long: $long});
    if ($gr.length === 8) {
        $grdisp = $gr.substr(0, 2) + " " + $gr.substr(2, 3) + " " + $gr.substr(5, 3);
    } else {
        $grdisp = $gr;
    }
    marker.bindPopup("<b>Grid Ref " + $grdisp + "</b><br/>Lat/Long " + $lat + " " + $long);
    marker.on('click', onClickPlaceMarker);
    ramblersMap.markerList.push(marker);
}
function addPlaceMarker($gr, $no, $lat, $long) {
    var $icon, $grdisp;
    switch ($no) {
        case 0:
            $icon = ramblersMap.s0;
            break;
        case 1:
            $icon = ramblersMap.s1;
            break;
        case 2:
            $icon = ramblersMap.s2;
            break;
        case 3:
            $icon = ramblersMap.s3;
            break;
        case 4:
            $icon = ramblersMap.s4;
            break;
        case 5:
            $icon = ramblersMap.s5;
            break;
    }

    var marker = L.marker([$lat, $long], {icon: $icon, gridref: $gr, no: $no, lat: $lat, long: $long});
    if ($gr.length === 8) {
        $grdisp = $gr.substr(0, 2) + " " + $gr.substr(2, 3) + " " + $gr.substr(5, 3);
    } else {
        $grdisp = $gr;
    }
    marker.gr = $gr;
    var text = "<br/><b>Searching for usage details ...</b>";
    marker.bindPopup("<b>Grid Ref " + $grdisp + "</b><br/>Lat/Long " + $lat + " " + $long + text, {maxWidth: 800});
    marker.on('click', onClickPlaceMarker, marker);
    return marker;
}
function getBounds(list) {
    var bounds = new L.LatLngBounds();
    var marker, i;
    for (i = 0; i < list.length; i++) {
        marker = list[i];
        bounds.extend(marker.getLatLng());
    }
    return bounds;
}

function walkdetails($url) {
    var page = $url;
    window.open(page, "_blank", "scrollbars=yes,width=990,height=480,menubar=yes,resizable=yes,status=yes");
}

function photos($gr) {
    var page = "http://www.geograph.org.uk/gridref/" + $gr;
    window.open(page, "_blank", "scrollbars=yes,width=990,height=480,menubar=yes,resizable=yes,status=yes");
}
function streetmap($gr) {
    var page = "http://www.streetmap.co.uk/grid/" + $gr + "&z=115";
    window.open(page, "_blank", "scrollbars=yes,width=900,height=580,menubar=yes,resizable=yes,status=yes");
}
function directions($lat, $long) {
//  var $directions = "<a href='https://maps.google.com?saddr=Current+Location&daddr=" + $lat + "," + $long + "' target='_blank'>[Directions]</a>";
    var page = "https://maps.google.com?saddr=Current+Location&daddr=" + $lat.toString() + "," + $long.toString();
    window.open(page, "_blank", "scrollbars=yes,width=900,height=580,menubar=yes,resizable=yes,status=yes");
}
function googlemap($lat, $long) {
    var page = "https://www.google.com/maps/place/" + $lat.toString() + "+" + $long.toString() + "/@" + $lat.toString() + "," + $long.toString() + ",15z";
    window.open(page, "Google Map", "scrollbars=yes,width=900,height=580,menubar=yes,resizable=yes,status=yes");
}

function kFormatter(num) {
    return num > 999 ? (num / 1000).toFixed(1) + 'K' : num;
}

function getBrowserStatus() {
    var out = "";
    out += "<br/>Mobile: " + L.Browser.mobile.toString();
    out += "<br/>Touch: " + L.Browser.touch.toString();
    out += "<br/>Pointer : " + L.Browser.pointer.toString();
    out += "<br/>Win: " + L.Browser.win.toString();
    out += "<br/>Android: " + L.Browser.android.toString();
    return out;
}

var getJSON = function (url, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open("get", url, true);
    xhr.responseType = "json";
    xhr.onload = function () {
        var status = xhr.status;
        var items;
        if (status === 200) {
            if (typeof xhr.response === 'string') {
                items = JSON.parse(xhr.response);
            } else {
                items = xhr.response;
            }
            callback(null, items);
        } else {
            callback(status);
        }
    };
    xhr.send();
};
var postJSON = function (url, data, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.responseType = "json";
    xhr.onload = function () {
        var status = xhr.status;
        var items;
        if (status === 200) {
            if (typeof xhr.response === 'string') {
                items = JSON.parse(xhr.response);
            } else {
                items = xhr.response;
            }
            callback(null, items);
        } else {
            callback(status);
        }
    };
    xhr.send(data);
};
function getMouseMoveAction(e) {
    var gr, gridref;
    var zoom = ramblersMap.map.getZoom();
    var p = new LatLon(e.latlng.lat, e.latlng.lng);
    var grid = OsGridRef.latLonToOsGrid(p);
    if (zoom > 16) {
        gr = grid.toString(8);
        gridref = '8 figure grid reference [10m<sup>2</sup>]<br/><span class="osgridref">' + gr + "</span><br/>";
    } else {
        gr = grid.toString(6);
        gridref = '6 figure grid reference [100m<sup>2</sup>]<br/><span class="osgridref">' + gr + "</span><br/>";
    }
    if (gr === "") {
        gridref = "Outside OS Grid<br/>";
    } else {
        if (ramblersMap.displayMouseGridSquare) {
            if (zoom > 12) {
                var bounds = osGridToLatLongSquare(grid, 100);
                // change rectangle
                ramblersMap.gridsquare100.setLatLngs(bounds);
            }
            if (zoom > 16) {
                var bounds2 = osGridToLatLongSquare(grid, 10);
                // change rectangle
                ramblersMap.gridsquare10.setLatLngs(bounds2);
            } else {
                ramblersMap.gridsquare10.setLatLngs([[84, -89], [84.00001, -89.000001]]);
            }
        } else {
            ramblersMap.gridsquare10.setLatLngs([[84, -89], [84.00001, -89.000001]]);
            ramblersMap.gridsquare100.setLatLngs([[84, -89], [84.00001, -89.000001]]);
        }
    }
    var lng = e.latlng.lng.toFixed(5);
    var lat = e.latlng.lat.toFixed(5);
    var value = "Lat/long: " + lat + ", " + lng; //+" z"+ zoom;
    //  value += '<br/>' + getMapCode(e.latlng.lat, e.latlng.lng, true);
    //  value += '<br/>' + getPlusCode(e.latlng.lat, e.latlng.lng, true);
    return  gridref + value;
}

function getPlusCode(lat, lng, link) {
    var pluscode = OpenLocationCode.encode(lat, lng);
    if (link) {
        return '<b><a href="https://plus.codes" target="_blank">Open Location Code:</a></b>' + pluscodeFormat(pluscode);
    } else {
        return  pluscodeFormat(pluscode);
    }
}
function getMapCode(lat, lng, link) {
    var results = encodeShortest(lat, lng);
    var out = "";
    if (results.length > 0) {
        if (link) {
            out = '<b><a href="http://www.mapcode.com" target="_blank">Mapcode:</a>' + results[0].mapcode + "</b> (" + results[0].territoryAlphaCode + "}";
        } else {
            out = results[0].mapcode + " " + results[0].territoryAlphaCode;
        }
    }
    return out;
}
function getWhat3Words(lat, lng, id, place) {
    var w3wurl = "https://api.what3words.com/v3/convert-to-3wa?key=6AZYMY7P&coordinates=";
    var url = w3wurl + lat.toFixed(7) + ',' + lng.toFixed(7);
    getJSON(url, function (err, items) {
        var tag = document.getElementById(id);
        if (err !== null || tag === null) {
            tag.innerHTML = "Error accessing What3Words: " + err + "<br/>";
        } else {
            var out = '<span class="w3w" onclick="javascript:w3wAboutUs()">///</span><abbr title=\'What3words - click /// to learn more\'>  ' + items.words + '</abbr><br/>';
            if (place) {
                out += '<b>Nearest Place: </b>' + items.nearestPlace + '<br/>';
            }
            tag.innerHTML = out;
        }
    });
}

function w3wAboutUs() {
    var page = "https://what3words.com/about-us/";
    window.open(page, "_blank", "scrollbars=yes,width=990,height=480,menubar=yes,resizable=yes,status=yes");
}

function pluscodeFormat(code) {
    var out = code.substr(0, 4) + "<b>" + code.substr(4) + "</b>";
    return out;
}

function osGridToLatLongSquare(gridref, size) {
//  if (!(gridref instanceof OsGridRef))
//      throw new TypeError('gridref is not OsGridRef object');

    var E1 = Math.floor(gridref.easting / size) * size;
    var N1 = Math.floor(gridref.northing / size) * size;
    var E2 = E1 + size;
    var N2 = N1 + size;
    var g1 = new OsGridRef(E1, N1);
    var g2 = new OsGridRef(E1, N2);
    var g3 = new OsGridRef(E2, N2);
    var g4 = new OsGridRef(E2, N1);
    var ll1 = OsGridRef.osGridToLatLon(g1);
    var ll2 = OsGridRef.osGridToLatLon(g2);
    var ll3 = OsGridRef.osGridToLatLon(g3);
    var ll4 = OsGridRef.osGridToLatLon(g4);
    var bounds = [[ll1.lat, ll1.lon],
        [ll2.lat, ll2.lon],
        [ll3.lat, ll3.lon],
        [ll4.lat, ll4.lon],
        [ll1.lat, ll1.lon]];
    return bounds;
}
function osMapGrid(layer) {
    var style = {color: '#333366', weight: 1, opacity: 0.2};
    var lines;
    for (east = 0; east < 700500; east += 100000) {
        lines = new Array();
        i = 0;
        for (north = 0; north < 1300500; north += 10000) {
            var gr = new OsGridRef(east, north);
            var latlong = OsGridRef.osGridToLatLon(gr);
            lines[i] = new L.latLng(latlong.lat, latlong.lon);
            i++;
        }
// L.polyline(lines, style).addTo(map);
        layer.addLayer(L.polyline(lines, style));
    }
    for (var north = 0; north < 1300500; north += 100000) {
        lines = new Array();
        i = 0;
        for (var east = 0; east < 700500; east += 10000) {
            gr = new OsGridRef(east, north);
            latlong = OsGridRef.osGridToLatLon(gr);
            lines[i] = new L.latLng(latlong.lat, latlong.lon);
            i++;
        }
// L.polyline(lines, style).addTo(map);
        layer.addLayer(L.polyline(lines, style));
    }
}

function m_to_km(v) {
    return v / 1000;
}

function  m_to_mi(v) {
    return v / 1609.34;
}
function naismith(dist, elevGain) {
    var mins = 60 * dist / 5000 + 60 * elevGain / 600;
    return convertToHoursMins(mins);
}
function convertToHoursMins(time) {
    if (time < 1) {
        return '';
    }
    var h = Math.floor(time / 60);
    var m = (time % 60);
    return  h + 'hrs ' + m.toFixed(0) + 'mins';
    ;
}
function setMarkerIcon(marker, name) {
    var icon;
    if (name === "") {
        icon = L.icon({
            iconUrl: ramblersMap.base + 'libraries/ramblers/leaflet/images/marker-icon.png',
            iconSize: [25, 41], // size of the icon
            iconAnchor: [12, 41],
            popupAnchor: [0, -41]
        });
        marker.setIcon(icon);
        return;
    }
    var file = ramblersMap.base + "libraries/ramblers/gpxsymbols/exists.php?file=" + name + ".png";
    marker.file = ramblersMap.base + "libraries/ramblers/gpxsymbols/" + name + ".png";
    ajaxGet(file, "", marker, setIcon);
}
function setIcon(marker, response) {
    var url = marker.file;
    var icon;
    marker.file = null;
    if (response === 'true') {
        icon = L.icon({
            iconUrl: url,
            iconSize: [32, 37],
            iconAnchor: [16, 37],
            popupAnchor: [0, -41]
        });
    } else {
        icon = L.icon({
            iconUrl: ramblersMap.base + 'libraries/ramblers/leaflet/images/redmarker.png',
            iconSize: [36, 41], // size of the icon
            iconAnchor: [18, 41],
            popupAnchor: [0, -41]
        });
    }
    marker.setIcon(icon);
}

function ajaxGet($url, $params, target, displayFunc) {
    var xmlhttp;
    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else
    {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
        {
            displayFunc(target, xmlhttp.responseText);
            // document.getElementById($div).innerHTML = xmlhttp.responseText;
        }
    };
    xmlhttp.open("GET", $url, true);
    //Send the proper header information along with the request
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    //   xmlhttp.setRequestHeader("Content-length", $params.length);
    //   xmlhttp.setRequestHeader("Connection", "close");
    xmlhttp.send($params);
}


function onClickPlaceMarker(e) {
    var marker = e.target;
    var $url = "https://places.walkinginfo.co.uk/details.php?gr=" + this.options.gridref;
    ajax($url, "", marker, displayDetails);
}
function displayDetails(marker, result) {
    var popup = marker.getPopup();
    var ll = marker.getLatLng();
    var $grdisp = marker.gr;
    if ($grdisp.length === 8) {
        $grdisp = $grdisp.substr(0, 2) + " " + $grdisp.substr(2, 3) + " " + $grdisp.substr(5, 3);
    }
    var gr = new LatLon(ll.lat, ll.lng);
    gr = OsGridRef.latLonToOsGrid(gr);
    var json = JSON.parse(result);
    var nolikes = "";
    if (json.likes > 0) {
        nolikes = "<sup>" + json.likes + "</sup>";
    }
    var nodislikes = "";
    if (json.dislikes > 0) {
        nodislikes = "<sup>" + json.dislikes + "</sup>";
    }
    var like = "<span class=\"agreebutton hasTip\" title=\"VOTE: This location is correct\"><a href=\"javascript:placecorrect('" + marker.gr + "') \"> &#9745;</a>" + nolikes + " </span>";
    var dislike = "<span class=\"agreebutton hasTip\" title=\"VOTE: This location is INCORRECT\"><a href=\"javascript:placeincorrect('" + marker.gr + "') \"> &#9746;</a>" + nodislikes + " </span>";
    var streetmap = "<span class=\"placebutton-green hasTip\" title=\"View location in streetmap.co.uk\"><a href=\"javascript:streetmap('" + marker.gr + "') \">Streetmap</a></span>";
    var google = "<span class=\"placebutton-green hasTip\" title=\"View location in Google maps\"><a href=\"javascript:googlemap(" + ll.lat + "," + ll.lng + ") \">Google Map</a></span>";
    var out = "<span class='placelocation'>Place Grid Ref " + $grdisp + " </span>" + like + dislike + streetmap + google;
    out += "<div id=" + marker.gr + "></div>";
    out += "<div >" + gr.toString(8) + "</div>";
    out += "<p><b>Description</b> [Date used / Score]</p>";
    out += "<ul>";
    var items = json.records;
    for (i = 0; i < items.length; i++) {
        var item = items[i];
        if (item.desc === "") {
            item.desc = "<i>no description</i>";
        }
        out += "<li>" + item.desc + " [" + item.lastread + "/" + item.score + "%]</li>";
    }
    out += "</ul>";
    popup.setContent(out);
    popup.update();
}

function placecorrect(gr) {
    var $url = "https://places.walkinginfo.co.uk/report.php?gr=" + gr + "&type=like";
    ajax($url, "", gr, votelike);
}
function placeincorrect(gr) {
    var $url = "https://places.walkinginfo.co.uk/report.php?gr=" + gr + "&type=dislike";
    ajax($url, "", gr, votedislike);
}
function votelike(gr, result) {
    document.getElementById(gr).innerHTML = "Correct vote recorded";
}
function votedislike(gr, result) {
    document.getElementById(gr).innerHTML = "Incorrect vote recorded";
}

