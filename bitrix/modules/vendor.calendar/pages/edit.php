<?php
// Include the Bitrix administrative prolog to initialize the admin panel environment
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin.php';
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); // Ensure script is executed within Bitrix

use Bitrix\Main\Loader;

// Load the "vendor.calendar" module
Loader::includeModule('vendor.calendar');

// Instantiate calendar and time display classes
$calendar = new Vendor\Calendar\Base();
$timeDisplay = new Vendor\Calendar\TimeDisplay();

// Get the current year
$year = date('Y');

// Handle "Reload" button action to recreate the calendar file using the API
if (isset($_POST['reload'])) {
    $calendar->recreateCalendarFile(); // Fetch and regenerate the calendar file
}

// Handle "Save Calendar" button action to update calendar data based on user input
if (isset($_POST['save_calendar'])) {
    $updatedData = [];
    foreach ($_POST['dates'] as $date => $status) {
        $updatedData[$date] = $status; // Collect updated date statuses from the form
    }
    $calendar->updateCalendarData($year, $updatedData); // Save updates to the calendar file
}

// Display a dynamic time block with real-time updates
echo $timeDisplay->getBlockHeader(); // Render the header for the time block
echo $timeDisplay->renderDynamicTimeBlock(); // Render the HTML and JS for the dynamic time block

// Initialize the calendar and retrieve data
$calendar->init(); // Ensure the calendar directory and files are set up
$calendarData = $calendar->getCalendarData($year); // Load calendar data for the current year

// Generate and render the visual calendar UI
$visualCalendar = $calendar->renderCalendarUI($calendarData, $year);
echo $visualCalendar;

// Initialize custom calendar styles using Bitrix core
\CJSCore::Init(['calendar_styles']);

// Include the Bitrix administrative epilog to finalize the admin page
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';