
var L;
L.Control.Locationsearch = L.Control.extend({
    options: {
        showResultIcons: false,
        collapsed: true,
        expand: 'click',
        position: 'topright',
        placeholder: 'Grid Reference/GBR Mapcode...',
        errorMessage: 'Invalid Grid Reference or Mapcode',
        suggestMinLength: 3,
        suggestTimeout: 250,
        locationsearch: {search: null},
        defaultMarkLocationsearch: true
    },
    includes: (L.Evented.prototype || L.Mixin.Events),
    initialize: function (options) {
        L.Util.setOptions(this, options);
        this._requestCount = 0;
    },
    onAdd: function (map) {
        var className = 'leaflet-control-locationsearch',
                container = L.DomUtil.create('div', className + ' leaflet-bar'),
                icon = L.DomUtil.create('button', className + '-icon', container),
                form = this._form = L.DomUtil.create('div', className + '-form', container),
                input;
        this._map = map;
        this._container = container;
        icon.innerHTML = '&nbsp;';
        icon.type = 'button';
        input = this._input = L.DomUtil.create('input', '', form);
        input.type = 'text';
        input.placeholder = this.options.placeholder;
        this._errorElement = L.DomUtil.create('div', className + '-form-no-error', container);
        this._errorElement.innerHTML = this.options.errorMessage;
        this._alts = L.DomUtil.create('ul',
                className + '-alternatives leaflet-control-locationsearch-alternatives-minimized',
                container);
        L.DomEvent.disableClickPropagation(this._alts);
        L.DomEvent.addListener(input, 'keydown', this._keydown, this);
        L.DomEvent.addListener(input, 'blur', function () {
            if (this.options.collapsed && !this._preventBlurCollapse) {
                this._collapse();
            }
            this._preventBlurCollapse = false;
        }, this);
        if (this.options.collapsed) {
            if (this.options.expand === 'click') {
                L.DomEvent.addListener(icon, 'click', function (e) {
                    // TODO: touch
                    if (e.button === 0 && e.detail !== 2) {
                        this._toggle();
                    }
                }, this);
            } else {
                L.DomEvent.addListener(icon, 'mouseover', this._expand, this);
                L.DomEvent.addListener(icon, 'mouseout', this._collapse, this);
                this._map.on('movestart', this._collapse, this);
            }
        } else {
            L.DomEvent.addListener(icon, 'click', function (e) {
                this._Locationsearch(e);
            }, this);
            this._expand();
        }

        if (this.options.defaultMarkLocationsearch) {
            this.on('markLocationsearch', this.markLocationsearch, this);
        }

        L.DomEvent.disableClickPropagation(container);
        return container;
    },
    _LocationsearchResult: function (results, suggest) {
        if (!suggest && results.length === 1) {
            this._LocationsearchResultSelected(results[0]);
        } else if (results.length > 0) {
            this._alts.innerHTML = '';
            this._results = results;
            L.DomUtil.removeClass(this._alts, 'leaflet-control-locationsearch-alternatives-minimized');
            for (var i = 0; i < results.length; i++) {
                this._alts.appendChild(this._createAlt(results[i], i));
            }
        } else {
            L.DomUtil.addClass(this._errorElement, 'leaflet-control-locationsearch-error');
        }
    },
    markLocationsearch: function (result) {
        result = result.Locationsearch || result;

        if (this._LocationsearchMarker) {
            this._map.removeLayer(this._LocationsearchMarker);
        }
        this._LocationsearchMarker = new L.Marker(result.center, {icon: ramblersMap.redmarkericon})
                .bindPopup(result.html || result.name)
                .addTo(this._map)
                .openPopup();
        this._map.setZoom(16);
        this._map.panTo(result.center);
        return this;
    },
    _Locationsearch: function (suggest) {
        var requestCount = ++this._requestCount,
                mode = suggest ? 'suggest' : 'Locationsearch';
        this._lastLocationsearch = this._input.value.toUpperCase().trim();
        if (!suggest) {
            this._clearResults();
        }

        // this.fire('start' + mode);
        var results = [];
        // first try grid reference
        try {
            var gr = OsGridRef.parse(this._lastLocationsearch);
            var latlong = OsGridRef.osGridToLatLon(gr);

            results[0] = {
                name: this._lastLocationsearch,
                center: L.latLng(latlong.lat, latlong.lon)
            };
            this._LocationsearchResult(results, suggest);
            return;
        } catch (err) {

        }
        try {
            var codes = decode(this._lastLocationsearch, "GBR");
            if (codes === false || codes === undefined) {
                this._LocationsearchResult(results, suggest);
                return;
            }
            results[0] = {
                name: this._lastLocationsearch,
                center: L.latLng(codes.y, codes.x)
            };
            this._LocationsearchResult(results, suggest);
            return;
        } catch (err) {

        }
        this._LocationsearchResult(results, suggest);
    },
    _LocationsearchResultSelected: function (result) {
        if (!this.options.collapsed) {
            this._clearResults();
        }

        this.fire('markLocationsearch', {Locationsearch: result});
    },
    _toggle: function () {
        if (this._container.className.indexOf('leaflet-control-locationsearch-expanded') >= 0) {
            this._collapse();
        } else {
            this._expand();
        }
    },
    _expand: function () {
        L.DomUtil.addClass(this._container, 'leaflet-control-locationsearch-expanded');
        this._input.select();
        this.fire('expand');
        if (this._LocationsearchMarker) {
            this._map.removeLayer(this._LocationsearchMarker);
        }
    },
    _collapse: function () {
        this._container.className = this._container.className.replace(' leaflet-control-locationsearch-expanded', '');
        L.DomUtil.addClass(this._alts, 'leaflet-control-locationsearch-alternatives-minimized');
        L.DomUtil.removeClass(this._errorElement, 'leaflet-control-locationsearch-error');
        this.fire('collapse');
    },
    _clearResults: function () {
        L.DomUtil.addClass(this._alts, 'leaflet-control-locationsearch-alternatives-minimized');
        this._selection = null;
        L.DomUtil.removeClass(this._errorElement, 'leaflet-control-locationsearch-error');
    },
    _createAlt: function (result, index) {
        var li = L.DomUtil.create('li', ''),
                a = L.DomUtil.create('a', '', li),
                icon = this.options.showResultIcons && result.icon ? L.DomUtil.create('img', '', a) : null,
                text = result.html ? undefined : document.createTextNode(result.name),
                mouseDownHandler = function mouseDownHandler(e) {
                    // In some browsers, a click will fire on the map if the control is
                    // collapsed directly after mousedown. To work around this, we
                    // wait until the click is completed, and _then_ collapse the
                    // control. Messy, but this is the workaround I could come up with
                    // for #142.
                    this._preventBlurCollapse = true;
                    L.DomEvent.stop(e);
                    this._LocationsearchResultSelected(result);
                    L.DomEvent.on(li, 'click', function () {
                        if (this.options.collapsed) {
                            this._collapse();
                        }
                    }, this);
                };
        if (icon) {
            icon.src = result.icon;
        }

        li.setAttribute('data-result-index', index);
        if (result.html) {
            a.innerHTML = a.innerHTML + result.html;
        } else {
            a.appendChild(text);
        }

        // Use mousedown and not click, since click will fire _after_ blur,
        // causing the control to have collapsed and removed the items
        // before the click can fire.
        L.DomEvent.addListener(li, 'mousedown', mouseDownHandler, this);
        return li;
    },
    _keydown: function (e) {
        var _this = this,
                select = function select(dir) {
                    if (_this._selection) {
                        L.DomUtil.removeClass(_this._selection, 'leaflet-control-locationsearch-selected');
                        _this._selection = _this._selection[dir > 0 ? 'nextSibling' : 'previousSibling'];
                    }
                    if (!_this._selection) {
                        _this._selection = _this._alts[dir > 0 ? 'firstChild' : 'lastChild'];
                    }

                    if (_this._selection) {
                        L.DomUtil.addClass(_this._selection, 'leaflet-control-locationsearch-selected');
                    }
                };
        switch (e.keyCode) {
            // Escape
            case 27:
                if (this.options.collapsed) {
                    this._collapse();
                }
                break;
                // Up
            case 38:
                select(-1);
                L.DomEvent.preventDefault(e);
                break;
                // Up
            case 40:
                select(1);
                L.DomEvent.preventDefault(e);
                break;
                // Enter
            case 13:
                if (this._selection) {
                    var index = parseInt(this._selection.getAttribute('data-result-index'), 10);
                    this._LocationsearchResultSelected(this._results[index]);
                    this._clearResults();
                } else {
                    this._Locationsearch();
                }
                L.DomEvent.preventDefault(e);
                break;
            default:
                var v = this._input.value;
                if (this.options.locationsearch.suggest && v !== this._lastLocationsearch) {
                    clearTimeout(this._suggestTimeout);
                    if (v.length >= this.options.suggestMinLength) {
                        this._suggestTimeout = setTimeout(L.bind(function () {
                            this._Locationsearch(true);
                        }, this), this.options.suggestTimeout);
                    } else {
                        this._clearResults();
                    }
                }
        }
    }
});
L.control.locationsearch = function (options) {
    return new L.Control.Locationsearch(options);
};
