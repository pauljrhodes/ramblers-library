var L, ramblersMap;
L.Control.GpxDownload = L.Control.extend({
    options: {
        title: 'Download your walking routes as a GPX file',
        titledisabled: 'No routes or markers defined',
        position: 'bottomright'
    },
    onAdd: function (map) {
        this._map = map;
        this.enabled = false;
        this.supported = true;
        this._info = {
            name: "",
            desc: "",
            author: "",
            copyright: "",
            gpxTrack: false,
            date: ""
        };
        var containerAll = L.DomUtil.create('div', 'leaflet-control-gpx-download ra-download-toolbar-button-disabled');
        var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control', containerAll);
        this._createIcon(container);
        this._container = container;
        L.DomEvent.disableClickPropagation(containerAll);
        L.DomEvent.disableClickPropagation(container);
        L.DomEvent.on(this.link, 'click', this._downloadGpx, this);
        this._appendButtons(containerAll);
        this.holder.style.display = "none";
        this.popup = L.popup({minWidth: 550, maxWidth: 550}).setLatLng([0, 0]).setContent('dummy');
        return containerAll;
    },
    _appendButtons: function (container) {
        this.holder = L.DomUtil.create('div', 'leaflet-gpx-options', container);

        var element = L.DomUtil.create('div', 'details', this.holder);
        element.innerHTML = "Edit Details";
        L.DomEvent.addListener(element, 'click', this._details, this);

        element = L.DomUtil.create('div', 'save', this.holder);
        element.innerHTML = "Save";
        L.DomEvent.addListener(element, 'click', this._save, this);

        element = L.DomUtil.create('div', 'cancel', this.holder);
        element.innerHTML = "Cancel";
        L.DomEvent.addListener(element, 'click', this._cancel, this);
    },
    setRouteItems: function (itemsCollection) {
        this._itemsCollection = itemsCollection;
    },
    set_name: function (value) {
        this._info.name = value;
    },
    set_desc: function (value) {
        this._info.desc = value;
    },
    set_author: function (value) {
        this._info.author = value;
    },
    set_copyright: function (value) {
        this._info.copyright = value;
    },
    set_date: function (value) {
        this._info.date = value;
    },
    setStatus: function (status) {
        this.enabled = true;
        if (status === "off") {
            this.enabled = false;
        }
        if (status === "auto") {
            this.enabled = this._itemsCollection.getLayers().length !== 0;
        }
        if (this.supported === false) {
            this.enabled = false;
        }
        if (this.enabled) {
            L.DomUtil.removeClass(this._container, 'ra-download-toolbar-button-disabled');
            this.link.title = this.options.title;
        } else {
            L.DomUtil.addClass(this._container, 'ra-download-toolbar-button-disabled');
            this.link.title = this.options.titledisabled;
        }
    },
    _createIcon: function (container) {
        var icon = L.DomUtil.create('a', '', container);
        this.link = icon;
        this.link.id = "leaflet-gpx-download";
        this.link.title = this.options.title;
        return this.link;
    },
    _downloadGpx: function (e) {
        if (this.enabled) {
            this._map.fire('download:started');
            this.holder.style.display = "inline-block";
        }
    },
    _cancel: function (evt) {
        this._map.fire('download:cancelled');
        this.holder.style.display = "none";
    },
    _details: function (evt) {
        this._map.fire('download:cancelled');
        this.holder.style.display = "none";
        var bounds = this._map.getBounds();
        var lng = (bounds.getEast() + bounds.getWest()) / 2;
        var lat = bounds.getSouth();
        //var centre = this._map.getCenter();
        //    var marker = L.marker(centre);
        var gpxName = this._info.name;
        var gpxDesc = this._info.desc;
        var gpxAuthor = this._info.author;
        var gpxDate = this._info.date;
        if (gpxDate.length > 10) {
            gpxDate = gpxDate.substring(0, 10)
        }
        var gpxTrack = this._info.gpxTrack;
        var content = '<form><span><b>File Name/Title</b></span><br/><input id="gpxName" type="text"/ value="' + gpxName + '" /><br/>';
        content += '<span><b>File Description<b/></span><br/><textarea id="gpxDesc" >' + gpxDesc + '</textarea><br/>';
        content += '<span><b>Author/Leader</b></span><br/><input id="gpxAuthor" type="text"/ value="' + gpxAuthor + '" /><br/>';
        content += '<span><b>Date</b></span><br/><input type="date" id="gpxDate" name="trip" value=' + gpxDate + ' min="1970-01-01" max="2100-12-31" /><br/>';
        content += 'Save As GPX Track rather than GPX Route<br/><input type="checkbox" id="gpxTrack" name="route" />';
        content += '</form>';

        this.popup.setLatLng([lat, lng]);
        this.popup.setContent(content);
        this.popup.openOn(this._map)
        var ele = document.getElementById('gpxTrack');
        ele.checked = gpxTrack;

    },
    _popupclose: function (e) {
        var popup = e.popup;
        if (popup === this.popup) {
            this._info.name = this.getElementValue('gpxName');
            this._info.desc = this.getElementValue('gpxDesc');
            this._info.author = this.getElementValue('gpxAuthor');
            //        this._info.copyright = this.getElementValue('gpxCopyright');
            //this._info.gpxTrack = this.getElementValue('gpxTrack').checked;
            var ele = document.getElementById('gpxTrack');
            this._info.gpxTrack = ele.checked;
            this._info.date = this.getElementValue('gpxDate');
            if (this._info.date != "") {
                this._info.date += "T12:00:00Z";
            }

        }
    },
    getElementValue: function (id) {
        var node = document.getElementById(id);
        if (node !== null) {
            return node.value;
        }
        return "Invalid";
    },
    _save: function (e) {
        if (this.enabled) {
            this.holder.style.display = "none";
            var drawnItems = this._itemsCollection;
            var hasItems = drawnItems.getLayers().length !== 0;
            if (!hasItems) {
                alert('No routes or markers defined');
            } else {
                var data = this._createGPXdata();
                try {
                    var blob = new Blob([data], {type: "application/gpx+xml;charset=utf-8"});
                    var name = this._info.name + ".gpx";
                    if (name === ".gpx") {
                        name = "route.gpx";
                    }
                    saveAs(blob, name);
                } catch (e) {
                    blurt('Your web browser does not support his option!');
                }
            }
        }
    },
    _createGPXdata: function () {
        var gpxData = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        gpxData += '<gpx' + "\n";
        gpxData += 'version="1.1"' + "\n";
        gpxData += 'creator="Ramblers-Webs - http://www.ramblers-webs.org.uk"' + "\n";
        gpxData += 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' + "\n";
        gpxData += 'xmlns="http://www.topografix.com/GPX/1/1"' + "\n";
        gpxData += 'xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">' + "\n";
        ra_gpx_download_this = this;
        gpxData += ra_gpx_download_this._addMetaData();
        this._itemsCollection.eachLayer(function (layer) {
            if (layer instanceof L.Marker) {
                gpxData += ra_gpx_download_this._addMarker(layer);
            }
            if (layer instanceof L.Polyline) {
                gpxData += ra_gpx_download_this._addPolyline(layer);
            }
        });

        gpxData += '</gpx>';
        return gpxData;
    },
    _escapeChars: function (text) {
        text = text.replace(/"/g, "&quot;");
        text = text.replace(/'/g, "&apos;");
        text = text.replace(/</g, "&lt;");
        text = text.replace(/>/g, "&gt;");
        text = text.replace(/&/g, "&amp;");
        return text;
    },
    _addMetaData: function () {
        out = "<metadata>";
        if (ra_gpx_download_this._info.name !== "") {
            out += "<name>" + ra_gpx_download_this._escapeChars(ra_gpx_download_this._info.name) + "</name>";
        }
        if (ra_gpx_download_this._info.desc !== "") {
            out += "<desc>" + ra_gpx_download_this._escapeChars(ra_gpx_download_this._info.desc) + "</desc>";
        }
        if (ra_gpx_download_this._info.author !== "") {
            out += "<author><name>" + ra_gpx_download_this._escapeChars(ra_gpx_download_this._info.author) + "</name></author>";
        }
        if (ra_gpx_download_this._info.date !== "") {
            out += "<time>" + ra_gpx_download_this._info.date + "</time>";
        }
        out += "</metadata>\n";
        return out;
    },
    _addMarker: function (marker) {
        var data = '<wpt lat="' + marker._latlng.lat + '" lon="' + marker._latlng.lng + '">' + "\n";
        if (marker.hasOwnProperty('name')) {
            data += '<name>' + marker.name + '</name>' + "\n";
        }
        if (marker.hasOwnProperty('desc')) {
            data += '<desc><![CDATA[' + marker.desc + ']]></desc>' + "\n";
        }
        if (marker.hasOwnProperty('symbol')) {
            data += '<sym>' + marker.symbol + '</sym>' + "\n";
        }
        data += '</wpt>\n';
        return data;
    },
    _addPolyline: function (polyline) {
        if (this._info.gpxTrack === true) {
            var data = '<trk>' + "\n";
            data += '<trkseg>' + "\n";
            latlngs = polyline.getLatLngs();
            data += ra_gpx_download_this._listtrack(latlngs);

            data += '</trkseg>' + "\n";
            data += '</trk>' + "\n";
        } else {
            var data = '<rte>' + "\n";
            latlngs = polyline.getLatLngs();
            data += ra_gpx_download_this._listroute(latlngs);
            data += '</rte>' + "\n";
        }
        return data;
    },
    _listtrack: function (latlngs) {
        var i, len;
        i = 0;
        var text = "";
        for (i = 0, len = latlngs.length; i < len; i++) {
            text += '<trkpt lat="' + latlngs[i].lat + '" lon="' + latlngs[i].lng + '">\n';
            if (latlngs[i].alt !== -999) {
                text += ' <ele>' + latlngs[i].alt + '</ele>\n';
            }
            text += '</trkpt>\n';
        }
        return text;
    },
    _listroute: function (latlngs) {
        var i, len;
        i = 0;
        var text = "";
        for (i = 0, len = latlngs.length; i < len; i++) {
            text += '<rtept lat="' + latlngs[i].lat + '" lon="' + latlngs[i].lng + '">\n';
            if (latlngs[i].alt !== -999) {
                text += ' <ele>' + latlngs[i].alt + '</ele>\n';
            }
            text += ' </rtept>\n';
        }
        return text;
    }

});
L.control.gpxdownload = function (options) {
    return new L.Control.GpxDownload(options);
};
