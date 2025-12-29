# leaflet/json Module - High Level Design

## Overview

The `leaflet/json` module provides JSON data source integration for Leaflet maps. It reads JSON data and displays markers with configurable display options.

**Purpose**: JSON data source for Leaflet maps.

**Key Class**: `RLeafletJsonList` extends `RLeafletMap`

## Public Interface

### RLeafletJsonList

```php
public function __construct($data)
public function setDisplayOptions($displayOptions)
public function display()
```

## Integration Points

- **RLeafletMap**: Base map class → [leaflet HLD](../HLD.md)
- **RLeafletTableColumns**: Column definitions → [leaflet/table HLD](../table/HLD.md)

## References

- [leaflet HLD](../HLD.md) - Main map system
- `leaflet/json/list.php` - RLeafletJsonList class


