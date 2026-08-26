<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Ссылки на JPG-версии legal-документов для форм и футера.
 *
 * @return array{consent: string, personal_data: string, cookie: string, recommendation: string}
 */
function proktologLegalImageUrls(): array
{
    static $images = null;
    if ($images === null) {
        $legal = include $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/config.php';
        $images = $legal['images'];
    }

    return $images;
}
