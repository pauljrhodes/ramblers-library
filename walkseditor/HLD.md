# walkseditor Module - High Level Design

## Overview

The `walkseditor` module provides the walk editing interface for creating and managing walks. It includes form handling, programme management, and email submission, and wires server-side PHP classes to a bundle of JavaScript helpers that render the editor UI, tabs, and list views.

**Purpose**: Walk editing and submission interface.

**Key Files**:
- `walkseditor/walkseditor.php` - Main asset loader (`RWalkseditor::addScriptsandCss`)
- `walkseditor/programme.php` - Programme management UI
- `walkseditor/submitform.php` - Form submission handler

## Component and Asset Flow

```mermaid
flowchart LR
    Programme[RWalkseditorProgramme<br/>programme.php]
    Submit[RWalkseditorSubmitform<br/>submitform.php]
    Loader[RWalkseditor::addScriptsandCss]
    RLoader[RLoad]
    BaseTabs[media/js/ra.tabs.js<br/>media/vendors/cvList/cvList.js]
    EditorJS[media/walkseditor/js/walkeditor.js<br/>walksEditorHelps.js<br/>viewWalks.js]
    FormJS[media/walkseditor/js/walk.js<br/>inputfields.js<br/>loader.js<br/>maplocation.js<br/>placeEditor.js]
    ProgrammeJS[media/walkseditor/js/form/programme.js]
    SubmitJS[media/walkseditor/js/form/submitwalk.js]
    Styles[media/walkseditor/css/style.css<br/>media/lib_ramblers/css/ra.tabs.css]
    Quill[cdn.jsdelivr.net/npm/quill@2.0.2]

    Programme --> Loader
    Submit --> Loader
    Loader --> RLoader
    RLoader --> BaseTabs
    RLoader --> EditorJS
    RLoader --> FormJS
    RLoader --> ProgrammeJS
    RLoader --> SubmitJS
    RLoader --> Styles
    RLoader --> Quill
```

`RWalkseditorProgramme::display()` and `RWalkseditorSubmitform::display()` both call `RWalkseditor::addScriptsandCss()`, which uses `RLoad` to enqueue the Ramblers tab/pagination foundation (`media/js/ra.tabs.js`, `media/vendors/cvList/cvList.js`) plus the module-specific editor scripts under `media/walkseditor/js`. The Quill CDN assets are also added to support rich-text editing.

## Media Dependencies

### JavaScript Files
- `media/walkseditor/js/walkeditor.js` - Main editor bootstrap and event wiring.
- `media/walkseditor/js/walksEditorHelps.js` - Helper pop-ups and guidance.
- `media/walkseditor/js/viewWalks.js` - Shared view renderer for lists and tabs.
- `media/walkseditor/js/comp/viewAllWalks.js` / `comp/viewAllPlaces.js` - Component renderers for list tabs.
- `media/walkseditor/js/walk.js`, `inputfields.js`, `loader.js` - Form model, validation, and loading overlay helpers.
- `media/walkseditor/js/maplocation.js`, `placeEditor.js` - Location picker and place editing utilities.
- `media/walkseditor/js/form/programme.js` - Programme form logic (loaded by `programme.php`).
- `media/walkseditor/js/form/submitwalk.js` - Submission form logic (loaded by `submitform.php`).
- `media/js/ra.tabs.js` - Shared tab UI; `media/vendors/cvList/cvList.js` - pagination widget used by tabbed lists.
- CDN: `https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js` for rich-text editing.

### CSS Files
- `media/walkseditor/css/style.css` - Editor styling.
- `media/lib_ramblers/css/ra.tabs.css` - Shared tab styling.
- CDN: `https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css` for Quill skin.

## Examples

### Example 1: Load Editor Assets for Programme View

```php
// Inside a Joomla view/controller
$programme = new RWalkseditorProgramme();
$programme->display(); // Calls addScriptsandCss via parent display()
// RLoad enqueues media/js/ra.tabs.js, media/vendors/cvList/cvList.js,
// and media/walkseditor/js/form/programme.js plus the core editor bundle.
```

### Example 2: Submit Form with Rich Text Support

```php
$submit = new RWalkseditorSubmitform();
$submit->display(); // Adds Quill assets and media/walkseditor/js/form/submitwalk.js
// Client-side JS extends the form with field validation, place picker, and tabs.
```

### Example 3: Direct Asset Injection

```php
RWalkseditor::addScriptsandCss(); // Standalone enqueue
// Includes media/walkseditor/js/viewWalks.js to extend the display list,
// media/walkseditor/js/walkeditor.js for page wiring,
// and media/walkseditor/css/style.css for layout.
```

