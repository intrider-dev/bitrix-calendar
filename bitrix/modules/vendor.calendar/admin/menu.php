<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;

// Initialize EventManager to handle events
$event_manager = EventManager::getInstance();
$event_manager->addEventHandler(
    'main',
    'OnBuildGlobalMenu',
    'onBuildSettingsMenuHandlerCalendar'
);

/**
 * Event handler for adding a custom menu item to the global settings menu in the Bitrix admin panel.
 *
 * @param array &$arGlobalMenu Array containing global menu structure.
 * @param array &$arModuleMenu Array containing module-specific menu items.
 * @return void
 */
function onBuildSettingsMenuHandlerCalendar(&$arGlobalMenu, &$arModuleMenu): void
{
    $moduleId = 'vendor.calendar';

    // Load localization messages for the current file
    Loc::loadMessages(__FILE__);

    // Include additional CSS for menu customization
    /** TODO: Add proper icon styling in the menu using CSS */
    $GLOBALS['APPLICATION']->SetAdditionalCss('/bitrix/css/' . $moduleId . '/menu.css');

    // Check user access rights for the module (adjust condition as needed)
    if (true) { // Replace with an actual access rights check if required
        // Add a new menu item under the "Settings" section
        $arModuleMenu[] = [
            'parent_menu' => 'global_menu_settings', // Place under the "Settings" section
            'section' => 'modules\vendor.calendar\install\vendor_calendar', // Unique section identifier
            'sort' => 100, // Menu order
            'text' => Loc::getMessage('CALENDAR_MENU_TEXT'), // Menu item text
            'title' => Loc::getMessage('CALENDAR_MENU_TITLE'), // Tooltip text for the menu item
            'url' => 'vendor_calendar_edit.php', // URL to the menu item's page
            'icon' => 'vendor_calendar_icon', // CSS class for the menu icon
            'page_icon' => 'vendor_calendar_page_icon', // CSS class for the page icon
            'items_id' => 'menu_vendor_calendar', // Unique ID for submenu items
            'items' => [] // Submenu items (empty array if none)
        ];
    }
}
