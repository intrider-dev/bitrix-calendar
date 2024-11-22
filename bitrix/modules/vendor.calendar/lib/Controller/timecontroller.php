<?php
namespace Vendor\Calendar\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;

/**
 * Class TimeController
 * Handles actions related to time, including providing the current date and time.
 */
class TimeController extends Controller
{
    /**
     * Configures the actions and their filters for the controller.
     *
     * @return array Configuration array for actions.
     */
    public function configureActions()
    {
        return [
            'getCurrentTime' => [
                'prefilters' => [
                    new ActionFilter\Authentication(), // Проверка авторизации
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_GET]), // Метод GET
                ],
            ],
        ];
    }

    /**
     * Returns the current date and time.
     *
     * @return array An associative array containing the current date and time.
     *               - `current_time` (string): The current date and time in 'Y-m-d H:i:s' format.
     */
    public function getCurrentTimeAction()
    {
        // Возвращаем текущую дату и время
        return [
            'current_time' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
    }
}

