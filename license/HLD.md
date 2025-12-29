# license Module - High Level Design

## Overview

The `license` module provides license key management for map providers and external services. It retrieves API keys from Joomla configuration.

**Purpose**: License key management for external services.

**Key File**: `license/license.php`

## Public Interface

### RLicense (if exists)

```php
public static function getBingMapKey()
public static function getESRILicenseKey()
public static function getOpenRoutingServiceKey()
public static function getOrdnanceSurveyLicenseKey()
public static function getMapBoxLicenseKey()
public static function getThunderForestLicenseKey()
public static function getW3WLicenseKey()
```

## Integration Points

- **RLeafletMapoptions**: Uses license keys → [leaflet HLD](../leaflet/HLD.md)

## References

- `license/license.php` - License key management
- [leaflet HLD](../leaflet/HLD.md) - License key usage


