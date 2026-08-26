<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require_once __DIR__ . '/legal_export_helpers.php';

function proktologLegalThirdPartiesData(): array
{
    static $data = null;
    if ($data === null) {
        $data = include __DIR__ . '/third_parties_data.php';
    }

    return $data;
}

function proktologLegalRenderThirdPartyServiceName(array $service): string
{
    $name = legal_var($service['name']);
    if (!empty($service['inn'])) {
        $name .= ' (ИНН ' . legal_var($service['inn']) . ')';
    }

    return $name;
}

function proktologLegalRenderThirdPartyBlockLine(array $block): string
{
    $links = [];
    foreach ($block['urls'] as $url) {
        $links[] = '<a href="' . legal_h($url) . '" target="_blank" rel="noopener">'
            . legal_var($url) . '</a>';
    }

    return implode(', ', $links) . ' — ' . legal_var($block['text']);
}

function proktologLegalRenderThirdPartyRecommendationLine(array $block, ?array $service = null): string
{
    $line = proktologLegalRenderThirdPartyBlockLine($block);
    if ($service !== null) {
        $line = proktologLegalRenderThirdPartyServiceName($service) . ' — ' . $line;
    }

    return $line;
}

/**
 * Единый список сторонних сервисов (URL + описание) для всех legal-документов.
 * Одна организация — один пункт списка, несколько URL через «;».
 */
function proktologLegalRenderThirdPartyUrlListItems(): string
{
    $html = '';
    foreach (proktologLegalThirdPartiesData()['services'] as $service) {
        if (empty($service['recommendation'])) {
            continue;
        }

        $parts = [];
        foreach ($service['recommendation'] as $block) {
            $parts[] = proktologLegalRenderThirdPartyBlockLine($block);
        }

        $line = proktologLegalRenderThirdPartyServiceName($service) . ' — ' . implode('; ', $parts);
        $html .= '<li' . legal_li_attr() . '>' . $line . ';</li>' . "\n        ";
    }

    return $html;
}
