<?php
// ludwinekaesthetic.pl: przed startem wszyscy widzą stronę „Wkrótce” (_wkrotce.php), a pełna strona otwiera się dopiero
// po haśle (/zaloguj). Pełna strona to dokumenty z _strony/: telefony dostają wersję Mobile, reszta Desktop
// (ciasteczko „widok” = m/d nadpisuje wybór; strony same je ustawiają, gdy okno nie pasuje do wersji).
// Strona wystartowała (PRZED_STARTEM = false): pełna strona jest dla wszystkich. Powrót do „Wkrótce”: ustaw true.
declare(strict_types=1);

const PRZED_STARTEM = false;
const HASLO_HASH = '$2y$12$HDfHP/AmBFipOwPs/BkPcOaiQ3Ha6tq67mnSHbjYMxQD3GqwVx/iq';
const CIASTKO = 'ludwinek_podglad';

const STRONY = [
    '' => 'Home', 'modelowanie-ust' => 'Zabieg', 'zabiegi' => 'Zabiegi', 'dermapen' => 'Dermapen',
    'terapia-blizn-i-rozstepow' => 'Blizny', 'zabiegi-pielegnacyjne' => 'Pielegnacja', 'event-glow' => 'EventGlow',
    'signature-glow' => 'Signature', 'dla-mezczyzn' => 'Mezczyzni', 'blizny-potradzikowe' => 'Problem',
    'cennik' => 'Cennik', 'vouchery' => 'Voucher', 'o-mnie' => 'Omnie', 'informacje' => 'Informacje', 'kontakt' => 'Kontakt',
];

$path = trim((string)parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH), '/');
$path = rawurldecode($path);
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$token = hash_hmac('sha256', 'ludwinek-podglad', HASLO_HASH);
$wpuszczony = !PRZED_STARTEM || hash_equals($token, (string)($_COOKIE[CIASTKO] ?? ''));

function wyslij_strone(string $klucz, bool $prywatna): void
{
    require_once __DIR__ . '/_edycje.php';
    // podgląd w panelu (/panel): wersja wybrana w panelu i skrypt do klikania w teksty i zdjęcia
    $wPanelu = isset($_GET['panel']) && function_exists('panel_zalogowany') && panel_zalogowany();
    $widok = $wPanelu ? (string)($_GET['widok'] ?? 'd') : ($_COOKIE['widok'] ?? '');
    if ($widok !== 'm' && $widok !== 'd') {
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        $widok = preg_match('/iPhone|iPod|Android.+Mobile|Mobile.+Firefox|Windows Phone|Opera Mini|BlackBerry|webOS/i', $ua) ? 'm' : 'd';
    }
    // sprzedaż voucherów: wersja z zakupem, gdy na serwerze jest klucz Stripe (klucz testowy: tylko dla urządzeń gabinetu)
    $kat = '_strony-wkrotce';
    $stripe = __DIR__ . '/_panel-dane/_stripe.php';
    if (is_file($stripe)) {
        $k = require $stripe;
        if (is_array($k) && !empty($k['klucz']) && (($k['tryb'] ?? '') === 'live' || ($_COOKIE['ludwinek_wewn'] ?? '') === '1')) $kat = '_strony';
    }
    $plik = __DIR__ . '/' . $kat . '/' . $klucz . '-' . ($widok === 'm' ? 'Mobile' : 'Desktop') . '.html';
    $html = edycje_zastosuj((string)file_get_contents($plik), $klucz);
    header('Content-Type: text/html; charset=utf-8');
    header('Vary: User-Agent, Cookie');
    if ($prywatna || $wPanelu) {
        header('X-Robots-Tag: noindex, nofollow');
        header('Cache-Control: private, no-store');
    } else {
        header('Cache-Control: public, max-age=60');
    }
    if ($wPanelu) {
        $v = (string)@filemtime(__DIR__ . '/assets/panel-edytor.js');
        $html = str_replace('</body>', '<script src="/assets/panel-edytor.js?v=' . $v . '" data-strona="' . htmlspecialchars($klucz) . '"></script></body>', $html);
    }
    echo $html;
}

if ($path === 'zaloguj') {
    $blad = false;
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        if (password_verify((string)($_POST['haslo'] ?? ''), HASLO_HASH)) {
            setcookie(CIASTKO, $token, ['expires' => time() + 60 * 86400, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
            header('Location: /', true, 303);
            exit;
        }
        $blad = true;
        usleep(800000);
    }
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');
    require __DIR__ . '/_zaloguj.php';
    exit;
}
if ($path === 'wyloguj') {
    setcookie(CIASTKO, '', ['expires' => time() - 3600, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
    header('Location: /', true, 303);
    exit;
}

// panel do edycji strony dla gabinetu (_panel.php); funkcje panelu są potrzebne też podglądowi stron
if ($path === 'panel' || strpos($path, 'panel/') === 0) {
    require __DIR__ . '/_panel.php';
    exit;
}
if (isset($_GET['panel'])) {
    require_once __DIR__ . '/_panel-auth.php';
}

// sprzedaż voucherów (Stripe): zamówienie, webhook, podziękowanie, voucher do wydruku
if ($path === 'voucher' || strpos($path, 'voucher/') === 0) {
    require __DIR__ . '/_vouchery.php';
    exit;
}

if ($path === 'formularz') {
    require __DIR__ . '/_formularz.php';
    exit;
}

// dokumenty prawne są publiczne także przed startem (strona „Wkrótce” do nich linkuje)
const DOKUMENTY = ['polityka-prywatnosci' => '_polityka-prywatnosci.php', 'regulamin' => '_regulamin.php', 'regulamin-voucherow' => '_regulamin-voucherow.php'];
if (isset(DOKUMENTY[$path])) {
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: public, max-age=300');
    require __DIR__ . '/' . DOKUMENTY[$path];
    exit;
}

if (!$wpuszczony || (PRZED_STARTEM && $path === '' && isset($_GET['wkrotce']))) {
    if ($path === '') {
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
        require __DIR__ . '/_wkrotce.php';
        exit;
    }
    header('Location: /', true, 302);
    exit;
}

if (isset(STRONY[$path])) {
    wyslij_strone(STRONY[$path], PRZED_STARTEM);
    exit;
}

http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex');
require __DIR__ . '/_404.php';
