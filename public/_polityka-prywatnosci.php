<?php
// Polityka prywatności (RODO art. 13; cookies: Prawo komunikacji elektronicznej art. 399). Publiczna przed startem.
require_once __DIR__ . '/_dane.php';
$tytul = 'Polityka prywatności';
$opis = 'Jak Ludwinek Aesthetic przetwarza dane osobowe i jakich plików cookies używa strona ludwinekaesthetic.pl.';
ob_start();
?>
<p class="kick">Ludwinek Aesthetic</p>
<h1>Polityka prywatności</h1>
<p class="data">Obowiązuje od <?= dana('od') ?></p>

<nav class="spis" aria-label="Spis treści"><b>W skrócie</b>
<ol>
<li><a href="#administrator">Kto przetwarza Twoje dane</a></li>
<li><a href="#cele">Po co i na jakiej podstawie</a></li>
<li><a href="#zdrowie">Informacje o zdrowiu</a></li>
<li><a href="#odbiorcy">Komu przekazujemy dane</a></li>
<li><a href="#poza-eog">Przekazywanie danych poza Europejski Obszar Gospodarczy</a></li>
<li><a href="#okres">Jak długo przechowujemy dane</a></li>
<li><a href="#prawa">Twoje prawa</a></li>
<li><a href="#cookies">Pliki cookies</a></li>
<li><a href="#zmiany">Zmiany polityki</a></li>
</ol></nav>

<h2 id="administrator">1. Kto przetwarza Twoje dane</h2>
<p>Administratorem Twoich danych osobowych jest <?= administrator() ?>,
<?= dana('adres') ?>, NIP <?= dana('nip') ?>, REGON <?= dana('regon') ?> (dalej: „gabinet”, „ja”).</p>
<p>W sprawach danych osobowych napisz na <a href="mailto:<?= DANE['email'] ?>"><?= dana('email') ?></a>, zadzwoń pod
<a href="tel:+48<?= str_replace(' ', '', DANE['telefon']) ?>"><?= dana('telefon') ?></a> albo napisz na adres gabinetu.
Gabinet nie wyznaczył inspektora ochrony danych, bo nie ma takiego obowiązku.</p>
<p>Polityka dotyczy strony <?= dana('domena') ?>, kontaktu z gabinetem (formularz, e-mail, telefon, wiadomości w mediach
społecznościowych) oraz obsługi wizyt w gabinecie.</p>

<h2 id="cele">2. Po co i na jakiej podstawie przetwarzamy dane</h2>
<div class="tw"><table>
<thead><tr><th>Cel</th><th>Jakie dane</th><th>Podstawa prawna (RODO)</th></tr></thead>
<tbody>
<tr><td>Odpowiedź na zapytanie z formularza „Zapytaj o termin”, e-mail, telefon lub wiadomość</td><td>imię i nazwisko, telefon, e-mail (jeśli podasz), wybrany zabieg, preferowany termin, treść wiadomości</td><td>art. 6 ust. 1 lit. b (działania na Twoje żądanie przed umówieniem wizyty) i lit. f (prawnie uzasadniony interes: odpowiadanie na pytania)</td></tr>
<tr><td>Umówienie i przeprowadzenie wizyty lub zabiegu, konsultacja, kwalifikacja do zabiegu, kontakt w sprawie wizyty</td><td>dane kontaktowe, termin wizyty, historia zabiegów, wywiad i zgoda na zabieg (karta klienta)</td><td>art. 6 ust. 1 lit. b (umowa o usługę); dla informacji o zdrowiu art. 9 ust. 2 lit. a (Twoja wyraźna zgoda)</td></tr>
<tr><td>Rozliczenia, rachunki i faktury, obowiązki podatkowe i księgowe</td><td>dane do rachunku lub faktury, kwoty</td><td>art. 6 ust. 1 lit. c (obowiązek prawny)</td></tr>
<tr><td>Ustalenie, dochodzenie lub obrona roszczeń, np. przy reklamacji zabiegu</td><td>dane potrzebne do sprawy</td><td>art. 6 ust. 1 lit. f (prawnie uzasadniony interes)</td></tr>
<tr><td>Prowadzenie profili gabinetu w mediach społecznościowych (Instagram, Facebook) i odpowiadanie na komentarze i wiadomości</td><td>nazwa profilu, treść wiadomości lub komentarza</td><td>art. 6 ust. 1 lit. f (prawnie uzasadniony interes: komunikacja z obserwującymi)</td></tr>
</tbody></table></div>
<p>Podanie danych jest dobrowolne, ale bez danych kontaktowych nie odpowiem na zapytanie, a bez wywiadu i zgody nie
przeprowadzę zabiegu. Nie podejmuję decyzji wyłącznie automatycznie i nie profiluję Cię w sposób, który wywołuje wobec
Ciebie skutki prawne.</p>
<p>Wizyty rezerwujesz w serwisie Booksy. Booksy przetwarza dane konta i rezerwacji na własnych zasadach, opisanych w jego
regulaminie i polityce prywatności. Gabinet dostaje z Booksy dane potrzebne do obsługi Twojej wizyty.</p>

<h2 id="zdrowie">3. Informacje o zdrowiu</h2>
<p>Przed zabiegiem pytam o stan zdrowia (np. przyjmowane leki, alergie, przeciwwskazania), bo bez tego nie da się
bezpiecznie zakwalifikować Cię do zabiegu. Te informacje zapisuję w karcie klienta i przetwarzam na podstawie Twojej
wyraźnej zgody (art. 9 ust. 2 lit. a RODO). Zgodę możesz cofnąć w każdej chwili, ale bez tych informacji nie mogę
wykonać zabiegu, a cofnięcie zgody nie wpływa na zgodność z prawem wcześniejszego przetwarzania.</p>
<p>W formularzu kontaktowym i w wiadomościach proszę nie podawać informacji o zdrowiu: omówimy je na konsultacji, gdzie
wypełniasz kartę klienta. Jeśli mimo to je napiszesz, wykorzystam je tylko do odpowiedzi i usunę razem z wiadomością.</p>

<h2 id="odbiorcy">4. Komu przekazujemy dane</h2>
<p>Dane przekazuję tylko wtedy, gdy jest to potrzebne do celów opisanych wyżej, i tylko tym, którzy pomagają w prowadzeniu
gabinetu i strony:</p>
<ul>
<li>dostawcy hostingu strony i serwera poczty (LH.pl),</li>
<li>dostawcy poczty e-mail, na którą trafiają wiadomości i zapytania z formularza (Google, usługa Gmail),</li>
<li>serwisowi rezerwacji Booksy, przy rezerwacji wizyty,</li>
<li>firmie, która technicznie obsługuje i rozwija stronę (Patoagencja sp. z o.o.),</li>
<li>biuru rachunkowemu, przy rozliczeniach,</li>
<li>organom publicznym, gdy wymaga tego prawo.</li>
</ul>
<p>Podmioty, które przetwarzają dane w imieniu gabinetu, robią to na podstawie umowy powierzenia i tylko według poleceń
gabinetu.</p>

<h2 id="poza-eog">5. Przekazywanie danych poza Europejski Obszar Gospodarczy</h2>
<p>Część dostawców (Google przy poczcie Gmail, Meta przy profilach w mediach społecznościowych) może przetwarzać dane także
w Stanach Zjednoczonych. Odbywa się to na podstawie decyzji
Komisji Europejskiej o odpowiednim stopniu ochrony (EU‑US Data Privacy Framework) albo standardowych klauzul umownych
zatwierdzonych przez Komisję. Kopię zabezpieczeń możesz otrzymać, pisząc do gabinetu.</p>

<h2 id="okres">6. Jak długo przechowujemy dane</h2>
<ul>
<li>Zapytania z formularza, e-maile i wiadomości: przez czas rozmowy, a potem do 12 miesięcy, chyba że umówisz wizytę.</li>
<li>Karta klienta i historia zabiegów: przez czas korzystania z usług gabinetu, a potem do upływu terminu przedawnienia
roszczeń (zwykle 6 lat, licząc do końca roku kalendarzowego).</li>
<li>Rachunki, faktury i dokumenty księgowe: 5 lat od końca roku, w którym powstał obowiązek podatkowy.</li>
<li>Informacje o zdrowiu w karcie klienta: jak karta klienta, chyba że wcześniej cofniesz zgodę.</li>
<li>Pliki cookies: przez okres podany w tabeli w punkcie 8.</li>
</ul>

<h2 id="prawa">7. Twoje prawa</h2>
<p>Masz prawo:</p>
<ul>
<li>dostępu do swoich danych i otrzymania ich kopii,</li>
<li>sprostowania danych, które są nieprawidłowe lub niepełne,</li>
<li>usunięcia danych, gdy nie ma już podstawy do ich przetwarzania,</li>
<li>ograniczenia przetwarzania,</li>
<li>przeniesienia danych, które przetwarzam na podstawie umowy lub zgody,</li>
<li>sprzeciwu wobec przetwarzania opartego na prawnie uzasadnionym interesie,</li>
<li>cofnięcia zgody w dowolnym momencie, bez wpływu na zgodność z prawem przetwarzania przed jej cofnięciem,</li>
<li>wniesienia skargi do Prezesa Urzędu Ochrony Danych Osobowych (ul. Stawki 2, 00‑193 Warszawa, uodo.gov.pl).</li>
</ul>
<p>Żeby skorzystać z praw, napisz na <a href="mailto:<?= DANE['email'] ?>"><?= dana('email') ?></a>. Odpowiem
najpóźniej w ciągu miesiąca.</p>

<h2 id="cookies">8. Pliki cookies</h2>
<p>Cookies to małe pliki zapisywane w Twojej przeglądarce. Strona używa wyłącznie cookies niezbędnych, bez których nie
działałaby poprawnie; na nie nie potrzeba zgody (art. 399 Prawa komunikacji elektronicznej). Strona nie używa dziś
narzędzi analitycznych ani reklamowych (np. Google Analytics, Meta Pixel). Jeśli to się zmieni, takie narzędzia będą
działać dopiero po Twojej zgodzie, wyrażonej w okienku zgód, a ta polityka wymieni je z nazwy. Zgody zmienisz w każdej
chwili przez link <a href="#ustawienia-cookies">„Ustawienia cookies”</a> w stopce strony; cookies usuniesz też
w ustawieniach przeglądarki.</p>
<div class="tw"><table>
<thead><tr><th>Nazwa</th><th>Rodzaj</th><th>Do czego służy</th><th>Jak długo</th></tr></thead>
<tbody>
<tr><td>zgody</td><td>niezbędne</td><td>zapamiętuje Twój wybór w okienku zgód (tylko jeśli go dokonasz)</td><td>12 miesięcy</td></tr>
<tr><td>widok</td><td>niezbędne</td><td>zapamiętuje, czy wyświetlać wersję strony na telefon, czy na komputer</td><td>12 miesięcy</td></tr>
<tr><td>ludwinek_podglad</td><td>niezbędne</td><td>logowanie do podglądu strony przed jej startem (tylko osoby z hasłem)</td><td>60 dni</td></tr>
</tbody></table></div>
<p>Czcionki są na serwerze gabinetu, a strona nie osadza filmów ani wtyczek społecznościowych, więc samo wejście na
stronę nie przekazuje danych innym firmom. Mapa Google na stronie Kontakt wczytuje się dopiero, gdy klikniesz „Pokaż
mapę Google”; wtedy Google (Google Ireland Ltd.) otrzymuje m.in. Twój adres IP i może zapisać swoje pliki cookies,
zgodnie z polityką prywatności Google. Linki do Booksy, Google Maps, Instagrama czy
Facebooka prowadzą do tych serwisów; po ich otwarciu obowiązują ich zasady prywatności.</p>

<h2 id="zmiany">9. Zmiany polityki</h2>
<p>Politykę zmieniam, gdy zmieniają się przepisy albo sposób działania gabinetu lub strony, np. przy uruchomieniu
sprzedaży voucherów online. Aktualna wersja jest zawsze pod tym adresem, z datą, od której obowiązuje.</p>
<?php
$tresc = ob_get_clean();
require __DIR__ . '/_dokument.php';
