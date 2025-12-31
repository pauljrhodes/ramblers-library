# Ramblers Library Architecture 
Comprehensive Architectural Overview + High-Level Design (HLD)

> **Scope**: Ramblers Library as a Joomla *site library* aggregating `jsonwalks`, `leaflet`, `event`, `organisation`, `accounts`, and shared utilities (caching, errors, geometry, HTML).

## 0. Quick orientation

### 0.1 What this library does (in one sentence)
It retrieves walk/event data (primarily from Walk Manager), normalises it into a domain model, then renders listings, maps, and calendar exports consistently across Joomla sites using a shared asset pipeline.

### 0.2 Key Classes by Module

| Module | Key Classes | Purpose |
|--------|-------------|---------|
| **jsonwalks** | `RJsonwalksFeed`, `RJsonwalksWalks`, `RJsonwalksWalk` | Walk data orchestration and domain modeling |
| **jsonwalks/std** | `RJsonwalksStdDisplay`, `RJsonwalksStdSimplelist`, `RJsonwalksStdWalktable` | Standard display presenters |
| **jsonwalks/wm** | `RJsonwalksWmFeed`, `RJsonwalksWmFileio`, `RJsonwalksWmCachefolder` | Walk Manager API integration |
| **leaflet** | `RLeafletMap`, `RLeafletScript`, `RLeafletMapoptions` | Interactive map rendering |
| **gpx** | `RGpxStatistics`, `RGpxFile`, `RGpxStatistic` | GPX folder scanning, stats, elevation metadata |
| **event** | `REventFeed`, `REventGroup`, `REventDownload` | Event aggregation |
| **ics** | `RIcsOutput`, `RIcsFile` | iCalendar export generation |
| **organisation** | `ROrganisation`, `ROrganisationArea`, `ROrganisationGroup` | Organisation data management |
| **accounts** | `RAccounts`, `RAccount`, `RLogfile` | Account management and logging |
| **feedhelper** | `RFeedhelper` | Generic HTTP feed retrieval with caching |
| **errors** | `RErrors` | Centralized error handling |
| **geometry** | `RGeometryGreatcircle` | Geographic calculations |
| **html** | `RHtml` | HTML formatting utilities |
| **sql** | `RSqlUtils` | Database utilities |
| **load** | `RLoad` | Asset loading with cache-busting |

**For detailed documentation, see individual module HLD documents:**
- [jsonwalks HLD](jsonwalks/HLD.md)
- [jsonwalks/wm HLD](jsonwalks/wm/HLD.md)
- [jsonwalks/std HLD](jsonwalks/std/HLD.md)
- [jsonwalks/walk HLD](jsonwalks/walk/HLD.md)
- [jsonwalks/leaflet HLD](jsonwalks/leaflet/HLD.md)
- [leaflet HLD](leaflet/HLD.md)
- [gpx HLD](gpx/HLD.md)
- [event HLD](event/HLD.md)
- [ics HLD](ics/HLD.md)
- [organisation HLD](organisation/HLD.md)
- [accounts HLD](accounts/HLD.md)
- [feedhelper HLD](feedhelper/HLD.md)
- [errors HLD](errors/HLD.md)
- [geometry HLD](geometry/HLD.md)
- [html HLD](html/HLD.md)
- [sql HLD](sql/HLD.md)
- [load HLD](load/HLD.md)

### 0.3 Integration overview (API, boundaries, caching, media assets)

- **Walk Manager API (OpenAPI-driven)**: `RJsonwalksWmFeed` constructs query-string calls to the WM Volunteer API (group codes, date ranges, walk/event/wellbeing toggles) and validates JSON payload shape before conversion.  
- **Joomla boundaries**: The library is packaged as a Joomla *site library* and pushes assets + JSON data into `JDocument` via `RLeafletScript`/`RLoad`, keeping presentation concerns inside Joomla’s document pipeline.  
- **Caching**: Walk Manager responses are cached on disk (`RJsonwalksWmCachefolder`) with a fixed 10-minute TTL, including fallback to stale cache on upstream failure.  
- **Media asset pipeline**: PHP presenters delegate to `RLoad` for cache-busted scripts/styles and to `RLeafletScript` for JSON payload injection, enabling JS bootstrap (`ra.bootstrapper`) to hydrate maps, tabs, and tables.

### 0.4 Key execution "spines"
- **Walk listing spine**: `RJsonwalksFeed → RJsonwalksWalks → presenter (RJsonwalksDisplaybase subclasses) → Leaflet bridge (RLeafletMap / RLeafletScript) → Joomla document injection`.  
- **Walk Manager spine**: `RJsonwalksSourcewalksmanager → RJsonwalksWmFeed → cache folder + file IO → WM API → error escalation`.  
- **Calendar spine**: `event/feed.php → RIcsOutput (RFC5545 output)` with optional shared JS dataset exposure via `RLeafletScript::registerWalks`.

### 0.5 Data Flow Overview

#### Complete BU51 Feed to Display Flow
This updated sequence diagram highlights the external WM API call (per the published OpenAPI spec), cache layering, and the media-asset pipeline driven by `RLoad`:

```mermaid
sequenceDiagram
    autonumber
    participant Page as "Joomla Page/Module"
    participant FeedOpts as RJsonwalksFeedoptions
    participant Feed as RJsonwalksFeed
    participant SrcWM as RJsonwalksSourcewalksmanager
    participant WMFeed as RJsonwalksWmFeed
    participant Cache as RJsonwalksWmCachefolder
    participant WMFileIO as RJsonwalksWmFileio
    participant WMAPI as Walk Manager API
    participant Walks as RJsonwalksWalks
    participant Presenter as "Display (Std/BU51/etc.)"
    participant LeafletMap as RLeafletMap
    participant LeafletScript as RLeafletScript
    participant RLoad as RLoad
    participant Doc as Joomla Document
    participant Browser as "Browser (ra.bootstrapper)"

    Note over Page: Construct feed options + presenter
    Page->>FeedOpts: "new('BU51') + group/date filters"
    Page->>Feed: "new(FeedOpts)"
    Page->>Presenter: "new(Display)"

    Note over Feed: Acquire data via WM API
    Feed->>SrcWM: "getWalks(Walks)"
    SrcWM->>WMFeed: "getGroupFutureItems(groups,...)"
    WMFeed->>Cache: "whichSource(cacheFile)"
    alt cache fresh
        Cache-->>WMFeed: cached JSON
    else read upstream
        WMFeed->>WMFileIO: "readFile(feedUrl)"
        WMFileIO->>WMAPI: "GET /volunteers/walksevents"
        WMAPI-->>WMFileIO: "JSON (OpenAPI schema)"
        WMFileIO-->>WMFeed: response body
        WMFeed->>Cache: "writeFile(cacheFile, body)"
    end
    WMFeed->>SrcWM: convertResults + normalise
    SrcWM->>Walks: "addWalk(walk)"

    Note over Presenter: Prepare render + assets
    Presenter->>LeafletMap: "setCommand(\"ra.display.walksTabs\") + payload"
    LeafletMap->>LeafletScript: "add(options + data)"
    LeafletScript->>RLoad: "addScript/addStyle (cache-busted)"
    RLoad->>Doc: enqueue assets + inline JSON

    Note over Doc: Deliver page
    Doc-->>Browser: "HTML + scripts/styles"
    Browser->>Browser: "ra.bootstrapper() hydrates display"
```

**Key Points:**
- Feed options configure data source (group codes, date ranges, types)
- Walk Manager feed handles caching (10-minute freshness, fallback to stale cache)
- Conversion layer transforms WM JSON to internal `RJsonwalksWalk` objects
- Display classes configure presentation (tabs, formats, styles)
- Leaflet integration injects assets and JSON payloads into Joomla document
- See [jsonwalks/wm HLD](jsonwalks/wm/HLD.md) for detailed WM feed architecture

### 0.6 Separation of concerns
- **Acquisition**: Feedhelper + Walk Manager adapters (jsonwalks/wm) isolate HTTP/caching from domain logic.
- **Domain**: jsonwalks aggregates walk entities and filtering/sorting rules.
- **Presentation**: jsonwalks/std and jsonwalks/leaflet presenters shape data for browsers, while leaflet/load manage asset/bootstrap responsibilities.
- **Exports**: event + ics convert walk collections into calendar formats without presentation dependencies.
- **Org/Accounts**: organisation/accounts add hosted-site intelligence using shared utilities without altering the walks domain.
- **Cross-cutting**: errors, geometry, html, and load offer reusable services without holding business state.

### 0.7 Integration Points

The library modules integrate through well-defined interfaces across three layers:

#### External interfaces
- **Walk Manager API** for walks/events/wellbeing data (jsonwalks/wm).
- **Organisation JSON feeds** for group metadata (organisation module).
- **Local GPX files/folders** for route and elevation summaries (gpx module feeding leaflet displays).
- **Joomla database tables** for accounts/organisation data persistence.
- **CDN/vendor assets** such as Leaflet, FullCalendar, cvList, Quill (loaded via `RLoad`/`RLeafletScript`).

#### Server-Side Integration (PHP Module Interactions)

**Data Acquisition Layer:**
- **feedhelper** → Used by `organisation` and `jsonwalks` sources for HTTP feed retrieval
- **jsonwalks/wm** → Provides Walk Manager API client used by `jsonwalks` source adapters
- **jsonwalks/sourcewalksmanager** → Converts WM JSON to internal walk domain objects

**Domain Layer:**
- **jsonwalks** → Core domain models (`RJsonwalksFeed`, `RJsonwalksWalks`, `RJsonwalksWalk`)
- **jsonwalks/walk** → Value objects (Admin, Basics, Items, TimeLocation, Flags, Bookings)
- **jsonwalks/feedoptions** → Configuration object consumed by feed classes

**Presentation Layer:**
- **jsonwalks/displaybase** → Abstract base for all display presenters
- **jsonwalks/std** → Standard display implementations (tabs, lists, tables, cancelled)
- **leaflet** → Map rendering and script injection
- **gpx** → Folder statistics and GPX map/list renderers feeding Leaflet commands
- **load** → Asset loading with cache-busting (used by all display modules)

**Export Layer:**
- **event** → Aggregates walks into events, uses `ics` for output
- **ics** → RFC5545 iCalendar format generation

**Organisation & Accounts:**
- **organisation** → Uses `feedhelper` for group feeds, `leaflet` for maps
- **accounts** → Uses `organisation`, `sql` utils, `html` formatting, `leaflet` maps

**Cross-Cutting Utilities:**
- **errors** → Used by all modules for error reporting
- **geometry** → Used by `jsonwalks` filtering and `leaflet` distance calculations
- **html** → Used by `accounts` and other modules for HTML formatting
- **sql** → Used by `accounts` and `organisation` for database operations

#### Leaflet data-source adapters (commands + payloads)
- **CSV/JSON/SQL lists**: `RLeafletCsvList`, `RLeafletJsonList`, and `RLeafletSqlList` read external files/queries, then call `setCommand('ra.display.tableList.display')` with table/pagination metadata for `ra.leafletmap` to render tabbed tables and markers.
- **Single GPX**: `RLeafletGpxMap::displayPath()` injects a single GPX track and elevation payload via `ra.display.gpxSingle`.
- **GPX folder list**: `RLeafletGpxMaplist` builds an item list from a folder (using `RGpxStatistics` for cached summaries) and publishes it through `ra.display.gpxFolder` with download state and styling options.
- **Route plotting**: `RLeafletMapdraw` sets `ra.display.plotRoute` and loads drawing/upload/download controls so the JS client (`ra.display.plotRoute`) can capture, style, and export user-drawn routes.

#### Client-Side Integration (JavaScript Module Interactions)

**Core Library Integration:**
- **ra.js** → Provides bootstrapper, utilities, event system used by all display modules
- **ra.map.js** → Map utilities and icon factory used by Leaflet displays
- **ra.feedhandler.js** → Location search used by map controls
- **ra.tabs.js** → Tab system used by tabbed displays
- **ra.paginatedDataList.js** → Pagination used by list/table displays
- **ra.walk.js** → Walk utilities and event management used by walk displays

**Display Module Integration:**
- **ra.display.walksTabs** → Standard walk display (List, Table, Calendar, Map tabs)
- **ra.display.walksMap** → Walk map marker display
- **ra.display.organisationMap** → Organisation area/group map display
- **ra.display.accountsMap** → Accounts map display
- **ra.display.gpxSingle** → GPX route display with elevation
- **ra.display.tableList** → Table-based data source display

**Map System Integration:**
- **ra.leafletmap** → Map wrapper with tabbed interface
- **ra._leafletmap** → Internal Leaflet map instance
- **L.Control.*** → Map controls (Places, Search, Tools, Routing, etc.)
- **ra.map.cluster** → Marker clustering system

**Vendor Library Integration:**
- **cvList** → Pagination library used by `ra.paginatedTable` and `ra.paginatedList`
- **FullCalendar** → Calendar view used by `ra.display.walksTabs`
- **Leaflet.js** → Core mapping library
- **Leaflet-gpx** → GPX parsing used by `ra.display.gpxSingle`
- **Leaflet.Elevation** → Elevation profiles used by GPX displays
- **geodesy** → Coordinate conversion used by map controls

#### Cross-Layer Integration (PHP ↔ JavaScript Communication)

**PHP → JavaScript:**
- **RLeafletScript** → Injects bootstrapper code and JSON data payloads
- **RJsonwalksStdDisplay** → Provides walk data for JavaScript rendering
- **RLeafletMap** → Configures map options and data for JavaScript
- **RLoad** → Loads JavaScript files with cache-busting

**JavaScript → PHP:**
- **AJAX Requests** → Form submissions (walkseditor), data fetching
- **Form Submissions** → Walk editor submits via `sendemail.php`
- **Error Reporting** → JavaScript errors sent to server via `ra.errors.toServer()`

**Asset Loading Pipeline:**
1. PHP display class calls `RLoad::addScript()` or `RLeafletScript::add()`
2. `RLoad` adds script/style to Joomla Document with cache-busting
3. Page renders with scripts/styles in `<head>` or before `</body>`
4. Browser loads assets
5. `ra.bootstrapper()` executes on page load
6. JavaScript display module initializes with data payload

**See individual module HLD documents for detailed integration patterns.**

### 0.8 Media Assets Architecture

The Ramblers Library uses a three-layer architecture: **Server-Side PHP**, **Presentation Layer**, and **Client-Side JavaScript**. Media assets bridge the presentation and client-side layers.

#### Three-Layer Architecture

```mermaid
flowchart TB
    subgraph Server["Server-Side PHP Layer"]
        Domain["Domain Models\nRJsonwalksWalk, RJsonwalksWalks"]
        Orchestration["Orchestration\nRJsonwalksFeed, RJsonwalksStdDisplay"]
        DataSources["Data Sources\nRJsonwalksWmFeed, RFeedhelper"]
    end

    subgraph Presentation["Presentation Layer"]
        Display["Display Classes\nRJsonwalksStdDisplay, RLeafletMap"]
        AssetLoad["Asset Loading\nRLoad, RLeafletScript"]
        DataInjection["Data Injection\nJSON payloads, script declarations"]
    end

    subgraph Client["Client-Side JavaScript Layer"]
        Core["Core Library\nra.js, ra.map.js"]
        DisplayJS["Display Modules\nra.display.walksTabs, ra.display.organisationMap"]
        Maps["Map System\nra.leafletmap, L.Control.*"]
        Vendors["Vendor Libraries\ncvList, Leaflet.js, FullCalendar"]
    end

    Server --> Presentation
    Presentation --> Client
    Client --> User[User Browser]
```

**Data Flow Across Layers:**
1. **Server-Side**: PHP classes fetch and process walk data
2. **Presentation**: Display classes inject data and initialize JavaScript
3. **Client-Side**: JavaScript renders interactive UI and handles user interactions

#### Core JavaScript Library

The `media/js/` module provides the foundation for all client-side functionality:

| File | Purpose | Loaded By | Dependencies |
|------|---------|-----------|--------------|
| `media/js/ra.js` | Core Ramblers library utilities, bootstrapper, event system | All display modules | None (base library) |
| `media/js/ra.map.js` | Map functionality and utilities, icon factory | Leaflet-based displays | ra.js |
| `media/js/ra.feedhandler.js` | Feed data handling, location search | Display modules with feeds | ra.js |
| `media/js/ra.paginatedDataList.js` | Pagination for data lists | List/table displays | ra.js, cvList.js |
| `media/js/ra.tabs.js` | Tab functionality | Tab-based displays (e.g., BU51) | ra.js |
| `media/js/ra.walk.js` | Walk-specific functionality, event management | Walk display modules | ra.js |

**See [media/js HLD](media/js/HLD.md) for complete documentation.**

#### Module-Specific JavaScript

Each module may have its own JavaScript files that integrate with the core library:

- **jsonwalks**: `media/jsonwalks/std/display.js`, `media/jsonwalks/leaflet/mapmarker.js` → [media/jsonwalks HLD](media/jsonwalks/HLD.md)
- **leaflet**: `media/leaflet/ra.leafletmap.js`, `media/leaflet/L.Control.*.js` → [media/leaflet HLD](media/leaflet/HLD.md)
- **organisation**: `media/organisation/organisation.js` → [media/organisation HLD](media/organisation/HLD.md)
- **accounts**: `media/accounts/accounts.js` → [media/accounts HLD](media/accounts/HLD.md)
- **walkseditor**: `media/walkseditor/js/*.js` → [media/walkseditor HLD](media/walkseditor/HLD.md)

#### Vendor Libraries

Customized or heavily integrated vendor libraries:

- **cvList**: Pagination library → [media/vendors HLD](media/vendors/HLD.md)
- **Leaflet.Elevation**: Customized elevation profiles → [media/vendors HLD](media/vendors/HLD.md)
- **leaflet-gpx**: GPX file parsing → [media/vendors HLD](media/vendors/HLD.md)
- **geodesy**: Coordinate conversion → [media/vendors HLD](media/vendors/HLD.md)

**Loading Mechanism:**
- Display classes call `RLoad::addScript()` or `RLeafletScript::add()` to enqueue assets
- Assets are injected into Joomla Document with cache-busting via file modification times
- JavaScript bootstrapper (`ra.bootstrapper`) initializes display modules with data payloads
- See [load HLD](load/HLD.md) for asset loading details

---

## 1. One-page architecture (read this first)

### 1.1 System context diagram (packages + external dependencies)
```mermaid
flowchart LR
  Client["Browser / Client"] --> Joomla[Joomla Site]

  subgraph RL["Ramblers Library (site library)"]
    JW["jsonwalks\nfeed + domain + presenters"]
    LF["leaflet\nmap + script + options"]
    GPX["gpx\nfolder stats + map feeds"]
    EV["event + ics\ncalendar exports"]
    ORG["organisation + accounts\norg feed + DB"]
    UTIL["utilities\nerrors + load + geometry + html"]
  end

  Joomla --> RL

  JW --> WMAPI["Walk Manager API\nwalks / groups"]
  ORG --> ORGFEED[Organisation JSON feeds]
  JW --> Cache["(Disk cache\ncache/ra_wm_feed)"]
  GPX --> GPXFS["Local GPX files/folders"]
  ORG --> DB["(Joomla DB tables)"]
  LF --> JoomlaDoc["Joomla Document\nscripts + styles + JSON payload"]
  UTIL --> Log[Error telemetry + Joomla messages]
```
**Notes**
- Walk/event data is predominantly sourced from Walk Manager; organisation feeds are separate JSON endpoints.  
- JS/CSS assets and JSON payloads are injected via Joomla’s Document pipeline using `RLoad` and `RLeafletScript`.

### 1.2 Component map by tier
```mermaid
flowchart TB
  subgraph Acquire["Data acquisition"]
    FH["RFeedhelper\nHTTP + cache + errors"]
    WM["RJsonwalksWmFeed\nWM client + caching"]
  end

  subgraph Domain["Domain modelling"]
    Feed[RJsonwalksFeed]
    Walks[RJsonwalksWalks]
    Walk[RJsonwalksWalk]
  end

  subgraph Present["Presentation + mapping"]
    Base[RJsonwalksDisplaybase]
    Std[RJsonwalksStdDisplay]
    List[RJsonwalksStdSimplelist]
    Table[RJsonwalksStdWalktable]
    Cancel[RJsonwalksStdCancelledwalks]
    Marker[RJsonwalksLeafletMapmarker]
    GpxMap[RLeafletGpxMap]
    GpxList[RLeafletGpxMaplist]
    Map[RLeafletMap]
    Script[RLeafletScript]
    Load[RLoad]
  end

  subgraph Export["Exports"]
    EG["event/feed.php\n(ICS aggregation entrypoint)"]
    Ics[RIcsOutput]
  end

  subgraph Org["Organisation + accounts"]
    OrgMod[ROrganisation]
    Acc[RAccounts]
    Sql[RSqlUtils]
    Html[RHtml]
  end

  subgraph Cross["Cross-cutting"]
    Err[RErrors]
    Geo[RGeometryGreatcircle]
  end

  %% Dependencies
  Feed --> Walks --> Walk
  Feed --> Base
  Base --> Std
  Base --> List
  Base --> Table
  Base --> Cancel
  Base --> Marker
  Base --> GpxMap

  Std --> Map
  Marker --> Map
  GpxMap --> Map
  GpxList --> Map
  Map --> Script --> Load

  Feed --> WM
  FH --> Err
  WM --> Err

  EG --> Ics --> Err
  OrgMod --> FH
  Acc --> OrgMod
  Acc --> Sql
  Acc --> Html
```
This is the “shape” of the library: `jsonwalks` does orchestration + domain; presenters render; Leaflet is a shared bootstrap; GPX helpers plug into Leaflet for route/folder visualisation; event/ICS exports reuse the domain objects; organisation/accounts reuse feed and mapping utilities.

### 1.3 Integration + cache + asset-loading view (component/sequence hybrid)
```mermaid
sequenceDiagram
  autonumber
  participant Joomla as "Joomla Module/Page"
  participant FeedOpts as RJsonwalksFeedoptions
  participant Feed as RJsonwalksFeed
  participant WMFeed as RJsonwalksWmFeed
  participant Cache as RJsonwalksWmCachefolder
  participant WMAPI as Walk Manager API
  participant Walks as RJsonwalksWalks
  participant Presenter as "Display (Std/BU51/etc.)"
  participant LeafletMap as RLeafletMap
  participant RLoad as RLoad
  participant Doc as Joomla Document
  participant Browser as "Browser (ra.bootstrapper)"

  Joomla->>FeedOpts: "configure (groups, date range, types)"
  Joomla->>Feed: "new(FeedOpts)"
  Feed->>WMFeed: "getGroupFutureItems(...)"
  WMFeed->>Cache: "whichSource(cacheFile)"
  alt cache fresh (<10 min)
    Cache-->>WMFeed: cached JSON
  else fetch upstream
    WMFeed->>WMAPI: "GET /volunteers/walksevents (OpenAPI schema)"
    WMAPI-->>WMFeed: JSON payload
    WMFeed->>Cache: "writeFile(cacheFile, payload)"
  end
  WMFeed-->>Feed: items[]
  Feed->>Walks: hydrate + filter + flags
  Joomla->>Presenter: "configure tabs/view"
  Presenter->>LeafletMap: setCommand + data payload
  LeafletMap->>RLoad: "add script/style assets (cache-busted)"
  RLoad->>Doc: enqueue assets + inline JSON payload
  Doc-->>Browser: HTML + assets
  Browser->>Browser: "ra.bootstrapper initialises modules"
```

**Highlights:** External WM API calls, cache freshness checks, and the RLoad-driven media pipeline that hands the bootstrapper JSON payloads for client-side rendering.

### 1.4 Primary runtime flow: render a walk listing page (complete flow)

**Server-Side Flow:**
```mermaid
sequenceDiagram
  autonumber
  actor Page as "Joomla Page / Module"
  participant Display as "Presenter (StdDisplay / Simplelist / Walktable / Cancelled / Mapmarker)"
  participant Feed as RJsonwalksFeed
  participant Walks as RJsonwalksWalks
  participant Wm as RJsonwalksWmFeed
  participant Cache as RJsonwalksWmCachefolder
  participant IO as RJsonwalksWmFileio
  participant WMAPI as Walk Manager API
  participant Map as RLeafletMap
  participant Script as RLeafletScript
  participant Doc as Joomla Document

  Page->>Display: instantiate + configure
  Display->>Feed: "new(options)"
  Feed->>Wm: request WM data
  Wm->>Cache: check cached snapshot
  alt Cache hit
    Cache-->>Wm: cached JSON
  else Cache miss / stale
    Wm->>IO: fetch JSON
    IO->>WMAPI: HTTP request
    WMAPI-->>IO: JSON response
    IO-->>Wm: JSON response
    Wm->>Cache: write snapshot
  end
  Feed->>Walks: hydrate + filter + flags
  Display->>Map: set command + data
  Map->>Script: add options + payload
  Script->>Doc: enqueue assets + bootstrap
  Display-->>Page: HTML output
```

### 1.5 Module summary (top-level + jsonwalks/wm and jsonwalks/std)

| Module | Role | Key Touchpoints |
|--------|------|-----------------|
| **jsonwalks** | Walk feed orchestration, domain model, filtering/sorting | Consumes Walk Manager adapters; feeds presenters and exports |
| **jsonwalks/wm** | Walk Manager API client, cache, IO, optional org delta checks | Called by jsonwalks sources; uses `RErrors` for telemetry |
| **jsonwalks/std** | Standard presenters (tabs/list/table/cancelled) plus schema injection | Calls `RLeafletMap`/`RLeafletScript`/`RLoad`; depends on `RJsonwalksWalks` |
| **leaflet** | Map container, options, script injection, data-source adapters (CSV/JSON/SQL/GPX) | Provides bootstrap + plugin loading for all map views |
| **gpx** | Folder scanning, cached statistics, GPX map/list renderers | Supplies `ra.display.gpxSingle/gpxFolder` payloads into Leaflet |
| **event + ics** | Aggregates walks into events and outputs ICS downloads | Uses `RJsonwalksFeed`/`RJsonwalksWalks`; exposes ICS text/download helpers |
| **organisation + accounts** | Hosted-site and area intelligence, DB-backed summaries, maps | Reuse `RFeedhelper`, `RLeafletMap`, `RSqlUtils`, `RHtml` |
| **walkseditor** | Asset loader and UI wiring for walk editing/programme/submit forms | Uses `RLoad`, Quill CDN, Ramblers JS foundation |
| **load + media/js** | Asset loading and shared JS foundations (tabs, pagination, bootstrapper) | Called by presenters and map scripts |
| **errors + geometry + html + sql** | Cross-cutting utilities for telemetry, geo maths, HTML formatting, DB helpers | Used across modules without owning business state |

---

## 2. Architecture overview (packaging, tiers, interactions)

### 2.1 Context and Packaging
Ramblers Library is delivered as a Joomla site library that aggregates multiple feature folders—such as jsonwalks, leaflet, event, organisation, and supporting utilities—to deliver Ramblers walk management, mapping, and integration capabilities in a reusable package.

### 2.2 Component Map (structural tiers)
- **Data acquisition and caching**: Feed handling lives in feedhelper, which wraps external HTTP retrieval with Joomla-aware caching and error reporting. This capability is reused by organisation lookups and other consumers that depend on remote JSON feeds.  
- **Domain modelling for walks**: The jsonwalks folder contains feed orchestration, aggregate walk collections, and individual walk entities with filtering, sorting, and augmentation logic that power downstream displays and exports.  
- **Presentation and interaction services**: The leaflet module coordinates client-side asset loading and data injection for interactive maps, collaborating with reusable loaders in load to handle cache-busting and Joomla document integration.  
- **GPX processing**: The gpx module scans GPX folders, caches statistics, and feeds map/list payloads to Leaflet renderers.  
- **Events and calendaring**: The event and ics folders expose iCalendar-compatible exports layered atop the walks domain model to support downloads and calendar integration.  
- **Organisational intelligence**: organisation and accounts combine feed ingestion, database updates, and presentation helpers to surface hosted-site inventories and geographic context, again leveraging shared utilities for SQL and mapping.  
- **Cross-cutting utilities**: Error propagation (errors), geometry helpers (geometry), and HTML formatting (html) provide reusable services consumed across the library.

### 2.3 Runtime interactions (typical request)
1. External feeds are retrieved through `RFeedhelper`, which caches responses to disk, normalises URLs, and uses `RErrors` to raise structured Joomla messages when fetches fail.  
2. JSON responses populate domain models in `jsonwalks`; `RJsonwalksFeed` hydrates `RJsonwalksWalks`, applies filters, and marks bookings / “new” statuses.  
3. Presenters pass data to `RLeafletMap` / `RLeafletScript`, which inject assets and JSON payloads via `RLoad` into the Joomla document.  
4. Calendar helpers output iCalendar feeds through `RIcsOutput` (RFC5545), managing escaping/wrapping and sequencing.  
5. Organisation and account features orchestrate `RFeedhelper`, `RSqlUtils`, and Leaflet wrappers for tables/maps and DB hydration.

---

## 3. High-Level Design (HLD)

### 3.1 Feed acquisition and error handling (shared helper)
`RFeedhelper` encapsulates remote retrieval with disk caching keyed by URL, sanitised filenames, and refresh windows; escalates problems through `RErrors::notifyError` for consistent observability.

### 3.2 Walks domain layer (jsonwalks core)
`RJsonwalksFeed` orchestrates loading and filtering; `RJsonwalksWalks` provides dedupe/filter/sort and booking flags; `RJsonwalksWalk` aggregates sub-records and exposes getters and helpers used by filters and presenters.

### 3.3 Mapping and client integration (leaflet + load)
`RLeafletMap` owns `RLeafletMapoptions` and delegates injection to `RLeafletScript`, which loads assets via `RLoad` (with cache-busting via file mtimes for local assets).

### 3.4 Events and calendaring (event + ics)
`event/feed.php` (aka “REventFeed” in older notes) renders ICS payloads through `RIcsOutput`, which handles RFC5545 formatting, escaping, wrapping, and sequencing.

### 3.5 Organisation and account features (organisation + accounts)
Organisation discovery uses `RFeedhelper` to fetch national group feeds; accounts uses `RSqlUtils` for conditional table access and renders HTML via `RHtml`, optionally projecting results onto Leaflet maps.

### 3.6 Supporting utilities
`RErrors` for telemetry + validation; `RGeometryGreatcircle` for geo maths; `RLoad` for asset injection and cache invalidation via versioning.

---

## 4. Component library (requested components) — dependency view
Focus: **RJsonwalks, RLeaflet, RLoad, RErrors, RFeedhelper, ROrganisation, RAccounts**.

```mermaid
classDiagram
direction LR

class RJsonwalksFeed
class RJsonwalksWalks
class RJsonwalksWalk

class RLeafletMap
class RLeafletMapoptions
class RLeafletScript
class RLoad
class RLeafletGpxMap
class RGpxStatistics

class RFeedhelper
class RErrors
class ROrganisation
class RAccounts
class RSqlUtils
class RHtml

RJsonwalksFeed --> RJsonwalksWalks : "builds/filters"
RJsonwalksWalks "1" --> "0..*" RJsonwalksWalk : contains
RJsonwalksFeed --> RLeafletMap : publish map payload
RLeafletMap --> RLeafletMapoptions : owns
RLeafletMap --> RLeafletScript : delegates injection
RLeafletScript --> RLoad : enqueue assets
RLeafletGpxMap --> RGpxStatistics : "folder stats/elevation"
RLeafletGpxMap --> RLeafletMap : map payload

RFeedhelper --> RErrors : "errors/validation"
ROrganisation --> RFeedhelper : organisation feed retrieval
RAccounts --> ROrganisation : hydrate org metadata
RAccounts --> RSqlUtils : "table checks/queries"
RAccounts --> RHtml : render tables
RAccounts --> RLeafletMap : hosted site markers
ROrganisation --> RLeafletMap : area map overlays
```


---

## 5. jsonwalks package structure and extension model
(unchanged; retained for completeness)

- **std/** – standard display suite.  
- **walk/** – domain value objects.  
- **leaflet/** – map bridge helpers.  
- **wm/** – Walk Manager gateway.  
- **ml/** – print-friendly monthly listings.  
- **ns/** – document export workflows.  

Area lookups reuse `RJsonwalksSourcewalksmanagerarea` via the same adapter with geo filters.

---

## 6. Walk Manager lifecycle & caching (deep dive)
(unchanged; retained)

```mermaid
flowchart LR
  Caller[RJsonwalksFeed] --> WmFeed[RJsonwalksWmFeed]

  WmFeed --> Cache[RJsonwalksWmCachefolder]
  Cache --> Hit{Cache hit and fresh?}

  Hit -- Yes --> CachedJSON[Use cached JSON]
  Hit -- No --> IO[RJsonwalksWmFileio]
  IO --> WMAPI[Walk Manager API]
  WMAPI --> FreshJSON[Fresh JSON]
  FreshJSON --> CacheWrite[Write cache snapshot]
  CacheWrite --> CachedJSON

  WmFeed --> OrgOpt["RJsonwalksWmOrganisation\noptional delta check"]
  OrgOpt --> OrgSlow{Slow or mismatch?}
  OrgSlow -- Yes --> Err[RErrors]
  OrgSlow -- No --> OK[Proceed]

  IO -->|failure| Err
  WmFeed -->|escalation| Err
```


---

## 7. Display + mapping pipeline (jsonwalks → leaflet)
(unchanged; retained)

```mermaid
flowchart TB
  Feed[RJsonwalksFeed] --> Walks[RJsonwalksWalks]

  subgraph Presenters["Presenters (Display specialisations)"]
    Base[RJsonwalksDisplaybase]
    Std[RJsonwalksStdDisplay]
    List[RJsonwalksStdSimplelist]
    Table[RJsonwalksStdWalktable]
    Cancel[RJsonwalksStdCancelledwalks]
    Marker[RJsonwalksLeafletMapmarker]
  end

  Feed --> Base
  Base --> Std
  Base --> List
  Base --> Table
  Base --> Cancel
  Base --> Marker

  Std --> Map[RLeafletMap]
  Marker --> Map
  List --> Script[RLeafletScript]
  Table --> Script
  Cancel --> Script

  Map --> Opts[RLeafletMapoptions]
  Map --> Script
  Script --> Load[RLoad]
  Load --> Doc[Joomla Document]
```


---

## 8. Domain composition (walk object model)
(unchanged; retained)

```mermaid
classDiagram
direction LR

class RJsonwalksFeed
class RJsonwalksWalks
class RJsonwalksWalk
class RJsonwalksWalkAdmin
class RJsonwalksWalkBasics
class RJsonwalksWalkItems
class RJsonwalksWalkTimelocation
class RJsonwalksWalkFlags
class RJsonwalksWalkBookings

RJsonwalksFeed --> RJsonwalksWalks
RJsonwalksWalks "1" --> "0..*" RJsonwalksWalk

RJsonwalksWalk "1" --> "1" RJsonwalksWalkAdmin
RJsonwalksWalk "1" --> "1" RJsonwalksWalkBasics
RJsonwalksWalk "1" --> "0..*" RJsonwalksWalkItems : "walks / meeting / start / finish / contacts"
RJsonwalksWalkItems --> RJsonwalksWalkTimelocation : "time/location nodes"
RJsonwalksWalk "1" --> "0..1" RJsonwalksWalkFlags
RJsonwalksWalk "1" --> "0..1" RJsonwalksWalkBookings
```


---

## 9. Organisation + accounts (hosted-site intelligence)
(unchanged; retained)

```mermaid
flowchart TB
  OrgFeed[Organisation JSON feed] --> FH[RFeedhelper]
  FH --> Org[ROrganisation]
  Org --> Map[RLeafletMap]
  Org --> Html[RHtml]

  Org --> DB["(Joomla DB)"]
  Acc[RAccounts] --> DB
  Acc --> Sql[RSqlUtils]
  Acc --> Map
```


---

## 10. Appendix: merged collaboration (compact but complete)
(unchanged; retained)

```mermaid
classDiagram
direction LR

class RJsonwalksFeed
class RJsonwalksWalks
class RJsonwalksWalk
class RJsonwalksDisplaybase
class RJsonwalksStdDisplay
class RJsonwalksStdSimplelist
class RJsonwalksStdWalktable
class RJsonwalksStdCancelledwalks
class RJsonwalksLeafletMapmarker

class RLeafletMap
class RLeafletMapoptions
class RLeafletScript
class RLoad
class RLeafletGpxMap
class RLeafletGpxMaplist
class RGpxStatistics

class RJsonwalksSourcewalksmanager
class RJsonwalksSourcewalksmanagerarea
class RJsonwalksWmFeed
class RJsonwalksWmCachefolder
class RJsonwalksWmFileio
class RJsonwalksWmOrganisation
class RErrors

class RAccounts
class ROrganisation
class RFeedhelper
class REventDownload
class RIcsOutput
class RSqlUtils
class RHtml

RJsonwalksDisplaybase <|-- RJsonwalksStdDisplay
RJsonwalksDisplaybase <|-- RJsonwalksStdSimplelist
RJsonwalksDisplaybase <|-- RJsonwalksStdWalktable
RJsonwalksDisplaybase <|-- RJsonwalksStdCancelledwalks
RJsonwalksDisplaybase <|-- RJsonwalksLeafletMapmarker

RJsonwalksFeed --> RJsonwalksWalks
RJsonwalksWalks "1" --> "0..*" RJsonwalksWalk
RJsonwalksFeed --> RJsonwalksDisplaybase

RJsonwalksStdDisplay --> RLeafletMap
RJsonwalksStdSimplelist --> RLeafletScript
RJsonwalksStdWalktable --> RLeafletScript
RJsonwalksStdCancelledwalks --> RLeafletScript
RJsonwalksLeafletMapmarker --> RLeafletMap
RLeafletGpxMap --> RLeafletMap
RLeafletGpxMaplist --> RLeafletMap
RLeafletGpxMaplist --> RGpxStatistics

RLeafletMap --> RLeafletMapoptions
RLeafletMap --> RLeafletScript
RLeafletScript --> RLoad

RJsonwalksFeed --> RJsonwalksSourcewalksmanager
RJsonwalksSourcewalksmanagerarea --> RJsonwalksSourcewalksmanager
RJsonwalksSourcewalksmanager --> RJsonwalksWmFeed

RJsonwalksWmFeed "1" --> "1" RJsonwalksWmCachefolder
RJsonwalksWmFeed "1" --> "1" RJsonwalksWmFileio
RJsonwalksWmFeed "1" --> "0..1" RJsonwalksWmOrganisation

RJsonwalksWmFileio --> RErrors
RJsonwalksWmFeed --> RErrors
RJsonwalksWmOrganisation --> RErrors

RFeedhelper --> RErrors
ROrganisation --> RFeedhelper
RAccounts --> ROrganisation
RAccounts --> RSqlUtils
RAccounts --> RHtml
RAccounts --> RLeafletMap
ROrganisation --> RLeafletMap

RJsonwalksWalk --> RIcsOutput
REventDownload --> RIcsOutput
RIcsOutput --> RErrors
RGpxStatistics --> RGpxFile
RGpxStatistics --> RGpxStatistic
RLeafletGpxMap --> RGpxStatistics
```


---

## 11. Method dependency trees (focused views)

### 11.1 jsonwalks + leaflet + exports
```mermaid
flowchart LR
  FeedCtor["RJsonwalksFeed::__construct"] --> WalksCtor["RJsonwalksWalks::__construct"]
  FeedCtor --> FeedFilters[RJsonwalksFeed: ":filter*()]"
  FeedCtor --> FeedDisplay[RJsonwalksFeed: ":display]"
  FeedDisplay --> StdDisplay[RJsonwalksStdDisplay: ":DisplayWalks]"
  FeedDisplay --> Simple[RJsonwalksStdSimplelist: ":DisplayWalks]"
  FeedDisplay --> Table[RJsonwalksStdWalktable: ":DisplayWalks]"
  FeedDisplay --> Cancel[RJsonwalksStdCancelledwalks: ":DisplayWalks]"
  StdDisplay --> MapCtor[RLeafletMap: ":__construct]"
  StdDisplay --> MapCommand[RLeafletMap: ":setCommand]"
  StdDisplay --> MapDisplay[RLeafletMap: ":display]"
  MapDisplay --> LeafletAdd[RLeafletScript: ":add]"
  LeafletAdd --> LoadAssets[RLoad: ":addScript/addStyleSheet]"
  FeedDisplay --> MapMarker[RJsonwalksLeafletMapmarker: ":DisplayWalks]"
  MapMarker --> MapDisplay
  FeedDisplay --> IcsDownload[RJsonwalksFeed: ":displayIcsDownload]"
  IcsDownload --> EventGroup[REventGroup: ":addWalks]"
  IcsDownload --> EventDownload[REventDownload: ":Display]"
  EventGroup --> IcsOutput[RIcsOutput: ":addRecord/addSequence]"
```

### 11.2 Accounts + organisation + feedhelper + errors
```mermaid
flowchart LR
  OrgLoad["ROrganisation::load"] --> OrgFeed["ROrganisation::readFeed"]
  OrgFeed --> FHGet[RFeedhelper: ":getFeed]"
  FHGet --> FHCache[RFeedhelper: ":createCachedFileFromUrl]"
  FHGet --> FHError[RErrors: ":notifyError]"
  OrgLoad --> OrgDisplay[ROrganisation: ":display]"
  OrgDisplay --> MapSet[RLeafletMap: ":setDataObject]"
  OrgDisplay --> LoadAssets[RLoad: ":addScript/addStyleSheet]"

  AccUpdate["RAccounts::updateAccounts"] --> OrgConstruct["ROrganisation::__construct"]
  AccUpdate --> DbUpdate[RAccounts: ":updateDatabase]"
  DbUpdate --> SqlUtils[RSqlUtils: ":executeQuery]"
  AccUpdate --> MapMarkers[RAccounts: ":addMapMarkers]"
  MapMarkers --> MapSet
```

### 11.3 GPX + data-source adapters
```mermaid
flowchart LR
  GpxFolder["RLeafletGpxMaplist::display"] --> Stats["RGpxStatistics::buildStatistics"]
  Stats --> GpxJson[RGpxStatistic: ":jsonSerialize]"
  GpxFolder --> LeafletCmd[RLeafletMap: ":setCommand(\\"ra.display.gpxFolder\\")]"
  GpxFolder --> LeafletData[RLeafletMap: ":setDataObject]"
  LeafletData --> LeafletAdd[RLeafletScript: ":add]"
  LeafletAdd --> LoadAssets[RLoad: ":addScript/addStyleSheet]"

  CsvList["RLeafletCsvList::display"] --> CsvCmd["RLeafletMap::setCommand("\\\"ra.display.tableList\\\"")"];
  CsvList --> CsvData[RLeafletMap: ":setDataObject]"
  CsvData --> LeafletAdd
```


---

## 12. High-Level Design (HLD) Documents

Detailed architecture documentation is available for each module:

### Core Walk Management
- [jsonwalks/HLD.md](jsonwalks/HLD.md) - Main orchestration, domain models, display base classes
- [jsonwalks/wm/HLD.md](jsonwalks/wm/HLD.md) - Walk Manager API integration (detailed)
- [jsonwalks/std/HLD.md](jsonwalks/std/HLD.md) - Standard display implementations
- [jsonwalks/walk/HLD.md](jsonwalks/walk/HLD.md) - Walk domain value objects
- [jsonwalks/leaflet/HLD.md](jsonwalks/leaflet/HLD.md) - Map marker integration

### Data Acquisition & Caching
- [feedhelper/HLD.md](feedhelper/HLD.md) - Generic HTTP feed retrieval with caching
- [jsonwalks/wm/HLD.md](jsonwalks/wm/HLD.md) - WM feed system (see above)

### Presentation & Mapping
- [leaflet/HLD.md](leaflet/HLD.md) - Leaflet map integration, script loading, options
- [leaflet/csv/HLD.md](leaflet/csv/HLD.md) - CSV data source for maps
- [leaflet/gpx/HLD.md](leaflet/gpx/HLD.md) - GPX data source for maps
- [leaflet/json/HLD.md](leaflet/json/HLD.md) - JSON data source for maps
- [leaflet/sql/HLD.md](leaflet/sql/HLD.md) - SQL data source for maps
- [leaflet/table/HLD.md](leaflet/table/HLD.md) - Table column definitions
- [load/HLD.md](load/HLD.md) - Asset loading with cache-busting

### Events & Calendaring
- [event/HLD.md](event/HLD.md) - Event aggregation and ICS export
- [ics/HLD.md](ics/HLD.md) - iCalendar (RFC5545) output generation

### Organisation & Accounts
- [organisation/HLD.md](organisation/HLD.md) - Organisation data management
- [accounts/HLD.md](accounts/HLD.md) - Account management and logging

### Utilities
- [errors/HLD.md](errors/HLD.md) - Centralized error handling and telemetry
- [geometry/HLD.md](geometry/HLD.md) - Geographic calculations
- [html/HLD.md](html/HLD.md) - HTML formatting utilities
- [sql/HLD.md](sql/HLD.md) - Database utilities

### Specialized Modules
- [calendar/HLD.md](calendar/HLD.md) - Calendar functionality
- [gpx/HLD.md](gpx/HLD.md) - GPX file processing
- [gpxsymbols/HLD.md](gpxsymbols/HLD.md) - GPX symbol display
- [directory/HLD.md](directory/HLD.md) - Directory listing
- [dns/HLD.md](dns/HLD.md) - DNS record management
- [walkseditor/HLD.md](walkseditor/HLD.md) - Walk editing interface
- [license/HLD.md](license/HLD.md) - License management

### Media Assets (Client-Side JavaScript)
- [media/js/HLD.md](media/js/HLD.md) - Core JavaScript library (bootstrapper, utilities, events)
- [media/jsonwalks/HLD.md](media/jsonwalks/HLD.md) - Walk display JavaScript (tabs, lists, maps)
- [media/leaflet/HLD.md](media/leaflet/HLD.md) - Leaflet map JavaScript (controls, layers, GPX)
- [media/organisation/HLD.md](media/organisation/HLD.md) - Organisation map JavaScript
- [media/accounts/HLD.md](media/accounts/HLD.md) - Accounts map JavaScript
- [media/walkseditor/HLD.md](media/walkseditor/HLD.md) - Walk editor JavaScript and PHP email
- [media/gpxsymbols/HLD.md](media/gpxsymbols/HLD.md) - GPX symbol existence check PHP
- [media/vendors/HLD.md](media/vendors/HLD.md) - Customized vendor libraries (cvList, elevation, GPX, geodesy)

Each HLD document includes: component architecture diagrams, public interfaces, data flow sequences, integration points, media dependencies, examples, performance notes, and error handling strategies.

---

## 13. Maintainers' notes (operational debugging)

### 13.1 Debugging “no walks” or “stale walks”
- Confirm which adapter was selected (`RJsonwalksSourcewalksmanager` vs area variant).  
- Check cache presence/age and whether WM requests are failing and falling back to cached artefacts (see §6).  
- If enabling organisation delta checks, remember the groups endpoint can be slow and is treated as experimental.  

### 13.2 Debugging “map loads but no markers”
- Validate the presenter is calling `registerWalks()` or setting `RLeafletMap::setDataObject()` (see §7).  
- Confirm scripts/styles were enqueued via `RLoad` and required plugins are enabled.  

---
_End of merged document._
