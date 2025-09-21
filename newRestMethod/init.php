<?php
//classes
CModule::IncludeModule("crm");
require_once($_SERVER["DOCUMENT_ROOT"] . '/local/classes/myClass.php');


CJSCore::Init("jquery");
use Bitrix\Crm\Item,
Bitrix\Crm\Service\Container,
Bitrix\Crm\Service,
Bitrix\Crm\Service\Operation,
Bitrix\Main\DI,
Bitrix\Main\Loader,
Bitrix\Main\Result,
Bitrix\Main\Error;
/*
подключения...
*/

//наш файл
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/your_path/contactDealsRest.php';