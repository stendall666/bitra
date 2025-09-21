<?php
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$APPLICATION->SetTitle('Задачи подчиненных');

$APPLICATION->IncludeComponent(
    'custom:tasks_subordinates',
    '.default',
    array(
        'SET_TITLE' => 'N'
    ),
    false
);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>