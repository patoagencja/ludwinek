<?php
// Dane gabinetu do dokumentów prawnych, zgodnie z wpisem w CEIDG (J.L Julia Ludwinek, NIP 6572998853).
// Puste pole = dokument pokazuje „uzupełnimy wkrótce”.
const DANE = [
    'wlasciciel' => 'Julia Ludwinek',              
    'firma'      => 'J.L Julia Ludwinek',              
    'nip'        => '6572998853',
    'regon'      => '541967128',
    'siedziba'   => 'ul. Zagnańska 78B/7, 25-528 Kielce', // adres siedziby z CEIDG
    'adres'      => 'Plac Konstytucji 1, 00-647 Warszawa',
    'email'      => 'ludwinekaesthetic@gmail.com',
    'telefon'    => '737 460 105',
    'domena'     => 'ludwinekaesthetic.pl',
    'ga4'        => 'G-5LJXC4VE72',                // identyfikator Google Analytics 4 (G-…); ten sam wpisz w assets/zgody.js (GA4_ID)
    'od'         => '10 listopada 2026',  // od kiedy obowiązują dokumenty
];

function dana(string $k): string
{
    $v = DANE[$k] ?? '';
    return $v !== '' ? htmlspecialchars($v) : '<em>(uzupełnimy wkrótce)</em>';
}

/** Kto prowadzi gabinet, do zdań „Administratorem jest …” i „Stronę prowadzi …”. */
function administrator(): string
{
    if (DANE['wlasciciel'] === '') return 'gabinet kosmetologii estetycznej „Ludwinek Aesthetic”';
    $f = DANE['firma'] !== '' ? ' pod firmą ' . htmlspecialchars(DANE['firma']) : '';
    return htmlspecialchars(DANE['wlasciciel']) . ', prowadząca działalność gospodarczą' . $f . ' (gabinet kosmetologii estetycznej „Ludwinek Aesthetic”)';
}
