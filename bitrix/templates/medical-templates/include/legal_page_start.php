<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var string $legalTitle */
/** @var string|null $legalSubtitle */
?>
<div class="wrapper">
    <div class="main text">
        <article class="legal-page">
            <h1 class="legal-page__title"><?=htmlspecialcharsbx($legalTitle)?></h1>
            <?php if (!empty($legalSubtitle)): ?>
                <p class="legal-page__subtitle"><?=htmlspecialcharsbx($legalSubtitle)?></p>
            <?php endif; ?>
            <div class="legal-page__body">
