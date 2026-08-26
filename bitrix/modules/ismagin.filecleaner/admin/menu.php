<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

IncludeModuleLangFile(__FILE__);
include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/ismagin.filecleaner/lang/'.LANGUAGE_ID.'/admin.php');

$aMenu = array(
    "parent_menu" => "global_menu_settings",
    "section" => "ismagin_filecleaner",
    "sort" => 1000,
    "text" => GetMessage("ISMAGIN_MENU_TITLE"),
    "title" => GetMessage("ISMAGIN_MENU_TITLE"),
    "icon" => "fileman_menu_icon",
    "page_icon" => "fileman_page_icon",
    "items_id" => "menu_ismagin_filecleaner",
    "items" => array(
        array(
            "text" => GetMessage("ISMAGIN_MENU_ITEM_CLEANER"),
            "title" => GetMessage("ISMAGIN_MENU_ITEM_CLEANER"),
            "url" => "ismagin_filecleaner.php?lang=".LANGUAGE_ID,
            "more_url" => array()
        )
    )
);

return $aMenu;