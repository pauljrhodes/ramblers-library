# leaflet/csv Module - High Level Design

## Overview

The `leaflet/csv` module provides CSV data source integration for Leaflet maps. It reads CSV files and displays markers on maps with configurable display options.

**Purpose**: CSV data source for Leaflet maps.

**Key Class**: `RLeafletCsvList` extends `RLeafletMap`

## Public Interface

### RLeafletCsvList

```php
public function __construct($filename)
public function setDisplayOptions($displayOptions)
public function display()
```

## Integration Points

- **RLeafletMap**: Base map class → [leaflet HLD](../HLD.md)
- **RLeafletTableColumns**: Column definitions → [leaflet/table HLD](../table/HLD.md)

## References

- [leaflet HLD](../HLD.md) - Main map system
- `leaflet/csv/list.php` - RLeafletCsvList class


