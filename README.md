# Drupal 10 Event Registration Module

Custom event registration system for Drupal 10/11.

## Features (Planned)
## Features
- Create events with capacity limits.
- Register for events.
- Email confirmations.
- CSV Export of registrations.
- Admin settings.

## Installation
1. Enable the module.
2. Configure permissions.
 into `web/modules/custom/event_registration`.
2. Enable the module via Drush:
   ```bash
   drush en event_registration -y
   ```
## Configuration
1. Navigate to `Configuration > Event Registration > Settings`.
2. Enable email notifications if desired.
3. Manage permissions in `People > Permissions`.

## Usage
- **Create Event**: Add an 'Event' node and set capacity.
- **Register**: Users click "Register" tab on event node.
- **Export**: Admins can export CSV from "Export Registrations" tab.
