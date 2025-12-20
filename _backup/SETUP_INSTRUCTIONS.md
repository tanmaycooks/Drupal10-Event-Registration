# Quick Setup Instructions

Follow these steps to set up your Event Registration module in Drupal 10:

## Step 1: Create Drupal 10 Site

Open PowerShell and run:

```powershell
# Navigate to your preferred location
cd C:\Users\anand

# Create new Drupal 10 project (this will take a few minutes)
composer create-project drupal/recommended-project drupal10-event-site

# Navigate into the project
cd drupal10-event-site
```

## Step 2: Copy Your Module

```powershell
# Create custom modules directory
New-Item -ItemType Directory -Force -Path web\modules\custom

# Copy your event registration module
Copy-Item -Recurse "C:\Users\anand\Drupal 10 – Event Registration Module" web\modules\custom\event_registration
```

## Step 3: Install Drupal

**Option A - Using Browser:**
```powershell
# Start PHP development server
php -S localhost:8000 -t web
```
Then open http://localhost:8000 in your browser and follow the installation wizard.

**Option B - Using Drush (Command Line):**
```powershell
# Install Drush
composer require drush/drush

# Install Drupal (adjust database credentials)
vendor/bin/drush site:install standard --db-url=mysql://root:@localhost/drupal10 --site-name="Event Registration Site" --account-name=admin --account-pass=admin123 -y
```

## Step 4: Enable Your Module

```powershell
# Enable the module
vendor/bin/drush en event_registration -y

# Clear cache
vendor/bin/drush cr
```

## Step 5: Open in VS Code

```powershell
# Open the Drupal root directory in VS Code
code .
```

**Important:** Open the Drupal root (`drupal10-event-site`), not the module directory. This way VS Code will have access to all Drupal core files and the IDE errors will disappear.

## Verify Installation

1. Visit http://localhost:8000/admin/modules - you should see "Event Registration" enabled
2. Visit http://localhost:8000/admin/config/event-registration - module settings page
3. Visit http://localhost:8000/admin/event-registration/events - create your first event

## Troubleshooting

**If you don't have MySQL installed:**
- Use SQLite instead: `--db-url=sqlite://sites/default/files/.ht.sqlite`

**If Composer is slow:**
- Add `--prefer-dist` flag to speed up downloads

**If you get permission errors:**
- Run PowerShell as Administrator

## Next Steps

Once installed, you can:
- Create events at `/admin/event-registration/events/add`
- View registrations at `/admin/event-registration/registrations`
- Configure settings at `/admin/config/event-registration`
- Test public registration at `/event-registration`
