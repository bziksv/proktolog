<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @param string $legalTitle
 * @param string $contentInclude relative to /local/php_interface/include/legal/
 */
function proktologRenderLegalPrintPage(string $legalTitle, string $contentInclude): void
{
    $legal = include $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/config.php';
    $legalPageHeading = $legalTitle . ' ' . $legal['operator_name'];

    $cssBase = SITE_TEMPLATE_PATH . '/css';
    $cssVersion = static function (string $fileName) use ($cssBase): string {
        $path = $_SERVER['DOCUMENT_ROOT'] . $cssBase . '/' . $fileName;
        $version = is_file($path) ? (string) filemtime($path) : '1';

        return $cssBase . '/' . $fileName . '?v=' . $version;
    };

    header('Content-Type: text/html; charset=UTF-8');
    header('X-Robots-Tag: noindex, nofollow');

    ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=900">
    <title><?= htmlspecialcharsbx($legalPageHeading) ?></title>
    <link rel="stylesheet" href="<?= htmlspecialcharsbx($cssVersion('fonts.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialcharsbx($cssVersion('legal.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialcharsbx($cssVersion('legal-print.css')) ?>">
</head>
<body>
<div class="legal-print">
    <div class="legal-page legal-page--print">
        <div class="legal-page__head">
            <h1 class="legal-page__title"><?= htmlspecialcharsbx($legalPageHeading) ?></h1>
        </div>
        <div class="legal-page__body">
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/' . $contentInclude; ?>
        </div>
    </div>
</div>
</body>
</html>
    <?php

    \CMain::FinalActions();
    die();
}
