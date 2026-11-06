<?php
// Formularz „Zapytaj o termin” z /kontakt: POST /formularz (JSON), wysyłka mailem do gabinetu.
// Ochrona przed spamem: ukryte pole „strona” (wypełniają je tylko boty), minimalny czas wypełniania (pole „t”,
// liczone przez skrypt strony), limit zgłoszeń z jednego adresu IP i odrzucanie wiadomości z wieloma linkami.
// Kopii do osoby wysyłającej nie wysyłamy (formularz nie może służyć do wysyłania maili na cudze adresy).
declare(strict_types=1);
require_once __DIR__ . '/_dane.php';
date_default_timezone_set('Europe/Warsaw');

const DO_KOGO = 'ludwinekaesthetic@gmail.com';
const NADAWCA = 'formularz@ludwinekaesthetic.pl';
const LIMIT = 5;          // zgłoszeń z jednego IP
const LIMIT_OKNO = 3600;  // w ciągu sekund

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

function odpowiedz(int $kod, array $dane): void
{
    http_response_code($kod);
    echo json_encode($dane, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    odpowiedz(405, ['ok' => false]);
}

$pole = static function (string $k, int $max): string {
    $v = $_POST[$k] ?? '';
    if (!is_string($v)) return '';
    $v = trim(str_replace(["\r\n", "\r"], "\n", $v));
    $v = preg_replace('/[^\P{C}\n\t]/u', '', $v) ?? '';   // bez znaków sterujących
    return mb_substr($v, 0, $max);
};

$imie = preg_replace('/\s+/u', ' ', $pole('name', 120)) ?? '';
$tel = preg_replace('/\s+/u', ' ', $pole('tel', 30)) ?? '';
$mail = $pole('email', 160);
$zabieg = preg_replace('/\s+/u', ' ', $pole('zabieg', 80)) ?? '';
$termin = preg_replace('/\s+/u', ' ', $pole('termin', 160)) ?? '';
$wiad = $pole('msg', 3000);
$zgoda = ($_POST['zgoda'] ?? '') === '1';

// boty: ukryte pole wypełnione albo formularz „wypełniony” w mniej niż 3 s; udajemy sukces, nic nie wysyłamy
$czas = (int)($_POST['t'] ?? 0);
if ($pole('strona', 200) !== '' || $czas < 3000) {
    odpowiedz(200, ['ok' => true]);
}

$bledy = [];
$cyfry = preg_replace('/\D/', '', $tel) ?? '';
if (mb_strlen($imie) < 2) $bledy['name'] = 'Wpisz imię i nazwisko.';
if ($cyfry === '') $bledy['tel'] = 'Wpisz numer telefonu, żebym mogła oddzwonić.';
elseif (strlen($cyfry) < 9 || strlen($cyfry) > 12 || preg_match('/[^\d\s()+.-]/', $tel)) $bledy['tel'] = 'Sprawdź numer telefonu, np. 601 234 567.';
if ($mail !== '' && (!filter_var($mail, FILTER_VALIDATE_EMAIL) || preg_match('/[\s,;<>]/', $mail))) $bledy['mail'] = 'Sprawdź adres e-mail, np. anna@gmail.com.';
if (!$zgoda) $bledy['ok'] = 'Zaznacz zgodę, żebym mogła się z Tobą skontaktować.';
if ($bledy) odpowiedz(422, ['ok' => false, 'bledy' => $bledy]);

if (preg_match_all('~https?://|www\.~i', $imie . ' ' . $termin . ' ' . $wiad) > 2) {
    odpowiedz(200, ['ok' => true]);
}

// limit zgłoszeń z jednego IP (plik w katalogu tymczasowym serwera, IP zapisane jako skrót)
$ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$plik = rtrim(sys_get_temp_dir(), '/') . '/ludwinek-formularz-' . substr(hash('sha256', 'ludwinek|' . $ip), 0, 24);
$teraz = time();
$slady = [];
if (is_file($plik)) {
    foreach (explode(',', (string)@file_get_contents($plik)) as $x) {
        if ((int)$x > $teraz - LIMIT_OKNO) $slady[] = (int)$x;
    }
}
if (count($slady) >= LIMIT) {
    odpowiedz(429, ['ok' => false, 'blad' => 'limit']);
}

$temat = 'Zapytanie ze strony: ' . $imie . ($zabieg !== '' ? ' (' . $zabieg . ')' : '');
$tresc = "Nowe zapytanie z formularza „Zapytaj o termin” na " . DANE['domena'] . "\n\n"
    . "Imię i nazwisko: $imie\n"
    . "Telefon: $tel\n"
    . 'E-mail: ' . ($mail !== '' ? $mail : '(nie podano)') . "\n"
    . 'Zabieg: ' . ($zabieg !== '' ? $zabieg : '(nie wybrano)') . "\n"
    . 'Preferowany termin: ' . ($termin !== '' ? $termin : '(nie podano)') . "\n\n"
    . "Wiadomość:\n" . ($wiad !== '' ? $wiad : '(brak)') . "\n\n"
    . "--\nWysłano " . date('d.m.Y H:i') . ". Osoba zaznaczyła, że zapoznała się z informacją o przetwarzaniu danych.\n"
    . ($mail !== '' ? "Odpowiedz na tego maila, żeby napisać do tej osoby.\n" : '');

$naglowki = [
    'From' => mb_encode_mimeheader('Strona Ludwinek Aesthetic', 'UTF-8', 'Q') . ' <' . NADAWCA . '>',
    'MIME-Version' => '1.0',
    'Content-Type' => 'text/plain; charset=UTF-8',
    'Content-Transfer-Encoding' => '8bit',
];
if ($mail !== '') {
    $naglowki['Reply-To'] = $mail;
}

$wyslane = @mail(DO_KOGO, mb_encode_mimeheader($temat, 'UTF-8', 'Q'), $tresc, $naglowki, '-f' . NADAWCA);
if (!$wyslane) {
    odpowiedz(500, ['ok' => false, 'blad' => 'poczta']);
}
$slady[] = $teraz;
@file_put_contents($plik, implode(',', $slady), LOCK_EX);
odpowiedz(200, ['ok' => true]);
