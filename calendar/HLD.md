# calendar Module - High Level Design

## Overview

The `calendar` module renders month-by-month calendars and injects per-day walk/event details supplied by event/feed modules.

**Purpose**: Calendar display for walks and events.

**Key File**: `calendar/calendar.php`

## Component Diagram

```
                +-------------------+
                | REventCalendar    | (event display adapter)
                +-------------------+
                          |
                          v Display(...) passes events + display flags
+-------------------+   show(display, events)   +-------------------+
| RCalendar         |-------------------------->| REventGroup / feed |
| (calendar layout) |<------ addEvent() --------+-------------------+
|                   |      (per day content)
+---------+---------+
          |
          v renders HTML
   Front-end calendar widget (uses ra.js toggle)
```

## Key Classes / Functions

### `RCalendar`
- **State**: sizing (`$size`), label formats, current month/year cursors, event provider reference, and whether to display all months.
- **Responsibilities**: iterate months, build navigation, render day grids, and delegate per-day content to the supplied events object via `addEvent`.
- **Core Methods**:
  - `show($display, $events)`: drives month rendering starting from the current month through the last available event date.
  - `_showDay($cellNumber)`: positions days in the grid, highlights today, and injects event HTML.
  - `_createNavi($navtype)`: renders Prev/Next controls wired to `ra.html.toggleVisibilities`.

### `REventCalendar` (in `event/calendar.php`)
- Adapts JSON walks/event data for `RCalendar`.
- Adds CSS/JS assets (`calendar.css`, `ramblerslibrary.css`, `ra.js`), configures month labels, and invokes `RCalendar->show`.

### `REventGroup::addEvent` (from `event/group.php`)
- Supplies the `addEvent` interface expected by `RCalendar`, returning HTML for each day (lists, hover blocks, etc.).

## Public Interfaces & Usage

- `new RCalendar(int $size, bool $displayAll)`: set calendar size variant and whether to render all months at once.
- `setMonthFormat(string $format)`: PHP date format used in month headers (default `Y M`).
- `show($display, $events): void`: emit calendar HTML using the provided event provider.

Typical usage flows through `REventCalendar`:
```php
$adapter = new REventCalendar(250);   // size: 0, 200, 250, or 400
$adapter->displayAll();               // optional: render all months up to last event
$adapter->setMonthFormat("F 'y");     // optional custom header format
$adapter->Display($eventGroup);       // $eventGroup implements addEvent/getLastDate
```
The `$eventGroup` is usually built from JSON walks or event feeds.

## Data Flow & Integration Points

- **Inputs**:
  - Event data from `REventGroup` populated by feeds (`event/feed.php`, `event/group.php`) or JSON walks (`jsonwalks/std`).
  - Display hints (`$display` string) passed through to `REventGroup::EventList`.
- **Processing**:
  - Determine calendar range from the current date to the last event date.
  - For each day cell, call `$events->addEvent($display, $text, $currentDate)` to render walk/event details.
  - Navigation links toggle month visibility via `ra.html.toggleVisibilities` in `media/lib_ramblers/js/ra.js`.
- **Outputs**:
  - HTML calendar blocks styled by `media/lib_ramblers/calendar/calendar.css` and `ramblerslibrary.css`.
  - Event content wrapped in interactive toggles/hover blocks provided by the event module.
- **Integration**:
  - Calendar pages commonly ingest event feeds (see [`event/HLD.md`](../event/HLD.md)).
  - JSON walks integrations (see [`jsonwalks/std/HLD.md`](../jsonwalks/std/HLD.md)) can supply the underlying event data structures.
  - Calendar markup can be embedded in Joomla components/modules alongside leaflet map scripts that register the same walks.

## References

- See [event HLD](../event/HLD.md) for event aggregation
- See [jsonwalks/std HLD](../jsonwalks/std/HLD.md) for calendar view in displays
- `calendar/calendar.php` - Calendar implementation

