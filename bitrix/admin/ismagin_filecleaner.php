<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

// Подключение необходимых модулей
CModule::IncludeModule("main");
CModule::IncludeModule("iblock");

// Подключение языковых файлов
IncludeModuleLangFile(__FILE__);
$langFile = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/ismagin.filecleaner/lang/'.LANGUAGE_ID.'/admin.php';
if (file_exists($langFile)) {
    include($langFile);
} else {
    die('Language file not found');
}

$module_id = "ismagin.filecleaner";

// Проверка прав администратора
global $USER;
if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

// Переменные для уведомлений
$savedSuccessfully = false;
$cleanResults = null;

// Обработка сохранения настроек
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save"]) && check_bitrix_sessid()) {
    COption::SetOptionString($module_id, "delete_files", $_POST["delete_files"] === "Y" ? "Y" : "N");
    COption::SetOptionString($module_id, "backup_files", $_POST["backup_files"] === "Y" ? "Y" : "N");
    COption::SetOptionString($module_id, "target_path", trim($_POST["target_path"]));
    COption::SetOptionString($module_id, "backup_path", trim($_POST["backup_path"]));
    $savedSuccessfully = true;
}

// Обработка очистки файлов
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["start_clean"]) && check_bitrix_sessid()) {
    set_time_limit(0);
    ignore_user_abort(true);
    
    $deleteFiles = COption::GetOptionString($module_id, "delete_files", "N");
    $backupFiles = COption::GetOptionString($module_id, "backup_files", "N");
    $targetPath = rtrim(COption::GetOptionString($module_id, "target_path", $_SERVER["DOCUMENT_ROOT"]."/upload/iblock/"), '/');
    $backupPath = rtrim(COption::GetOptionString($module_id, "backup_path", $_SERVER["DOCUMENT_ROOT"]."/upload/filecleaner_backup/"), '/');
    
    // Создаем папку для бэкапа если нужно
    if ($backupFiles == "Y" && !file_exists($backupPath)) {
        CheckDirPath($backupPath);
    }
    
    // Получаем все файлы из БД с их размерами
    $dbFiles = array();
    $res = CFile::GetList(array(), array("MODULE_ID" => "iblock"));
    while ($arFile = $res->Fetch()) {
        $dbFiles[strtolower($arFile["FILE_NAME"])] = array(
            'size' => $arFile["FILE_SIZE"],
            'path' => $arFile["SUBDIR"]."/".$arFile["FILE_NAME"]
        );
    }
    
    // Статистика
    $stats = array(
        'total_files' => 0,
        'total_size' => 0,
        'unused_files' => 0,
        'unused_size' => 0,
        'deleted_files' => 0,
        'freed_space' => 0,
        'backuped_files' => 0,
        'backup_size' => 0,
        'errors' => 0
    );
    
    // Обработка файлов
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $targetPath,
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
        ),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $path) {
        if ($path->isDir()) continue;
        
        $fileSize = $path->getSize();
        $stats['total_files']++;
        $stats['total_size'] += $fileSize;
        
        $fileName = strtolower($path->getFilename());
        $filePath = $path->getPathname();
        
        if (!isset($dbFiles[$fileName])) {
            $stats['unused_files']++;
            $stats['unused_size'] += $fileSize;
            
            // Создание бэкапа
            if ($backupFiles == "Y") {
                $relativePath = substr($filePath, strlen($targetPath));
                $backupFile = $backupPath . $relativePath;
                
                if (!file_exists(dirname($backupFile))) {
                    mkdir(dirname($backupFile), 0755, true);
                }
                
                if (copy($filePath, $backupFile)) {
                    $stats['backuped_files']++;
                    $stats['backup_size'] += $fileSize;
                } else {
                    $stats['errors']++;
                }
            }
            
            // Удаление файла
            if ($deleteFiles == "Y") {
                if (unlink($filePath)) {
                    $stats['deleted_files']++;
                    $stats['freed_space'] += $fileSize;
                } else {
                    $stats['errors']++;
                }
            }
        }
    }
    
    // Форматирование размеров
    function formatSize($bytes) {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    $cleanResults = array(
        'total_files' => $stats['total_files'],
        'total_size' => formatSize($stats['total_size']),
        'unused_files' => $stats['unused_files'],
        'unused_size' => formatSize($stats['unused_size']),
        'deleted_files' => $stats['deleted_files'],
        'freed_space' => formatSize($stats['freed_space']),
        'backuped_files' => $stats['backuped_files'],
        'backup_size' => formatSize($stats['backup_size']),
        'errors' => $stats['errors']
    );
}

// Получение текущих настроек
$deleteFiles = COption::GetOptionString($module_id, "delete_files", "N");
$backupFiles = COption::GetOptionString($module_id, "backup_files", "N");
$defaultTargetPath = rtrim($_SERVER["DOCUMENT_ROOT"], '/').'/upload/iblock/';
$defaultBackupPath = rtrim($_SERVER["DOCUMENT_ROOT"], '/').'/upload/filecleaner_backup/';
$targetPath = COption::GetOptionString($module_id, "target_path", $defaultTargetPath);
$backupPath = COption::GetOptionString($module_id, "backup_path", $defaultBackupPath);

// Установка заголовка
$APPLICATION->SetTitle(GetMessage("ISMAGIN_PAGE_TITLE"));

// Подключение шапки админки
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?if ($savedSuccessfully):?>
<div class="adm-info-message">
    <div class="adm-info-message-title"><?=GetMessage("ISMAGIN_CLEAN_SETTINGS_SAVED")?></div>
</div>
<?endif?>

<?if ($cleanResults):?>
<div class="adm-info-message">
    <div class="adm-info-message-title"><?=GetMessage("ISMAGIN_SETTINGS_STAT")?></div>
    <div class="adm-info-message-content">
        <table class="adm-detail-content-table">
            <tr>
                <td width="40%"><?=GetMessage("ISMAGIN_SETTINGS_VSEGO")?></td>
                <td><b><?=$cleanResults['total_files']?></b> (<?=$cleanResults['total_size']?>)</td>
            </tr>
            <tr>
                <td><?=GetMessage("ISMAGIN_SETTINGS_NEISP")?></td>
                <td><b><?=$cleanResults['unused_files']?></b> (<?=$cleanResults['unused_size']?>)</td>
            </tr>
            <tr>
                <td><?=GetMessage("ISMAGIN_SETTINGS_BACKUP")?></td>
                <td><b><?=$cleanResults['backuped_files']?></b> (<?=$cleanResults['backup_size']?>)</td>
            </tr>
            <tr>
                <td><?=GetMessage("ISMAGIN_SETTINGS_DEL")?></td>
                <td><b><?=$cleanResults['deleted_files']?></b></td>
            </tr>
            <tr>
                <td><?=GetMessage("ISMAGIN_SETTINGS_PLACE")?></td>
                <td><b><?=$cleanResults['freed_space']?></b></td>
            </tr>
            <tr>
                <td><?=GetMessage("ISMAGIN_SETTINGS_ER")?></td>
                <td><b><?=$cleanResults['errors']?></b></td>
            </tr>
        </table>
    </div>
</div>
<?endif?>

<form method="post" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
    <?=bitrix_sessid_post()?>
    
    <div class="adm-detail-content">
        <div class="adm-detail-title"><?=GetMessage("ISMAGIN_SETTINGS_TITLE")?></div>
        
        <div class="adm-detail-content-item-block">
            <table class="adm-detail-content-table edit-table">
                <tr>
                    <td width="40%" class="adm-detail-content-cell-l">
                        <label for="delete_files"><?=GetMessage("ISMAGIN_FIELD_DELETE_FILES")?></label>
                    </td>
                    <td width="60%" class="adm-detail-content-cell-r">
                        <input type="checkbox" name="delete_files" id="delete_files" value="Y" <?=$deleteFiles == "Y" ? "checked" : ""?>>
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        <label for="backup_files"><?=GetMessage("ISMAGIN_FIELD_BACKUP_FILES")?></label>
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input type="checkbox" name="backup_files" id="backup_files" value="Y" <?=$backupFiles == "Y" ? "checked" : ""?>>
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        <label for="target_path"><?=GetMessage("ISMAGIN_FIELD_TARGET_PATH")?></label>
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input type="text" name="target_path" id="target_path" value="<?=htmlspecialcharsbx(!empty($targetPath) ? $targetPath : $defaultTargetPath)?>" size="50" placeholder="<?=htmlspecialcharsbx($defaultTargetPath)?>">
                    </td>
                </tr>
                <tr>
                    <td class="adm-detail-content-cell-l">
                        <label for="backup_path"><?=GetMessage("ISMAGIN_FIELD_BACKUP_PATH")?></label>
                    </td>
                    <td class="adm-detail-content-cell-r">
                        <input type="text" name="backup_path" id="backup_path" value="<?=htmlspecialcharsbx(!empty($backupPath) ? $backupPath : $defaultBackupPath)?>" size="50" placeholder="<?=htmlspecialcharsbx($defaultBackupPath)?>">
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="adm-detail-content-btns">
            <input type="submit" name="save" value="<?=GetMessage("ISMAGIN_SAVE_BTN")?>" class="adm-btn-save">
                    </div>
    </div>
</form>

<div class="adm-detail-content" style="margin-top: 20px;">
    <div class="adm-detail-title"><?=GetMessage("ISMAGIN_START_CLEAN_BTN")?></div>
    <div class="adm-detail-content-item-block">
        <form method="post" action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
            <?=bitrix_sessid_post()?>
            <input type="submit" name="start_clean" value="<?=GetMessage("ISMAGIN_START_CLEAN_BTN")?>" class="adm-btn" onclick="return confirm('<?=GetMessage("ISMAGIN_START_CLEAN_UVEREN")?>')">
        </form>
    </div>
</div>

<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>