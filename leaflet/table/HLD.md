# leaflet/table Module - High Level Design

## Overview

The `leaflet/table` module provides table column definition utilities for Leaflet data source displays. It defines column structures for CSV, JSON, and SQL data sources.

**Purpose**: Table column definitions for data source displays.

**Key Classes**: 
- `RLeafletTableColumns` - Column collection
- `RLeafletTableColumn` - Individual column definition

## Public Interface

### RLeafletTableColumns

```php
public function addColumn($column)
public function getColumns()
```

### RLeafletTableColumn

```php
public function __construct($title, $field)
```

## Integration Points

- **RLeafletCsvList**: CSV column definitions → [leaflet/csv HLD](../csv/HLD.md)
- **RLeafletJsonList**: JSON column definitions → [leaflet/json HLD](../json/HLD.md)
- **RLeafletSqlList**: SQL column definitions → [leaflet/sql HLD](../sql/HLD.md)

## References

- [leaflet HLD](../HLD.md) - Main map system
- `leaflet/table/columns.php` - RLeafletTableColumns class
- `leaflet/table/column.php` - RLeafletTableColumn class


