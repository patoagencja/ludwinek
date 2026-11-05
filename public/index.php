<?php
// ludwinekaesthetic.pl: przed startem wszyscy widzą stronę „Wkrótce” (_wkrotce.php), a pełna strona otwiera się dopiero
// po haśle (/zaloguj). Pełna strona to dokumenty z _strony/: telefony dostają wersję Mobile, reszta Desktop
// (ciasteczko „widok” = m/d nadpisuje wybór; strony same je ustawiają, gdy okno nie pasuje do wersji).
// START STRONY: ustaw PRZED_STARTEM na false; wtedy pełna strona jest dla wszystkich, a /zaloguj nie jest potrzebne.
declare(strict_types=1);

const PRZED_STARTEM = true;
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
header('X-Diag: ' . str_replace(["","
"], '', json_encode(array_filter($_SERVER, function ($k) { return strpos($k, 'HTTP_') === 0 && $k !== 'HTTP_COOKIE' || in_array($k, ['HTTPS','SERVER_PORT','REQUEST_SCHEME','REMOTE_ADDR','SERVER_ADDR','SSL_PROTOCOL','REDIRECT_HTTPS'], true); }, ARRAY_FILTER_USE_KEY))));
$token = hash_hmac('sha256', 'ludwinek-podglad', HASLO_HASH);
$wpuszczony = !PRZED_STARTEM || hash_equals($token, (string)($_COOKIE[CIASTKO] ?? ''));

function wyslij_strone(string $klucz, bool $prywatna): void
{
    $widok = $_COOKIE['widok'] ?? '';
    if ($widok !== 'm' && $widok !== 'd') {
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        $widok = preg_match('/iPhone|iPod|Android.+Mobile|Mobile.+Firefox|Windows Phone|Opera Mini|BlackBerry|webOS/i', $ua) ? 'm' : 'd';
    }
    $plik = __DIR__ . '/_strony/' . $klucz . '-' . ($widok === 'm' ? 'Mobile' : 'Desktop') . '.html';
    header('Content-Type: text/html; charset=utf-8');
    header('Vary: User-Agent, Cookie');
    if ($prywatna) {
        header('X-Robots-Tag: noindex, nofollow');
        header('Cache-Control: private, no-store');
    } else {
        header('Cache-Control: public, max-age=300');
    }
    readfile($plik);
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

if (!$wpuszczony || ($path === '' && isset($_GET['wkrotce']))) {
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
