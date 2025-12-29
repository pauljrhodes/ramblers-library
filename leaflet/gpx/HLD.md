# leaflet/gpx Module - High Level Design

## Overview

The `leaflet/gpx` module provides GPX route file display on Leaflet maps with elevation profiles. It renders GPX tracks as map overlays with interactive elevation charts.

**Purpose**: GPX route visualization with elevation profiles.

**Key Classes**: 
- `RLeafletGpxMap` extends `RLeafletMap`
- `RLeafletGpxMaplist` (if exists)

## Public Interface

### RLeafletGpxMap

```php
public function displayPath($gpx)
public $linecolour = "#782327";
public $imperial = false;
public $addDownloadLink = "Users";
```

## Media Dependencies

- `media/leaflet/gpx/maplist.js` - GPX map list functionality
- `media/vendors/leaflet-gpx-1.3.1/gpx.js` - GPX parsing
- `media/vendors/Leaflet.Elevation-0.0.4-ra/` - Elevation charts

## References

- [leaflet HLD](../HLD.md) - Main map system
- `leaflet/gpx/map.php` - RLeafletGpxMap class
- `leaflet/gpx/maplist.php` - RLeafletGpxMaplist class (if exists)


