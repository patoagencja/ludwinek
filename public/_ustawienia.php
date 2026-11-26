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
        // stały rabat na vouchery online względem ceny w gabinecie (w projekcie 10%; 0 = voucher w cenie z cennika)
        'vouchery' => ['rabat' => max(0, min(50, (int)($d['vouchery']['rabat'] ?? 10)))],
        // ceny zabiegów w gabinecie dla voucherów, zmienione w panelu: „slug|grupa|wiersz” => zł
        'ceny' => array_map('intval', array_filter((array)($d['ceny'] ?? []), static fn($v) => (int)$v > 0)),
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

/** Zwykły rabat na vouchery online (% od ceny w gabinecie), ustawiany w panelu; w projekcie 10%. */
function voucher_rabat_staly(): int
{
    return ustawienia()['vouchery']['rabat'];
}

/** Cena vouchera online z ceny zabiegu w gabinecie: zwykły rabat online, a w promocji z opcją „vouchery w cenie
    promocyjnej” cena zabiegu po rabacie, jeśli jest niższa. [cena, zwykła cena online] */
function voucher_cena(int $gab): array
{
    $std = (int)round($gab * (100 - voucher_rabat_staly()) / 100);
    $p = promo_vouchery_procent();
    return [$p > 0 ? min($std, (int)round($gab * (100 - $p) / 100)) : $std, $std];
}

/** Zabiegi i warianty z danych voucherów (strona /vouchery) z cenami w gabinecie: z projektu i po zmianach z panelu. */
function voucher_wiersze(): array
{
    static $w = null;
    if ($w !== null) return $w;
    $w = [];
    $html = edycje_zastosuj((string)@file_get_contents(__DIR__ . '/_strony/Voucher-Desktop.html'), 'Voucher');
    if (!preg_match('/const D = (\{.*?\});\n/s', $html, $m) || !is_array($D = json_decode($m[1], true))) return $w;
    $ceny = ustawienia()['ceny'];
    foreach ($D['vz'] ?? [] as $z) foreach ($z['g'] as $gi => $g) foreach ($g[1] as $ri => $r) {
        $k = $z['slug'] . '|' . $gi . '|' . $ri;
        $w[] = ['klucz' => $k, 'slug' => $z['slug'], 'zabieg' => $z['name'], 'grupa' => (string)$g[0], 'wariant' => (string)$r[0],
                'gab0' => (int)$r[2], 'gab' => $ceny[$k] ?? (int)$r[2]];
    }
    return $w;
}

// strony zabiegów → vouchery, których ceny pokazują
const VOUCHER_STRONY = ['Zabieg' => ['modelowanie-ust', 'usuwanie-kwasu-hialuronowego'], 'Hialuronowy' => ['usuwanie-kwasu-hialuronowego'],
    'Stymulatory' => ['stymulatory'], 'Mezoterapia' => ['mezoterapia'], 'Dermapen' => ['dermapen'], 'Blizny' => ['blizny'], 'Biopeeling' => ['biopeeling'],
    'Pielegnacja' => ['pielegnacja', 'oczyszczanie'], 'EventGlow' => ['event-glow'], 'Signature' => ['signature'], 'BrwiRzesy' => ['brwi-rzesy'], 'SkoraGlowy' => ['skora-glowy']];

// inne nazwy zabiegów z jedną ceną (cennik, karty), przy których stoi cena w gabinecie
const CENA_NAZWY = ['modelowanie-ust' => ['Modelowanie ust', 'Powiększanie i modelowanie ust 0,5–1 ml'],
    'usuwanie-kwasu-hialuronowego' => ['Usuwanie kwasu hialuronowego', 'Rozpuszczanie kwasu hialuronowego']];

/** Ceny voucherów online na stronach i w płatności według ustawień z panelu: ceny zabiegów, zwykły rabat online i promocja.
    Zmienia dane voucherów (z nich liczy też płatność, _vouchery-dane.php) i napisy „voucher online … zł” na stronach.
    W źródle stron vouchery online są o 10% tańsze niż zabieg, więc cenę w gabinecie odtwarzamy jako cena / 0,9. */
function promo_html(string $html, string $klucz = ''): string
{
    $r0 = voucher_rabat_staly(); $p = promo_vouchery_procent(); $ceny = ustawienia()['ceny'];
    if ($r0 === 10 && $p <= 0 && !$ceny) return $html;
    $wiersze = voucher_wiersze();
    $wg = static fn(array $slugi) => array_values(array_filter($wiersze, static fn($w) => in_array($w['slug'], $slugi, true)));
    $od = static function (array $ws): string {   // „od 420 zł” / „420 zł” z wierszy vouchera
        $c = array_map(static fn($w) => voucher_cena($w['gab'])[0], $ws);
        return $c ? (count(array_unique($c)) > 1 ? 'od ' : '') . min($c) . ' zł' : '';
    };
    // dane strony /vouchery: wiersze [nazwa, cena online, cena w gabinecie]
    $html = (string)preg_replace_callback('/const D = (\{.*?\});\n/s', static function ($m) use ($ceny, $od, $wg) {
        $D = json_decode($m[1], true);
        if (!is_array($D)) return $m[0];
        if (!empty($D['vz'])) {
            foreach ($D['vz'] as &$z) foreach ($z['g'] as $gi => &$g) foreach ($g[1] as $ri => &$r) {
                $r[2] = $ceny[$z['slug'] . '|' . $gi . '|' . $ri] ?? (int)$r[2];
                $r[1] = voucher_cena((int)$r[2])[0];
            }
        } elseif (!empty($D['treat'])) {   // lista zabiegów: „voucher online od … zł” z wierszy danego vouchera
            foreach ($D['treat'] as &$t) {
                $ws = $wg([(string)($t['slug'] ?? '')]);
                if (!$ws || empty($t['online'])) continue;
                $t['online'] = $od($ws);
                if (isset($t['vlabel'])) $t['vlabel'] = preg_replace('/voucher online (od )?\d+ zł/u', 'voucher online ' . $t['online'], (string)$t['vlabel']);
                if (!empty($t['salon']) && array_filter($ws, static fn($w) => $w['gab'] !== $w['gab0'])) {   // cena w gabinecie z panelu
                    $g = array_map(static fn($w) => $w['gab'], $ws);
                    $t['salon'] = (string)preg_replace('/^(od )?\d+/', (count(array_unique($g)) > 1 ? 'od ' : '') . min($g), (string)$t['salon']);
                }
                $t['_ok'] = 1;
            }
        } else return $m[0];
        return 'const D = ' . json_encode($D, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ";\n";
    }, $html);
    if (strpos($html, '"_ok":1') !== false) {   // lista zabiegów gotowa (bez przeliczania napisów niżej)
        $html = str_replace(',"_ok":1', '', $html);
    } else {
        // napisy na stronach zabiegów: cena w gabinecie z projektu → cena z panelu (tylko wiersze voucherów tej strony)
        $ws = $wg(VOUCHER_STRONY[$klucz] ?? []);
        $mapa = [];
        foreach ($ws as $w) if (!isset($mapa[$w['gab0']])) $mapa[$w['gab0']] = $w['gab'];
        // cena w gabinecie stojąca obok ceny vouchera („700 zł” + „voucher online 630 zł”): voucher wskazuje zabieg
        $gab = static function (string $stara, int $v) use ($mapa): string {
            $g0 = (int)round($v / 0.9);
            return isset($mapa[$g0]) && $mapa[$g0] !== $g0 ? $mapa[$g0] . ' zł' : $stara;
        };
        $html = (string)preg_replace_callback('/(<dd>)(\d+\s?zł)(<\/dd><\/div><div><dt>Voucher online · 10% taniej<\/dt><dd>)(\d+)( zł)/u',
            static fn($m) => $m[1] . $gab($m[2], (int)$m[4]) . $m[3] . $m[4] . $m[5], $html);
        $html = (string)preg_replace_callback('/(voucher online )(\d+)( zł<\/span><b[^>]*>)(\d+\s?zł)/u',
            static fn($m) => $m[1] . $m[2] . $m[3] . $gab($m[4], (int)$m[2]), $html);
        $html = (string)preg_replace_callback('/(<b>)(\d+\s?zł)(<\/b><\/div>\s*<div class="orow"><span>Voucher online · 10% taniej<\/span><b[^>]*>)(\d+)( zł)/u',
            static fn($m) => $m[1] . $gab($m[2], (int)$m[4]) . $m[3] . $m[4] . $m[5], $html);
        $html = (string)preg_replace_callback('/("(?:od )?)(\d+)(\s?zł", "voucher online )((?:od )?)(\d+)( zł")/u',
            static fn($m) => $m[1] . (($g = $gab('', (int)$m[5])) !== '' ? (int)$g . ' zł", "voucher online ' : $m[2] . $m[3]) . $m[4] . $m[5] . $m[6], $html);
        $z = static function (int $n) use ($mapa): int { $g = (int)round($n / 0.9); return voucher_cena($mapa[$g] ?? $g)[0]; };
        $html = (string)preg_replace_callback('/(voucher online )(od )?(\d+)( zł)/i', static fn($m) => $m[1] . $m[2] . $z((int)$m[3]) . $m[4], $html);
        $html = (string)preg_replace_callback('/("online": ")(od )?(\d+)( zł")/', static fn($m) => $m[1] . $m[2] . $z((int)$m[3]) . $m[4], $html);
        $pierwszy = $wg(array_slice(VOUCHER_STRONY[$klucz] ?? [], 0, 1));
        $html = (string)preg_replace_callback('/(Voucher online · 10% taniej<\/(?:dt><dd|span><b[^>]*)>)(od )?(\d+)( zł)/',
            static fn($m) => $m[1] . ($m[2] && $pierwszy ? $od($pierwszy) : $m[2] . $z((int)$m[3]) . $m[4]), $html);
        // tabela cen na stronie zabiegu (warianty: [nazwa, cena, „voucher online …”]) to te same wiersze co vouchery tej strony,
        // w tej samej kolejności: wpisujemy cenę z panelu i voucher z niej policzony
        $html = (string)preg_replace_callback('/const D = (\{.*?\});\n/s', static function ($m) use ($ws) {
            $D = json_decode($m[1], true);
            if (!is_array($D) || empty($D['variants'])) return $m[0];
            $miejsca = [];
            foreach ($D['variants'] as $gi => $g) foreach ($g[2] ?? [] as $ri => $r) if (str_contains((string)($r[2] ?? ''), 'voucher online')) $miejsca[] = [$gi, $ri];
            if (count($miejsca) !== count($ws)) return $m[0];
            foreach ($miejsca as $i => [$gi, $ri]) {
                $r = &$D['variants'][$gi][2][$ri]; $w = $ws[$i];
                if ($w['wariant'] !== '' && $w['wariant'] !== $r[0]) return $m[0];   // inne wiersze niż w voucherach: zostawiamy
                if ($w['gab'] === $w['gab0']) { unset($r); continue; }
                $r[1] = (string)preg_replace('/^(od )?\d+\s?zł/u', $w['gab'] . ' zł', (string)$r[1]);
                $r[2] = 'voucher online ' . voucher_cena($w['gab'])[0] . ' zł';
                unset($r);
            }
            return 'const D = ' . json_encode($D, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ";\n";
        }, $html);
    }
    // zabiegi z jedną ceną zmienioną w panelu: cena przy nazwie zabiegu w kartach, cenniku i na stronie głównej
    foreach ($wiersze as $w) {
        if ($w['gab'] === $w['gab0'] || count($wg([$w['slug']])) !== 1) continue;
        foreach (array_unique(array_merge([$w['zabieg']], CENA_NAZWY[$w['slug']] ?? [])) as $n)
            $html = (string)preg_replace('/(>' . preg_quote($n, '/') . '<\/(?:b|span|h3)>\s*<(?:span|b)[^>]*>)\d+\s?zł/u', '${1}' . $w['gab'] . ' zł', $html);
    }
    if ($ceny && $klucz === 'Cennik') $html = cennik_ceny($html, $wiersze);
    // podpis przy cenie vouchera
    $podpis = $p > $r0 ? ' · cena promocyjna' : ($r0 > 0 ? " · {$r0}% taniej" : '');
    return str_replace(['Voucher online · 10% taniej', 'VOUCHER ONLINE · 10% TANIEJ'], ['Voucher online' . $podpis, 'VOUCHER ONLINE' . mb_strtoupper($podpis)], $html);
}

/** Kod wstawiany przed </body> każdej strony, gdy promocja jest włączona. */
function promo_wstawka(): string
{
    $u = ustawienia();
    $cfg = [];
    if (promo_aktywne($u['rabat']) && $u['rabat']['procent'] > 0) $cfg['rabat'] = $u['rabat']['procent'];
    // vouchery w cenie promocyjnej: mapa „nowa cena → zwykła cena online”, żeby strona /vouchery mogła przekreślić starą cenę
    if (promo_vouchery_procent() > 0) {
        $html = edycje_zastosuj((string)@file_get_contents(__DIR__ . '/_strony/Voucher-Desktop.html'), 'Voucher');
        if (preg_match('/const D = (\{.*?\});\n/s', $html, $m) && is_array($D = json_decode($m[1], true))) {
            $mapa = [];
            foreach (voucher_wiersze() as $w) {
                [$nowa, $std] = voucher_cena($w['gab']);
                if ($nowa < $std) $mapa[(string)$nowa] = $std;
            }
            if ($mapa) { $cfg['vmapa'] = $mapa; $cfg['vstd'] = voucher_rabat_staly(); }
        }
    }
    if (promo_aktywne($u['pasek']) && trim($u['pasek']['tekst']) !== '') $cfg['pasek'] = ['tekst' => $u['pasek']['tekst'], 'link' => $u['pasek']['link']];
    if (!$cfg) return '';
    $v = (string)@filemtime(__DIR__ . '/assets/promocje.js');
    return '<script>window.LA_PROMO=' . json_encode($cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) . ';</script>'
        . '<script src="/assets/promocje.js?v=' . $v . '" defer></script>';
}

// cennik: grupa (link do strony zabiegu) → vouchery z tymi zabiegami
const CENNIK_STRONY = ['/modelowanie-ust' => ['modelowanie-ust'], '/usuwanie-kwasu-hialuronowego' => ['usuwanie-kwasu-hialuronowego'],
    '/stymulatory-tkankowe' => ['stymulatory'], '/mezoterapia-iglowa' => ['mezoterapia'], '/mezoterapia-skory-glowy' => ['skora-glowy'],
    '/dermapen' => ['dermapen'], '/terapia-blizn-i-rozstepow' => ['blizny'], '/biopeeling' => ['biopeeling'],
    '/zabiegi-pielegnacyjne' => ['pielegnacja', 'oczyszczanie'], '/event-glow' => ['event-glow'], '/signature-glow' => ['signature'], '/brwi-i-rzesy' => ['brwi-rzesy']];

/** Cennik: ceny zmienione w panelu („Ceny zabiegów w voucherach”) wpisuje w wiersze cennika. Wiersz cennika łączymy z wierszem
    vouchera po cenie z projektu, a gdy ta sama cena jest w kilku wierszach, po nazwie grupy i wariantu. */
function cennik_ceny(string $html, array $wiersze): string
{
    $norm = static fn(string $t) => mb_strtolower(trim(html_entity_decode(strip_tags($t), ENT_QUOTES, 'UTF-8')));
    return (string)preg_replace_callback('/<div class="cgrp"><a href="([^"]+)"[^>]*><b[^>]*>(.*?)<\/b>.*?(?=<div class="cgrp">|<\/div><\/div>\s*(?:<\/div>|$))/su',
        static function ($g) use ($wiersze, $norm) {
            $slugi = CENNIK_STRONY[$g[1]] ?? [];
            $ws = array_values(array_filter($wiersze, static fn($w) => in_array($w['slug'], $slugi, true)));
            if (!$ws || !array_filter($ws, static fn($w) => $w['gab'] !== $w['gab0'])) return $g[0];
            $grupa = $norm($g[2]);
            return (string)preg_replace_callback('/(<span[^>]*>)([^<]*)(<\/span><span class="v">)(\d+)(\s?zł)/u', static function ($r) use ($ws, $grupa, $norm) {
                $n = (int)$r[4]; $wiersz = $norm($r[2]);
                $kand = array_values(array_filter($ws, static fn($w) => $w['gab0'] === $n));
                if (count(array_unique(array_map(static fn($w) => $w['gab'], $kand))) > 1) {   // ta sama cena w kilku wariantach: po nazwie
                    $pkt = static fn($w) => ($w['grupa'] !== '' && str_contains($grupa, $norm($w['grupa'])) ? 2 : 0)
                        + (str_contains($norm($w['grupa'] . ' ' . $w['wariant']), $wiersz) || $norm($w['wariant']) === $wiersz ? 1 : 0);
                    usort($kand, static fn($a, $b) => $pkt($b) <=> $pkt($a));
                    if (count($kand) > 1 && $pkt($kand[0]) === $pkt($kand[1])) return $r[0];
                }
                return $kand ? $r[1] . $r[2] . $r[3] . $kand[0]['gab'] . ' zł' : $r[0];
            }, $g[0]);
        }, $html);
}
