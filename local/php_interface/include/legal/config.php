<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

return [
    'operator_name' => 'ООО «ВИЛМЕД»',
    'operator_short' => 'ООО «ВИЛМЕД»',
    'operator_legal_form' => 'ООО',
    'inn' => '3662302802',
    'ogrn' => '1223600020599',
    'kpp' => '366201001',
    'site' => 'https://proktolog.su/',
    'site_host' => 'proktolog.su',
    'email' => 'info@proktolog.su',
    'phone' => '8-800-100-37-97',
    'phone_tel' => '+78001003797',
    'address_legal' => '394026, Россия, Воронежская обл., г. Воронеж, пр-кт Московский, д. 19, помещ. 1/19',
    'urls' => [
        'cookie' => '/legal/proktolog-politika-cookie/',
        'recommendation' => '/legal/proktolog-rekomend-tech/',
        'personal_data' => '/legal/proktolog-politika-pd/',
        'consent' => '/legal/proktolog-soglasie-pd/',
    ],
    'images' => [
        'consent' => '/upload/proktolog-soglasie-pd.jpg',
        'personal_data' => '/upload/proktolog-politika-pd.jpg',
        'cookie' => '/upload/proktolog-politika-cookie.jpg',
        'recommendation' => '/upload/proktolog-rekomend-tech.jpg',
    ],
    'third_parties' => include __DIR__ . '/third_parties_data.php',
];
