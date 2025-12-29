# leaflet/sql Module - High Level Design

## Overview

The `leaflet/sql` module provides SQL query data source integration for Leaflet maps. It executes SQL queries and displays results as map markers.

**Purpose**: SQL data source for Leaflet maps.

**Key Class**: `RLeafletSqlList` extends `RLeafletMap`

## Public Interface

### RLeafletSqlList

```php
public function __construct($query)
public function setDisplayOptions($displayOptions)
public function display()
```

## Integration Points

- **RLeafletMap**: Base map class → [leaflet HLD](../HLD.md)
- **RLeafletTableColumns**: Column definitions → [leaflet/table HLD](../table/HLD.md)
- **Joomla Database**: Query execution

## References

- [leaflet HLD](../HLD.md) - Main map system
- `leaflet/sql/list.php` - RLeafletSqlList class


