<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

function proktologRenderLegalPage(string $legalTitle, string $contentInclude, ?string $legalSubtitle = null): void
{
    global $APPLICATION;

    $APPLICATION->SetTitle($legalTitle);
    $APPLICATION->SetPageProperty('title', $legalTitle . ' — Proktolog.su');
    $APPLICATION->SetPageProperty('description', $legalTitle . ' интернет-магазина Proktolog.su.');
    $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/css/legal.css');

    include $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/include/legal_page_start.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/' . $contentInclude;
    include $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/include/legal_page_end.php';
}
