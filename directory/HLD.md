# directory Module - High Level Design

## Overview

The `directory` module renders simple directory listings, filtering by allowed extensions and decorating entries with optional descriptions.

**Purpose**: Directory listing utilities for front-end rendering within Joomla-powered pages.

**Key File**: `directory/list.php`

## Component Diagram

```
+----------------+    reads          +----------------------+
| RDirectoryList |------------------>| Filesystem folder    |
|                |    uses           +----------------------+
|                |----> JFactory/JText (messages)
|                |----> JURI::base() (link roots)
+----------------+
```

## Key Classes / Functions

### `RDirectoryList`
- **State**: allowed file extensions (`$fileTypes`) and collected filenames.
- **Responsibilities**: verify folder existence, enumerate files, filter by extension, load optional `.text`/`.txt` description files, and emit HTML `<ul>` listings.
- **Helper**: `endsWith($haystack, $needle)` to match file extensions.

## Public Interfaces & Usage

- `__construct(array $fileTypes)`: set the extensions to include (e.g., `['.pdf', '.docx']`).
- `listItems(string $folder, int $sort = self::ASC): void`: echo an HTML unordered list of links sorted ascending or descending. Errors are routed through Joomla messaging (`enqueueMessage`).

**Example**
```php
$listing = new RDirectoryList(['.pdf', '.docx']);
$listing->listItems('media/downloads', RDirectoryList::DESC);
```
This produces a `<ul>` of download links rooted at `JURI::base() . 'media/downloads'`, with optional descriptions from `filename.text` or `filename.txt`.

## Data Flow & Integration Points

- **Input**: target folder path and sort order from calling code.
- **Processing**:
  - Validate folder existence; surface errors via `JFactory::getApplication()->enqueueMessage`.
  - Enumerate directory contents (`opendir`/`readdir`), filter by `fileTypes`, and load matching description sidecar files.
  - Generate link URLs using `JURI::base()` so links honor the site base path.
- **Output**: HTML written directly (echo) for Joomla templates or module outputs.
- **Integration**: typically embedded in Joomla components/modules; depends on Joomla globals (`JFactory`, `JText`, `JURI`) and the filesystem. Can be paired with media or document folders where sidecar description files are maintained.

## References

- `directory/list.php` - Directory listing implementation

