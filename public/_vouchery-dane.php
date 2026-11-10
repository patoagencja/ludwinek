<?php
// Vouchery: konfiguracja Stripe, cennik ze strony, lista sprzedanych, wydawanie i maile. Używają: _vouchery.php, _panel.php.
declare(strict_types=1);
require_once __DIR__ . '/_dane.php';
require_once __DIR__ . '/_edycje.php';
date_default_timezone_set('Europe/Warsaw');

const VOUCHER_MIESIECY = 12;
const VOUCHERY_PLIK = EDYCJE_KATALOG . '/_vouchery.json';
const VOUCHER_NADAWCA = 'vouchery@ludwinekaesthetic.pl';

function stripe_konfig(): ?array
{
    $p = EDYCJE_KATALOG . '/_stripe.php';
    if (!is_file($p)) return null;
    $k = require $p;
    return is_array($k) && !empty($k['klucz']) ? $k : null;
}

function stripe_api(string $metoda, string $sciezka, array $dane = []): array
{
    $k = stripe_konfig();
    if (!$k) return ['_blad' => 'brak konfiguracji'];
    $baza = rtrim((string)(getenv('LA_STRIPE_API') ?: ($k['api'] ?? 'https://api.stripe.com')), '/');
    $url = $baza . $sciezka;
    $body = http_build_query($dane, '', '&');
    if ($metoda === 'GET' && $body !== '') { $url .= '?' . $body; $body = ''; }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 25, CURLOPT_CUSTOMREQUEST => $metoda,
        CURLOPT_USERPWD => $k['klucz'] . ':', CURLOPT_HTTPHEADER => ['Stripe-Version: 2024-06-20'],
    ]);
    if ($metoda === 'POST') curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    $odp = curl_exec($ch);
    $kod = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $j = json_decode((string)$odp, true);
    if (!is_array($j)) return ['_blad' => 'brak odpowiedzi Stripe (HTTP ' . $kod . ')'];
    if ($kod >= 400) return ['_blad' => (string)($j['error']['message'] ?? 'błąd Stripe ' . $kod)];
    return $j;
}

function vj(int $kod, array $d): void
{
    http_response_code($kod);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function zl(int $grosze): string
{
    return number_format($grosze / 100, $grosze % 100 ? 2 : 0, ',', ' ') . ' zł';
}

/** Cennik voucherów prosto ze strony /vouchery (z poprawkami z panelu), żeby cena była zawsze ta sama co na stronie. */
function voucher_cennik(): array
{
    $html = edycje_zastosuj((string)file_get_contents(__DIR__ . '/_strony/Voucher-Desktop.html'), 'Voucher');
    if (!preg_match('/const D = (\{.*?\});\n/s', $html, $m)) return [];
    $d = json_decode($m[1], true);
    return is_array($d) ? $d : [];
}

/** Wybór z formularza → [nazwa, szczegóły, kwota w groszach, cena w gabinecie w groszach] albo null. */
function voucher_wybor(array $w): ?array
{
    $D = voucher_cennik();
    $liczba = static fn($x) => (int)preg_replace('/\D/', '', (string)$x);
    if (($w['typ'] ?? '') === 'kwota') {
        $va = $D['va'] ?? [];
        $i = (int)($w['va'] ?? -1);
        if (!isset($va[$i])) return null;
        $kw = $liczba($va[$i]) * 100;
        return $kw > 0 ? ['Voucher kwotowy', zl($kw) . ' do wykorzystania na dowolne zabiegi', $kw, 0, ''] : null;
    }
    foreach ($D['vz'] ?? [] as $t) {
        if (($t['slug'] ?? '') !== ($w['zabieg'] ?? null)) continue;
        $G = $t['g'] ?? [];
        if (!$G) return null;
        $gi = max(0, min((int)($w['g'] ?? 0), count($G) - 1));
        $R = $G[$gi][1] ?? [];
        if (!$R) return null;
        $ri = max(0, min((int)($w['r'] ?? 0), count($R) - 1));
        $r = $R[$ri];
        $det = array_values(array_filter([count($G) > 1 ? $G[$gi][0] : '', count($R) > 1 ? $r[0] : '']));
        $kw = (int)round((float)$r[1] * 100);
        return $kw > 0 ? ['Voucher: ' . $t['name'], implode(', ', $det), $kw, (int)round((float)($r[2] ?? 0) * 100), (string)$t['slug']] : null;
    }
    return null;
}

function voucher_sekret(): string
{
    $p = EDYCJE_KATALOG . '/_sekret.txt';
    if (!is_file($p)) {
        if (!is_dir(EDYCJE_KATALOG)) @mkdir(EDYCJE_KATALOG, 0755, true);
        @file_put_contents($p, bin2hex(random_bytes(32)), LOCK_EX);
    }
    return trim((string)@file_get_contents($p)) ?: 'ludwinek';
}

function voucher_link(string $kod): string
{
    return 'https://' . DANE['domena'] . '/voucher/' . $kod . '?t=' . substr(hash_hmac('sha256', $kod, voucher_sekret()), 0, 20);
}

/** Odczyt i zapis listy voucherów pod blokadą pliku. $zmien dostaje listę przez referencję. */
function vouchery(?callable $zmien = null): array
{
    if (!is_dir(EDYCJE_KATALOG)) @mkdir(EDYCJE_KATALOG, 0755, true);
    $f = fopen(VOUCHERY_PLIK, 'c+');
    if (!$f) return [];
    flock($f, LOCK_EX);
    $lista = json_decode((string)stream_get_contents($f), true);
    if (!is_array($lista)) $lista = [];
    $wynik = $lista;
    if ($zmien) {
        $wynik = $zmien($lista);
        ftruncate($f, 0); rewind($f);
        fwrite($f, (string)json_encode($lista, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        fflush($f);
    }
    flock($f, LOCK_UN); fclose($f);
    return is_array($wynik) ? $wynik : [];
}

function voucher_nowy_kod(array $lista): string
{
    $abc = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $zajete = array_column($lista, 'kod');
    do {
        $k = 'LA-';
        for ($i = 0; $i < 8; $i++) { $k .= $abc[random_int(0, strlen($abc) - 1)]; if ($i === 3) $k .= '-'; }
    } while (in_array($k, $zajete, true));
    return $k;
}

/** Wydanie vouchera po opłaconej sesji Stripe (bezpieczne do wielokrotnego wołania: drugi raz zwraca ten sam voucher). */
function voucher_wydaj(array $s): ?array
{
    if (($s['payment_status'] ?? '') !== 'paid' || ($s['object'] ?? '') !== 'checkout.session') return null;
    $m = $s['metadata'] ?? [];
    if (($m['sklep'] ?? '') !== 'ludwinek-vouchery') return null;
    $nowy = false;
    $v = vouchery(function (array &$lista) use ($s, $m, &$nowy) {
        foreach ($lista as $x) if (($x['sesja'] ?? '') === $s['id']) return $x;
        $teraz = time();
        $x = [
            'kod' => voucher_nowy_kod($lista), 'sesja' => (string)$s['id'], 'platnosc' => (string)($s['payment_intent'] ?? ''),
            'nazwa' => (string)($m['nazwa'] ?? 'Voucher'), 'szczegoly' => (string)($m['szczegoly'] ?? ''),
            'kwota' => (int)($s['amount_total'] ?? 0), 'cena_gabinet' => (int)($m['cena_gabinet'] ?? 0), 'zabieg' => (string)($m['zabieg'] ?? ''),
            'email' => (string)($s['customer_details']['email'] ?? $s['customer_email'] ?? ''),
            'kupujacy' => (string)($s['customer_details']['name'] ?? ''),
            'kupiono' => date('c', $teraz), 'wazny_do' => date('Y-m-d', strtotime('+' . VOUCHER_MIESIECY . ' months', $teraz)),
            'status' => 'aktywny', 'tryb' => !empty($s['livemode']) ? 'live' : 'test',
        ];
        $lista[] = $x;
        $nowy = true;
        return $x;
    });
    if ($nowy && $v) { voucher_mail_klient($v); voucher_mail_gabinet($v); }
    return $v ?: null;
}

function voucher_data(string $ymd): string
{
    $m = ['', 'stycznia', 'lutego', 'marca', 'kwietnia', 'maja', 'czerwca', 'lipca', 'sierpnia', 'września', 'października', 'listopada', 'grudnia'];
    $t = strtotime($ymd);
    return date('j', $t) . ' ' . $m[(int)date('n', $t)] . ' ' . date('Y', $t);
}

function voucher_wyslij(string $do, string $temat, string $tekst, string $html, string $odpowiedz = ''): bool
{
    $gr = 'la' . bin2hex(random_bytes(8));
    $nag = [
        'From' => mb_encode_mimeheader('Ludwinek Aesthetic', 'UTF-8', 'Q') . ' <' . VOUCHER_NADAWCA . '>',
        'Reply-To' => $odpowiedz ?: DANE['email'],
        'MIME-Version' => '1.0',
        'Content-Type' => 'multipart/alternative; boundary="' . $gr . '"',
    ];
    $tresc = "--$gr\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($tekst))
        . "--$gr\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($html)) . "--$gr--\r\n";
    return @mail($do, mb_encode_mimeheader($temat, 'UTF-8', 'B'), $tresc, $nag, '-f' . VOUCHER_NADAWCA);
}

function voucher_mail_klient(array $v): void
{
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) return;
    $h = static fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $link = voucher_link($v['kod']);
    $do = voucher_data($v['wazny_do']);
    $tel = DANE['telefon'];
    $tekst = "Dziękuję za zakup vouchera w Ludwinek Aesthetic!\n\n"
        . $v['nazwa'] . ($v['szczegoly'] ? ' (' . $v['szczegoly'] . ')' : '') . "\n"
        . 'Kod vouchera: ' . $v['kod'] . "\n"
        . 'Ważny do: ' . $do . "\n\n"
        . "Voucher do wydruku lub zapisania jako PDF:\n$link\n\n"
        . "Jak skorzystać: umów wizytę przez Booksy albo telefonicznie ($tel) i podaj kod vouchera. "
        . "Zabieg poprzedza krótka konsultacja i kwalifikacja.\n\n"
        . 'Regulamin voucherów: https://' . DANE['domena'] . "/regulamin-voucherow\n"
        . "Zapłacono: " . zl((int)$v['kwota']) . ". Potwierdzenie płatności przyszło osobno od Stripe.\n\n"
        . "Do zobaczenia!\nJulia Ludwinek\nLudwinek Aesthetic, " . DANE['adres'] . "\n";
    $html = '<!doctype html><html lang="pl"><body style="margin:0;background:#EFEBE4;font-family:Helvetica,Arial,sans-serif;color:#3A3A3A">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EFEBE4;padding:24px 12px"><tr><td align="center">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#FAF8F5">'
        . '<tr><td style="padding:28px 32px 8px;font:500 11px/1.4 Helvetica,Arial,sans-serif;letter-spacing:3px;text-transform:uppercase;color:#6F675C">Ludwinek Aesthetic</td></tr>'
        . '<tr><td style="padding:0 32px 18px;font:300 30px/1.15 Georgia,serif;color:#141414">Dziękuję za zakup <i>vouchera.</i></td></tr>'
        . '<tr><td style="padding:0 32px"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#1F1E1C;color:#FAF8F5">'
        . '<tr><td style="padding:24px 24px 6px;font:500 10px/1.4 Helvetica,Arial,sans-serif;letter-spacing:3px;text-transform:uppercase;color:#C9A97F">Voucher</td></tr>'
        . '<tr><td style="padding:0 24px 4px;font:300 26px/1.2 Georgia,serif">' . $h(preg_replace('/^Voucher: /', '', $v['nazwa'])) . '</td></tr>'
        . ($v['szczegoly'] ? '<tr><td style="padding:0 24px 4px;font:14px/1.5 Helvetica,Arial,sans-serif;color:#D8D2C8">' . $h($v['szczegoly']) . '</td></tr>' : '')
        . '<tr><td style="padding:16px 24px 4px;font:500 10px/1.4 Helvetica,Arial,sans-serif;letter-spacing:3px;text-transform:uppercase;color:#A9A195">Kod</td></tr>'
        . '<tr><td style="padding:0 24px 4px;font:600 26px/1.3 Courier,monospace;letter-spacing:2px;color:#FFFFFF">' . $h($v['kod']) . '</td></tr>'
        . '<tr><td style="padding:10px 24px 22px;font:14px/1.5 Helvetica,Arial,sans-serif;color:#D8D2C8">Ważny do ' . $h($do) . '</td></tr>'
        . '</table></td></tr>'
        . '<tr><td style="padding:22px 32px 6px"><a href="' . $h($link) . '" style="display:inline-block;background:#6e133b;color:#ffffff;text-decoration:none;padding:14px 22px;font:500 12px/1 Helvetica,Arial,sans-serif;letter-spacing:2px;text-transform:uppercase">Voucher do wydruku / PDF</a></td></tr>'
        . '<tr><td style="padding:14px 32px 0;font:15px/1.6 Helvetica,Arial,sans-serif"><b style="color:#141414">Jak skorzystać?</b><br>Umów wizytę przez Booksy albo zadzwoń: <a href="tel:+48' . str_replace(' ', '', $tel) . '" style="color:#141414">' . $h($tel) . '</a>, i podaj kod vouchera. Zabieg poprzedza krótka konsultacja i kwalifikacja.</td></tr>'
        . '<tr><td style="padding:14px 32px 0;font:13px/1.6 Helvetica,Arial,sans-serif;color:#6F675C">Zapłacono ' . $h(zl((int)$v['kwota'])) . '. Potwierdzenie płatności przyszło osobno od Stripe. Zasady, także 14 dni na odstąpienie od umowy: <a href="https://' . DANE['domena'] . '/regulamin-voucherow" style="color:#6F675C">regulamin voucherów</a>.</td></tr>'
        . '<tr><td style="padding:22px 32px 28px;font:15px/1.6 Helvetica,Arial,sans-serif">Do zobaczenia!<br><b style="color:#141414">Julia Ludwinek</b><br><span style="color:#6F675C;font-size:13px">Ludwinek Aesthetic, ' . $h(DANE['adres']) . '</span></td></tr>'
        . '</table></td></tr></table></body></html>';
    voucher_wyslij($v['email'], 'Twój voucher Ludwinek Aesthetic: ' . $v['kod'], $tekst, $html);
}

function voucher_mail_gabinet(array $v): void
{
    $tekst = "Sprzedany voucher" . ($v['tryb'] === 'test' ? ' (TEST, bez prawdziwej płatności)' : '') . "\n\n"
        . $v['nazwa'] . ($v['szczegoly'] ? ' (' . $v['szczegoly'] . ')' : '') . "\n"
        . 'Kod: ' . $v['kod'] . "\nZapłacono: " . zl((int)$v['kwota']) . "\nWażny do: " . voucher_data($v['wazny_do']) . "\n"
        . 'Kupujący: ' . trim($v['kupujacy'] . ' ' . $v['email']) . "\n\n"
        . "Lista voucherów i realizacja: https://" . DANE['domena'] . "/panel/vouchery\n";
    voucher_wyslij(DANE['email'], 'Sprzedany voucher ' . $v['kod'] . ': ' . zl((int)$v['kwota']), $tekst, '<pre style="font:14px/1.6 Helvetica,Arial,sans-serif">' . htmlspecialchars($tekst) . '</pre>', $v['email']);
}
