<?php
namespace Vendor\Calendar;

use Bitrix\Main\Localization\Loc;

/**
 * Class TimeDisplay
 * Provides methods for displaying and dynamically updating time on a webpage.
 */
class TimeDisplay
{
    /**
     * Returns the current date and time in the specified format.
     *
     * @param string $format The date and time format (default is 'Y-m-d H:i:s').
     * @return string The current date and time.
     */
    public function getCurrentDateTimeAction(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format);
    }

    /**
     * Renders an HTML block and JavaScript for dynamically updating the time on the page.
     *
     * @param string $blockId The ID of the block to update (default is 'time-display').
     * @return string The HTML and JavaScript code.
     */
    public function renderDynamicTimeBlock(string $blockId = 'time-display'): string
    {
        $currentDateTime = $this->getCurrentDateTimeAction();
        $html = <<<HTML
            <div id="{$blockId}" style="font-size: 16px; font-family: Arial, sans-serif; padding: 10px; border: 1px solid #ddd; display: inline-block;">
                {$currentDateTime}
            </div>
            <script>
                function updateTime() {
                    const timeBlock = document.getElementById('{$blockId}');
                    if (!timeBlock) return;
                    BX.ajax.runAction('vendor:calendar.TimeController.getCurrentTime', {
                        data: {}
                    }).then(function (response) {
                        timeBlock.textContent =  response.data.current_time;
                    }).catch(function (error) {
                        console.error('Ошибка обновления времени:', error);
                    });
                }
                setInterval(updateTime, 1000);
            </script>
        HTML;
        return $html;
    }

    /**
     * Returns the header block HTML.
     *
     * @return string The HTML code for the block header.
     */
    public function getBlockHeader() {
        return '<h3>' . Loc::getMessage('BLOCK_HEADER') . ':</h3>';
    }
}