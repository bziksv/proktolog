<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/legal_export_helpers.php';
$legal = include $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/config.php';

$consentLink = legal_doc_link($legal, 'consent', 'согласие на обработку персональных данных');
$policyLink = legal_doc_link($legal, 'personal_data', 'Политикой обработки персональных данных');
$recommendLink = legal_doc_link($legal, 'recommendation', 'рекомендательные технологии');

$mainText = 'Сайт использует файлы cookies для корректной работы, аналитики трафика и настройки рекламы. Продолжая использовать сайт, вы даёте '
    . $consentLink
    . ' и соглашаетесь с '
    . $policyLink
    . '. Чтобы отказаться от сохранения cookie, отключите их в настройках браузера. На сайте также применяются '
    . $recommendLink
    . '.';

$arResult['MAINTEXT'] = CNigesCookiesAcceptHelper::sanitizeHtml($mainText);
