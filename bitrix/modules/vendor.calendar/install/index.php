<?php

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

Loader::includeModule('vendor.calendar');

class vendor_calendar extends CModule
{
    public const MODULE_ID = 'vendor.calendar';
    public $MODULE_ID = self::MODULE_ID;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    public function __construct()
    {
        $arModuleVersion = [];
        include_once dirname(__FILE__) . '/version.php';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = 'VendorModule - Производственный календарь';
        $this->MODULE_DESCRIPTION = 'Модуль для работы с производственным календарем РФ';
        $this->PARTNER_NAME = 'Vendor';
        $this->PARTNER_URI = 'https://vendor-site.ru';
    }

    public function DoInstall()
    {
        global $APPLICATION;

        // Регистрируем модуль
        ModuleManager::registerModule($this->MODULE_ID);

        // Копируем файлы
        $this->installFiles();

        // Подключаем страницу установки
        $APPLICATION->IncludeAdminFile(
            "Установка модуля " . $this->MODULE_NAME,
            __DIR__ . "/step.php"
        );
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        // Удаляем таблицы, если требуется
        if (isset($_REQUEST['delete_data']) && $_REQUEST['delete_data'] === 'Y') {
            $this->uninstallDB();
        }

        // Удаляем файлы
        $this->uninstallFiles();

        // Разрегистрация модуля
        ModuleManager::unRegisterModule($this->MODULE_ID);

        // Подключаем страницу удаления
        $APPLICATION->IncludeAdminFile(
            "Удаление модуля " . $this->MODULE_NAME,
            __DIR__ . "/uninstall.php"
        );
    }

    public function installFiles(): void
    {
        $directories = [
            "/../admin/pages" => "/bitrix/admin",
            "/../css" => "/bitrix/css/{$this->MODULE_ID}",
            "/../images" => "/bitrix/images/{$this->MODULE_ID}"
        ];

        foreach ($directories as $source => $destination) {
            $sourcePath = __DIR__ . $source;
            $destinationPath = $_SERVER["DOCUMENT_ROOT"] . $destination;

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            CopyDirFiles($sourcePath, $destinationPath, true, true);
        }
    }

    public function uninstallFiles(): void
    {
        $directories = [
            "/../admin/pages" => "/bitrix/admin",
            "/../css" => "/bitrix/css/{$this->MODULE_ID}",
            "/../images" => "/bitrix/images/{$this->MODULE_ID}"
        ];

        foreach ($directories as $source => $destination) {
            DeleteDirFiles(__DIR__ . $source, $_SERVER["DOCUMENT_ROOT"] . $destination);
        }
    }

    public function installDB(): void
    {

    }

    public function uninstallDB(): void
    {

    }
}
