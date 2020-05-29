L.Control.DisplayHelp = L.Control.extend({
    options: {
        title: 'Display help',
        position: 'topleft'
    },
    onAdd: function (map) {
        this._map = map;
        this.enabled = false;
        _ra_gpx_reverse_this = this;
        var container = L.DomUtil.create('div', 'leaflet-control leaflet-bar leaflet-control-display-help');

        this._createIcon(container);
        this._container = container;

        L.DomEvent.disableClickPropagation(container);
        L.DomEvent.on(this.link, 'click', this._display_help, this);
        // this._appendButtons(container);
        // this.holder.style.display = "none";
        return container;
    },
    _createIcon: function (container) {
        var icon = L.DomUtil.create('a', '', container);
        this.link = icon;
        this.link.id = "leaflet-display-help";
        this.link.title = this.options.title;
        return this.link;
    },
    _display_help: function (evt) {
        var page = ramblersMap.maphelppage;
        open(page, "_blank", "scrollbars=yes,width=900,height=580,menubar=yes,resizable=yes,status=yes");

    }
});

L.control.displayhelp = function (options) {
    return new L.Control.DisplayHelp(options);
};
