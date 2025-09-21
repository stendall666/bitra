<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

CJSCore::Init(['ui', 'ajax', 'sidepanel', 'date']);
?>




<div class="tasks-subordinates-wrapper" style="max-width: 1400px; margin: 0 auto; padding: 20px 40px; font-family: var(--ui-font-family-primary, var(--ui-font-family-helvetica));">
    <h1 class="ui-title-1" style="margin-bottom: 24px; color: #333; font-size: 28px; font-weight: 600;">Задачи подчиненных</h1>
    
    <!-- Фильтр -->
    <div class="main-ui-filter-wrapper" style="margin-bottom: 24px; background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0;">
        <form method="GET" action="" class="main-ui-filter-form">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px; align-items: end;">
                <!-- Поиск по названию -->
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333; font-size: 14px;">Название</label>
                    <input type="text" 
                           value="<?= htmlspecialcharsbx($_GET['FIND'] ?? '') ?>" 
                           name="FIND" 
                           placeholder="Введите название задачи"
                           style="width: 100%; padding: 12px; border: 1px solid #dde2e6; border-radius: 4px; font-size: 14px; background: white; height: 44px; box-sizing: border-box;"
                           autocomplete="off">
                </div>

                <!-- Фильтр по статусу -->
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333; font-size: 14px;">Статус</label>
                    <select name="STATUS" style="width: 100%; padding: 12px; border: 1px solid #dde2e6; border-radius: 4px; font-size: 14px; background: white; height: 44px; box-sizing: border-box;">
                        <option value="">Все статусы</option>
                        <option value="2" <?= ($_GET['STATUS'] ?? '') == '2' ? 'selected' : '' ?>>Ждет выполнения</option>
                        <option value="3" <?= ($_GET['STATUS'] ?? '') == '3' ? 'selected' : '' ?>>Выполняется</option>
                        <option value="4" <?= ($_GET['STATUS'] ?? '') == '4' ? 'selected' : '' ?>>Ждет контроля</option>
                        <option value="5" <?= ($_GET['STATUS'] ?? '') == '5' ? 'selected' : '' ?>>Завершена</option>
                        <option value="6" <?= ($_GET['STATUS'] ?? '') == '6' ? 'selected' : '' ?>>Отложена</option>
                    </select>
                </div>

                <!-- Фильтр по ролям -->
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333; font-size: 14px;">Роль</label>
                    <select name="ROLE" style="width: 100%; padding: 12px; border: 1px solid #dde2e6; border-radius: 4px; font-size: 14px; background: white; height: 44px; box-sizing: border-box;">
                        <option value="">Все роли</option>
                        <option value="DOING" <?= ($_GET['ROLE'] ?? '') == 'DOING' ? 'selected' : '' ?>>Делаю</option>
                        <option value="ASSIGNED" <?= ($_GET['ROLE'] ?? '') == 'ASSIGNED' ? 'selected' : '' ?>>Поручил</option>
                        <option value="OBSERVING" <?= ($_GET['ROLE'] ?? '') == 'OBSERVING' ? 'selected' : '' ?>>Наблюдаю</option>
                    </select>
                </div>
            </div>

            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; align-items: end;">
                <!-- Фильтр по ответственному -->
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333; font-size: 14px;">Ответственный</label>
                    <select name="RESPONSIBLE" style="width: 100%; padding: 12px; border: 1px solid #dde2e6; border-radius: 4px; font-size: 14px; background: white; height: 44px; box-sizing: border-box;">
                        <option value="">Все ответственные</option>
                        <?php foreach ($arResult['SUBORDINATES'] as $id => $name): ?>
                            <option value="<?= $id ?>" <?= ($_GET['RESPONSIBLE'] ?? '') == $id ? 'selected' : '' ?>>
                                <?= htmlspecialcharsbx($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Кнопки фильтра -->
                <div style="display: flex; gap: 12px; align-items: end;">
                    <button type="submit" style="background: #2fc6f6; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 14px; height: 44px; font-weight: 600;">
                        Применить фильтры
                    </button>
                    <a href="<?= $APPLICATION->GetCurPage() ?>" style="color: #2fc6f6; text-decoration: none; padding: 12px 24px; border: 1px solid #2fc6f6; border-radius: 4px; display: inline-flex; align-items: center; height: 44px; box-sizing: border-box; font-weight: 600;">
                        Сбросить
                    </a>
                </div>
            </div>
        </form>
    </div>

    
    <button type="button" onclick="BX.TasksSubordinates.showCreateForm()" 
            style="background: #2fc6f6; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; margin-bottom: 24px; font-size: 14px; font-weight: 600;">
        Создать задачу
    </button>

    <!-- Форма создания задачи -->
    <div id="create-task-form" style="display: none; margin-bottom: 24px; padding: 24px; background: white; border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 20px; color: #333; font-size: 18px; font-weight: 600;">Создание новой задачи</h3>
        <form method="POST" action="">
            <?= bitrix_sessid_post() ?>
            <input type="hidden" name="create_task" value="1">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333; font-size: 14px;">Название задачи:</label>
                <input type="text" name="TITLE" required 
                       style="width: 100%; padding: 12px; border: 1px solid #dde2e6; border-radius: 4px; font-size: 14px; background: white; height: 44px; box-sizing: border-box;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333; font-size: 14px;">Описание:</label>
                <textarea name="DESCRIPTION" 
                          style="width: 100%; padding: 12px; border: 1px solid #dde2e6; border-radius: 4px; font-size: 14px; height: 100px; resize: vertical; box-sizing: border-box;"></textarea>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333; font-size: 14px;">Ответственный:</label>
                <select name="RESPONSIBLE_ID" 
                        style="width: 100%; padding: 12px; border: 1px solid #dde2e6; border-radius: 4px; font-size: 14px; background: white; height: 44px; box-sizing: border-box;">
                    <?php foreach ($arResult['SUBORDINATES'] as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialcharsbx($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333; font-size: 14px;">Крайний срок:</label>
                <input type="datetime-local" name="DEADLINE" 
                       style="width: 100%; padding: 12px; border: 1px solid #dde2e6; border-radius: 4px; font-size: 14px; background: white; height: 44px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 8px; border-radius: 4px; transition: background 0.2s;">
                    <input type="checkbox" name="ALLOW_CHANGE_DEADLINE" value="Y" 
                           style="width: 18px; height: 18px; margin: 0;">
                    <span style="font-weight: 600; color: #333; font-size: 14px;">Не завершать без результата</span>
                </label>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <button type="submit" 
                        style="background: #2fc6f6; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600;">
                    Создать задачу
                </button>
                <button type="button" onclick="BX.TasksSubordinates.hideCreateForm()" 
                        style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600;">
                    Отмена
                </button>
            </div>
        </form>
    </div>

    <!-- сами задачи -->
    <div class="tasks-list">
        <?php if (!empty($arResult['TASKS'])): ?>
            <div style="overflow-x: auto; background: white; border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 16px; text-align: left; border-bottom: 2px solid #e0e0e0; font-weight: 600; color: #333;">Название</th>
                            <th style="padding: 16px; text-align: left; border-bottom: 2px solid #e0e0e0; font-weight: 600; color: #333;">Ответственный</th>
                            <th style="padding: 16px; text-align: left; border-bottom: 2px solid #e0e0e0; font-weight: 600; color: #333;">Статус</th>
                            <th style="padding: 16px; text-align: left; border-bottom: 2px solid #e0e0e0; font-weight: 600; color: #333;">Срок</th>
                            <th style="padding: 16px; text-align: left; border-bottom: 2px solid #e0e0e0; font-weight: 600; color: #333;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($arResult['TASKS'] as $task): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 16px; color: #333;"><?= htmlspecialcharsbx($task['TITLE']) ?></td>
                                <td style="padding: 16px; color: #333;"><?= htmlspecialcharsbx($arResult['ALL_EMPLOYEES'][$task['RESPONSIBLE_ID']] ?? 'Неизвестно') ?></td>
                                <td style="padding: 16px;">
                                    <span style="padding: 6px 12px; border-radius: 16px; font-size: 12px; font-weight: 600; background: <?= $component->getStatusColor($task['STATUS']) ?>;">
                                        <?= $component->getStatusText($task['STATUS']) ?>
                                    </span>
                                </td>
                                <td style="padding: 16px; color: #333;"><?= $task['DEADLINE'] ? FormatDate('d.m.Y', MakeTimeStamp($task['DEADLINE'])) : '-' ?></td>
                                <td style="padding: 16px;">
                                    <div style="display: flex; gap: 8px;">
                                        <a href="/company/personal/user/<?= $arResult['CURRENT_USER_ID'] ?>/tasks/task/view/<?= $task['ID'] ?>/" 
                                           style="background: #2fc6f6; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600;">
                                            Просмотр
                                        </a>
                                        <button type="button" onclick="BX.TasksSubordinates.showChangeResponsible(<?= $task['ID'] ?>)" 
                                                style="background: #2fc6f6; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">
                                            Сменить ответственного
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            
            <?php if ($arResult['NAV_OBJECT']): ?>
                <div style="margin-top: 24px; text-align: center;">
                    <?php
                    $nav = $arResult['NAV_OBJECT'];
                    if ($nav->getPageCount() > 1): ?>
                        <div style="display: inline-flex; gap: 4px; background: white; padding: 12px; border-radius: 8px; border: 1px solid #e0e0e0;">
                            <?php if ($nav->getCurrentPage() > 1): ?>
                                <a href="?<?= $nav->getQueryString('PAGEN_' . $nav->getId() . '=' . ($nav->getCurrentPage() - 1)) ?>" 
                                   style="padding: 8px 12px; border: 1px solid #dde2e6; border-radius: 4px; text-decoration: none; color: #2fc6f6; font-weight: 600;">
                                    Назад
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $nav->getPageCount(); $i++): ?>
                                <?php if ($i == $nav->getCurrentPage()): ?>
                                    <span style="padding: 8px 12px; background: #2fc6f6; color: white; border-radius: 4px; font-weight: 600;">
                                        <?= $i ?>
                                    </span>
                                <?php else: ?>
                                    <a href="?<?= $nav->getQueryString('PAGEN_' . $nav->getId() . '=' . $i) ?>" 
                                       style="padding: 8px 12px; border: 1px solid #dde2e6; border-radius: 4px; text-decoration: none; color: #2fc6f6; font-weight: 600;">
                                        <?= $i ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($nav->getCurrentPage() < $nav->getPageCount()): ?>
                                <a href="?<?= $nav->getQueryString('PAGEN_' . $nav->getId() . '=' . ($nav->getCurrentPage() + 1)) ?>" 
                                   style="padding: 8px 12px; border: 1px solid #dde2e6; border-radius: 4px; text-decoration: none; color: #2fc6f6; font-weight: 600;">
                                    Вперед
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div style="padding: 40px; text-align: center; background: white; border-radius: 8px; border: 1px solid #e0e0e0;">
                <div style="color: #6c757d; font-size: 16px;">
                    Задачи не найдены
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Форма смены ответственного -->
    <div id="change-responsible-form" style="display: none; margin-top: 24px; padding: 24px; background: white; border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 20px; color: #333; font-size: 18px; font-weight: 600;">Смена ответственного</h3>
        <form method="POST" action="">
            <?= bitrix_sessid_post() ?>
            <input type="hidden" name="change_responsible" value="1">
            <input type="hidden" name="TASK_ID" id="change-task-id">
            
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333; font-size: 14px;">Новый ответственный:</label>
                <select name="NEW_RESPONSIBLE_ID" 
                        style="width: 100%; padding: 12px; border: 1px solid #dde2e6; border-radius: 4px; font-size: 14px; background: white; height: 44px; box-sizing: border-box;">
                    <?php foreach ($arResult['ALL_EMPLOYEES'] as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialcharsbx($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <button type="submit" 
                        style="background: #2fc6f6; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600;">
                    Сохранить
                </button>
                <button type="button" onclick="BX.TasksSubordinates.hideChangeResponsible()" 
                        style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600;">
                    Отмена
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    BX.TasksSubordinates = {
        showCreateForm: function() {
            document.getElementById('create-task-form').style.display = 'block';
        },
        
        hideCreateForm: function() {
            document.getElementById('create-task-form').style.display = 'none';
        },
        
        showChangeResponsible: function(taskId) {
            document.getElementById('change-task-id').value = taskId;
            document.getElementById('change-responsible-form').style.display = 'block';
        },
        
        hideChangeResponsible: function() {
            document.getElementById('change-responsible-form').style.display = 'none';
        }
    };
</script>