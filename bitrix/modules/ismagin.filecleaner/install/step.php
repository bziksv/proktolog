<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<div style="padding: 20px;">
    <h2><?=Loc::getMessage("ISMAGIN_INSTALL_MESSAGE")?></h2>
    <p><?=Loc::getMessage("ISMAGIN_INSTALL_DESCRIPTION")?></p>
</div>