<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentDescription = [
    'NAME' => 'Задачи подчиненных',
    'DESCRIPTION' => 'Компонент для отображения и управления задачами подчиненных',
    'PATH' => [
        'ID' => 'custom',
        'NAME' => 'Кастомные компоненты',
        'CHILD' => [
            'ID' => 'tasks',
            'NAME' => 'Задачи'
        ]
    ],
    'CACHE_PATH' => 'Y',
    'COMPLEX' => 'Y'
];
?>