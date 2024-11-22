<?php
namespace Vendor\Calendar\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;

class TimeController extends Controller
{
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

    public function getCurrentTimeAction()
    {
        // Возвращаем текущую дату и время
        return [
            'current_time' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
    }
}

