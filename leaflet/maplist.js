/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var L, ramblersMap, ramblersGpx, markerRoute;

function RamblersLeafletGpx() {
    this.routes = null;
    this.folder = null;
    this.linecolour = "#782327";
    this.dateorder = false;
    this.authororder = false;
    this.distorder = false;
    this.titleorder = false;
    this.gainorder = false;
    this.download = 0;
    this.elevation = null;
    this.description = true;
    this.displayAsPreviousWalks = false;
    this.gpx = null;
    this.searchtext = '';
}
function displayGPX(file, linecolour, imperial) {
    // remove old gpx route
    if (ramblersGpx.elevation !== null) {
        ramblersGpx.elevation.remove();
        ramblersGpx.elevation = null;
    }
    if (ramblersGpx.gpx !== null) {
        ramblersGpx.gpx.remove();
        ramblersGpx.gpx = null;
    }
    var el = L.control.elevation({
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
    el.addTo(ramblersMap.map);
    var g = new L.GPX(ramblersMap.base + file, {async: true,
        polyline_options: {color: linecolour},
        marker_options: {
            startIconUrl: ramblersMap.base + 'ramblers/leaflet/images/pin-icon-start.png',
            endIconUrl: ramblersMap.base + 'ramblers/leaflet/images/pin-icon-end.png',
            shadowUrl: ramblersMap.base + 'ramblers/leaflet/images/pin-shadow.png'
        }});
    g.on('addline', function (e) {
        el.addData(e.line);
    });
    g.on('addpoint', function (e) {
        if (e.point_type === "waypoint") {
            var marker = e.point;
            icon = L.icon({
                iconUrl: ramblersMap.base + 'ramblers/leaflet/images/redmarker.png',
                iconSize: [36, 41], // size of the icon
                iconAnchor: [18, 41],
                popupAnchor: [0, -41]
            });
            marker.setIcon(icon);
            var sSymbol = marker.options.iconkey;
            setMarkerIcon(marker, sSymbol);
        }
    });
    g.on('loaded', function (e) {
        ramblersMap.map.fitBounds(e.target.getBounds());
        displayGpxdetails(g);
    });
    g.addTo(ramblersMap.map);
    ramblersGpx.elevation = el;
    ramblersGpx.gpx = g;

}
function displayGpxdetails(g) {
    if (document.getElementById('gpxsingleheader') !== null) {
        var info = g._info;
        var header = "";
        if (info !== "undefined" && info !== null) {
            if (info.name !== "undefined" && info.name !== null) {
                header = '<b>Name:</b> ' + info.name + "<br/>";
            }
            header += '<b>Distance:</b> ' + getGPXDistance(info.length) + '<br/>';
            if (info.desc !== "undefined" && info.desc !== null) {
                header += '<b>Description:</b> ' + info.desc + '<br/>';
            }
            if (info.elevation.gain === 0) {
                header += "No elevation data<br/>";
            } else {
                header += '<b>Min Altitude:</b> ' + info.elevation.min.toFixed(0) + ' m<br/>';
                header += '<b>Max Altitude:</b> ' + info.elevation.max.toFixed(0) + ' m<br/>';
                header += '<b>Elevation Gain:</b> ' + info.elevation.gain.toFixed(0) + ' m<br/>';
            }
            header += "<b>Est time:</b> " + naismith(info.length, info.elevation.gain);
            document.getElementById('gpxsingleheader').innerHTML = header;
        }
    }
}

// Tab control

function ra_tab(evt, name) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("ra_tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("ra_tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(name).style.display = "block";
    evt.currentTarget.className += " active";
}

function openTab(evt, tabName) {
// Declare all variables
    var i, tabcontent, tablinks;
    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

// Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

// Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}

function showhide(evt, idName) {
    var x = document.getElementById(idName);
    if (x.style.display === "none") {
        x.style.display = "block";
    } else {
        x.style.display = "none";
    }

}

// code to handle list of GPX Routes
function displayGPXNames() {
    var out;
    out = "<div class='gpxlist'>";
    out += '<ul>';
    for (index = 0; index < ramblersGpx.routes.length; ++index) {
        var route = ramblersGpx.routes[index];
        if (displayRoute(route)) {
            out += "<li>" + displayGPXName(route) + "</li>";
        }
    }
    out += '</ul>';
    out += '</div>';
    document.getElementById("tabRouteList").innerHTML = out;
}
function displayGPXName(route) {
    link = '<b><a href="javascript:updateGPXid(' + route.id + ')">' + route.title + '</a></b>';
    return link;
}
function displayGPXTable() {
    var out;
    var tag;
    var extra = "";
    tag = document.getElementById("tabRouteDetails");
    if (tag !== null) {
        out = '<table id="gpxdetails">';
        if (ramblersGpx.displayAsPreviousWalks) {
            extra = "<th onclick='sortGPXTable(\"date\")'>Date</th><th onclick='sortGPXTable(\"author\")'>Leader</th>";
        }
        out += "<tr>" + extra + "<th onclick='sortGPXTable(\"title\")'>Title</th><th onclick='sortGPXTable(\"distance\")'>Distance</th><th>min Altitude</th><th>max Altitude</th><th onclick='sortGPXTable(\"gain\")'>Elevation Gain</th>";
        if (ramblersGpx.download === 0) {
            out += "</tr>";
        } else {
            out += "<th>GPX</th></tr>";
        }
        for (index = 0; index < ramblersGpx.routes.length; ++index) {
            var route = ramblersGpx.routes[index];
            if (displayRoute(route)) {
                out += '<tr>';
                out += displayGPXRow(route);
                out += '</tr>';
            }
        }
        out += '</table>';
        if (ramblersGpx.download === 1) {
            out += "<p>* To be able to download GPX Routes, you need to log on to our web site.</p>";
        }
        tag.innerHTML = out;
    }
}
function displayGPXRow(route) {
    var link = "";
    if (ramblersGpx.displayAsPreviousWalks) {
        link += '<td><b>' + route.date + '</b></td>';
        link += '<td class="alignleft">' + route.author + '</td>';
    }
    link += '<td class="alignleft"><b><a href="javascript:updateGPXid(' + route.id + ')">' + route.title + '</a></b></td>';
    link += '<td>' + getGPXDistance(route.distance) + '</td>';
    if (route.cumulativeElevationGain === 0) {
        link += '<td>...</td>';
        link += '<td>...</td>';
        link += '<td>...</td>';
    } else {
        link += '<td>' + route.minAltitude.toFixed(0) + '</td>';
        link += '<td>' + route.maxAltitude.toFixed(0) + '</td>';
        link += '<td>' + route.cumulativeElevationGain.toFixed(0) + '</td>';
    }
    link += '<td>' + getGPXdownloadLink(route) + '</td>';
    return link;
}
function getGPXDistance(distance) {
    var dist, miles;
    dist = m_to_km(distance);
    miles = m_to_mi(distance);
    return dist.toFixed(1) + ' km / ' + miles.toFixed(2) + 'mi';
}
function updateGPXid(id) {
    var header, path;
    var route = getRoutefromID(id);
    header = "<h2>" + route.title + "</h2>";
    header += "<button style='float:right' class=\"link-button button-p5565 small white\" onclick=\"showhide(event, 'gpxDetails')\">Show/Hide Details</button><div id='gpxDetails'><p>";
    if (ramblersGpx.displayAsPreviousWalks) {
        header += '<b>Date:</b> ' + route.date + '<br/>';
        header += '<b>Leader:</b> ' + route.author + '<br/>';
    }
    header += '<b>Distance:</b> ' + getGPXDistance(route.distance) + '<br/>';
    if (route.description !== '') {
        header += '<b>Description:</b> ' + route.description + '<br/>';
    }
    header += formatAltitude(route);
    header += "<b>Est time:</b> " + naismith(route.distance, route.cumulativeElevationGain) + '<br/>';
    header += "<b>Download route:</b> " + getGPXdownloadLink(route) + '<br/>';
    if (route.tracks > 0) {
        header += "<b>Tracks:</b> " + route.tracks.toFixed(0);
    }

    if (route.routes > 0) {
        header += "<b>Routes:</b> " + route.routes.toFixed(0);
    }

    header += "</p></div>";
    path = ramblersGpx.folder + "/" + route.filename;
    document.getElementById('gpxheader').innerHTML = header;
    displayGPX(path, ramblersGpx.linecolour, 0);
    location.hash = '#gpxheader';
}
function getRoutefromID(id) {
    for (index = 0; index < ramblersGpx.routes.length; ++index) {
        var route = ramblersGpx.routes[index];
        if (route.id === id) {
            return route;
        }
    }
    return null;
}
function displayGPXDescriptions() {
    var out;
    out = "<div class='gpxdescriptions'>";
    out += '<p>';
    for (index = 0; index < ramblersGpx.routes.length; ++index) {
        var route = ramblersGpx.routes[index];
        if (route.description !== '') {
            if (displayRoute(route)) {
                out += displayGPXName(route) + " [" + getGPXDistance(route.distance) + "] - " + route.description;
            }
        }
        out += '</p>';
    }
    out += '</div>';
    document.getElementById("tabDescriptions").innerHTML = out;
}
function addGPXMarkers() {
    for (index = 0; index < ramblersGpx.routes.length; ++index) {
        var route = ramblersGpx.routes[index];
        if (displayRoute(route)) {
            addGPXMarker(route);
        }
    }
}
function addGPXMarker(route) {
    var $popup, $lat, $long;
    $popup = "<span style='font-size:120%'>" + displayGPXName(route) + "</span>";
    $popup += ' - ' + getGPXDistance(route.distance) + '<br/>';
    $popup += formatAltitude(route);
    $lat = route.latitude;
    $long = route.longitude;
    addMarker($popup, $lat, $long, markerRoute);
}
function formatAltitude(route) {
    var popup;
    popup = "";
    if (route.cumulativeElevationGain === 0) {
        return "No elevation data<br/>";
    } else {
        popup += '<b>Min Altitude:</b> ' + route.minAltitude.toFixed(0) + ' m<br/>';
        popup += '<b>Max Altitude:</b> ' + route.maxAltitude.toFixed(0) + ' m<br/>';
        popup += '<b>Elevation Gain:</b> ' + route.cumulativeElevationGain.toFixed(0) + ' m<br/>';
    }
    return popup;
}
function getGPXdownloadLink(route) {
    var path, link;
    link = "";
    if (ramblersGpx.download === 1) {
        link = "*";
    }
    if (ramblersGpx.download === 2) {
        path = ramblersMap.base + ramblersGpx.folder + "/" + route.filename;
        link = "<a href='" + path + "'><img  alt='gpx' src='" + ramblersMap.base + "ramblers/images/orange-gpx-32.png' width='20' height='20'></a>";
    }
    return link;
}
function sortGPXTable(order) {
    if (order === "date") {
        sortOn(ramblersGpx.routes, 'date', ramblersGpx.dateorder, false);
        ramblersGpx.dateorder = !ramblersGpx.dateorder;
    }
    if (order === "author") {
        sortOn(ramblersGpx.routes, 'author', ramblersGpx.authororder, false);
        ramblersGpx.authororder = !ramblersGpx.authororder;
    }
    if (order === "distance") {
        sortOn(ramblersGpx.routes, 'distance', ramblersGpx.distorder, true);
        ramblersGpx.distorder = !ramblersGpx.distorder;
    }
    if (order === "title") {
        sortOn(ramblersGpx.routes, 'title', ramblersGpx.titleorder, false);
        ramblersGpx.titleorder = !ramblersGpx.titleorder;
    }
    if (order === "gain") {
        sortOn(ramblersGpx.routes, 'cumulativeElevationGain', ramblersGpx.gainorder, true);
        ramblersGpx.gainorder = !ramblersGpx.gainorder;
    }
    displayGPXTable();
}
function displayRoute(route) {
    if (ramblersGpx.searchtext === '') {
        return true;
    }
    if (route.title.toLowerCase().includes(ramblersGpx.searchtext)) {
        return true;
    }
    return false;
}
function gpxsearch() {
    var x = document.getElementById("searchform");
    var text = "";
    var i;
    for (i = 0; i < x.length; i++) {
        text += x.elements[i].value;
        y = x.elements[i];
    }
    ramblersGpx.searchtext = text.toLowerCase();
    displayGPXNames();
    displayGPXDescriptions();
    displayGPXTable();

    removeClusterMarkers();
    addGPXMarkers();
    ramblersMap.markersCG.addLayers(ramblersMap.markerList);
    return false;
}
var sortOn = function (arr, prop, reverse, numeric) {

// Ensure there's a property
    if (!prop || !arr) {
        return arr;
    }

// Set up sort function
    var sort_by = function (field, rev, primer) {

// Return the required a,b function
        return function (a, b) {

// Reset a, b to the field
            a = primer(a[field]), b = primer(b[field]);
            // Do actual sorting, reverse as needed
            return ((a < b) ? -1 : ((a > b) ? 1 : 0)) * (rev ? -1 : 1);
        };
    };
    // Distinguish between numeric and string to prevent 100's from coming before smaller
    // e.g.
    // 1
    // 20
    // 3
    // 4000
    // 50

    if (numeric) {

// Do sort "in place" with sort_by function
        arr.sort(sort_by(prop, reverse, function (a) {

// - Force value to a string.
// - Replace any non numeric characters.
// - Parse as float to allow 0.02 values.
            return parseFloat(String(a).replace(/[^0-9.-]+/g, ''));
        }));
    } else {

// Do sort "in place" with sort_by function
        arr.sort(sort_by(prop, reverse, function (a) {

// - Force value to string.
            return String(a).toUpperCase();
        }));
    }
};