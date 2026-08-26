<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/form_links.php';
$proktologLegalImages = proktologLegalImageUrls();
?>
<span>Я даю <a target="_blank" href="<?= htmlspecialcharsbx($proktologLegalImages['consent']) ?>">согласие</a> на обработку персональных данных в соответствии с нашей <a target="_blank" href="<?= htmlspecialcharsbx($proktologLegalImages['personal_data']) ?>">Политикой обработки персональных данных</a>.</span>
