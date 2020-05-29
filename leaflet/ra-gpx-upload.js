L.Control.GpxUpload = L.Control.extend({
    options: {
        title: 'Up load a walking route from a GPX file',
        position: 'bottomright'
    },
    onAdd: function (map) {
        this._map = map;
        this.enabled = true;
        this.filename = "";
        ra_gpx_upload_this = this;
        this._info = {
            name: "",
            desc: "",
            author: "",
            copyright: "",
            date: ""
        };
        var container = L.DomUtil.create('div', 'leaflet-control-gpx-upload leaflet-bar leaflet-control');
        this._createIcon(container);
        container.title = this.options.title;
        this._container = container;
        container.getElementById('gpx-file-upload').addEventListener('change', this._uploadGpx, false);
        return container;
    },
    setRouteItems: function (itemsCollection) {
        this._itemsCollection = itemsCollection;
    },
    _createIcon: function (container) {
        var div = L.DomUtil.create('div', 'image-upload', container);
        div.innerHTML = '<label for="gpx-file-upload"><div id="upload-icon"></div></label><input id="gpx-file-upload" type="file" accept=".gpx"/>';
        return div;
    },
    setStatus: function (status) {
        this.enabled = status !== "off";
        if (this.enabled) {
            document.getElementById("gpx-file-upload").disabled = false;
            L.DomUtil.removeClass(this._container, 'ra-upload-toolbar-button-disabled');
        } else {
            document.getElementById("gpx-file-upload").disabled = true;
            L.DomUtil.addClass(this._container, 'ra-upload-toolbar-button-disabled');
        }
    },
    get_name: function () {
        return this._info.name;
    },
    get_desc: function () {
        return this._info.desc;
    },
    get_author: function () {
        return this._info.author;
    },
    get_copyright: function () {
        return this._info.copyright;
    },
    get_date: function () {
        return this._info.date;
    },
    _gpxreader: function (gpx, options) {
        var _MAX_POINT_INTERVAL_MS = 15000;
        var _DEFAULT_MARKER_OPTS = {
            wptIconUrls: {
                '': 'libraries/ramblers/leaflet/gpx/images/pin-icon-wpt.png'
            },
            iconSize: [25, 41],
            shadowSize: [41, 41],
            iconAnchor: [12, 41],
            shadowAnchor: [25, 41],
            clickable: false
        };
        var _DEFAULT_POLYLINE_OPTS = {
            color: '#782327'
        };
        var _DEFAULT_GPX_OPTS = {
            parseElements: ['track', 'route', 'waypoint']
        };
        options.max_point_interval = options.max_point_interval || _MAX_POINT_INTERVAL_MS;
        options.marker_options = this._ra_gpx_merge_objs(
                _DEFAULT_MARKER_OPTS,
                options.marker_options || {});
        options.polyline_options = this._ra_gpx_merge_objs(
                _DEFAULT_POLYLINE_OPTS,
                options.polyline_options || {});
        options.gpx_options = this._ra_gpx_merge_objs(
                _DEFAULT_GPX_OPTS,
                options.gpx_options || {});
        L.Util.setOptions(this, options);
        // Base icon class for track pins.
        L.GPXTrackIcon = L.Icon.extend({options: options.marker_options});
        //this._gpx = gpx;

        this._ra_gpx_parse(gpx, options, this.options.async);
    },
    _ra_gpx_merge_objs: function (a, b) {
        var _ = {};
        for (var attr in a) {
            _[attr] = a[attr];
        }
        for (var attr in b) {
            _[attr] = b[attr];
        }
        return _;
    },
    _ra_gpx_parse: function (input, options, async) {

        var cb = function (input, options) {
            var gpx = ra_gpx_upload_this.BuildXMLFromString(input);
            ra_gpx_upload_this._ra_gpx_parse_gpx_data(gpx, options);
            ra_gpx_upload_this._itemsCollection.fire('upload:loaded');
        };
        if (input.substr(0, 1) === '<') { // direct XML has to start with a <
            if (async) {
                setTimeout(function () {
                    cb(input, options);
                });
            } else {
                cb(input, options);
            }
        } else {
            alert("File does not appear to be an GPX file");
        }
    },
    _ra_gpx_parse_gpx_data: function (xml, options) {
        var j, i, el;
        var tags = [];
        var parseElements = options.gpx_options.parseElements;
        if (parseElements.indexOf('route') > -1) {
            tags.push(['rte', 'rtept']);
        }
        if (parseElements.indexOf('track') > -1) {
            tags.push(['trkseg', 'trkpt']);
        }
        this._info.name = '';
        this._info.desc = '';
        this._info.author = '';
        this._info.date = '';
      //  ra_gpx_upload_this = this;
        var meta = xml.getElementsByTagName('metadata');
        //  var test = this._ra_get_child(meta[0], 'time');
        if (typeof meta !== 'undefined') {
            if (meta.length > 0) {
                ra_gpx_upload_this._info.name = this._ra_get_child_text(meta[0], 'name');

                ra_gpx_upload_this._info.desc = this._ra_get_child_text(meta[0], 'desc');

                ra_gpx_upload_this._info.author = this._ra_get_children_text(meta[0], 'author', 'name');

                ra_gpx_upload_this._info.date = this._ra_get_child_text(meta[0], 'time');
            }
        }
        if (ra_gpx_upload_this._info.name === "") {
            ra_gpx_upload_this._info.name = ra_gpx_upload_this.filename;
        }


        for (j = 0; j < tags.length; j++) {
            el = xml.getElementsByTagName(tags[j][0]);
            for (i = 0; i < el.length; i++) {
                var coords = this._ra_gpx_parse_trkseg(el[i], tags[j][1]);
                if (coords.length === 0)
                    continue;
                // add track
                var l = new L.Polyline(coords, options.polyline_options);
                ra_gpx_upload_this._itemsCollection.addLayer(l);
                ra_gpx_upload_this._itemsCollection.fire('upload:addline', {line: l});
            }
        }

        // parse waypoints and add markers for each of them
        if (parseElements.indexOf('waypoint') > -1) {
            el = xml.getElementsByTagName('wpt');
            for (i = 0; i < el.length; i++) {
                var ll = new L.LatLng(
                        el[i].getAttribute('lat'),
                        el[i].getAttribute('lon'));
                var name = this._ra_get_text(el[i], 'name');
                var desc = this._ra_get_text(el[i], 'desc');
                var symKey = this._ra_get_text(el[i], 'sym');
                // add WayPointMarker, based on "sym" element if avail and icon is configured
                var symIcon;
                if (options.marker_options.wptIcons && options.marker_options.wptIcons[symKey]) {
                    symIcon = options.marker_options.wptIcons[symKey];
                } else if (options.marker_options.wptIconUrls && options.marker_options.wptIconUrls[symKey]) {
                    symIcon = new L.GPXTrackIcon({iconUrl: options.marker_options.wptIconUrls[symKey]});
                } else {
                    //   console.log('No icon or icon URL configured for symbol type "' + symKey
                    //           + '"; ignoring waypoint.');
                    symIcon = new L.GPXTrackIcon({iconUrl: options.marker_options.wptIconUrls['']});
                }

                var marker = new L.Marker(ll, {
                    clickable: true,
                    title: name,
                    icon: symIcon
                });
                marker.name = name;
                marker.desc = desc;
                marker.symbol = symKey;
                marker.bindPopup("<b>" + name + "</b>" + (desc.length > 0 ? '<br>' + desc : '')).openPopup();
                ra_gpx_upload_this._itemsCollection.fire('upload:addpoint', {point: marker, point_type: 'waypoint'});
                ra_gpx_upload_this._itemsCollection.addLayer(marker);
                //  layers.push(marker);
            }
        }


    },
    _ra_get_child_text: function (elem, name) {
        var children = elem.childNodes;

        ra_gpx_upload_this = this;
        ra_gpx_upload_this.result = "";
        ra_gpx_upload_this.findname = name;
        if (typeof children !== 'undefined') {

            children.forEach(
                    function (node, currentIndex, listObj) {
                        var find = ra_gpx_upload_this.findname;
                        //console.log(node + ', ' + currentIndex + ', ' + this);
                        if (node.nodeName == find) {
                            ra_gpx_upload_this.result = node.textContent;
                        }
                    },
                    "name"
                    );
        }
        return ra_gpx_upload_this.result;
    },
    _ra_get_children_text: function (elem, name1, name2) {
        var child = this._ra_get_child(elem, name1);
        if (typeof child !== 'undefined') {
            return   this._ra_get_child_text(child, name2);
        }
        return "";
    },
    _ra_get_child: function (elem, name) {
        var children = elem.childNodes;

        ra_gpx_upload_this = this;
        ra_gpx_upload_this.result = undefined;
        ra_gpx_upload_this.findname = name;
        if (typeof children !== 'undefined') {

            children.forEach(
                    function (node, currentIndex, listObj) {
                        var find = ra_gpx_upload_this.findname;
                        console.log(node + ', ' + currentIndex + ', ' + this);
                        if (node.nodeName == find) {
                            ra_gpx_upload_this.result = node;
                        }
                    },
                    "name"
                    );
        }
        return ra_gpx_upload_this.result;
    },
    _ra_get_text: function (elem, tag) {
        var textEl = elem.getElementsByTagName(tag);
        var text = '';
        if (textEl.length > 0) {
            text = textEl[0].textContent;
        }
        return text;
    }
    ,
    _ra_gpx_parse_trkseg: function (line, tag) {
        var el = line.getElementsByTagName(tag);
        if (!el.length)
            return [];
        var coords = [];
        var last = null;
        for (var i = 0; i < el.length; i++) {
            var tag;
            var ll = new L.LatLng(el[i].getAttribute('lat'), el[i].getAttribute('lon'), -999);
            last = ll;
            coords.push(ll);
        }
        return coords;
    },
    _uploadGpx: function (evt) {

        var files = evt.target.files; // FileList object
        var file = files[0];
        var reader = new FileReader();
        // Closure to capture the file information.
        reader.onload = (function (theFile) {
            return function (e) {
                ra_gpx_upload_this._gpxreader(reader.result, {async: true});
            };
        })(file);
        // Read in the image file as a data URL.
        reader.readAsText(file);
        ra_gpx_upload_this.filename = file.name;
        ra_gpx_upload_this.filename= ra_gpx_upload_this.filename.replace(/.gpx$/i,'');
        return false;
    },
    BuildXMLFromString: function (text) {

        var parser = new DOMParser();
        var xmlDoc = null;
        try {
            xmlDoc = parser.parseFromString(text, "text/xml");
        } catch (e) {
            alert("XML parsing error.");
            return null;
        }
        var errorMsg = null;
        if (xmlDoc.parseError && xmlDoc.parseError.errorCode != 0) {
            errorMsg = "XML Parsing Error: " + xmlDoc.parseError.reason
                    + " at line " + xmlDoc.parseError.line
                    + " at position " + xmlDoc.parseError.linepos;
        } else {
            if (xmlDoc.documentElement) {
                if (xmlDoc.documentElement.nodeName == "parsererror") {
                    errorMsg = xmlDoc.documentElement.childNodes[0].nodeValue;
                }
            } else {
                errorMsg = "XML Parsing Error!";
            }
        }

        if (errorMsg) {
            alert(errorMsg);
            return null;
        }

        return xmlDoc;
    }
});
L.control.gpxupload = function (options) {
    return new L.Control.GpxUpload(options);
};
