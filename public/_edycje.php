<?php
// Zmiany zrobione przez Julię w panelu (/panel): teksty i zdjęcia nakładane na strony z _strony/ przy wysyłce.
// Dane leżą tylko na serwerze (_panel-dane/_edycje.json, zdjęcia w img/p/), wdrożenie z repo ich nie nadpisuje.
// Każda zmiana: id, strona (klucz strony albo „*” = wszystkie), typ (tekst|zdjecie), z (stary tekst/adres), na, kiedy.
declare(strict_types=1);

const EDYCJE_KATALOG = __DIR__ . '/_panel-dane';
const EDYCJE_PLIK = EDYCJE_KATALOG . '/_edycje.json';

function edycje_wczytaj(): array
{
    if (!is_file(EDYCJE_PLIK)) return [];
    $d = json_decode((string)file_get_contents(EDYCJE_PLIK), true);
    return is_array($d) ? array_values(array_filter($d, 'is_array')) : [];
}

function edycje_zapisz(array $e): bool
{
    if (!is_dir(EDYCJE_KATALOG) && !@mkdir(EDYCJE_KATALOG, 0755, true)) return false;
    if (!is_file(EDYCJE_KATALOG . '/.htaccess')) {
        @file_put_contents(EDYCJE_KATALOG . '/.htaccess', "Require all denied\n");
    }
    // kopia poprzedniej wersji, na wypadek pomyłki
    if (is_file(EDYCJE_PLIK)) @copy(EDYCJE_PLIK, EDYCJE_KATALOG . '/_edycje-poprzednie.json');
    $tmp = EDYCJE_PLIK . '.tmp';
    $json = json_encode(array_values($e), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false || @file_put_contents($tmp, $json, LOCK_EX) === false) return false;
    return @rename($tmp, EDYCJE_PLIK);
}

/** Te same słowa mogą stać w HTML-u (z encjami) albo w danych skryptu (napis JSON). Zwraca pary [z, na] dla każdej formy. */
function edycje_warianty(string $z, string $na): array
{
    $j = static fn(string $s): string => substr((string)json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 1, -1);
    $h = static fn(string $s): string => htmlspecialchars($s, ENT_NOQUOTES | ENT_HTML5, 'UTF-8', false);
    $pary = [[$j($z), $j($na)], [$h($z), $h($na)], [$z, $na]];
    $wynik = [];
    foreach ($pary as $p) {
        if ($p[0] !== '' && !isset($wynik[$p[0]])) $wynik[$p[0]] = $p;
    }
    return array_values($wynik);
}

/** Tekst tylko jako cały kawałek tekstu strony: między znacznikami HTML (>…<) albo na brzegach napisu w danych ("…"). */
function edycje_wzor(string $z): string
{
    return '/(?<=[>"])' . preg_quote($z, '/') . '(?=[<"])/u';
}

function edycje_dotyczy(array $e, string $klucz): bool
{
    return ($e['strona'] ?? '') === '*' || ($e['strona'] ?? '') === $klucz;
}

/** Zamiana wszędzie, gdzie tekst stoi jako cały kawałek. */
function edycje_wszedzie(string $html, string $z, string $na, int &$n = 0): string
{
    $n = 0;
    foreach (edycje_warianty($z, $na) as [$zz, $nn]) {
        $html = (string)preg_replace_callback(edycje_wzor($zz), static fn() => $nn, $html, -1, $k);
        $n += $k;
    }
    return $html;
}

/** Zamiana w jednym miejscu: pierwszy taki tekst zaraz po tekście-kotwicy ($przed, np. nazwa zabiegu przed ceną). */
function edycje_jedno(string $html, string $przed, string $z, string $na, int &$n = 0): string
{
    $cele = [];
    foreach (edycje_warianty($przed, $przed) as [$p]) {
        if (!preg_match_all(edycje_wzor($p), $html, $m, PREG_OFFSET_CAPTURE)) continue;
        foreach ($m[0] as [$t, $off]) {
            $od = $off + strlen($t);
            $naj = null;
            foreach (edycje_warianty($z, $na) as [$zz, $nn]) {
                if (preg_match(edycje_wzor($zz), $html, $mm, PREG_OFFSET_CAPTURE, $od) && $mm[0][1] - $od < 4000
                    && ($naj === null || $mm[0][1] < $naj[0])) {
                    $naj = [$mm[0][1], strlen($zz), $nn];
                }
            }
            if ($naj) $cele[$naj[0]] = $naj;
        }
    }
    krsort($cele);
    foreach ($cele as $c) $html = substr_replace($html, $c[2], $c[0], $c[1]);
    $n = count($cele);
    return $html;
}

function edycje_zastosuj(string $html, string $klucz, ?array $edycje = null): string
{
    foreach ($edycje ?? edycje_wczytaj() as $e) {
        if (!edycje_dotyczy($e, $klucz)) continue;
        $z = (string)($e['z'] ?? ''); $na = (string)($e['na'] ?? '');
        if (($e['typ'] ?? '') === 'zdjecie') { $html = str_replace($z, $na, $html); continue; }
        $html = ($e['przed'] ?? '') !== '' ? edycje_jedno($html, (string)$e['przed'], $z, $na) : edycje_wszedzie($html, $z, $na);
    }
    return $html;
}

/** Ile razy tekst (albo adres zdjęcia) występuje w stronie. */
function edycje_ile(string $html, string $z, bool $zdjecie = false): int
{
    if ($zdjecie) return substr_count($html, $z);
    $n = 0;
    foreach (edycje_warianty($z, $z) as [$w]) $n += (int)preg_match_all(edycje_wzor($w), $html);
    return $n;
}
