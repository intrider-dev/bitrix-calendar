<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin.php';
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;

Loader::includeModule('vendor.calendar');


$calendar = new Vendor\Calendar\Base();
$timeDisplay = new Vendor\Calendar\TimeDisplay();

$year = date('Y');

if (isset($_POST['reload'])) {
    $calendar->recreateCalendarFile(); // Перезагрузка из API
}

if (isset($_POST['save_calendar'])) {
    $updatedData = [];
    foreach ($_POST['dates'] as $date => $status) {
        $updatedData[$date] = $status;
    }
    $calendar->updateCalendarData($year, $updatedData);
}

// Вывод динамического блока с обновлением времени
echo $timeDisplay->getBlockHeader();
echo $timeDisplay->renderDynamicTimeBlock();

// Генерация UI календаря
$calendar->init();
$calendarData = $calendar->getCalendarData($year);
$visualCalendar = $calendar->renderCalendarUI($calendarData, $year);

echo $visualCalendar;

\CJSCore::Init(['calendar_styles']);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php'; ?>
