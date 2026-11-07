<?php
// Panel do prostej edycji strony dla Julii: /panel. Klik w tekst albo zdjęcie na podglądzie i zamiana.
// Zmiany zapisują się na serwerze (_edycje.php) i od razu widać je na stronie. Wołane z index.php ($path zaczyna się od „panel”).
declare(strict_types=1);
require_once __DIR__ . '/_edycje.php';

require_once __DIR__ . '/_panel-auth.php';

function panel_json(int $kod, array $d): void
{
    http_response_code($kod);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Strony z _strony/ (obie wersje) z już naniesionymi zmianami. */
function panel_strony(array $klucze, array $edycje): array
{
    $out = [];
    foreach ($klucze as $k) {
        foreach (['Desktop', 'Mobile'] as $w) {
            $p = __DIR__ . '/_strony/' . $k . '-' . $w . '.html';
            if (is_file($p)) $out[$k . '-' . $w] = edycje_zastosuj((string)file_get_contents($p), $k, $edycje);
        }
    }
    return $out;
}

/** Nowy tekst: bez znaczników i znaków, które mogłyby zepsuć stronę; proste cudzysłowy zamienione na polskie. */
function panel_czysc(string $s): string
{
    $s = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $s);
    $s = preg_replace('/[^\P{C}]/u', '', $s) ?? '';
    $s = str_replace(['<', '>', '\\', '{{', '}}', '`'], '', $s);
    $i = 0;
    $s = preg_replace_callback('/"/', static function () use (&$i) { return $i++ % 2 ? '”' : '„'; }, $s) ?? $s;
    return mb_substr($s, 0, 2000);
}

/** Zapis zmiany: jeśli ktoś zmienia tekst, który sam wcześniej wpisał, poprawiamy tamtą zmianę zamiast dokładać nową. */
function panel_dodaj(array $edycje, string $strona, string $typ, string $z, string $na, string $przed = ''): array
{
    foreach ($edycje as $i => $e) {
        if (($e['typ'] ?? '') === $typ && ($e['strona'] ?? '') === $strona && ($e['na'] ?? null) === $z && ($e['przed'] ?? '') === $przed) {
            if ($na === ($e['z'] ?? '')) { array_splice($edycje, $i, 1); return $edycje; }
            $edycje[$i]['na'] = $na;
            $edycje[$i]['kiedy'] = date('c');
            return $edycje;
        }
    }
    $edycje[] = ['id' => bin2hex(random_bytes(6)), 'strona' => $strona, 'typ' => $typ, 'z' => $z, 'na' => $na] + ($przed !== '' ? ['przed' => $przed] : []) + ['kiedy' => date('c')];
    return $edycje;
}

/** Zdjęcie z panelu: sprawdzenie, obrót wg EXIF, zmniejszenie do 2400 px, zapis jako JPG bez danych EXIF. */
function panel_zapisz_zdjecie(string $tmp): ?string
{
    $info = @getimagesize($tmp);
    if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) return null;
    $kat = __DIR__ . '/img/p';
    if (!is_dir($kat) && !@mkdir($kat, 0755, true)) return null;
    $nazwa = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.jpg';
    if (!function_exists('imagecreatefromstring')) {
        if ($info[2] !== IMAGETYPE_JPEG) return null;
        return @move_uploaded_file($tmp, "$kat/$nazwa") || @copy($tmp, "$kat/$nazwa") ? "/img/p/$nazwa" : null;
    }
    $im = @imagecreatefromstring((string)file_get_contents($tmp));
    if (!$im) return null;
    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $o = (int)(@exif_read_data($tmp)['Orientation'] ?? 1);
        $kat_obrotu = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
        if ($kat_obrotu) { $r = imagerotate($im, $kat_obrotu, 0); if ($r) { imagedestroy($im); $im = $r; } }
    }
    $w = imagesx($im); $h = imagesy($im); $max = 2400;
    $s = min(1, $max / max($w, $h));
    $nw = max(1, (int)round($w * $s)); $nh = max(1, (int)round($h * $s));
    $out = imagecreatetruecolor($nw, $nh);
    imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
    imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
    $ok = imagejpeg($out, "$kat/$nazwa", 84);
    imagedestroy($im); imagedestroy($out);
    return $ok ? "/img/p/$nazwa" : null;
}

// ---------------------------------------------------------------------------------------------------------------------

date_default_timezone_set('Europe/Warsaw');
header('X-Robots-Tag: noindex, nofollow');
$akcja = trim(substr($path, 5), '/');   // $path z index.php: „panel”, „panel/tekst” …
$metoda = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($akcja === 'wyloguj') {
    setcookie(PANEL_CIASTKO, '', ['expires' => time() - 3600, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
    header('Location: /panel', true, 303);
    exit;
}

if (!panel_zalogowany()) {
    if ($akcja !== '') panel_json(401, ['ok' => false, 'blad' => 'Sesja wygasła. Odśwież stronę i zaloguj się ponownie.']);
    $blad = false;
    if ($metoda === 'POST') {
        // limit prób logowania z jednego IP: 10 na 15 minut
        $plik = rtrim(sys_get_temp_dir(), '/') . '/ludwinek-panel-' . substr(hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? '')), 0, 24);
        $proby = array_filter(explode(',', (string)@file_get_contents($plik)), static fn($t) => (int)$t > time() - 900);
        if (count($proby) < 10 && password_verify((string)($_POST['haslo'] ?? ''), PANEL_HASLO_HASH)) {
            $do = time() + PANEL_DNI * 86400;
            setcookie(PANEL_CIASTKO, panel_token($do), ['expires' => $do, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
            header('Location: /panel', true, 303);
            exit;
        }
        $proby[] = time();
        @file_put_contents($plik, implode(',', $proby), LOCK_EX);
        $blad = count($proby) >= 10 ? 'Za dużo prób. Spróbuj za kwadrans.' : 'Złe hasło. Spróbuj jeszcze raz.';
        usleep(800000);
    }
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    require __DIR__ . '/_panel-widok.php';
    exit;
}

if ($akcja === '') {
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Frame-Options: DENY');
    require __DIR__ . '/_panel-widok.php';
    exit;
}

// API panelu: tylko POST z nagłówkiem X-Panel (inne strony nie mogą go wysłać bez zgody serwera)
if ($metoda !== 'POST' || ($_SERVER['HTTP_X_PANEL'] ?? '') !== '1') panel_json(400, ['ok' => false, 'blad' => 'Złe zapytanie.']);
$edycje = edycje_wczytaj();
$strona = (string)($_POST['strona'] ?? '');
if (!in_array($strona, STRONY, true)) $strona = '';
$wszedzie = ($_POST['wszedzie'] ?? '') === '1';
$cel = $wszedzie ? '*' : $strona;

if ($akcja === 'lista') {
    $strony = panel_strony(array_values(STRONY), $edycje);
    $nazwy = array_flip(STRONY);
    $lista = [];
    foreach (array_reverse($edycje) as $e) {
        $dziala = false;
        foreach ($strony as $k => $html) {
            if (edycje_dotyczy($e, explode('-', $k)[0]) && edycje_ile($html, (string)$e['na'], ($e['typ'] ?? '') === 'zdjecie') > 0) { $dziala = true; break; }
        }
        $lista[] = $e + ['dziala' => $dziala, 'adres' => $e['strona'] === '*' ? '' : '/' . ($nazwy[$e['strona']] ?? '')];
    }
    panel_json(200, ['ok' => true, 'zmiany' => $lista]);
}

if ($akcja === 'cofnij') {
    $id = (string)($_POST['id'] ?? '');
    $edycje = array_values(array_filter($edycje, static fn($e) => ($e['id'] ?? '') !== $id));
    if (!edycje_zapisz($edycje)) panel_json(500, ['ok' => false, 'blad' => 'Nie udało się zapisać. Spróbuj ponownie.']);
    panel_json(200, ['ok' => true]);
}

if ($strona === '') panel_json(400, ['ok' => false, 'blad' => 'Nie wiem, której strony dotyczy zmiana.']);

if ($akcja === 'tekst') {
    $z = (string)($_POST['z'] ?? '');
    $na = panel_czysc((string)($_POST['na'] ?? ''));
    if (trim($z) === '' || mb_strlen($z) > 5000) panel_json(400, ['ok' => false, 'blad' => 'Nie widzę, który tekst zmienić.']);
    // zachowujemy odstępy z brzegów starego tekstu (oddzielają go od sąsiednich słów)
    preg_match('/^\s*/u', $z, $a); preg_match('/\s*$/u', $z, $b);
    $na = $a[0] . trim($na) . $b[0];
    if (trim($na) === '') panel_json(400, ['ok' => false, 'blad' => 'Tekst nie może być pusty.']);
    if ($na === $z) panel_json(200, ['ok' => true, 'bez_zmian' => true]);
    // „tylko to miejsce”: tekst-kotwica stojący tuż przed (np. nazwa zabiegu przed ceną)
    $przed = $wszedzie ? '' : (string)($_POST['przed'] ?? '');
    if (mb_strlen($przed) > 5000) $przed = '';
    $strony = panel_strony($wszedzie ? array_values(STRONY) : [$strona], $edycje);
    // liczymy miejsca na stronie (wersje na komputer i telefon to te same miejsca, więc bierzemy większą liczbę)
    $naStronie = [];
    foreach ($strony as $k => $html) {
        $s = explode('-', $k)[0];
        $n = 0;
        if ($przed !== '') edycje_jedno($html, $przed, $z, $na, $n); else $n = edycje_ile($html, $z);
        $naStronie[$s] = max($naStronie[$s] ?? 0, $n);
    }
    $ile = array_sum($naStronie);
    if ($ile === 0 && $przed !== '') panel_json(422, ['ok' => false, 'blad' => 'Nie umiem wskazać tego jednego miejsca. Wybierz „We wszystkich miejscach” albo napisz do Daniela.']);
    if ($ile === 0) panel_json(422, ['ok' => false, 'blad' => 'Tego tekstu nie da się zmienić w panelu (strona tworzy go automatycznie). Napisz do Daniela, co zmienić.']);
    if (!edycje_zapisz(panel_dodaj($edycje, $cel, 'tekst', $z, $na, $przed))) panel_json(500, ['ok' => false, 'blad' => 'Nie udało się zapisać. Spróbuj ponownie.']);
    panel_json(200, ['ok' => true, 'miejsc' => $ile]);
}

if ($akcja === 'zdjecie') {
    $z = (string)($_POST['z'] ?? '');
    if (!preg_match('~^/img/(p/)?[\w.-]+\.(jpe?g|png|webp)$~', $z)) panel_json(400, ['ok' => false, 'blad' => 'Tego zdjęcia nie da się zmienić w panelu.']);
    $f = $_FILES['plik'] ?? null;
    if (!$f || ($f['error'] ?? 1) !== UPLOAD_ERR_OK || !is_uploaded_file((string)$f['tmp_name'])) {
        panel_json(400, ['ok' => false, 'blad' => 'Nie dotarło zdjęcie. Spróbuj ponownie (najlepiej JPG do 10 MB).']);
    }
    $na = panel_zapisz_zdjecie((string)$f['tmp_name']);
    if ($na === null) panel_json(422, ['ok' => false, 'blad' => 'Nie umiem odczytać tego pliku. Wybierz zdjęcie JPG lub PNG.']);
    if (!edycje_zapisz(panel_dodaj($edycje, $cel, 'zdjecie', $z, $na))) panel_json(500, ['ok' => false, 'blad' => 'Nie udało się zapisać. Spróbuj ponownie.']);
    panel_json(200, ['ok' => true, 'na' => $na]);
}

panel_json(404, ['ok' => false, 'blad' => 'Nie ma takiej akcji.']);
