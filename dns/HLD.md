# dns Module - High Level Design

## Overview

The `dns` module resolves mail-related DNS records to help the library discover hosting-specific mail servers for a given domain.

**Purpose**: DNS record discovery for mail routing.

**Key File**: `dns/DnsRecord.php`

## Component Diagram

```
+-------------+       dns_get_record(DNS_ALL)       +---------------------+
| RDnsRecord  |------------------------------------>| External DNS server |
|             |<----------- array of records -------+---------------------+
+-------------+
```

## Key Classes / Functions

### `RDnsRecord`
- **State**: stores the target domain.
- **Responsibilities**: invoke PHP’s `dns_get_record` to retrieve DNS data and derive the appropriate SSL mail server hostname.
- **Core Method**: `getSSLMailServer()` uses the IPv4 address of `mail.<domain>` to construct the `mail<octet>.extendcp.co.uk` host expected by the hosting provider.

## Public Interfaces & Usage

- `__construct(string $domain)`: capture the domain whose mail server should be resolved.
- `getSSLMailServer(): string`: return the resolved mail host, or an empty string if resolution fails.

**Example**
```php
$resolver = new RDnsRecord('ramblers.org.uk');
$sslHost = $resolver->getSSLMailServer();
if ($sslHost !== '') {
    // Use $sslHost to configure IMAP/SMTP endpoints.
}
```

## Data Flow & Integration Points

- **Input**: a domain string supplied by calling code.
- **Processing**: `dns_get_record` queries external DNS, the first result’s `ip` field is parsed to derive the hosting-specific server name.
- **Output**: hostname string to feed into mail configuration code.
- **Integration**: consumed wherever the application needs to pre-fill or validate mail server settings (e.g., setup helpers or diagnostics). No direct coupling to other modules beyond PHP DNS functions.

## References

- `dns/DnsRecord.php` - DNS record class

