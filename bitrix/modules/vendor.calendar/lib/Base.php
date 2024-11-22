<?php

namespace Vendor\Calendar;

use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\Loc;
use DateTime;
use DateTimeZone;

class Base
{
    private string $calendarDir = '/local/calendar/';
    private string $calendarFile;
    private string $isDayOffApiUrl = 'https://isdayoff.ru/api/getdata';
    private string $countryCode = 'ru';
    private string $log = '';
    private bool $bDebug;

    public function __construct($type = 'normal')
    {
        $this->bDebug = $type == 'debug';
        $currentYear = date('Y');
        $this->calendarFile = $_SERVER['DOCUMENT_ROOT'] . $this->calendarDir . "calendar_{$currentYear}.json";
        $this->putToLog("Calendar initialized for year {$currentYear}.");
    }

    public function init(): void
    {
        $this->putToLog("Initializing calendar directory...");
        $this->createDirectory();
        if (!File::isFileExists($this->calendarFile)) {
            $this->putToLog("Calendar file for current year not found.");
        }
    }

    public function setDebugMode($dDebug = true): void
    {
        $this->bDebug = true; // Временно включаем для логирования
        $this->putToLog($dDebug ? "Debug mode enabled." : "Debug mode disabled.");
        $this->bDebug = $dDebug; // Устанавливаем новое значение
    }

    private function createDirectory(): void
    {
        $dir = new Directory($_SERVER['DOCUMENT_ROOT'] . $this->calendarDir);
        if (!$dir->isExists()) {
            $dir->create();
            $this->putToLog("Directory created at {$this->calendarDir}.");
        } else {
            $this->putToLog("Directory already exists at {$this->calendarDir}.");
        }
    }

    public function putToLog(string $info): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1]['function'] ?? 'global';
        $this->log .= $this->bDebug ? "[{$caller}] {$info}\r\n" : '';
    }

    public function getLog(): string
    {
        return $this->log;
    }

    public function generateCalendarFile(string $currentYear = ''): void
    {
        $currentYear = $currentYear ?: date('Y');
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $this->calendarDir . "calendar_{$currentYear}.json";

        $this->createDirectory();
        $apiUrl = "{$this->isDayOffApiUrl}?year={$currentYear}&cc={$this->countryCode}";
        $this->putToLog("Fetching data for year {$currentYear} from API: {$apiUrl}");

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->putToLog("HTTP Code: {$httpCode}");
        $this->putToLog("Response: {$response}");

        if ($httpCode === 200 && $response) {
            $calendarData = $this->parseApiResponse($response, $currentYear);
            File::putFileContents($filePath, json_encode($calendarData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->putToLog("Calendar file created for {$currentYear} at {$filePath}");
        } else {
            $this->putToLog("Failed to fetch calendar data from API for year {$currentYear}.");
        }
    }

    public function updateCalendarData(string $year, array $updatedData): void
    {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $this->calendarDir . "calendar_{$year}.json";
        $this->putToLog("Updating calendar data for year {$year}...");

        if (!File::isFileExists($filePath)) {
            $this->putToLog("Calendar file for year {$year} does not exist.");
            throw new \Exception("Calendar file for year {$year} does not exist.");
        }

        $jsonData = json_encode($updatedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($jsonData === false) {
            $this->putToLog("Failed to encode updated data to JSON.");
            throw new \Exception('Failed to encode updated data to JSON.');
        }

        File::putFileContents($filePath, $jsonData);
        $this->putToLog("Calendar file for year {$year} updated successfully.");
    }

    private function parseApiResponse(string $response, string $year = ''): array
    {
        $currentYear = $year ?: date('Y');
        $days = str_split(trim($response));
        $calendarData = [];

        foreach ($days as $dayIndex => $dayStatus) {
            $date = date('Y-m-d', strtotime("{$currentYear}-01-01 +{$dayIndex} days"));
            $calendarData[$date] = $this->mapDayStatus($dayStatus);
        }

        $this->putToLog("Parsed API response for year {$currentYear}.");
        return $calendarData;
    }

    private function mapDayStatus(string $dayStatus): string
    {
        $this->putToLog("Mapping day status {$dayStatus}...");
        switch ($dayStatus) {
            case '0': return 'workday';
            case '1': return 'weekend';
            case '2': return 'shortday';
            case '4': return 'covid_workday';
            default: return 'unknown';
        }
    }

    public function recreateCalendarFile(): void
    {
        $this->putToLog("Recreating calendar file...");
        if (File::isFileExists($this->calendarFile)) {
            File::deleteFile($this->calendarFile);
            $this->putToLog("Existing file deleted: {$this->calendarFile}");
        }
        $this->generateCalendarFile();
    }

    public function isHoliday(string $date): bool
    {
        $this->putToLog("Checking if {$date} is a holiday...");
        $calendarData = $this->getCalendarData();
        $isHoliday = isset($calendarData[$date]) && $calendarData[$date] === 'weekend';
        $this->putToLog("Result for {$date}: " . ($isHoliday ? "Yes" : "No"));
        return $isHoliday;
    }

    public function isShortDay(string $date): bool
    {
        $this->putToLog("Checking if {$date} is a short day...");
        $calendarData = $this->getCalendarData();
        $isShortDay = isset($calendarData[$date]) && $calendarData[$date] === 'shortday';
        $this->putToLog("Result for {$date}: " . ($isShortDay ? "Yes" : "No"));
        return $isShortDay;
    }

    public function isWorkday(string $date): bool
    {
        $this->putToLog("Checking if {$date} is a workday...");
        $calendarData = $this->getCalendarData();
        $isWorkday = !isset($calendarData[$date]) || $calendarData[$date] !== 'weekend';
        $this->putToLog("Result for {$date}: " . ($isWorkday ? "Yes" : "No"));
        return $isWorkday;
    }

    public function getCalendarData(): array
    {
        $this->putToLog("Retrieving calendar data...");
        if (!File::isFileExists($this->calendarFile)) {
            $this->putToLog("Calendar file not found: {$this->calendarFile}");
            throw new \Exception("Calendar file not found.");
        }

        $json = File::getFileContents($this->calendarFile);
        $this->putToLog("Data retrieved successfully.");
        return json_decode($json, true) ?? [];
    }

    public function renderCalendarUI(array $calendarData, string $year): string
    {
        global $APPLICATION;
        $bAdminPage = strpos($APPLICATION->GetCurDir(), 'bitrix/admin');
        $months = range(1, 12);

        $this->putToLog("Rendering calendar UI for year {$year}...");
        // Добавляем форму с кнопкой для перезагрузки
        if ($bAdminPage) {
            $html = '<form method="post" style="margin-bottom: 20px;">';
            $html .= '<button type="submit" name="reload" class="adm-btn-save" style="margin-right: 10px;">' . Loc::getMessage('RECREATE_FROM_API') . '</button>';
            $html .= '<input type="hidden" name="year" value="' . htmlspecialchars($year) . '">';
            $html .= '</form>';
        }

        $html .= '<form method="post">';
        $html .= bitrix_sessid_post();
        $html .= '<div class="calendar-container" style="font-family: Arial, sans-serif;">';

        foreach ($months as $month) {
            $html .= '<div class="month" style="margin: 20px; vertical-align: top;">';
            $html .= "<h3 style='text-align: center;'>" . Loc::getMessage('MONTH_'.$month) . "</h3>";
            $html .= '<table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; text-align: center;">';
            $html .= '<tr><th>Пн</th><th>Вт</th><th>Ср</th><th>Чт</th><th>Пт</th><th class="weekend">Сб</th><th class="weekend">Вс</th></tr>';

            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $firstDayOfWeek = (new \DateTime("$year-$month-01"))->format('N');

            $html .= '<tr>';
            $dayOfWeek = 1;

            // Добавление пустых ячеек для дней до начала месяца
            while ($dayOfWeek < $firstDayOfWeek) {
                $html .= '<td></td>';
                $dayOfWeek++;
            }

            // Цикл по дням месяца
            for ($day = 1; $day <= $daysInMonth; $day++, $dayOfWeek++) {
                $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $class = ''; // По умолчанию нет класса
                $checked = ''; // По умолчанию чекбокс не отмечен

                // Если день выходной или отмечен как праздник
                if (isset($calendarData[$currentDate]) && $calendarData[$currentDate] === 'weekend') {
                    $class = 'weekend';
                    $checked = 'checked';
                }

                // Генерация ячейки для дня
                $html .= "<td class='{$class}'>";
                $html .= "{$day}<br>";
                if ($bAdminPage) {
                    $html .= "<input type='checkbox' name='dates[{$currentDate}]' value='weekend' {$checked}>";
                }
                $html .= "</td>";

                // Переход на новую строку после воскресенья
                if ($dayOfWeek == 7 && $day < $daysInMonth) {
                    $html .= '</tr><tr>';
                    $dayOfWeek = 0;
                }
            }

            // Добавление пустых ячеек после окончания месяца
            while ($dayOfWeek <= 7 && $dayOfWeek != 1) {
                $html .= '<td></td>';
                $dayOfWeek++;
            }

            $html .= '</tr></table></div>';
        }

        $html .= '</div>';
        if ($bAdminPage) {
            $html .= '<button type="submit" name="save_calendar" class="adm-btn-save" style="margin-top: 20px;">'.Loc::getMessage('SAVE_CHANGES').'</button>';
        }
        $html .= '</form>';

        if ($bAdminPage) {
            $html .= <<<HTML
                <script>
                    document.querySelectorAll('.calendar-container td').forEach(td => {
                        const input = td.querySelector('input');
                        if (input && !input.checked) {
                            td.style.backgroundColor = '#f9f9f961';
                        }
                    
                        // Добавляем обработчик изменений
                        input?.addEventListener('change', () => {
                            td.style.backgroundColor = input.checked ? '#fdd' : '#f9f9f961';
                        });
                    });
                </script>
            HTML;
        }
        $this->putToLog("UI rendered successfully.");

        return $html;
    }

    public function getCurrentTimeObj(): \DateTime
    {
        $this->putToLog("Creating DateTime object...");
        $currentTime = new DateTime("now", new DateTimeZone("Europe/Moscow"));
        $this->putToLog("DateTime object created: {$currentTime->format('Y-m-d H:i:s')}");
        return $currentTime;
    }

    public function getNextFormattedDay(\DateTime $curTime, string $format = 'Y-m-d'): string
    {
        $this->putToLog("Getting next day from: {$curTime->format('Y-m-d')}...");
        $nextDay = $curTime->modify('+1 day')->format($format);
        $this->putToLog("Next day: {$nextDay}");
        return $nextDay;
    }

    public function getFormattedDay(\DateTime $curTime, string $format = 'Y-m-d'): string
    {
        $this->putToLog("Formatting date: {$curTime->format('Y-m-d')}...");
        $formattedDate = $curTime->format($format);
        $this->putToLog("Formatted date: {$formattedDate}");
        return $formattedDate;
    }

    public function getWorkday(self $calendar, string $startDate, string $format = 'Y-m-d'): string
    {
        $this->putToLog("Calculating next workday from {$startDate}...");
        $currentDate = new \DateTime($startDate);
        $year = $currentDate->format('Y'); // Текущий год

        // Загружаем календарь для текущего года
        $calendarData = $this->getCalendarDataForYear($year);

        while (true) {
            $dateStr = $currentDate->format($format);

            // Проверяем, является ли текущая дата рабочей
            if ($this->isWorkdayFromData($dateStr, $calendarData)) {
                $this->putToLog("Found workday: {$dateStr}");
                return $dateStr;
            }

            // Переходим к следующему дню
            $currentDate->modify('+1 day');

            // Проверяем, изменился ли год
            $newYear = $currentDate->format('Y');
            if ($newYear !== $year) {
                $this->putToLog("Year changed from {$year} to {$newYear}. Reloading calendar data...");
                $calendarData = $this->getCalendarDataForYear($newYear); // Загружаем календарь нового года
                $year = $newYear; // Обновляем текущий год
            }
        }
    }

    public function getCalendarDataForYear(string $year): array
    {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $this->calendarDir . "calendar_{$year}.json";
        $this->createDirectory();

        if (!File::isFileExists($filePath)) {
            $this->putToLog("File for {$year} not found. Generating...");
            $this->generateCalendarFile($year);
        }

        if (!File::isFileExists($filePath)) {
            $this->putToLog("Failed to generate calendar file for year {$year}.");
            throw new \Exception("Failed to generate calendar file for year {$year}.");
        }

        $this->putToLog("Path to Calendar file: {$filePath}.");
        $json = File::getFileContents($filePath);
        return json_decode($json, true) ?? [];
    }

    private function isWorkdayFromData(string $date, array $calendarData): bool
    {
        $this->putToLog("Checking if {$date} is a workday...");
        ([$date, $calendarData]);
        return !array_key_exists($date, $calendarData) || $calendarData[$date] !== 'weekend';
    }

    public function dayNFullMonthNameFormat(string $date): string
    {
        $this->putToLog("Formatting date: {$date}...");
        return FormatDate("j F", strtotime($date));
    }
}
