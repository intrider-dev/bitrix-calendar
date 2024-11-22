<?php
$arModuleVersion = include __DIR__ . '/install/version.php';

$arModuleDescription = [
    'NAME' => 'Календарь выходных и праздников',
    'DESCRIPTION' => 'Модуль для работы с календарем на базе isdayoff API',
    'PARTNER_NAME' => 'Vendor',
    'PARTNER_URI' => 'https://vendor-site.ru/',
    'VERSION' => $arModuleVersion['VERSION'],
    'VERSION_DATE' => $arModuleVersion['VERSION_DATE'],
];