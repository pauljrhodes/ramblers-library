var L, ramblersMap;
// code for drawing route/track
function addDrawControl(lat, long, zoom) {
    // zoom to correct area
    ramblersMap.map.setZoom(zoom);
    latlong = new L.LatLng(lat, long);
    ramblersMap.map.panTo(latlong);
    // add elevation display
    var imperial = false;
    ramblersMap.elevationcontrol = L.control.elevation({
        position: "topleft",
        theme: "steelblue-theme", //default: lime-theme
        width: 600,
        height: 125,
        margins: {
            top: 10,
            right: 20,
            bottom: 30,
            left: 50
        },
        useHeightIndicator: true, //if false a marker is drawn at map position
        interpolation: "linear", //see https://github.com/mbostock/d3/wiki/SVG-Shapes#wiki-area_interpolate
        hoverNumber: {
            decimalsX: 1, //decimals on distance (always in km)
            decimalsY: 0, //deciamls on hehttps://www.npmjs.com/package/leaflet.coordinatesight (always in m)
            formatter: undefined //custom formatter function may be injected
        },
        xTicks: undefined, //number of ticks in x axis, calculated by default according to width
        yTicks: undefined, //number of ticks on y axis, calculated by default according to height
        collapsed: true, //collapsed mode, show chart on click or mouseover
        imperial: imperial    //display imperial units instead of metric
    });
    ramblersMap.elevationcontrol.addTo(ramblersMap.map);

    ramblersMap.drawnItems = new L.FeatureGroup();
    L.drawLocal.draw.toolbar.buttons.polyline = 'Plot a walking route(s)';
    L.drawLocal.draw.toolbar.buttons.marker = 'Add a marker';
    L.drawLocal.edit.toolbar.buttons.edit = 'Edit walking route(s) and markers';
    L.drawLocal.edit.toolbar.buttons.editDisabled = 'No routes(s) to edit';
    L.drawLocal.edit.toolbar.buttons.remove = 'Delete walking route(s) or markers';
    L.drawLocal.edit.toolbar.buttons.removeDisabled = 'No route(s) to delete';

    ramblersMap.map.addLayer(ramblersMap.drawnItems);

    // load gpx download control
    var download = new L.Control.GpxDownload();
    download.setRouteItems(ramblersMap.drawnItems);
    ramblersMap.map.addControl(download);

    var upload = new L.Control.GpxUpload();
    upload.setRouteItems(ramblersMap.drawnItems);
    ramblersMap.map.addControl(upload);

    var reverse = new L.Control.ReverseRoute();
    reverse.setRouteItems(ramblersMap.drawnItems);
    ramblersMap.map.addControl(reverse);

    var simplify = new L.Control.GpxSimplify();
    simplify.setRouteItems(ramblersMap.drawnItems);
    ramblersMap.map.addControl(simplify);


    var drawControl = new L.Control.Draw({
        position: 'bottomright',
        draw: {
            polyline: {
                metric: true,
                shapeOptions: {
                    color: '#782327',
                    opacity: 1,
                    weight: 3
                }
            },
            polygon: false,
            marker: true,
            circle: false,
            circlemarker: false,
            rectangle: false
        },
        edit: {
            featureGroup: ramblersMap.drawnItems,
            title: 'Edit walking route',
            polyline: {
                metric: true,
                shapeOptions: {
                    color: '#007A87',
                    opacity: 1
                }
            }
        }
    });

    ramblersMap.map.addControl(drawControl);

    ramblersMap.map.on(L.Draw.Event.CREATED, function (e) {
        var type = e.layerType,
                layer = e.layer;
        if (type === 'marker') {
            layer.bindPopup('A popup!');
            var marker = layer;
            marker.name = '';
            marker.desc = '';
            marker.symbol = '';
        }
        ramblersMap.drawnItems.addLayer(layer);
        ramblersMap.map.addLayer(ramblersMap.drawnItems);
        addElevations(false);
    });
    ramblersMap.map.on('browser-print-start', function (e) {
        L.control.scale({
            position: 'topleft',
            imperial: false,
            maxWidth: 200
        }).addTo(e.printMap);
    });
    ramblersMap.drawnItems.on("upload:addline", function (e) {
        var bounds = ramblersMap.drawnItems.getBounds();
        ramblersMap.map.fitBounds(bounds);
        setGpxToolStatus('auto');
        addElevations(false);
    });
    ramblersMap.drawnItems.on('upload:addpoint', function (e) {
        if (e.point_type === "waypoint") {
            var marker = e.point;
            var sSymbol = marker.symbol;
            setMarkerIcon(marker, sSymbol);
        }
    });
    ramblersMap.drawnItems.on('upload:loaded', function (e) {
        download.set_name(upload.get_name());
        download.set_desc(upload.get_desc());
        download.set_author(upload.get_author());
        download.set_copyright(upload.get_copyright());
        download.set_date(upload.get_date());
    });
    ramblersMap.drawnItems.on("reverse:reversed", function (e) {
        addElevations(false);
    });

    ramblersMap.map.on(L.Draw.Event.DRAWSTART, function (e) {
        ramblersMap.displayMouseGridSquare = false;
        enableMapMoveDrawing();
        setGpxToolStatus('off');
    });
    ramblersMap.map.on(L.Draw.Event.DRAWSTOP, function () {
        ramblersMap.displayMouseGridSquare = true;
        disableMapMoveDrawing();
        addElevations(false);
        setGpxToolStatus('auto');

    });
    ramblersMap.map.on(L.Draw.Event.EDITSTART, function (e) {
        ramblersMap.map.setMaxZoom(22);
        ramblersMap.displayMouseGridSquare = false;
        enableMapMoveDrawing();
        setGpxToolStatus('off');
    });
    ramblersMap.map.on(L.Draw.Event.EDITED, function (e) {
        ramblersMap.map.setMaxZoom(18);
        ramblersMap.displayMouseGridSquare = true;
        disableMapMoveDrawing();
        addElevations(true);
        setGpxToolStatus('auto');
    });
    ramblersMap.map.on(L.Draw.Event.EDITSTOP, function (e) {
        ramblersMap.map.setMaxZoom(18);
        ramblersMap.displayMouseGridSquare = true;
        disableMapMoveDrawing();
        setGpxToolStatus('auto');
    });
    ramblersMap.map.on(L.Draw.Event.DELETESTART, function (e) {
        ramblersMap.displayMouseGridSquare = false;
        enableMapMoveDrawing();
        setGpxToolStatus('off');
    });
    ramblersMap.map.on(L.Draw.Event.DELETESTOP, function (e) {
        ramblersMap.displayMouseGridSquare = true;
        disableMapMoveDrawing();
        listDrawnItems();
        setGpxToolStatus('auto');
    });
    ramblersMap.map.on(L.Draw.Event.DELETED, function (e) {
        ramblersMap.displayMouseGridSquare = true;
        disableMapMoveDrawing();
        listDrawnItems();
        setGpxToolStatus('auto');
    });

    ramblersMap.map.on('simplify:started', function () {
        setGpxToolStatus('off');
        ramblersMap.map.removeControl(drawControl);

    });
    ramblersMap.map.on('simplify:saved', function () {
        setGpxToolStatus('auto');
        addElevations(true);
        ramblersMap.map.addControl(drawControl);
    });
    ramblersMap.map.on('simplify:cancelled', function () {
        setGpxToolStatus('auto');
        ramblersMap.map.addControl(drawControl);
    });


    ramblersMap.drawnItems.on('popupopen', function (e) {
        if (ramblersMap.processPopups === 'off') {
            return;
        }
        var marker = e.popup._source;
        if (typeof (marker) === 'undefined') {
            return;
        }
        findMarker(marker);
        if (marker.found) {
            var popup = marker.getPopup();
            var content = '<span><b>Name</b></span><br/><input id="markerName" type="text"/ value="' + marker.name + '" /><br/><span><b>Description<b/></span><br/><textarea id="markerDesc" style="resize:vertical;">' + marker.desc + '</textarea><br/><span><b>Symbol</b></span><br/><input id="markerSymbol" type="text" value="' + marker.symbol + '"/>';
            popup.setContent(content);
        }
        //    alert('open');
    });
    function findMarker(marker) {
        marker.found = false;
        ramblersMap.drawnItems.eachLayer(function (layer) {
            if (layer instanceof L.Marker) {
                if (layer === marker) {
                    marker.found = true;
                }
            }
        });
    }
    ramblersMap.drawnItems.on('popupclose', function (e) {
        if (ramblersMap.processPopups === 'off') {
            return;
        }
        var marker = e.popup._source;
        if (typeof (marker) === 'undefined') {
            return;
        }
        findMarker(marker);
        if (marker.found) {
            var popup = marker.getPopup();
            var sName = getElementValue('markerName');
            var sDesc = getElementValue('markerDesc');
            var sSymbol = getElementValue('markerSymbol');
            popup.setContent(sDesc);
            marker.name = sName;
            marker.desc = sDesc;
            marker.symbol = sSymbol;
            marker.title = sName + " - " + sDesc;
            setMarkerIcon(marker, sSymbol);

            //       marker.fire('revert-edited', {layer: marker});
        }
    });
    ramblersMap.map.on('popupclose', function (e) {
        download._popupclose(e);
    });

    function getElementValue(id) {
        var node = document.getElementById(id);
        if (node !== null) {
            return node.value;
        }
        return "Invalid";
    }
    function setGpxToolStatus(status) {
        ramblersMap.processPopups = status;
        reverse.setStatus(status);
        download.setStatus(status);
        simplify.setStatus(status);
        upload.setStatus(status);
        if (ramblersMap.postcodes !== null) {
            ramblersMap.postcodes.Enabled(status !== 'off');
        }

    }

//Move the map around when we're editing or drawing
    enableMapMoveDrawing = function () {
        ramblersMap.map.on('mousemove', mapMoveDrawingMouseMove, this);
    };
    mapMoveDrawingMouseMove = function (e) {
        var mousePos = e.containerPoint;
        var mapSize = ramblersMap.map.getSize();

        if (mousePos.y <= 20) {
            ramblersMap.map.panBy([0, -40]);
        } else if (mousePos.y + 20 > mapSize.y) {
            ramblersMap.map.panBy([0, 40]);
        }

        if (mousePos.x <= 20) {
            ramblersMap.map.panBy([-40, 0]);
        } else if (mousePos.x + 20 > mapSize.x) {
            ramblersMap.map.panBy([40, 0]);
        }
    };
    disableMapMoveDrawing = function () {
        ramblersMap.map.off('mousemove', mapMoveDrawingMouseMove, this);
    };
}
function listDrawnItems() {
    var hasItems = ramblersMap.drawnItems.getLayers().length !== 0;
    ramblersMap.elevationcontrol.clear();
    var text = "";
    if (!hasItems) {
        text = '<p>No routes or markers defined</p>';
    } else {
        text += "<table>";
        text += "<tr><th>Segment</th><th>Length</th><th>Elevation Gain</th><th>Est Time</th><th>Number of points</th></tr>";
        ramblersMap.drawnItems.eachLayer(function (layer) {
            var i = 1;
            if (layer instanceof L.Polyline) {
                ramblersMap.elevationcontrol.addData(layer);
                text += listpath(i, layer.getLatLngs());
                i += 1;
            }
        });
        text += "</table>";
    }
    var node = document.getElementById("ra-map-details");
    if (node !== null) {
        node.innerHTML = text;
    }
}

function listpath(i, latlngs) {
    var dist = polylineDistance(latlngs);
    var elevGain = polylineElevationGain(latlngs);
    var estTime = naismith(dist, elevGain);
    var text = "<tr><td>" + i + "</td><td> " + getGPXDistance(dist) + "</td><td>" + elevGain + "m</td><td>" + estTime + "</td><td>" + latlngs.length + "</td></tr>";
    return text;
}

function polylineDistance(latlngs) {
    var i, len;
    i = 0;
    len = latlngs.length;
    var tempLatLng = null;
    var totalDistance = 0.00000;
    for (i = 0, len = latlngs.length; i < len; i++) {
        latlng = latlngs[i];
        if (tempLatLng === null) {
            tempLatLng = latlng;
        } else {
            totalDistance += tempLatLng.distanceTo(latlng);
            tempLatLng = latlng;
        }
    }
    return totalDistance;
}
function polylineElevationGain(latlngs) {
    var i, len;
    i = 0;
    len = latlngs.length;
    var tempLatLng = null;
    var elevationGain = 0.00000;
    for (i = 0, len = latlngs.length; i < len; i++) {
        latlng = latlngs[i];
        if (tempLatLng === null) {
            tempLatLng = latlng;
        } else {
            if (latlng.alt > tempLatLng.alt) {
                elevationGain += latlng.alt - tempLatLng.alt;
            }
            tempLatLng = latlng;
        }
    }
    return  elevationGain;
}
function addElevations(force) {
    var node = document.getElementById("ra-map-details");
    if (node !== null) {
        node.innerHTML = "<br/><h4>Fetching elevations - please wait...</h4>";
    }
    var hasItems = ramblersMap.drawnItems.getLayers().length !== 0;
    if (hasItems) {
        ramblersMap.drawnItems.eachLayer(function (layer) {
            if (layer instanceof L.Polyline) {
                addElevation(layer.getLatLngs());
            }
        });
    }
    getElevations(force);
}
function addElevation(latlngs) {
    var i, len;
    i = 0;
    len = latlngs.length;
    for (i = 0, len = latlngs.length; i < len; i++) {
        if (typeof (latlngs[i].alt) === 'undefined') {
            // latlngs[i].meta = {ele: null};
            latlngs[i].alt = -999;
        }
    }
    return;
}
function getElevations(force) {
    var hasItems = ramblersMap.drawnItems.getLayers().length !== 0;
    var nullItems = [];
    if (hasItems) {
        ramblersMap.drawnItems.eachLayer(function (layer) {
            if (layer instanceof L.Polyline) {
                hasNullElevation(layer.getLatLngs(), nullItems, force);
            }
        });
    }
    if (nullItems.length > 0) { // need to fetch elevations
        if (nullItems.length > 500) {
            blurt("Tracks contains more than 500 points, response may be slow, please wait...");
        }
        points = "data=" + JSON.stringify(nullItems);
        var url = "https://elevation.theramblers.org.uk/";
        postJSON(url, points, function (err, items) {
            if (err !== null) {
                var msg = "Error: Something went wrong: " + err;
                alert(msg);
            } else {
                for (i = 0; i < items.length; i++) {
                    var item = items[i];
                    updateElevations(item);
                }
            }
            listDrawnItems();
        });
    } else {
        listDrawnItems();
    }
}
function hasNullElevation(latlngs, nullItems, force) {
    var i, len;
    i = 0;
    len = latlngs.length;
    for (i = 0, len = latlngs.length; i < len; i++) {
        //   if (latlngs[i].meta.ele === null || force) {
        if (latlngs[i].alt === "undefined" || latlngs[i].alt === -999 || force) {
            var point = [latlngs[i].lat, latlngs[i].lng];
            nullItems.push(point);
        }
    }
}
function updateElevations(item) {
    var noItems = ramblersMap.drawnItems.getLayers().length;
    if (noItems > 0) {
        ramblersMap.drawnItems.eachLayer(function (layer) {
            if (layer instanceof L.Polyline) {
                updateElevation(layer.getLatLngs(), item);
            }
        });
    }
}
function updateElevation(latlngs, item) {
    var i, len;
    i = 0;
    len = latlngs.length;
    for (i = 0, len = latlngs.length; i < len; i++) {
        if (Math.abs(latlngs[i].lat - item[0]) < .000001 & Math.abs(latlngs[i].lng - item[1]) < .000001) {
            latlngs[i].alt = item[2];
        }
    }
}