# walkseditor Module - High Level Design

## Overview

The `walkseditor` module provides walk editing interface for creating and managing walks. It includes form handling, programme management, and email submission.

**Purpose**: Walk editing and submission interface.

**Key Files**:
- `walkseditor/walkseditor.php` - Main editor class
- `walkseditor/programme.php` - Programme management
- `walkseditor/submitform.php` - Form submission handler

## Media Dependencies

### JavaScript Files
- `media/walkseditor/js/*.js` - Editor JavaScript files (14 files)
  - `walksEditorHelps.js`, `walkeditor.js`, `walk.js`, `viewWalks.js`, etc.

### PHP Files
- `media/walkseditor/Exception.php` - Exception handling
- `media/walkseditor/PHPMailer.php` - Email sending
- `media/walkseditor/SMTP.php` - SMTP configuration
- `media/walkseditor/sendemail.php` - Email sending utility

### CSS Files
- `media/walkseditor/css/*.css` - Editor stylesheets (5 files)

## References

- `walkseditor/walkseditor.php` - Main editor class
- `walkseditor/programme.php` - Programme class
- `walkseditor/submitform.php` - Form submission
- `media/walkseditor/` - Editor assets


