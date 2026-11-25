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
        'rabat' => ['wl' => !empty($d['rabat']['wl']), 'procent' => max(0, min(90, (int)($d['rabat']['procent'] ?? 0))), 'do' => (string)($d['rabat']['do'] ?? ''),
                    'vouchery' => !empty($d['rabat']['vouchery'])],
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

/** Procent rabatu na vouchery online (0 = bez zmian): gdy w panelu zaznaczono „vouchery w cenie promocyjnej”. */
function promo_vouchery_procent(): int
{
    $r = ustawienia()['rabat'];
    return promo_aktywne($r) && !empty($r['vouchery']) ? (int)$r['procent'] : 0;
}

/** Vouchery online w cenie promocyjnej: cena vouchera = cena zabiegu po rabacie (jeśli to mniej niż zwykła cena online).
    Zmienia dane voucherów (z nich liczy też płatność, _vouchery-dane.php) i napisy „voucher online … zł” na stronach. */
function promo_html(string $html): string
{
    $p = promo_vouchery_procent();
    if ($p <= 0) return $html;
    $nowa = static fn(int $gab, int $online): int => min($online, (int)round($gab * (100 - $p) / 100));
    // dane strony /vouchery: wiersze [nazwa, cena online, cena w gabinecie]
    $html = (string)preg_replace_callback('/const D = (\{.*?\});\n/s', static function ($m) use ($nowa) {
        $D = json_decode($m[1], true);
        if (!is_array($D) || empty($D['vz'])) return $m[0];
        foreach ($D['vz'] as &$z) foreach ($z['g'] as &$g) foreach ($g[1] as &$r) $r[1] = $nowa((int)$r[2], (int)$r[1]);
        return 'const D = ' . json_encode($D, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ";\n";
    }, $html);
    // pozostałe strony: zwykła cena online to cena w gabinecie − 10%
    $z = static fn(int $n): int => $nowa((int)round($n / 0.9), $n);
    $html = (string)preg_replace_callback('/(voucher online )(od )?(\d+)( zł)/i', static fn($m) => $m[1] . $m[2] . $z((int)$m[3]) . $m[4], $html);
    $html = (string)preg_replace_callback('/("online": ")(od )?(\d+)( zł")/', static fn($m) => $m[1] . $m[2] . $z((int)$m[3]) . $m[4], $html);
    $html = (string)preg_replace_callback('/(<dt>Voucher online · )10% taniej(<\/dt><dd>)(od )?(\d+)( zł)/', static fn($m) => $m[1] . 'cena promocyjna' . $m[2] . $m[3] . $z((int)$m[4]) . $m[5], $html);
    return str_replace(['Voucher online · 10% taniej', 'VOUCHER ONLINE · 10% TANIEJ'], ['Voucher online · cena promocyjna', 'VOUCHER ONLINE · CENA PROMOCYJNA'], $html);
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
