# gpxsymbols Module - High Level Design

## Overview

The `gpxsymbols` module renders available GPX waypoint symbols within Joomla views. It discovers symbol assets on disk, streams them to the browser, and wires the companion `/media/gpxsymbols` helper for existence checks.

**Purpose**: GPX symbol rendering and discovery.

**Key File**: `gpxsymbols/display.php`

## Component Architecture

```mermaid
flowchart TB
    subgraph Server[Server-Side]
        Display["RGpxsymbolsDisplay\ndisplay.php"]
        Exists["media/gpxsymbols/exists.php\nAsset check"]
    end

    subgraph Assets["Symbol Assets (/media/gpxsymbols)"]
        CSS["display.css\nStyles"]
        Symbols["letter/, number/, office/, transport/"]
    end

    subgraph Client[Client Browser]
        Html[Symbol grid HTML]
        Images[Loaded symbol images]
    end

    Display --> Exists
    Display --> CSS
    Display --> Html
    Html --> Images
    Exists --> Client
```

## Public Interface

### RGpxsymbolsDisplay

**Main symbol renderer.**

#### Constructor
```php
public function __construct()
```
- **Behavior**: Obtains the Joomla document and enqueues `/media/lib_ramblers/gpxsymbols/display.css` to style the rendered grid.

#### Symbol Listing Method
```php
public function listFolder($folder)
```
- **Parameters**: `$folder` - Absolute path to a symbol directory under `/media/lib_ramblers/gpxsymbols/*`.
- **Behavior**:
  - Enumerates files, sorts naturally, and renders a `<details>` panel with thumbnails and filenames.
  - Uses `displayImage()` to emit individual symbol entries.

#### Private Helper
```php
private function displayImage($folder, $entry)
```
- **Behavior**: Outputs an `<img>` element for the symbol and a caption derived from the filename.

### Key Features
- Automatic stylesheet injection for symbol grids.
- Natural sorting of symbol filenames for predictable display order.
- Reusable renderer that targets any `/media/lib_ramblers/gpxsymbols/*` subfolder.

## Data Flow

### Symbol Discovery Flow

```mermaid
sequenceDiagram
    participant Caller as Caller
    participant Display as RGpxsymbolsDisplay
    participant Doc as Joomla Document
    participant FS as File System
    participant Browser as Browser

    Caller->>Display: "new()"
    Display->>Doc: "addStyleSheet(/media/lib_ramblers/gpxsymbols/display.css)"
    Caller->>Display: "listFolder(media/.../gpxsymbols/letter)"
    Display->>FS: "opendir()/readdir()"
    FS-->>Display: filenames
    loop each symbol
        Display->>Browser: "<img src=\".../symbol.png\">"
    end
```

## Integration Points

### Uses
- **Joomla Document**: Queues `/media/gpxsymbols` assets for the rendered page.
- **`media/gpxsymbols/exists.php`**: Optional existence checks for symbol files → [media/gpxsymbols HLD](../media/gpxsymbols/HLD.md)

### Media Assets (/media/gpxsymbols)
- **Server-to-Client Loading**: `RGpxsymbolsDisplay::__construct()` injects the symbol stylesheet via the Joomla document API; the rendered HTML references symbol image files directly under `/media/lib_ramblers/gpxsymbols/*` when building the grid.
- **Asset Relationship**:

```mermaid
flowchart LR
    Display[RGpxsymbolsDisplay]
    Loader["JDocument::addStyleSheet"]
    Assets["/media/gpxsymbols\ndisplay.css + symbol sprites"]
    Grid[Rendered symbol grid]

    Display --> Loader
    Loader --> Assets
    Display --> Grid
```

### Related HLD Documents
- [media/gpxsymbols HLD](../media/gpxsymbols/HLD.md) - Symbol existence endpoint and asset inventory
