# leaflet/gpx Module - High Level Design

## Overview

The `leaflet/gpx` module provides GPX route file display on Leaflet maps with elevation profiles. It renders GPX tracks as map overlays with interactive elevation charts.

**Purpose**: GPX route visualization with elevation profiles.

**Key Classes (this component)**: 
- `RLeafletGpxMap` extends `RLeafletMap`
- `RLeafletGpxMaplist` extends `RLeafletMap` (folder-driven GPX list + map)

**Dependencies (supporting utilities/assets treated as part of the Leaflet stack)**:
- `RGpxStatistics` / `RGpxJsonlog` / `RGpxFile` / `RGpxStatistic` for folder scanning, caching, and per-file parsing.【F:gpx/statistics.php†L16-L130】
- Client assets under `media/leaflet/*` (e.g., `gpx/maplist.js`, elevation, draw/upload/download controls) are documented here as part of the Leaflet module’s media surface.

## Public Interface

### RLeafletGpxMap

```php
public function displayPath($gpx)
public $linecolour = "#782327";
public $imperial = false;
public $addDownloadLink = "Users";
```

**Behaviour**: Validates that the supplied file exists and has a `.gpx` extension, sets `gpxfile` and presentation flags on the data object, and configures the map command to `ra.display.gpxSingle` so the client loads the route with elevation. Downloads are exposed when the file is present; invalid input is surfaced via Joomla messages.【F:leaflet/gpx/map.php†L15-L78】

### RLeafletGpxMaplist (folder index)

```php
public $folder = "images";
public $addDownloadLink = "Users"; // None|Users|Public
public $descriptions = true;
public $getMetaFromGPX = true;
public $displayAsPreviousWalks = false;
public $displayTitle = true;
public $linecolour = "#782327";
public $imperial = false;

public function display();
```

**Behaviour**:
- Builds a folder summary via `RGpxStatistics`, which regenerates `0000gpx_statistics_file.json` when any file newer than the JSON exists; otherwise it reuses the cached JSON for fast loads.【F:leaflet/gpx/maplist.php†L24-L83】【F:gpx/statistics.php†L16-L55】
- Sorting: by title (default) or by date when `displayAsPreviousWalks` is true.【F:leaflet/gpx/maplist.php†L32-L65】
- Map options: clustering, elevation, fullscreen, mouseposition, rightclick, fitbounds, print, settings, and mylocation are enabled to support list + map views.【F:leaflet/gpx/maplist.php†L42-L53】
- Command: publishes `ra.display.gpxFolder` with a data object containing items, download state (0/1/2), folder name, line colour, and presentation flags for the client to render markers, elevation, pagination, and downloads.【F:leaflet/gpx/maplist.php†L67-L83】
- Assets: enqueues `maplist.js`, tabs, cvList, and shared ramblerslibrary styles to deliver the tabbed table/map UI.【F:leaflet/gpx/maplist.php†L75-L82】

### Supporting classes
- **RGpxStatistics**: Scans the configured folder, extracts metadata from each `.gpx` file (and optional `.txt` descriptions), and writes `0000gpx_statistics_file.json` via `RGpxJsonlog`; emits diagnostics during regeneration to aid admins.【F:gpx/statistics.php†L16-L130】
- **RGpxFile / RGpxStatistic**: Parse GPX contents to compute title/description (with optional GPX metadata), author/date, start/end coordinates, distance, elevation stats, tracks/segments/routes counts, and duration used in the JSON summary.【F:gpx/statistics.php†L79-L130】

## Media Dependencies

- `media/leaflet/gpx/maplist.js` - GPX folder display (list + map + elevation)
- `media/leaflet/ra.display.plotRoute.js` - Shared plotting helpers for interactive drawing (loaded by mapdraw, not maplist)
- `media/vendors/leaflet-gpx-1.3.1/gpx.js` - GPX parsing for single-route display
- `media/vendors/Leaflet.Elevation-0.0.4-ra/` - Elevation charts
- `media/lib_ramblers/vendors/cvList/cvList.js|css` - Pagination for folder list/table
- `media/lib_ramblers/js/ra.tabs.js` - Tabbed UI for list/map toggle

## References

- [leaflet HLD](../HLD.md) - Main map system
- `leaflet/gpx/map.php` - RLeafletGpxMap class
- `leaflet/gpx/maplist.php` - RLeafletGpxMaplist class (if exists)
- `gpx/statistics.php` - Folder scanning + JSON caching
