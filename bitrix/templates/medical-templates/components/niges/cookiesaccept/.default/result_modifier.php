<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/legal_export_helpers.php';
$legal = include $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/config.php';

$cookieHref = legal_doc_href($legal, 'cookie');
$mainText = 'Этот сайт использует cookie-файлы для настройки рекламы и сбора статистики. Оставаясь на сайте, вы соглашаетесь на обработку ваших персональных данных в соответствии с нашей <a href="' . $cookieHref . '" target="_blank" rel="noopener noreferrer">политикой cookie</a>';

$arResult['MAINTEXT'] = CNigesCookiesAcceptHelper::sanitizeHtml($mainText);
