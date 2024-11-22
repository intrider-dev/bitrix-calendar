<?php

namespace Vendor\Calendar;

use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Data\Cache;
use DateTime;
use DateTimeZone;

/**
 * Class Base
 * Calendar management class with API integration and utility methods.
 */
class Base
{
    /**
     * @var string Directory for storing calendar files.
     */
    private string $calendarDir = '/local/calendar/';
    /**
     * @var string Full path to the calendar file.
     */
    private string $calendarFile;
    /**
     * @var string API URL for fetching holidays and weekends data.
     */
    private string $isDayOffApiUrl = 'https://isdayoff.ru/api/getdata';
    /**
     * @var string Country code for calendar data.
     */
    private string $countryCode = 'ru';
    /**
     * @var string Log content for debugging purposes.
     */
    private string $log = '';
    /**
     * @var bool Debug mode flag.
     */
    private bool $bDebug;
    /**
     * @var bool Flag to disable caching.
     */
    private bool $noCache;

    /**
     * Base constructor.
     * Initializes the calendar with the current year and debug mode.
     *
     * @param string $type Type of initialization ('normal', 'debug', 'nocache').
     */
    public function __construct(string $type = 'normal')
    {
        $this->bDebug = $type === 'debug';
        $this->noCache = $type === 'nocache'; // Disable cache if 'nocache' is specified

        $currentYear = date('Y');
        $this->calendarFile = $_SERVER['DOCUMENT_ROOT'] . $this->calendarDir . "calendar_{$currentYear}.json";
        $this->putToLog("Calendar initialized for year {$currentYear}. Cache enabled: " . ($this->noCache ? "No" : "Yes"));
    }

    /**
     * Initializes the calendar directory and checks for the calendar file.
     *
     * @return void
     */
    public function init(): void
    {
        $this->putToLog("Initializing calendar directory...");
        $this->createDirectory();
        if (!File::isFileExists($this->calendarFile)) {
            $this->putToLog("Calendar file for current year not found.");
        }
    }

    /**
     * Enables or disables debug mode.
     *
     * @param bool $dDebug Debug mode flag.
     * @return void
     */
    public function setDebugMode($dDebug = true): void
    {
        $this->bDebug = true; // Временно включаем для логирования
        $this->putToLog($dDebug ? "Debug mode enabled." : "Debug mode disabled.");
        $this->bDebug = $dDebug; // Устанавливаем новое значение
    }

    /**
     * Creates the calendar directory if it does not exist.
     *
     * @return void
     */
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

    /**
     * Logs a message if debug mode is enabled.
     *
     * @param string $info Message to log.
     * @return void
     */
    public function putToLog(string $info): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1]['function'] ?? 'global';
        $this->log .= $this->bDebug ? "[{$caller}] {$info}\r\n" : '';
    }

    /**
     * Returns the content of the log.
     *
     * @return string Log content.
     */
    public function getLog(): string
    {
        return $this->log;
    }

    /**
     * Generates a calendar file for the specified year.
     *
     * @param string $currentYear Year to generate the calendar for.
     * @return void
     */
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

    /**
     * Updates calendar data for the specified year.
     *
     * @param string $year Year to update.
     * @param array $updatedData New calendar data.
     * @return void
     * @throws \Exception If the calendar file does not exist or JSON encoding fails.
     */
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

    /**
     * Parses the API response and converts it to a structured format.
     *
     * @param string $response API response string.
     * @param string $year Year for the calendar.
     * @return array Parsed calendar data.
     */
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

    /**
     * Maps API day status codes to human-readable values.
     *
     * @param string $dayStatus API day status code.
     * @return string Mapped status.
     */
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

    /**
     * Recreates the calendar file for the current year.
     *
     * @return void
     */
    public function recreateCalendarFile(): void
    {
        $this->putToLog("Recreating calendar file...");
        if (File::isFileExists($this->calendarFile)) {
            File::deleteFile($this->calendarFile);
            $this->putToLog("Existing file deleted: {$this->calendarFile}");
        }
        $this->generateCalendarFile();
    }

    /**
     * Checks if a given date is a holiday.
     *
     * @param string $date Date in 'Y-m-d' format.
     * @return bool True if the date is a holiday, false otherwise.
     */
    public function isHoliday(string $date): bool
    {
        $this->putToLog("Checking if {$date} is a holiday...");
        $calendarData = $this->getCalendarData();
        $isHoliday = isset($calendarData[$date]) && $calendarData[$date] === 'weekend';
        $this->putToLog("Result for {$date}: " . ($isHoliday ? "Yes" : "No"));
        return $isHoliday;
    }

    /**
     * Checks if a given date is a short day.
     *
     * @param string $date Date in 'Y-m-d' format.
     * @return bool True if the date is a short day, false otherwise.
     */
    public function isShortDay(string $date): bool
    {
        $this->putToLog("Checking if {$date} is a short day...");
        $calendarData = $this->getCalendarData();
        $isShortDay = isset($calendarData[$date]) && $calendarData[$date] === 'shortday';
        $this->putToLog("Result for {$date}: " . ($isShortDay ? "Yes" : "No"));
        return $isShortDay;
    }

    /**
     * Checks if a given date is a workday.
     *
     * @param string $date Date in 'Y-m-d' format.
     * @return bool True if the date is a workday, false otherwise.
     */
    public function isWorkday(string $date): bool
    {
        $this->putToLog("Checking if {$date} is a workday...");
        $calendarData = $this->getCalendarData();
        $isWorkday = !isset($calendarData[$date]) || $calendarData[$date] !== 'weekend';
        $this->putToLog("Result for {$date}: " . ($isWorkday ? "Yes" : "No"));
        return $isWorkday;
    }

    /**
     * Retrieves calendar data from the calendar file.
     *
     * @return array Parsed calendar data.
     * @throws \Exception If the calendar file does not exist.
     */
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

    /**
     * Renders a calendar UI for the specified year.
     *
     * @param array $calendarData Calendar data to render.
     * @param string $year Year for the calendar.
     * @return string Rendered HTML.
     */
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

    /**
     * Creates a DateTime object for the current time in the 'Europe/Moscow' timezone.
     *
     * @return DateTime Current time object.
     */
    public function getCurrentTimeObj(): \DateTime
    {
        $this->putToLog("Creating DateTime object...");
        $currentTime = new DateTime("now", new DateTimeZone("Europe/Moscow"));
        $this->putToLog("DateTime object created: {$currentTime->format('Y-m-d H:i:s')}");
        return $currentTime;
    }

    /**
     * Returns the formatted string of the next day based on a given DateTime object.
     *
     * @param \DateTime $curTime Current date.
     * @param string $format Date format.
     * @return string Formatted next day.
     */
    public function getNextFormattedDay(\DateTime $curTime, string $format = 'Y-m-d'): string
    {
        $this->putToLog("Getting next day from: {$curTime->format('Y-m-d')}...");
        $nextDay = $curTime->modify('+1 day')->format($format);
        $this->putToLog("Next day: {$nextDay}");
        return $nextDay;
    }

    /**
     * Returns the formatted string of a given date.
     *
     * @param \DateTime $curTime Date to format.
     * @param string $format Desired date format.
     * @return string Formatted date.
     */
    public function getFormattedDay(\DateTime $curTime, string $format = 'Y-m-d'): string
    {
        $this->putToLog("Formatting date: {$curTime->format('Y-m-d')}...");
        $formattedDate = $curTime->format($format);
        $this->putToLog("Formatted date: {$formattedDate}");
        return $formattedDate;
    }

    /**
     * Finds the next workday starting from a given date.
     *
     * @param self $calendar Calendar object.
     * @param string $startDate Starting date in 'Y-m-d' format.
     * @param string $format Desired date format.
     * @return string Next workday in the specified format.
     */
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

    /**
     * Retrieves calendar data for the specified year, with optional caching.
     *
     * @param string $year Year to retrieve data for.
     * @return array Calendar data.
     * @throws \Exception If the calendar file cannot be generated or loaded.
     */
    public function getCalendarDataForYear(string $year): array
    {
        if ($this->noCache) {
            $this->putToLog("Cache bypassed for year {$year}.");
            return $this->loadCalendarData($year);
        }

        $cacheTtl = 3600; // Cache TTL in seconds (1 hour)
        $cacheDir = '/vendor/calendar/';
        $cacheId = 'calendar_data_' . $year;

        $cache = Cache::createInstance();

        if ($cache->initCache($cacheTtl, $cacheId, $cacheDir)) {
            $this->putToLog("Cache hit for year {$year}.");
            return $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $this->putToLog("Cache miss for year {$year}. Loading data...");
            $data = $this->loadCalendarData($year);

            $cache->endDataCache($data);
            $this->putToLog("Data for year {$year} cached.");
            return $data;
        }

        $this->putToLog("Cache failed for year {$year}.");
        return [];
    }

    /**
     * Clears the cache for the specified year or all years.
     *
     * @param string|null $year Year to clear from cache, or null to clear all.
     * @return void
     */
    public function clearCache(?string $year = null): void
    {
        $cacheDir = '/vendor/calendar/';

        if ($year !== null) {
            $cacheId = 'calendar_data_' . $year;
            $cache = Cache::createInstance();
            $cache->clean($cacheId, $cacheDir);
            $this->putToLog("Cache cleared for year {$year}.");
        } else {
            $cache = Cache::createInstance();
            $cache->cleanDir($cacheDir);
            $this->putToLog("Cache cleared for all years.");
        }
    }

    /**
     * Loads calendar data from the file, generating it if necessary.
     *
     * @param string $year Year to load data for.
     * @return array Calendar data.
     * @throws \Exception If the calendar file cannot be generated or loaded.
     */
    private function loadCalendarData(string $year): array
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

        $this->putToLog("Loading calendar file for year {$year} from {$filePath}.");
        $json = File::getFileContents($filePath);
        return json_decode($json, true) ?? [];
    }

    /**
     * Determines if a date is a workday based on calendar data.
     *
     * @param string $date Date in 'Y-m-d' format.
     * @param array $calendarData Calendar data array.
     * @return bool True if the date is a workday, false otherwise.
     */
    private function isWorkdayFromData(string $date, array $calendarData): bool
    {
        $this->putToLog("Checking if {$date} is a workday...");
        ([$date, $calendarData]);
        return !array_key_exists($date, $calendarData) || $calendarData[$date] !== 'weekend';
    }

    /**
     * Formats a date to "day number and full month name" format.
     *
     * @param string $date Date to format.
     * @return string Formatted date.
     */
    public function dayNFullMonthNameFormat(string $date): string
    {
        $this->putToLog("Formatting date: {$date}...");
        return FormatDate("j F", strtotime($date));
    }
}
