var L, ramblersMap;
L.Control.Mouse = L.Control.extend({
    options: {
        position: 'bottomleft',
        separator: ', ',
        emptyString: 'Outside OS Grid',
        lngFirst: false,
        numDigits: 5
    },
    onAdd: function (map) {
        this._container = L.DomUtil.create('div', 'leaflet-control-mouseposition');
        L.DomEvent.disableClickPropagation(this._container);
        map.on('mousemove', this._update, this);
        this._container.innerHTML = this.options.emptyString;
        return this._container;
    },
    onRemove: function (map) {
        map.off('mousemove', this._onMouseMove);
    },
    _update: function (e) {
        var text = getMouseMoveAction(e);
        this._container.innerHTML = text;
    }
});

L.Map.mergeOptions({
    positionControl: false
});

L.Map.addInitHook(function () {
    if (this.options.positionControl) {
        this.positionControl = new L.Control.MousePosition();
        this.addControl(this.positionControl);
    }
});

L.control.mouse = function (options) {
    return new L.Control.Mouse(options);
};

L.Control.PostcodeStatus = L.Control.extend({
    options: {
        position: 'bottomleft',
        defaultString: 'Zoom in and right click/tap hold to see nearby postcodes'
    },
    displaymap: null,
    onAdd: function (map) {
        displaymap = map;
        this._container = L.DomUtil.create('div', 'leaflet-control-postcodeposition');
        L.DomEvent.disableClickPropagation(this._container);
        displaymap.on('zoomend', this._onZoomEnd, this);
        displaymap.on('contextmenu', this._onRightClick, this);
        this._container.innerHTML = this.options.defaultString;
        return this._container;
    },
    onRemove: function () {
        displaymap.off('zoomend', this._onZoomEnd);
        displaymap.off('contextmenu', this._onClick);
    },
    _onRightClick: function (e) {
        var zoom = displaymap.getZoom();
        if (zoom > 12) {
            displayPostcodes(e);
        } else {
            ramblersMap.postcodelayer.clearLayers();
            this._container.innerHTML = "Zoom in to view postcodes!";
        }
    },
    _onZoomEnd: function (e) {
        var zoom = ramblersMap.map.getZoom();
        if (zoom <= 12) {
            this._container.innerHTML = "Zoom in and right click to see nearby postcodes";
            if (zoom <= 9) {
                ramblersMap.postcodelayer.clearLayers();
            }
        } else {
            this._container.innerHTML = "Right click to see nearby postcodes";
        }
    }
});

L.control.postcodeStatus = function (options) {
    return new L.Control.PostcodeStatus(options);
};