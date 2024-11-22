<?php

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

Loader::includeModule('vendor.calendar');

/**
 * Class vendor_calendar
 * Represents the module "VendorModule - Production Calendar" for Bitrix.
 */
class vendor_calendar extends CModule
{
    /**
     * Module ID constant.
     */
    public const MODULE_ID = 'vendor.calendar';
    /**
     * @var string Module ID.
     */
    public $MODULE_ID = self::MODULE_ID;
    /**
     * @var string Module version.
     */
    public $MODULE_VERSION;
    /**
     * @var string Module version date.
     */
    public $MODULE_VERSION_DATE;
    /**
     * @var string Module name.
     */
    public $MODULE_NAME;
    /**
     * @var string Module description.
     */
    public $MODULE_DESCRIPTION;

    /**
     * Constructor.
     * Initializes module information (version, name, description, etc.).
     */
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

    /**
     * Handles module installation.
     * Registers the module, copies files, and includes the installation page.
     *
     * @return void
     */
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

    /**
     * Handles module uninstallation.
     * Removes module files, unregisters the module, and optionally deletes the database.
     *
     * @return void
     */
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

    /**
     * Copies module files to the appropriate Bitrix directories.
     *
     * @return void
     */
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

    /**
     * Removes module files from Bitrix directories.
     *
     * @return void
     */
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
        /** TODO - Implement database installation for the module. */
    }

    public function uninstallDB(): void
    {
        /** TODO - Implement database uninstallation for the module. */
    }
}
