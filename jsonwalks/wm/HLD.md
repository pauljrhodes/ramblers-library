# Walks Manager (WM) Feed System - High Level Design

## Overview

The `jsonwalks/wm` module provides the Walks Manager integration layer for Ramblers Library. It builds WM API URLs, retrieves JSON (with retries and gzip support), applies a 10-minute cache window, and hands validated items back to the `jsonwalks` domain for conversion into `RJsonwalksWalk` objects.

**Key Classes (module-owned)**
- `RJsonwalksWmFeed` – Orchestrates reads from WM API or cache and performs JSON validation.【F:jsonwalks/wm/feed.php†L17-L133】
- `RJsonwalksWmCachefolder` – Manages cache directory operations (read/write/mtime checks).【F:jsonwalks/wm/cachefolder.php†L14-L65】
- `RJsonwalksWmFileio` – HTTP/file reader with retry, HTTPS upgrade, gzip, and error redaction.【F:jsonwalks/wm/fileio.php†L9-L69】
- `RJsonwalksWmFeedoptions` – Builds WM API URLs and deterministic cache filenames.【F:jsonwalks/wm/feedoptions.php†L7-L85】
- `RJsonwalksWmOrganisation` – Optional source selector based on WM “last updated” timestamps (rarely used in practice).【F:jsonwalks/wm/organisation.php†L13-L113】

**Constants**
- `WALKMANAGER` – Base WM API URL (`https://walks-manager.ramblers.org.uk/api/volunteers/walksevents?`).【F:jsonwalks/wm/feed.php†L9-L21】
- `APIKEY` – WM API key (redacted in logs via `RJsonwalksWmFileio::setSecretStrings`).【F:jsonwalks/wm/feed.php†L9-L33】
- `READSOURCE` – Enum for `FEED` vs `CACHE`.【F:jsonwalks/wm/feed.php†L136-L143】

## Component Architecture

```
Joomla module/page
        │
        ▼
RJsonwalksFeed → RJsonwalksSourcewalksmanager → RJsonwalksWmFeed
                                                    │
                                                    ├─ RJsonwalksWmFeedoptions (URL + cache key)
                                                    ├─ RJsonwalksWmCachefolder (read/write/mtime)
                                                    └─ RJsonwalksWmFileio (HTTP/local IO, retries)
```

## RJsonwalksWmFeed (`feed.php`)

### Public Interface

- `getGroupFutureItems($groups, $readwalks, $readevents, $readwellbeingwalks): array`  
  Builds a 12-month date window starting “today”, sets inclusion flags, and delegates to `getFeed` for cache/HTTP retrieval.【F:jsonwalks/wm/feed.php†L25-L50】

- `getItemsWithinArea($latitude, $longitude, $distance, $readwalks, $readevents, $readwellbeingwalks): array`  
  Builds an area query (lat/long/radius) and delegates to `getFeed`. Note: both walk/event inclusion flags are mirrored into `include_events/include_walks` before calling WM.【F:jsonwalks/wm/feed.php†L52-L75】

### Core Behaviour

- **Cache selection**: `whichSource()` returns `CACHE` when a cache file exists and is younger than 10 minutes; otherwise `FEED`. Organisation-based source selection can be toggled by changing `$method`.【F:jsonwalks/wm/feed.php†L85-L118】
- **Cache fallback**: On feed failure, it will read any existing cache file even if stale; on success it writes the fresh body back to cache.【F:jsonwalks/wm/feed.php†L85-L118】
- **Validation**: `convertResults()` ensures JSON starts with `{`, decodes, and checks for expected properties before returning `$items->data`; returns `[]` on any failure.【F:jsonwalks/wm/feed.php†L120-L158】
- **Error reporting**: Errors are routed through `RJsonwalksWmFileio::errorMsg`/`RErrors::notifyError` with API keys redacted.【F:jsonwalks/wm/feed.php†L160-L175】【F:jsonwalks/wm/fileio.php†L9-L69】

## RJsonwalksWmFileio (`fileio.php`)

### Public Interface

- `readFile($urlOrPath)` – Reads HTTP(S) URLs or local files with HTTPS upgrade, gzip support, 3 retry attempts, and an 8s timeout. Returns string or `false`. Secrets registered via `setSecretStrings()` are redacted in errors.【F:jsonwalks/wm/fileio.php†L9-L69】
- `writeFile($filename, $data)` – Thin wrapper over Joomla file write (used by cache).【F:jsonwalks/wm/fileio.php†L49-L57】
- `setSecretStrings(array $values)` – Registers secrets to mask in logs.【F:jsonwalks/wm/fileio.php†L13-L17】

### Error Handling

Errors are reported through `RErrors::notifyError` with sensitive substrings removed; no exceptions are thrown (callers see `false`).【F:jsonwalks/wm/fileio.php†L28-L44】

## RJsonwalksWmCachefolder (`cachefolder.php`)

### Responsibilities

- Ensures the cache directory exists under `JPATH_SITE/cache/<name>`.【F:jsonwalks/wm/cachefolder.php†L14-L28】
- Provides `fileExists`, `readFile`, `writeFile`, and `lastModified` helpers for cache management.【F:jsonwalks/wm/cachefolder.php†L28-L65】
- Used by `RJsonwalksWmFeed` to decide freshness and to persist successful feed reads.

## RJsonwalksWmFeedoptions (`feedoptions.php`)

### Responsibilities

- Holds query parameters (group codes, date range, inclusion flags, area search).【F:jsonwalks/wm/feedoptions.php†L7-L57】
- Builds the WM API URL via `getFeedURL()` (uses `http_build_query`) and constructs deterministic cache filenames via `getCacheFileName($extension)`.【F:jsonwalks/wm/feedoptions.php†L57-L85】

### Key Properties

`groupCode`, `include_walks`, `include_events`, `include_wellbeing_walks`, `latitude`, `longitude`, `distance`, `date_start`, `date_end`.【F:jsonwalks/wm/feedoptions.php†L7-L57】

## RJsonwalksWmOrganisation (`organisation.php`)

### Role

Optional helper that can choose FEED vs CACHE based on WM group “last updated” timestamps. It is wired into `RJsonwalksWmFeed` but bypassed when `$method === "time"` (the default).【F:jsonwalks/wm/feed.php†L81-L91】【F:jsonwalks/wm/organisation.php†L13-L113】

## Data Flow (time-based mode)

```
RJsonwalksFeedoptions → RJsonwalksSourcewalksmanager → RJsonwalksWmFeed
   → RJsonwalksWmFeedoptions (URL + cache key)
   → whichSource(): cache? (<10 min) → read cache
                    else → RJsonwalksWmFileio::readFile(feed URL)
   → on success: cache write
   → convertResults(): decode/validate → items->data
   → returned to Source → converted to RJsonwalksWalk → presenters
```

## Error Handling Philosophy

- Graceful degradation: return `[]` on failure; never throw.
- Redacted logging: API keys are masked in error output.
- Cache-first resilience: stale cache is used when WM API is unavailable.

## Testing/Mocking Hooks

- Mock `RJsonwalksWmFileio::readFile()` to simulate API responses/timeouts.
- Inject test cache files and vary mtimes to exercise `whichSource()`.
- Verify redaction by registering dummy secrets via `setSecretStrings()`.
