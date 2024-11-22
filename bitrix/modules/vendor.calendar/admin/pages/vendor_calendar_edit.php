<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Loader;

$filePath = Loader::getLocal('modules/vendor.calendar') . '/pages/edit.php';
if (file_exists($filePath)) {
    require_once ($filePath);
}