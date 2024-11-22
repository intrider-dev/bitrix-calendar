# Bitrix Calendar Module

### A Bitrix module designed to manage production calendars with API integration, user-friendly methods for working with them, caching and a customizable user interface.

![Functional UI for the production calendar module](https://raw.githubusercontent.com/intrider-dev/bitrix-calendar/refs/heads/assets/src/noname.png)

#### Functional UI for the production calendar module

---

## Features

- Integration with [isdayoff.ru API](https://isdayoff.ru/) to fetch holiday and workday data.
- Caching for improved performance.
- Manage production calendars by year.
- Dynamic time display with UI updates.
- Localization support.

---

## Installation

1. Clone the module into the `/bitrix/modules/` directory on your server:

   ```bash
   git clone https://github.com/intrider-dev/bitrix-calendar.git /path/to/bitrix/modules/vendor.calendar
   ```

2. Navigate to the Bitrix admin panel and install the module via the Modules section.

## Module Structure

```plaintext
vendor.calendar/
├── admin/                               # Administrative pages
│   ├── pages/                           # Menu scripts
│   │   └── vendor_calendar_edit.php     # bitrix/admin page for module
│   └── menu.php
├── css/                                 # Module styles
│   └── styles.css
├── install/                             # Installation/uninstallation scripts
│   ├── index.php                        # Main installation script
│   ├── step.php
│   ├── uninstall.php
│   └── version.php
├── lang/                                # Localization
│   └── ru/
│       ├── admin/                       # Admin panel localization
│       │   └──  menu.php
│       ├── lib/                         # Library file localization
│       │   ├── Base.php
│       │   ├── Calendar.php
│       │   └── TimeDisplay.php
│       ├── install.php
│       └── options.php
├── lib/                                  # Business logic
│   ├── Controller/                       # Bitrix controllers
│   │   └── timecontroller.php            # Time controller API logic
│   ├── Base.php                          # Core calendar management class
│   ├── services.php                      # API service helpers
│   └── TimeDisplay.php                   # Time display logic
├── pages/                                # Admin pages
│   └── edit.php                          # Admin module edit page
├── .description.php
├── .settings.php
├── include.php
└── options.php
```

## Usage
### Include module after installing

```php
use Bitrix\Main\Loader;

// Include the custom calendar module
Loader::includeModule('vendor.calendar');
```

### Initializing the Calendar

```php
// Default mode with caching
$calendar = new \Vendor\Calendar\Base();

// Debug mode with detailed logging
$calendar = new \Vendor\Calendar\Base('debug');

// Disable caching
$calendar = new \Vendor\Calendar\Base('nocache');
```

### Fetching Calendar Data

```php
// Fetch data for the current year
$year = date('Y');
$calendarData = $calendar->getCalendarDataForYear($year);

// Fetch data for a specific year
$calendarData2025 = $calendar->getCalendarDataForYear('2025');
```

### Checking Day Type

```php
$date = '2024-12-25';

// Check if the date is a holiday
$isHoliday = $calendar->isHoliday($date);

// Check if the date is a short day
$isShortDay = $calendar->isShortDay($date);

// Check if the date is a workday
$isWorkday = $calendar->isWorkday($date);
```

### Working with Cache

```php
// Clear cache for a specific year
$calendar->clearCache('2024');

// Clear cache for all data
$calendar->clearCache();

// Turn on nocache mode during the creation of the class instance
$calendar = new \Vendor\Calendar\Base('nocache');
```

### Debugging

```php
// Turn on debug mode during the creation of the class instance
$calendar = new \Vendor\Calendar\Base('debug'); // Put debug mode true

// Turn on debug mode by method
$calendar->setDebugMode(1);

// Turn off debug mode by method
$calendar->setDebugMode(0);

// Display the debug log
echo $calendar->getLog();
```

Example:
```plaintext
[__construct] Calendar initialized for year 2024.
[init] Initializing calendar directory...
[createDirectory] Directory already exists at /local/calendar/.
[getFormattedDay] Formatting date: 2024-12-29...
[getFormattedDay] Formatted date: 2024-12-29
[getNextFormattedDay] Getting next day from: 2024-12-29...
[getNextFormattedDay] Next day: 2024-12-30
[getWorkday] Calculating next workday from 2024-12-29...
[createDirectory] Directory already exists at /local/calendar/.
[getCalendarDataForYear] Path to Calendar file: /var/www/www-root/data/www/vendor-site.ru/local/calendar/calendar_2024.json.
[isWorkdayFromData] Checking if 2024-12-29 is a workday...
[isWorkdayFromData] Checking if 2024-12-30 is a workday...
[isWorkdayFromData] Checking if 2024-12-31 is a workday...
[getWorkday] Year changed from 2024 to 2025. Reloading calendar data...
[createDirectory] Directory already exists at /local/calendar/.
[getCalendarDataForYear] Path to Calendar file: /var/www/www-root/data/www/vendor-site.ru/local/calendar/calendar_2025.json.
[isWorkdayFromData] Checking if 2025-01-01 is a workday...
[isWorkdayFromData] Checking if 2025-01-02 is a workday...
[isWorkdayFromData] Checking if 2025-01-03 is a workday...
[isWorkdayFromData] Checking if 2025-01-04 is a workday...
[isWorkdayFromData] Checking if 2025-01-05 is a workday...
[isWorkdayFromData] Checking if 2025-01-06 is a workday...
[isWorkdayFromData] Checking if 2025-01-07 is a workday...
[isWorkdayFromData] Checking if 2025-01-08 is a workday...
[isWorkdayFromData] Checking if 2025-01-09 is a workday...
[getWorkday] Found workday: 2025-01-09
[getWorkday] Calculating next workday from 2024-12-30...
[createDirectory] Directory already exists at /local/calendar/.
[getCalendarDataForYear] Path to Calendar file: /var/www/www-root/data/www/vendor-site.ru/local/calendar/calendar_2024.json.
[isWorkdayFromData] Checking if 2024-12-30 is a workday...
[isWorkdayFromData] Checking if 2024-12-31 is a workday...
[getWorkday] Year changed from 2024 to 2025. Reloading calendar data...
[createDirectory] Directory already exists at /local/calendar/.
[getCalendarDataForYear] Path to Calendar file: /var/www/www-root/data/www/vendor-site.ru/local/calendar/calendar_2025.json.
[isWorkdayFromData] Checking if 2025-01-01 is a workday...
[isWorkdayFromData] Checking if 2025-01-02 is a workday...
[isWorkdayFromData] Checking if 2025-01-03 is a workday...
[isWorkdayFromData] Checking if 2025-01-04 is a workday...
[isWorkdayFromData] Checking if 2025-01-05 is a workday...
[isWorkdayFromData] Checking if 2025-01-06 is a workday...
[isWorkdayFromData] Checking if 2025-01-07 is a workday...
[isWorkdayFromData] Checking if 2025-01-08 is a workday...
[isWorkdayFromData] Checking if 2025-01-09 is a workday...
[getWorkday] Found workday: 2025-01-09
```

### Rendering the Calendar UI

```php
// Get data for rendering the calendar
$calendarData = $calendar->getCalendarDataForYear('2024');
$html = $calendar->renderCalendarUI($calendarData, '2024');

// Output HTML
echo $html;
```

### Example of usage in project

```php
// Initialize the calendar class
$calendar = new \Vendor\Calendar\Base('debug'); // Put debug mode true
$calendar->init(); // Initialize the calendar directory and files

// Set the current time
$curTime = $calendar->getCurrentTimeObj(); // Current time (e.g., Moscow time)

// Extract the current hour
$currentHour = (int)$curTime->format('G'); // Current hour in 24-hour format

// Get today's formatted date
$day = $calendar->getFormattedDay($curTime); // Example: '2024-12-29'

// Get tomorrow's formatted date
$nextDay = $calendar->getNextFormattedDay($curTime); // Example: '2024-12-30'

// Get the nearest workday starting from today
$workDay = $calendar->getWorkday($calendar, $day); // Example: '2024-12-30' if today is a weekend

// Get the nearest workday starting from tomorrow
$nextWorkDay = $calendar->getWorkday($calendar, $nextDay); // Example: '2024-12-31'

// Output the results
print_r([$day, $nextDay, $workDay, $nextWorkDay]);

// Display the debug log (if debug mode is enabled)
echo $calendar->getLog();
```

### Auxiliary class

```php
// Instantiate time display class
$timeDisplay = new Vendor\Calendar\TimeDisplay();

// Display a dynamic time block with real-time updates
echo $timeDisplay->getBlockHeader(); // Render the header for the time block
echo $timeDisplay->renderDynamicTimeBlock(); // Render the HTML and JS for the dynamic time block
```

## Development

To enhance the module, you can:

- Add additional API data sources.
- Extend functionality for managing workdays and holidays.
- Improve localization support.

For questions or suggestions, please create an issue in the repository.
















