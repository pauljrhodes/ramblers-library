# media/gpxsymbols Module - High Level Design

## Overview

The `media/gpxsymbols` module provides a PHP endpoint for checking GPX symbol file existence. It's used by the GPX symbol display system to verify if symbol image files are available before attempting to display them.

**Purpose**: GPX symbol existence checking endpoint.

**Key Responsibilities**:
- Check if GPX symbol files exist
- Return boolean response (true/false)
- Sanitize file path input
- Provide safe file existence checking

## Component Architecture

```mermaid
flowchart TB
    subgraph Endpoint["PHP Endpoint"]
        Exists[exists.php<br/>File check]
    end

    subgraph Input["Input"]
        GETRequest[GET Request<br/>?file=path]
    end

    subgraph Output["Output"]
        Response[true/false<br/>String response]
    end

    subgraph FileSystem["File System"]
        SymbolFiles[Symbol Image Files<br/>letter/, number/, etc.]
    end

    GETRequest --> Exists
    Exists --> FileSystem
    FileSystem --> Exists
    Exists --> Response
```

## Public Interface

### exists.php

**File existence check endpoint.**

#### Request
```
GET /media/lib_ramblers/gpxsymbols/exists.php?file=<filepath>
```

#### Parameters
- `file` - File path to check (relative or absolute)

#### Response
- `"true"` - File exists
- `"false"` - File does not exist

#### Behavior
1. Receives GET parameter `file`
2. Sanitizes input with `htmlspecialchars()`
3. Checks file existence with `file_exists()`
4. Returns "true" or "false" as plain text

## Data Flow

### Symbol Existence Check Flow

```mermaid
sequenceDiagram
    autonumber
    participant Client as JavaScript Client
    participant Exists as exists.php
    participant FileSystem as File System

    Client->>Exists: GET ?file=media/.../symbol.png
    Exists->>Exists: htmlspecialchars(file)
    Exists->>FileSystem: file_exists(filepath)
    FileSystem-->>Exists: true/false
    Exists-->>Client: "true" or "false"
```

## Integration Points

### JavaScript Integration
- **GPX Display**: Used by GPX symbol display to check symbol availability
- **Map Markers**: Used when displaying waypoint symbols

### File System
- **Symbol Directories**: 
  - `media/gpxsymbols/letter/` - Letter symbols (a-z)
  - `media/gpxsymbols/number/` - Number symbols (0-100)
  - `media/gpxsymbols/number_white/` - White number symbols
  - `media/gpxsymbols/office/` - Office symbols
  - `media/gpxsymbols/transport/` - Transport symbols

## Media Dependencies

### Symbol Image Files
- `media/gpxsymbols/letter/*.png` - 26 letter symbols
- `media/gpxsymbols/number/*.png` - 101 number symbols
- `media/gpxsymbols/number_white/*.png` - 101 white number symbols
- `media/gpxsymbols/office/*.png` - 74 office symbols
- `media/gpxsymbols/transport/*.png` - 101 transport symbols

### CSS Dependencies
- `media/gpxsymbols/display.css` - Symbol display styles

## Examples

### Example 1: Check Symbol Existence

```javascript
// JavaScript check
fetch('media/lib_ramblers/gpxsymbols/exists.php?file=media/lib_ramblers/gpxsymbols/letter/a.png')
    .then(response => response.text())
    .then(result => {
        if (result === "true") {
            // Symbol exists, display it
        } else {
            // Symbol not found, use default
        }
    });
```

## Performance Notes

### File System Checks
- **Fast**: `file_exists()` is fast for local files
- **No Caching**: Each request checks file system
- **Network Overhead**: Minimal (small response)

### Optimization Opportunities
1. **Response Caching**: Cache existence checks client-side
2. **Batch Checking**: Check multiple symbols in single request
3. **Symbol Manifest**: Pre-generate list of available symbols

## Error Handling

### Input Validation
- **Sanitization**: `htmlspecialchars()` prevents XSS
- **Path Validation**: No path traversal protection (consider adding)

### File System Errors
- **Missing Files**: Returns "false" (graceful)
- **Permission Errors**: May return false or error (depends on PHP config)

## References

### Related HLD Documents
- [gpxsymbols HLD](../../gpxsymbols/HLD.md) - GPX symbols PHP module
- [media/leaflet HLD](../leaflet/HLD.md) - GPX map display

### Key Source Files
- `media/gpxsymbols/exists.php` - Existence check endpoint (18 lines)

### Related Media Files
- `media/gpxsymbols/display.css` - Symbol stylesheet
- `media/gpxsymbols/letter/*.png` - Letter symbols (26 files)
- `media/gpxsymbols/number/*.png` - Number symbols (101 files)
- `media/gpxsymbols/number_white/*.png` - White number symbols (101 files)
- `media/gpxsymbols/office/*.png` - Office symbols (74 files)
- `media/gpxsymbols/transport/*.png` - Transport symbols (101 files)


