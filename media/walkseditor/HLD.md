# media/walkseditor Module - High Level Design

## Overview

The `media/walkseditor` module provides a complete walk editing interface with both PHP backend (email submission) and JavaScript frontend (form editing, validation, place management). It enables users to create and submit walk data via a web-based form that sends structured walk data via email.

**Purpose**: Walk editing and submission system with email delivery.

**Key Responsibilities**:
- Provide interactive walk editing form
- Validate walk data
- Manage place/location data
- Handle form submission via AJAX
- Send walk data via email using PHPMailer
- Support programme management

## Component Architecture

```mermaid
flowchart TB
    subgraph Frontend["JavaScript Frontend"]
        WalkEditor["ra.walkseditor.walkeditor<br/>Main editor"]
        InputFields["ra.walkseditor.inputFields<br/>Form fields"]
        PlaceEditor["ra.walkseditor.placeEditor<br/>Place editing"]
        MapLocation["ra.walkseditor.maplocation<br/>Map integration"]
        ViewWalks["ra.walkseditor.viewWalks<br/>Walk viewing"]
        FormSubmit["form/submitwalk.js<br/>Form submission"]
        Programme["form/programme.js<br/>Programme management"]
    end

    subgraph Backend["PHP Backend"]
        SendEmail["sendemail.php<br/>Email handler"]
        PHPMailer["PHPMailer.php<br/>Email library"]
        SMTP["SMTP.php<br/>SMTP transport"]
        Exception["Exception.php<br/>Error handling"]
    end

    subgraph Integration["Integration"]
        JoomlaConfig["Joomla Configuration<br/>Email settings"]
        LeafletMap["Leaflet Map<br/>Location selection"]
    end

    WalkEditor --> InputFields
    WalkEditor --> PlaceEditor
    WalkEditor --> MapLocation
    FormSubmit --> SendEmail
    SendEmail --> PHPMailer
    PHPMailer --> SMTP
    PHPMailer --> Exception
    SendEmail --> JoomlaConfig
    PlaceEditor --> LeafletMap
```

## Public Interface

### JavaScript Components

#### ra.walkseditor.walkeditor

**Main walk editor interface.**

```javascript
ra.walkseditor.walkeditor()
  - load(editDiv, walk, formmode) // Load editor form
  - addBasics(div) // Add basic details section
  - addWalk() // Add walk item
  - addMeetingType(div) // Add meeting section
  - addStartType(div) // Add start section
  - addFinish(div) // Add finish section
  - addContact(div) // Add contact section
  - addFacilities(div) // Add facilities section
```

**Form Sections**:
- Basic Details (title, date, description)
- Walk (distance, grade, shape, pace)
- Meeting (location, time)
- Start (location, time)
- Finish (location, time)
- Contact (name, email, phone)
- Facilities (accessibility, transport)
- Editor's Notes

#### ra.walkseditor.inputFields

**Form field utilities.**

```javascript
ra.walkseditor.inputFields()
  - addHeader(div, tag, title, help) // Add section header
  - addInputField() // Add input field
  - addSelectField() // Add select field
  - addTextareaField() // Add textarea field
```

#### ra.walkseditor.placeEditor

**Place/location editing.**

```javascript
ra.walkseditor.placeEditor()
  - editPlace(place) // Edit place
  - savePlace() // Save place
  - deletePlace() // Delete place
```

#### ra.walkseditor.maplocation

**Map-based location selection.**

- Integrates with Leaflet maps
- Location picker
- Coordinate display

#### form/submitwalk.js

**Form submission handler.**

```javascript
// Handles form submission
  - validateForm() // Validate walk data
  - submitWalk() // Submit via AJAX
  - handleResponse() // Handle server response
```

#### form/programme.js

**Programme management.**

- Programme view/editing
- Walk list management

### PHP Components

#### sendemail.php

**Email submission endpoint.**

```php
// Receives POST request with walk data
// Validates JSON
// Sends email via PHPMailer
// Returns JSON response
```

**Request Format**:
```json
{
  "walk": {...},
  "walkbody": "<html>...</html>",
  "email": {"name": "...", "email": "...", "message": "..."},
  "fromSite": "https://...",
  "subject": "Walk Submission",
  "coords": {"email@example.com": "Name"}
}
```

**Response Format**:
```json
{
  "error": false,
  "message": "MESSAGE HAS BEEN SENT",
  "sent": true
}
```

#### PHPMailer.php

**Email library class.**

- Full PHPMailer implementation
- HTML email support
- Attachment support
- SMTP and mail() transport

#### SMTP.php

**SMTP transport class.**

- SMTP connection handling
- Authentication support
- TLS/SSL support

#### Exception.php

**Exception handling.**

- PHPMailer exception classes
- Error reporting

## Data Flow

### Walk Submission Flow

```mermaid
sequenceDiagram
    autonumber
    participant User as User
    participant Editor as Walk Editor JS
    participant Validate as Form Validation
    participant AJAX as AJAX Request
    participant SendEmail as "sendemail.php"
    participant PHPMailer as PHPMailer
    participant SMTP as SMTP Server
    participant Coordinator as Coordinator Email

    User->>Editor: Fill walk form
    User->>Editor: Click submit
    Editor->>Validate: "validateForm()"
    Validate-->>Editor: "valid/invalid"
    alt Valid
        Editor->>AJAX: "POST walk data (JSON)"
        AJAX->>SendEmail: Receive POST
        SendEmail->>SendEmail: "json_decode(data)"
        SendEmail->>SendEmail: "checkDecode()"
        SendEmail->>PHPMailer: "new PHPMailer()"
        SendEmail->>PHPMailer: "Configure (SMTP/mail)"
        SendEmail->>PHPMailer: "setFrom(), addAddress()"
        SendEmail->>PHPMailer: "AddStringAttachment(walk JSON)"
        SendEmail->>PHPMailer: "msgHTML(body)"
        PHPMailer->>SMTP: Send email
        SMTP->>Coordinator: Deliver email
        PHPMailer-->>SendEmail: "Success/failure"
        SendEmail->>SendEmail: Create response object
        SendEmail-->>AJAX: JSON response
        AJAX->>Editor: "handleResponse()"
        Editor->>User: "Show success/error message"
    else Invalid
        Editor->>User: Show validation errors
    end
```

### Place Editing Flow

```mermaid
sequenceDiagram
    participant User as User
    participant PlaceEditor as Place Editor
    participant Map as Leaflet Map
    participant Storage as Local Storage

    User->>PlaceEditor: Edit place
    PlaceEditor->>Map: Show location picker
    User->>Map: Select location
    Map-->>PlaceEditor: Coordinates
    PlaceEditor->>PlaceEditor: Save place data
    PlaceEditor->>Storage: "Store place (localStorage/API)"
```

## Integration Points

### Used By
- **RWalkseditorProgramme**, **RWalkseditorSubmitform**: Server-side entry points that render the editor, enqueue assets, and emit the client bootstrap → [walkseditor HLD](../../walkseditor/HLD.md#integration-points).

### Uses
- **RLoad**: Adds `/media/walkseditor/js/*`, `/media/js/ra.tabs.js`, and `/media/vendors/cvList/cvList.js` with cache-busting → [load HLD](../../load/HLD.md#integration-points).
- **PHPMailer** (via bundled `PHPMailer.php`, `SMTP.php`, `Exception.php`): Email delivery for submissions → [walkseditor HLD](../../walkseditor/HLD.md#integration-points).
- **Leaflet controls**: Optional map picker support via `/media/leaflet` assets → [media/leaflet HLD](../leaflet/HLD.md#integration-points).

### Data Sources
- **Form payload**: User-supplied walk JSON plus metadata (subject, origin site, coordinator list) → [walkseditor HLD](../../walkseditor/HLD.md#data-flow).

### Display Layer
- **Client editor**: `ra.walkseditor.walkeditor` renders the tabbed editor UI and status messaging → [media/js HLD](../js/HLD.md#integration-points).

### Joomla Integration
- **Document pipeline**: `RLoad` injects scripts/styles into `JDocument`; `sendemail.php` relies on Joomla configuration for SMTP settings.

### Vendor Library Integration
- **cvList**: Pagination for walk/place lists in the editor UI.
- **Leaflet**: Map picker and clustering when maplocation is enabled.

### Media Asset Relationships (Server → Client)

```mermaid
flowchart LR
    PHP["RWalkseditorProgramme / Submitform"]
    Loader["RLoad::addScript"]
    BaseJS["/media/js<br/>ra.tabs.js"]
    Vendors["/media/vendors/cvList/cvList.js"]
    EditorJS["/media/walkseditor/js<br/>walkeditor.js, walk.js, form/*.js"]
    Bootstrap["Bootstrap script → ra.walkseditor.walkeditor"]

    PHP --> Loader
    Loader --> BaseJS
    Loader --> Vendors
    Loader --> EditorJS
    PHP --> Bootstrap
```

The PHP helpers queue the shared tab library and cvList vendor script alongside the editor bundle; the emitted bootstrap initializes `ra.walkseditor.walkeditor`, which then binds AJAX submissions to `sendemail.php`.

### External Services
- **SMTP server** via Joomla config for email dispatch to coordinator recipients.

## Media Dependencies & Key Features

### JavaScript Files (13 files in `js/` subfolder)

#### Core Editor Files
- `walkeditor.js` - Main editor (779+ lines); **Key features**: bootstraps the UI, wires events, orchestrates AJAX.
- `walk.js` - Walk object handling; **Key features**: domain model, validation helpers.
- `viewWalks.js` - Walk viewing interface; **Key features**: renders tabbed lists and details.
- `inputfields.js` - Form field utilities; **Key features**: validation and standardised inputs.
- `loader.js` - Asset loading; **Key features**: loading overlays and progress feedback.

#### Place Management
- `placeEditor.js` - Place editing; **Key features**: CRUD helpers and map sync.
- `maplocation.js` - Map location selection; **Key features**: Leaflet-based picker and coordinate handling.
- `comp/places.js` - Places component; **Key features**: list rendering and pagination hooks.
- `comp/viewAllPlaces.js` - Places list view; **Key features**: table rendering and filters.

#### Form Handling
- `form/submitwalk.js` - Walk submission; **Key features**: validation, payload assembly, and postback.
- `form/programme.js` - Programme management; **Key features**: programme tab wiring and list refresh.

#### Component Views
- `comp/viewAllWalks.js` - Walks list view; **Key features**: list rendering and pagination with cvList.

#### Utilities
- `walksEditorHelps.js` - Help system; **Key features**: contextual tips and modal guidance.

### PHP Files

#### Email Handling
- `sendemail.php` - Email submission handler (105 lines); **Key features**: PHPMailer orchestration and JSON response shaping.
- `PHPMailer.php` - PHPMailer library (5253+ lines)
- `SMTP.php` - SMTP transport
- `Exception.php` - Exception classes

## Examples

### Example 1: Form Submission

```javascript
// JavaScript form submission
var walkData = {
    walk: {...walkObject...},
    walkbody: "<html>...</html>",
    email: {name: "John", email: "john@example.com", message: "..."},
    fromSite: window.location.href,
    subject: "Walk Submission",
    coords: {"coord@example.com": "Coordinator Name"}
};

fetch('media/lib_ramblers/walkseditor/sendemail.php', {
    method: 'POST',
    body: formData // FormData with walk file
})
.then(response => response.json())
.then(data => {
    if (data.sent) {
        // Show success message
    } else {
        // Show error message
    }
});
```

### Example 2: Editor Initialization

```javascript
var editor = new ra.walkseditor.walkeditor();
editor.load(editDiv, walkObject, false);
```

## Performance Observations

### Form Rendering
- **Dynamic Form**: Form generated client-side (fast)
- **Large Forms**: May be slow for complex walks with many items
- **Map Integration**: Leaflet map adds overhead

### Email Sending
- **SMTP Connection**: Connection time adds latency
- **Large Attachments**: Walk JSON files typically small (<100KB)
- **Multiple Recipients**: Sends to all coordinators

### Optimization Opportunities
1. **Form Caching**: Cache form structure
2. **Lazy Map Loading**: Load map only when needed
3. **Progressive Validation**: Validate fields as user types

## Error Handling

### JavaScript Errors
- **Validation Errors**: Highlight invalid fields inline and block submission.
- **Submission Errors**: Error modal shown when AJAX returns failure.
- **AJAX Failures**: Network issues handled with user-facing error messages.

### PHP Errors
- **JSON Decode Errors**: Guarded by `checkDecode()`; replies with error JSON.
- **Email Send Failures**: PHPMailer errors are caught and surfaced in the JSON response.
- **Missing Configuration**: Falls back to defaults and reports issues.

### User Feedback
- **Success**: Success banner/modal on send completion.
- **Errors**: Detailed error messages pushed to the UI.
- **Validation**: Inline prompts for required/invalid fields.

## References

### Related HLD Documents
- [walkseditor HLD](../../walkseditor/HLD.md) - PHP walkseditor integration
- [media/js HLD](../js/HLD.md) - Core JavaScript library
- [media/leaflet HLD](../leaflet/HLD.md) - Leaflet map integration

### Key Source Files
- `media/walkseditor/js/walkeditor.js` - Main editor (779+ lines)
- `media/walkseditor/js/walk.js` - Walk object
- `media/walkseditor/js/placeEditor.js` - Place editor
- `media/walkseditor/js/form/submitwalk.js` - Form submission
- `media/walkseditor/sendemail.php` - Email handler (105 lines)
- `media/walkseditor/PHPMailer.php` - Email library (5253+ lines)

### Related Media Files
- `media/walkseditor/css/` - Editor stylesheets (5 CSS files)
- `media/walkseditor/css/*.png` - Editor icons (6 PNG files)
