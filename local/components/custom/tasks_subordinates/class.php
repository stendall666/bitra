<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Tasks\Internals\TaskTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Application;

class TasksSubordinatesComponent extends CBitrixComponent
{
    private $currentUserId;
    private $subordinates = [];
    private $allEmployees = [];

    public function onPrepareComponentParams($arParams)
    {
        $arParams['SET_TITLE'] = $arParams['SET_TITLE'] ?? 'Y';
        return $arParams;
    }

    public function executeComponent()
    {
        if (!$this->checkModules()) {
            return;
        }

        $this->includeComponentLang('class.php');
        
        $this->currentUserId = CurrentUser::get()->getId();
        $this->getSubordinates();
        $this->getAllEmployees(); 

        if ($this->startResultCache()) {
            $this->processRequest();
            $this->prepareResult();
            $this->includeComponentTemplate();
        }
    }

    private function checkModules()
    {
        if (!Loader::includeModule('tasks')) {
            ShowError('Модуль задач не установлен');
            return false;
        }
        
        if (!Loader::includeModule('intranet')) {
            ShowError('Модуль интранета не установлен');
            return false;
        }
        
        return true;
    }

    private function getSubordinates()
    {
        // Получаем подчиненных текущего пользователя
        $rsSubordinates = CIntranetUtils::getSubordinateEmployees($this->currentUserId, true);
        
        if ($rsSubordinates) {
            while ($subordinate = $rsSubordinates->Fetch()) {
                $this->subordinates[] = (int)$subordinate['ID'];
            }
        }
    }

    private function getAllEmployees()
    {
        // Получаем всех сотрудников для выбора ответственного
        $rsUsers = CUser::GetList(
            'last_name',
            'asc',
            ['ACTIVE' => 'Y'],
            ['FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME']]
        );

        while ($user = $rsUsers->Fetch()) {
            $name = trim($user['LAST_NAME'] . ' ' . $user['NAME'] . ' ' . $user['SECOND_NAME']);
            $this->allEmployees[$user['ID']] = $name;
        }
    }

    private function processRequest()
    {
        $request = Application::getInstance()->getContext()->getRequest();
        
        
        if ($request->getPost('create_task') && check_bitrix_sessid()) {
            $this->createTask($request);
        }

        if ($request->getPost('change_responsible') && check_bitrix_sessid()) {
            $this->changeResponsible($request);
        }
    }

    private function prepareResult()
    {
        global $APPLICATION;

        if ($this->arParams['SET_TITLE'] === 'Y') {
            $APPLICATION->SetTitle('Задачи подчиненных');
        }

        // Получаем задачи подчиненных
        $this->arResult['TASKS'] = $this->getSubordinatesTasks();
        $this->arResult['SUBORDINATES'] = $this->getSubordinatesList();
        $this->arResult['ALL_EMPLOYEES'] = $this->allEmployees;
        $this->arResult['CURRENT_USER_ID'] = $this->currentUserId;
        $this->arResult['NAV_OBJECT'] = $this->getNavigation();
    }

    private function getSubordinatesTasks()
    {
        $nav = $this->getNavigation();
        $tasks = [];
        $request = Application::getInstance()->getContext()->getRequest();

         $filter = [];

    // Фильтр по ролям
    $role = $request->get('ROLE');
    if ($role === 'DOING') {
        $filter['RESPONSIBLE_ID'] = $this->currentUserId;
    } elseif ($role === 'ASSIGNED') {
        $filter['CREATED_BY'] = $this->currentUserId;
    } elseif ($role === 'OBSERVING') {
        $filter['RESPONSIBLE_ID'] = $this->subordinates;
    } else {
        $filter['RESPONSIBLE_ID'] = $this->subordinates;
    }

    // Фильтр по статусу
    if ($request->get('STATUS') && $request->get('STATUS') !== '') {
        $filter['STATUS'] = (int)$request->get('STATUS');
    }

    // Поиск по названию задачи
    if ($request->get('FIND') && $request->get('FIND') !== '') {
        $filter['%TITLE'] = $request->get('FIND');
    }

    // Фильтр по конкретному ответственному
    if ($request->get('RESPONSIBLE') && $request->get('RESPONSIBLE') !== '') {
        $filter['RESPONSIBLE_ID'] = (int)$request->get('RESPONSIBLE');
    }

        $countQuery = new Query(TaskTable::getEntity());
        $countQuery->setSelect(['CNT' => new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)')]);
        $countQuery->setFilter($filter);
        $totalCount = $countQuery->exec()->fetch()['CNT'];
        
        $nav->setRecordCount($totalCount);

        $query = new Query(TaskTable::getEntity());
        $query->setSelect([
            'ID', 'TITLE', 'DESCRIPTION', 'STATUS', 'PRIORITY',
            'CREATED_BY', 'RESPONSIBLE_ID', 'DEADLINE', 'CREATED_DATE'
        ]);

        $query->setFilter($filter);
        $query->setOrder(['CREATED_DATE' => 'DESC']);
        $query->setOffset($nav->getOffset());
        $query->setLimit($nav->getLimit());

        $result = $query->exec();

        while ($task = $result->fetch()) {
            $tasks[] = [
                'ID' => $task['ID'],
                'TITLE' => $task['TITLE'],
                'DESCRIPTION' => $task['DESCRIPTION'],
                'STATUS' => $task['STATUS'],
                'PRIORITY' => $task['PRIORITY'],
                'CREATED_BY' => $task['CREATED_BY'],
                'RESPONSIBLE_ID' => $task['RESPONSIBLE_ID'],
                'DEADLINE' => $task['DEADLINE'],
                'CREATED_DATE' => $task['CREATED_DATE']
            ];
        }

        return $tasks;
    }

    private function getSubordinatesList()
    {
        $subordinatesList = [];
        
        if (empty($this->subordinates)) {
            return $subordinatesList;
        }

        $rsUsers = CUser::GetList(
            'last_name',
            'asc',
            ['ID' => implode('|', $this->subordinates)],
            ['FIELDS' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME']]
        );

        while ($user = $rsUsers->Fetch()) {
            $name = trim($user['LAST_NAME'] . ' ' . $user['NAME'] . ' ' . $user['SECOND_NAME']);
            $subordinatesList[$user['ID']] = $name;
        }

        return $subordinatesList;
    }

    private function getNavigation()
    {
        $nav = new PageNavigation('nav-subordinates-tasks');
        $nav->allowAllRecords(false)
            ->setPageSize(20)
            ->initFromUri();

        return $nav;
    }

    private function createTask($request)
{
    $taskFields = [
        'TITLE' => trim($request->getPost('TITLE')),
        'DESCRIPTION' => trim($request->getPost('DESCRIPTION')),
        'RESPONSIBLE_ID' => (int)$request->getPost('RESPONSIBLE_ID'),
        'CREATED_BY' => $this->currentUserId,
        'PRIORITY' => 2, // По умолчанию
    ];

    if (!empty($request->getPost('DEADLINE'))) {
        $taskFields['DEADLINE'] = ConvertTimeStamp(strtotime($request->getPost('DEADLINE')), "FULL");
    }

    // "Не завершать без результата"
    if ($request->getPost('ALLOW_CHANGE_DEADLINE') === 'Y') {
        $taskFields['ALLOW_CHANGE_DEADLINE'] = 'Y';
    }

    $task = new CTasks();
    $taskId = $task->Add($taskFields);

    if ($taskId) {
        LocalRedirect($this->request->getRequestUri());
    } else {
        $this->arResult['ERROR'] = $task->GetErrors();
    }
}

    private function changeResponsible($request)
    {
        $taskId = (int)$request->getPost('TASK_ID');
        $newResponsibleId = (int)$request->getPost('NEW_RESPONSIBLE_ID');

        $task = new CTasks();
        if ($task->Update($taskId, ['RESPONSIBLE_ID' => $newResponsibleId])) {
            LocalRedirect($this->request->getRequestUri());
        } else {
            $this->arResult['ERROR'] = $task->GetErrors();
        }
    }


    public function getStatusText($statusId)
    {
        $statuses = [
            1 => 'Новая',
            2 => 'Ждет выполнения',
            3 => 'Выполняется',
            4 => 'Ждет контроля',
            5 => 'Завершена',
            6 => 'Отложена',
            7 => 'Отклонена'
        ];
        
        return $statuses[$statusId];
    }


    public function getStatusColor($statusId)
    {
        $colors = [
            1 => '#e3f2fd', // Новая - голубой
            2 => '#fff3e0', // Ждет выполнения - оранжевый
            3 => '#e8f5e8', // Выполняется - зеленый
            4 => '#f3e5f5', // Ждет контроля - фиолетовый
            5 => '#e8eaf6', // Завершена - синий
            6 => '#f5f5f5', // Отложена - серый
            7 => '#ffebee'  // Отклонена - красный
        ];
        
        return $colors[$statusId] ?? '#f5f5f5';
    }
}
?>