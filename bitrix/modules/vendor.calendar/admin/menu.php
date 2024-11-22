<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;

// Инициализация EventManager
$event_manager = EventManager::getInstance();
$event_manager->addEventHandler('main', 'OnBuildGlobalMenu', 'onBuildSettingsMenuHandlerCalendar');

function onBuildSettingsMenuHandlerCalendar(&$arGlobalMenu, &$arModuleMenu)
{
    $moduleId = 'vendor.calendar';

    // Загрузка сообщений локализации
    Loc::loadMessages(__FILE__);

    // Подключение CSS
    /** TODO - сделать подключение иконок в меню через CSS */
    $GLOBALS['APPLICATION']->SetAdditionalCss('/bitrix/css/' . $moduleId . '/menu.css');

    // Проверка прав доступа к модулю
    if (true) { // Замените на реальную проверку прав доступа, если необходимо
        // Добавление пункта меню в раздел "Настройки"
        $arModuleMenu[] = [
            'parent_menu' => 'global_menu_settings', // Раздел "Настройки"
            'section' => 'modules\vendor.calendar\install\vendor_calendar',
            'sort' => 100, // Порядок
            'text' => Loc::getMessage('CALENDAR_MENU_TEXT'), // Текст пункта меню
            'title' => Loc::getMessage('CALENDAR_MENU_TITLE'), // Тултип пункта меню
            'url' => 'vendor_calendar_edit.php', // URL страницы
            'icon' => 'vendor_calendar_icon', // CSS-класс для иконки
            'page_icon' => 'vendor_calendar_page_icon', // Иконка на странице
            'items_id' => 'menu_vendor_calendar',
            'items' => [] // Подменю, если необходимо
        ];
    }
}
