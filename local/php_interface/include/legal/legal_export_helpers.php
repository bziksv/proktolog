<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

function legal_h($value): string
{
    return htmlspecialcharsbx((string) $value);
}

function legal_var($value): string
{
    return legal_h($value);
}

function legal_li_attr(): string
{
    return '';
}

function legal_link(string $url, ?string $text = null): string
{
    $text = $text ?? $url;

    return '<a href="' . legal_h($url) . '" target="_blank" rel="noopener">' . legal_var($text) . '</a>';
}

function legal_mailto(string $email): string
{
    return '<a href="mailto:' . legal_h($email) . '">' . legal_var($email) . '</a>';
}

function legal_tel(string $phone, string $telHref): string
{
    return '<a href="tel:' . legal_h($telHref) . '">' . legal_var($phone) . '</a>';
}

function legal_internal_link(string $path, string $host): string
{
    return '<a href="' . legal_h($path) . '">' . legal_var($host . $path) . '</a>';
}

function legal_doc_href(array $legal, string $key): string
{
    return (string) ($legal['images'][$key] ?? '');
}

function legal_doc_link(array $legal, string $key, ?string $text = null): string
{
    return legal_link(legal_doc_href($legal, $key), $text);
}

function legal_doc_internal_link(array $legal, string $key): string
{
    return legal_internal_link(legal_doc_href($legal, $key), $legal['site_host']);
}
