<?php
use Bitrix\Main\Loader;

Bitrix\Main\Loader::registerAutoloadClasses(
    'vendor.calendar',
    [
        'Vendor\\Calendar\\Base' => 'lib/Base.php',
        'Vendor\\Calendar\\TimeDisplay' => 'lib/TimeDisplay.php',
        'Vendor\\Calendar\\Controller\\TimeController' => 'lib/Controller/timecontroller.php'
    ]
);

\CJSCore::RegisterExt('calendar_styles', [
    'css' => '/bitrix/css/vendor.calendar/styles.css',
]);