<?php
// Include the Bitrix prolog to initialize the environment before executing the script
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;

// Define the path to the "edit.php" page in the "vendor.calendar" module
$filePath = Loader::getLocal('modules/vendor.calendar') . '/pages/edit.php';

// Check if the file exists before including it
if (file_exists($filePath)) {
    require_once($filePath); // Include the "edit.php" file if it exists
}
