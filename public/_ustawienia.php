<?php
// Ustawienia promocji z panelu (/panel/promocje): pasek z informacją o promocji i rabat procentowy na ceny zabiegów.
// Dane: _panel-dane/_ustawienia.json (tylko na serwerze). Strony dostają je przy wysyłce (index.php → promo_wstawka()).
declare(strict_types=1);
require_once __DIR__ . '/_edycje.php';

const USTAWIENIA_PLIK = EDYCJE_KATALOG . '/_ustawienia.json';

function ustawienia(): array
{
    $d = is_file(USTAWIENIA_PLIK) ? json_decode((string)file_get_contents(USTAWIENIA_PLIK), true) : null;
    $d = is_array($d) ? $d : [];
    return [
        'pasek' => ['wl' => !empty($d['pasek']['wl']), 'tekst' => (string)($d['pasek']['tekst'] ?? ''), 'link' => (string)($d['pasek']['link'] ?? ''),
                    'do' => (string)($d['pasek']['do'] ?? '')],
        'rabat' => ['wl' => !empty($d['rabat']['wl']), 'procent' => max(0, min(90, (int)($d['rabat']['procent'] ?? 0))), 'do' => (string)($d['rabat']['do'] ?? '')],
        'zmieniono' => (string)($d['zmieniono'] ?? ''),
    ];
}

function ustawienia_zapisz(array $u): bool
{
    if (!is_dir(EDYCJE_KATALOG) && !@mkdir(EDYCJE_KATALOG, 0755, true)) return false;
    if (!is_file(EDYCJE_KATALOG . '/.htaccess')) @file_put_contents(EDYCJE_KATALOG . '/.htaccess', "Require all denied\n");
    $u['zmieniono'] = date('c');
    $tmp = USTAWIENIA_PLIK . '.tmp';
    return @file_put_contents($tmp, (string)json_encode($u, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX) !== false
        && @rename($tmp, USTAWIENIA_PLIK);
}

/** Czy ustawienie działa dziś (włączone i nie po dacie „do”). */
function promo_aktywne(array $x): bool
{
    if (empty($x['wl'])) return false;
    return ($x['do'] ?? '') === '' || date('Y-m-d') <= $x['do'];
}

/** Kod wstawiany przed </body> każdej strony, gdy promocja jest włączona. */
function promo_wstawka(): string
{
    $u = ustawienia();
    $cfg = [];
    if (promo_aktywne($u['rabat']) && $u['rabat']['procent'] > 0) $cfg['rabat'] = $u['rabat']['procent'];
    if (promo_aktywne($u['pasek']) && trim($u['pasek']['tekst']) !== '') $cfg['pasek'] = ['tekst' => $u['pasek']['tekst'], 'link' => $u['pasek']['link']];
    if (!$cfg) return '';
    $v = (string)@filemtime(__DIR__ . '/assets/promocje.js');
    return '<script>window.LA_PROMO=' . json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) . ';</script>'
        . '<script src="/assets/promocje.js?v=' . $v . '" defer></script>';
}
