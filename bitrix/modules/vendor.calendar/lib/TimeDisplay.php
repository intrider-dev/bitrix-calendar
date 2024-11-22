<?php
namespace Vendor\Calendar;

use Bitrix\Main\Localization\Loc;

class TimeDisplay
{
    /**
     * Возвращает текущую дату и время в указанном формате.
     *
     * @param string $format Формат даты и времени (по умолчанию 'Y-m-d H:i:s')
     * @return string Текущая дата и время
     */
    public function getCurrentDateTimeAction(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format);
    }

    /**
     * Возвращает HTML-блок и JavaScript для динамического обновления времени на странице.
     *
     * @param string $blockId ID блока для обновления
     * @return string HTML и JS код
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


    public function getBlockHeader() {
        return '<h3>' . Loc::getMessage('BLOCK_HEADER') . ':</h3>';
    }
}