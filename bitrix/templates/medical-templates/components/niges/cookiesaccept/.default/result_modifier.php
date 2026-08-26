<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/legal_export_helpers.php';
$legal = include $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/config.php';

$cookieLink = legal_doc_link($legal, 'cookie', 'политикой cookie');

$mainText = 'Этот сайт использует cookie-файлы для настройки рекламы и сбора статистики. Оставаясь на сайте, вы соглашаетесь на обработку ваших персональных данных в соответствии с нашей '
    . $cookieLink;

$arResult['MAINTEXT'] = CNigesCookiesAcceptHelper::sanitizeHtml($mainText);
