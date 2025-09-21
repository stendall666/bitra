<?php
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'main',
    'OnProlog',
    'addSubordinatesButtonScript'
);

function addSubordinatesButtonScript()
{
    global $APPLICATION;

    $currentPage = $APPLICATION->GetCurPage();
    if (preg_match('#/company/personal/user/\d+/tasks/$#', $currentPage)) {
        CJSCore::RegisterExt('subordinates_button', array(
            'js' => '/local/components/custom/tasks_subordinates/templates/.default/script.js'
        ));
        CJSCore::Init('subordinates_button');
    }
}