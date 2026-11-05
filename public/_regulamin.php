<?php
// Regulamin strony (ustawa o świadczeniu usług drogą elektroniczną, art. 8). Publiczny przed startem.
// Regulamin sprzedaży voucherów dojdzie osobno, razem ze sprzedażą voucherów online.
require_once __DIR__ . '/_dane.php';
$tytul = 'Regulamin strony';
$opis = 'Regulamin korzystania ze strony ludwinekaesthetic.pl i formularza kontaktowego gabinetu Ludwinek Aesthetic.';
ob_start();
?>
<p class="kick">Ludwinek Aesthetic</p>
<h1>Regulamin strony</h1>
<p class="data">Obowiązuje od <?= dana('od') ?></p>

<h2 id="s1">§ 1. Kto prowadzi stronę</h2>
<ol>
<li>Stronę <?= dana('domena') ?> (dalej: „strona”) prowadzi <?= administrator() ?>,
<?= dana('adres') ?>, NIP <?= dana('nip') ?>, REGON <?= dana('regon') ?>, wpisana do Centralnej Ewidencji i Informacji
o Działalności Gospodarczej (dalej: „gabinet”).</li>
<li>Kontakt z gabinetem: e-mail <a href="mailto:<?= DANE['email'] ?>"><?= dana('email') ?></a>, telefon
<a href="tel:+48<?= str_replace(' ', '', DANE['telefon']) ?>"><?= dana('telefon') ?></a>, adres gabinetu podany wyżej.</li>
<li>Regulamin określa zasady korzystania ze strony i z usług udostępnianych przez nią drogą elektroniczną. Jest dostępny
bezpłatnie pod adresem <?= dana('domena') ?>/regulamin; możesz go zapisać lub wydrukować.</li>
</ol>

<h2 id="s2">§ 2. Usługi na stronie</h2>
<ol>
<li>Przez stronę gabinet bezpłatnie świadczy usługi:
<ol type="a">
<li>udostępniania informacji o gabinecie, zabiegach, cenach i zasadach wizyt,</li>
<li>formularza „Zapytaj o termin”, który pozwala wysłać gabinetowi pytanie lub prośbę o kontakt,</li>
<li>zapamiętania Twoich ustawień plików cookies.</li>
</ol></li>
<li>Umowa o korzystanie z informacji na stronie zaczyna się, gdy otwierasz stronę, i kończy, gdy ją zamykasz. Umowa
o usługę formularza zaczyna się z chwilą wysłania formularza i kończy z chwilą odpowiedzi gabinetu. Możesz zrezygnować
z usług w każdej chwili, opuszczając stronę lub prosząc o usunięcie wysłanej wiadomości.</li>
<li>Rezerwacja wizyt odbywa się w zewnętrznym serwisie Booksy, na zasadach jego regulaminu. Sama strona nie służy do
rezerwacji ani do zawierania umów o zabiegi.</li>
</ol>

<h2 id="s3">§ 3. Wymagania techniczne</h2>
<ol>
<li>Do korzystania ze strony potrzebujesz urządzenia z dostępem do internetu i aktualnej przeglądarki internetowej
z włączoną obsługą JavaScript i plików cookies.</li>
<li>Do wysłania formularza potrzebny jest numer telefonu lub adres e-mail, na który gabinet może odpowiedzieć.</li>
<li>Korzystanie z internetu wiąże się z typowymi zagrożeniami (np. złośliwe oprogramowanie, przechwycenie danych). Strona
korzysta z szyfrowanego połączenia (https); warto też dbać o aktualne oprogramowanie urządzenia.</li>
</ol>

<h2 id="s4">§ 4. Zasady korzystania</h2>
<ol>
<li>Nie wolno przesyłać przez stronę treści bezprawnych, obraźliwych ani wprowadzających w błąd, ani podawać cudzych
danych bez zgody tej osoby.</li>
<li>Informacje na stronie, w tym ceny zabiegów, mają charakter informacyjny i nie są ofertą w rozumieniu art. 66
Kodeksu cywilnego. O doborze, przebiegu i cenie zabiegu decyduje się po konsultacji w gabinecie, która obejmuje
kwalifikację do zabiegu i sprawdzenie przeciwwskazań.</li>
<li>Opisy efektów zabiegów są ogólne. Efekt zależy od indywidualnych cech skóry i organizmu, a zdjęcia „przed i po”
pokazują rezultaty konkretnych osób, publikowane za ich zgodą.</li>
<li>Teksty, zdjęcia, logo i wygląd strony są chronione prawem autorskim. Nie można ich kopiować ani wykorzystywać bez
zgody gabinetu, poza przypadkami dozwolonymi przez prawo.</li>
</ol>

<h2 id="s5">§ 5. Reklamacje</h2>
<ol>
<li>Uwagi i reklamacje dotyczące działania strony zgłoś na <a href="mailto:<?= DANE['email'] ?>"><?= dana('email') ?></a>
albo na adres gabinetu. Opisz, czego dotyczy zgłoszenie, i podaj dane do odpowiedzi.</li>
<li>Gabinet odpowie w ciągu 14 dni, w ten sam sposób, w jaki przyszła reklamacja, chyba że poprosisz o inny.</li>
<li>Reklamacje zabiegów rozpatrywane są na zasadach Kodeksu cywilnego; zgłoś je w gabinecie albo tymi samymi kanałami.</li>
<li>Jeśli jesteś konsumentem, możesz skorzystać z pozasądowych sposobów rozwiązywania sporów, np. pomocy miejskiego lub
powiatowego rzecznika konsumentów albo wojewódzkiego inspektoratu Inspekcji Handlowej. Informacje znajdziesz na
<a href="https://prawakonsumenta.uokik.gov.pl" rel="noopener" target="_blank">prawakonsumenta.uokik.gov.pl</a>.</li>
</ol>

<h2 id="s6">§ 6. Dane osobowe i cookies</h2>
<p>Zasady przetwarzania danych osobowych i używania plików cookies opisuje <a href="/polityka-prywatnosci">polityka
prywatności</a>.</p>

<h2 id="s7">§ 7. Postanowienia końcowe</h2>
<ol>
<li>Gabinet może zmienić regulamin z ważnych przyczyn, np. zmiany przepisów lub zakresu usług na stronie. Zmiana nie
dotyczy formularzy wysłanych przed jej wejściem w życie.</li>
<li>W sprawach nieuregulowanych stosuje się prawo polskie, w szczególności ustawę o świadczeniu usług drogą
elektroniczną, Kodeks cywilny i ustawę o prawach konsumenta. Regulamin nie ogranicza praw konsumenta wynikających
z przepisów.</li>
</ol>
<?php
$tresc = ob_get_clean();
require __DIR__ . '/_dokument.php';
