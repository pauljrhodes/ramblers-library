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

### Used By
- **ROrganisation::display()** for area/group mapping in Joomla pages/modules → [media/organisation HLD](../media/organisation/HLD.md#integration-points).
- **RAccounts::updateAccounts()** to enrich hosted-site data with group/area codes → [accounts HLD](../accounts/HLD.md#integration-points).

### Uses
- **RFeedhelper** for cached HTTP retrieval of the organisation JSON → [feedhelper HLD](../feedhelper/HLD.md#integration-points).
- **RLeafletMap / RLeafletScript** to set commands/data and enqueue Leaflet assets → [leaflet HLD](../leaflet/HLD.md#integration-points).
- **RLoad** to add `/media/organisation/organisation.js` plus `/media/js` foundations → [load HLD](../load/HLD.md#integration-points).
- **RHtml** for tabular display alternatives → [html HLD](../html/HLD.md#integration-points).

### Data Sources
- **Organisation Feed**: `https://groups.theramblers.org.uk/` JSON endpoint with areas/groups.

### Display Layer
- **Server**: `ROrganisation::display()` prepares map options and JSON payloads.
- **Client**: `ra.display.organisationMap` renders clustered markers and popups.

### Joomla Integration
- **Document pipeline**: Assets and payloads injected via `RLoad` and `RLeafletMap::display()`, respecting Joomla base paths and cache-busting.

### Key Features (`ROrganisation`)
- Fetches, caches, and converts the organisation feed into area/group domain objects.
- Provides display configuration flags (links, codes, colours) consumable by client scripts.
- Emits map-ready JSON and bootstrap commands for `ra.display.organisationMap`.

## Media Integration

### Server-to-Client Asset Relationship
### Vendor Library Integration
- **Leaflet.js + markercluster** loaded through `RLeafletScript`.

### Media Asset Relationships (Server → Client)

```mermaid
flowchart LR
    Org[ROrganisation::display]
    Loader[RLoad::addScript]
    Map[RLeafletMap::display]
    BaseJS[/media/js<br/>ra.js, ra.map.js, ra.tabs.js]
    OrgJS[/media/organisation/organisation.js]
    Bootstrap[ra.bootstrapper → ra.display.organisationMap]

    Org --> Loader
    Loader --> BaseJS
    Loader --> OrgJS
    Org --> Map
    Map --> Bootstrap
```

`ROrganisation::display()` enqueues `/media/organisation/organisation.js` plus the shared `/media/js` stack through `RLoad`; `RLeafletMap::display()` then injects the bootstrapper, so the browser spins up `ra.display.organisationMap` with the JSON data from PHP.

### Media Asset Loading
- **JavaScript entry point**: `/media/organisation/organisation.js` (instantiates `ra.display.organisationMap`).
- **Server-to-client flow**: PHP sets the command/data on `RLeafletMap`, leverages `RLoad` to add `/media` assets, and relies on `RLeafletScript::add()` for Leaflet dependencies before the client bootstrap runs.

### Key Features (`organisation.js`)
- Renders area and group markers with scope-aware colours and clustering.
- Popups surface names, codes, descriptions, and links when configured.
- Supports centring/highlighting a specific group and toggling visibility flags supplied by PHP.

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

## Performance Observations

### Data Loading
- **Caching**: 60-minute TTL via `RFeedhelper`.
- **JSON Parsing**: Fast for typical organisation size (<1000 groups).
- **Memory**: Areas/groups held in memory to support repeated displays.

### Map Rendering
- **Marker Clustering**: Keeps map interaction responsive for large group counts.
- **Client-Side**: Rendering handled entirely in JavaScript once payload delivered.

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
