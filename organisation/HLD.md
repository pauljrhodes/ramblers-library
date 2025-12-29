# organisation Module - High Level Design

## Overview

The `organisation` module manages Ramblers organisation data (areas and groups) by fetching JSON feeds, converting to internal structures, and displaying on maps. It provides geographic visualization of the Ramblers organisational structure.

**Purpose**: Organisation data management and geographic visualization.

**Key Responsibilities**:
- Fetch organisation JSON feed from ramblers.org.uk
- Convert JSON to internal area/group structures
- Display organisation hierarchy on Leaflet maps
- Provide area and group listing functionality
- Support custom styling and filtering

## Component Architecture

```mermaid
flowchart TB
    subgraph Organisation["Organisation Module"]
        ROrganisation[ROrganisation<br/>Main class]
        Area[ROrganisationArea<br/>Area object]
        Group[ROrganisationGroup<br/>Group object]
    end

    subgraph Data["Data Sources"]
        FeedHelper[RFeedhelper<br/>HTTP feed]
        OrgFeed[Organisation JSON Feed<br/>groups.theramblers.org.uk]
    end

    subgraph Display["Display Layer"]
        LeafletMap[RLeafletMap<br/>Map rendering]
        Html[RHtml<br/>HTML formatting]
    end

    subgraph Client["Client-Side"]
        OrgJS[organisation.js<br/>Map display]
    end

    ROrganisation --> FeedHelper
    FeedHelper --> OrgFeed
    ROrganisation --> Area
    ROrganisation --> Group
    ROrganisation --> LeafletMap
    ROrganisation --> Html
    LeafletMap --> OrgJS
```

## Public Interface

### ROrganisation

**Main organisation data manager.**

#### Constructor
```php
public function __construct()
```
- **Behavior**: Automatically calls `load()` to fetch and process organisation data

#### Data Loading Method
```php
public function load()
```
- **Behavior**: 
  - Creates `RFeedhelper` with 60-minute cache
  - Fetches organisation feed from `https://groups.theramblers.org.uk/`
  - Converts JSON to internal area/group structures
  - Populates `$areas` and `$groups` arrays

#### Display Methods
```php
public function listAreas()
```
- **Returns**: Array of area objects
- **Behavior**: Returns all areas

```php
public function display($map)
```
- **Parameters**: `$map` - `RLeafletMap` instance
- **Behavior**: 
  - Configures map options
  - Sets command to `"ra.display.organisationMap"`
  - Injects area/group data as JSON
  - Loads `organisation.js` script

```php
public function myGroup($myGroup, $zoom)
```
- **Parameters**: 
  - `$myGroup` - Group code to highlight
  - `$zoom` - Map zoom level
- **Behavior**: Centers map on specified group

#### Public Properties
```php
public $groups = [];      // Array of group objects
public $areas = [];       // Array of area objects
public $showLinks = true;
public $showCodes = true;
public $showGroups = true;
public $colourMyGroup = '#ff0000';
public $colourMyArea = '#00ff00';
public $colourOtherGroups = '#0000ff';
public $centreGroup = "";
public $mapZoom = -1;
```

## Data Flow

### Organisation Loading Flow

```mermaid
sequenceDiagram
    autonumber
    participant Caller as Module/Page
    participant ROrganisation as ROrganisation
    participant FeedHelper as RFeedhelper
    participant OrgFeed as Organisation Feed
    participant Areas as Areas Array
    participant Groups as Groups Array

    Caller->>ROrganisation: new()
    ROrganisation->>ROrganisation: load()
    ROrganisation->>FeedHelper: new(cache, 60)
    ROrganisation->>FeedHelper: getFeed(url, title)
    FeedHelper->>OrgFeed: HTTP request
    OrgFeed-->>FeedHelper: JSON response
    FeedHelper-->>ROrganisation: contents
    ROrganisation->>ROrganisation: json_decode()
    ROrganisation->>ROrganisation: convert(json)
    ROrganisation->>Areas: Populate areas[]
    ROrganisation->>Groups: Populate groups[]
```

## Integration Points

### Data Sources
- **RFeedhelper**: HTTP feed retrieval → [feedhelper HLD](../feedhelper/HLD.md)
- **Organisation Feed**: `https://groups.theramblers.org.uk/` JSON endpoint

### Display Layer
- **RLeafletMap**: Map rendering → [leaflet HLD](../leaflet/HLD.md)
- **RHtml**: HTML formatting → [html HLD](../html/HLD.md)

### Used By
- **RAccounts**: Organisation data for account updates → [accounts HLD](../accounts/HLD.md)

## Server-to-Client Asset Relationship

```mermaid
flowchart LR
    Org[ROrganisation]
    Map[RLeafletMap]
    Loader[RLoad]
    BaseJS[media/js<br/>ra.js<br/>ra.map.js<br/>ra.tabs.js]
    OrgJS[media/organisation/organisation.js]
    Leaflet[Leaflet core + plugins]

    Org --> Map
    Map --> Loader
    Loader --> BaseJS
    Loader --> Leaflet
    Loader --> OrgJS
```

`ROrganisation::display()` uses `RLoad` to enqueue the shared `media/js` foundation (core utilities, map helpers, tabs) and Leaflet dependencies before adding `media/organisation/organisation.js`, ensuring the organisation map display has access to the Ramblers UI primitives and mapping stack.

## Media Dependencies

### JavaScript File

#### `media/organisation/organisation.js`
- **Purpose**: Client-side organisation map display
- **Dependencies**: `ra.js`, `ra.leafletmap.js`, Leaflet.js
- **Integration**: Loaded via `RLoad::addScript()` in `display()`
- **Key Functions**:
  - `ra.display.organisationMap(options, data)` - Main initialization
  - `this.addMarkers(areas)` - Add area/group markers
  - `this.addMarker(item, area)` - Add individual marker
  - Marker styling based on scope (Area vs Group)
  - Popup content generation
- **API**:
  - `this.lmap` - Leaflet map instance
  - `this.cluster` - Marker clustering
  - `this.data` - Organisation data (areas, groups)
  - `this.load()` - Initialize map and markers
- **Usage**: Automatically initialized when `RLeafletMap` sets command to `"ra.display.organisationMap"`

**Loading**: `ROrganisation::display()` calls `RLoad::addScript()` for `media/js/ra.js`, `media/js/ra.map.js`, `media/js/ra.tabs.js`, and `media/organisation/organisation.js`, then defers Leaflet bootstrap to `RLeafletMap::display()`.

## Examples

### Example 1: Basic Organisation Display

```php
$org = new ROrganisation();
$map = new RLeafletMap();
$org->display($map);
```

### Example 2: Highlight Specific Group

```php
$org = new ROrganisation();
$org->myGroup('BU51', 10);
$map = new RLeafletMap();
$org->display($map);
```

### Example 3: Custom Styling

```php
$org = new ROrganisation();
$org->colourMyGroup = '#ff0000';
$org->colourMyArea = '#00ff00';
$org->showCodes = false;
$map = new RLeafletMap();
$org->display($map);
```

### Asset Inclusion Example

```php
$org = new ROrganisation();
$map = new RLeafletMap();
$org->display($map);
// RLoad enqueues media/js/ra.js and media/organisation/organisation.js
// RLeafletScript adds Leaflet + controls before ra.display.organisationMap runs
```

## Performance Notes

### Data Loading
- **Caching**: 60-minute TTL via `RFeedhelper`
- **JSON Parsing**: Fast for typical organisation size (<1000 groups)
- **Memory**: All areas/groups loaded into memory

### Map Rendering
- **Marker Clustering**: Used for large datasets
- **Client-Side**: Map rendering handled by JavaScript

## Error Handling

### Feed Errors
- **Read Failures**: Shows warning, uses empty array
- **Invalid JSON**: Shows error message
- **Missing Properties**: Validated via `checkJsonProperties()`

### Display Errors
- **Missing Data**: Map shows empty (graceful)
- **Invalid Coordinates**: Markers skipped (logged)

## References

### Related HLD Documents
- [feedhelper HLD](../feedhelper/HLD.md) - Feed retrieval
- [leaflet HLD](../leaflet/HLD.md) - Map rendering
- [html HLD](../html/HLD.md) - HTML formatting
- [accounts HLD](../accounts/HLD.md) - Account integration

### Key Source Files
- `organisation/organisation.php` - ROrganisation class
- `organisation/area.php` - ROrganisationArea class (if exists)
- `organisation/group.php` - ROrganisationGroup class (if exists)

### Related Media Files
- `media/organisation/organisation.js` - Client-side map display

