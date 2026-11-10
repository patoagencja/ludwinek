<?php
// Sprzedaż voucherów przez Stripe Checkout. Wołane z index.php dla ścieżek voucher/…
//   POST /voucher/zamow        wybór z /vouchery → sesja płatności Stripe (cena liczona tu, z cennika na stronie)
//   POST /voucher/stripe       webhook Stripe (checkout.session.completed / async_payment_succeeded)
//   GET  /voucher/dziekujemy   powrót po płatności: potwierdzenie i kod (też wydaje voucher, gdyby webhook się spóźnił)
//   GET  /voucher/<KOD>?t=…    voucher do wydruku / zapisania jako PDF
// Klucz Stripe i sekret webhooka: _panel-dane/_stripe.php (zapisuje je wdrożenie z sekretów repo, nie ma ich w kodzie).
// Sprzedane vouchery: _panel-dane/_vouchery.json (podgląd i realizacja w /panel/vouchery).
declare(strict_types=1);

require_once __DIR__ . '/_vouchery-dane.php';

// ---------------------------------------------------------------------------------------------------------------------

$akcja = trim(substr($path, strlen('voucher')), '/');
$metoda = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($akcja === 'zamow') {
    if ($metoda !== 'POST') vj(405, ['ok' => false]);
    if (!stripe_konfig()) vj(503, ['ok' => false, 'blad' => 'Sprzedaż voucherów jeszcze nie działa. Zadzwoń: ' . DANE['telefon'] . '.']);
    $mail = trim((string)($_POST['email'] ?? ''));
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL) || preg_match('/[\s,;<>]/', $mail)) vj(422, ['ok' => false, 'bledy' => ['mail' => 'Sprawdź adres e-mail, np. anna@gmail.com.']]);
    if (($_POST['zgoda'] ?? '') !== '1') vj(422, ['ok' => false, 'bledy' => ['ok' => 'Zaakceptuj regulamin voucherów, żeby przejść dalej.']]);
    $w = voucher_wybor($_POST);
    if (!$w) vj(422, ['ok' => false, 'blad' => 'Nie rozpoznaję wybranego vouchera. Odśwież stronę i wybierz go jeszcze raz.']);
    [$nazwa, $szcz, $kwota, $gab, $slug] = $w;
    if ($kwota < 200 || $kwota > 2000000) vj(422, ['ok' => false, 'blad' => 'Nieprawidłowa kwota vouchera.']);
    $baza = 'https://' . ($_SERVER['HTTP_HOST'] ?? DANE['domena']);
    if (!preg_match('/^https:\/\/(localhost|127\.0\.0\.1)(:\d+)?$|^https:\/\/(www\.)?ludwinekaesthetic\.pl$/', $baza)) $baza = 'https://' . DANE['domena'];
    if (getenv('LA_STRIPE_API')) $baza = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');   // test lokalny
    $meta = ['sklep' => 'ludwinek-vouchery', 'nazwa' => $nazwa, 'szczegoly' => mb_substr($szcz, 0, 400), 'cena_gabinet' => (string)$gab, 'zabieg' => $slug];
    $s = stripe_api('POST', '/v1/checkout/sessions', [
        'mode' => 'payment',
        'locale' => 'pl',
        'customer_email' => $mail,
        'line_items' => [[
            'quantity' => 1,
            'price_data' => ['currency' => 'pln', 'unit_amount' => $kwota, 'product_data' => [
                'name' => $nazwa,
                'description' => trim(($szcz ? $szcz . '. ' : '') . 'Ważny ' . VOUCHER_MIESIECY . ' miesięcy od zakupu. Kod przyjdzie e-mailem zaraz po płatności.'),
            ]],
        ]],
        'metadata' => $meta,
        'payment_intent_data' => ['description' => $nazwa . ($szcz ? ' (' . $szcz . ')' : ''), 'metadata' => $meta],
        'success_url' => $baza . '/voucher/dziekujemy?s={CHECKOUT_SESSION_ID}',
        'cancel_url' => $baza . '/vouchery?anulowano=1' . ($slug ? '#v-' . $slug : ''),
    ]);
    if (!empty($s['_blad']) || empty($s['url'])) {
        error_log('Stripe: ' . ($s['_blad'] ?? 'brak url'));
        vj(502, ['ok' => false, 'blad' => 'Nie udało się otworzyć płatności. Spróbuj za chwilę albo zadzwoń: ' . DANE['telefon'] . '.']);
    }
    vj(200, ['ok' => true, 'url' => $s['url'], 'kwota' => $kwota / 100, 'nazwa' => $nazwa, 'zabieg' => $slug]);
}

if ($akcja === 'stripe') {
    if ($metoda !== 'POST') vj(405, ['ok' => false]);
    $k = stripe_konfig();
    $tresc = (string)file_get_contents('php://input');
    $podpis = (string)($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
    $t = 0; $v1 = [];
    foreach (explode(',', $podpis) as $cz) {
        [$a, $b] = array_pad(explode('=', trim($cz), 2), 2, '');
        if ($a === 't') $t = (int)$b; elseif ($a === 'v1') $v1[] = $b;
    }
    $ok = $k && !empty($k['whsec']) && $t && abs(time() - $t) < 600;
    if ($ok) {
        $ok = false;
        $oczek = hash_hmac('sha256', $t . '.' . $tresc, (string)$k['whsec']);
        foreach ($v1 as $x) if (hash_equals($oczek, $x)) $ok = true;
    }
    if (!$ok) vj(400, ['ok' => false, 'blad' => 'zły podpis']);
    $e = json_decode($tresc, true);
    $typ = (string)($e['type'] ?? '');
    if (in_array($typ, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
        voucher_wydaj((array)($e['data']['object'] ?? []));
    }
    vj(200, ['ok' => true]);
}

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

if ($akcja === 'dziekujemy') {
    $id = (string)($_GET['s'] ?? '');
    $v = null; $czeka = false;
    if (preg_match('/^cs_[A-Za-z0-9_]{10,200}$/', $id) && stripe_konfig()) {
        $s = stripe_api('GET', '/v1/checkout/sessions/' . $id);
        if (empty($s['_blad'])) {
            $v = voucher_wydaj($s);
            if (!$v && ($s['status'] ?? '') === 'complete') $czeka = true;   // płatność w toku (np. przelew)
        }
    }
    require __DIR__ . '/_voucher-widok.php';
    exit;
}

if (preg_match('/^LA-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $akcja)) {
    $t = (string)($_GET['t'] ?? '');
    $v = null;
    if (hash_equals(substr(hash_hmac('sha256', $akcja, voucher_sekret()), 0, 20), $t)) {
        foreach (vouchery() as $x) if ($x['kod'] === $akcja) $v = $x;
    }
    if (!$v) { http_response_code(404); require __DIR__ . '/_404.php'; exit; }
    $druk = true;
    require __DIR__ . '/_voucher-widok.php';
    exit;
}

http_response_code(404);
require __DIR__ . '/_404.php';
