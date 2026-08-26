<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
?>
<label class="main-user-consent-request">
	<input type="checkbox" value="Y"<?=($arParams['IS_CHECKED'] === true ? ' checked' : '')?> name="<?=htmlspecialcharsbx($arParams['INPUT_NAME'])?>">
	<?php include $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/include/legal_form_consent.php'; ?>
</label>
