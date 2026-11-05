<?php
// Dane gabinetu do dokumentów prawnych. Uzupełnić zgodnie z wpisem w CEIDG przed startem strony.
// Puste pole = dokument pokazuje „uzupełnimy przed startem” (strona i tak jest za hasłem).
const DANE = [
    'wlasciciel' => '',                  // imię i nazwisko przedsiębiorcy, np. „Julia Ludwinek”
    'firma'      => '',                  // pełna nazwa firmy z CEIDG, np. „Ludwinek Aesthetic Julia Ludwinek”
    'nip'        => '',
    'regon'      => '',
    'adres'      => 'Plac Konstytucji 1, 00-647 Warszawa',
    'email'      => 'ludwinekaesthetic@gmail.com',
    'telefon'    => '737 460 105',
    'domena'     => 'ludwinekaesthetic.pl',
    'od'         => '5 listopada 2026',  // od kiedy obowiązują dokumenty
];

function dana(string $k): string
{
    $v = DANE[$k] ?? '';
    return $v !== '' ? htmlspecialchars($v) : '<em>(uzupełnimy przed startem strony)</em>';
}

/** Kto prowadzi gabinet, do zdań „Administratorem jest …” i „Stronę prowadzi …”. */
function administrator(): string
{
    if (DANE['wlasciciel'] === '') return 'gabinet kosmetologii estetycznej „Ludwinek Aesthetic”';
    $f = DANE['firma'] !== '' ? ' pod firmą ' . htmlspecialchars(DANE['firma']) : '';
    return htmlspecialchars(DANE['wlasciciel']) . ', prowadząca działalność gospodarczą' . $f . ' (gabinet kosmetologii estetycznej „Ludwinek Aesthetic”)';
}
