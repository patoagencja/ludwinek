<?php
// Regulamin sprzedaży voucherów online (ustawa o prawach konsumenta, Kodeks cywilny). Strona /regulamin-voucherow.
require_once __DIR__ . '/_dane.php';
$tytul = 'Regulamin voucherów';
$opis = 'Zasady zakupu i realizacji voucherów podarunkowych Ludwinek Aesthetic: płatność, ważność 12 miesięcy, odstąpienie od umowy, reklamacje.';
$tel = '<a href="tel:+48' . str_replace(' ', '', DANE['telefon']) . '">' . dana('telefon') . '</a>';
$mail = '<a href="mailto:' . DANE['email'] . '">' . dana('email') . '</a>';
ob_start();
?>
<p class="kick">Ludwinek Aesthetic</p>
<h1>Regulamin voucherów</h1>
<p class="data">Obowiązuje od <?= dana('od') ?></p>

<nav class="spis" aria-label="Spis treści"><b>Spis treści</b>
<ol>
<li><a href="#s1">Sprzedawca</a></li>
<li><a href="#s2">Czym jest voucher</a></li>
<li><a href="#s3">Zamówienie i płatność</a></li>
<li><a href="#s4">Dostarczenie vouchera</a></li>
<li><a href="#s5">Ważność i realizacja</a></li>
<li><a href="#s6">Odstąpienie od umowy (14 dni)</a></li>
<li><a href="#s7">Reklamacje</a></li>
<li><a href="#s8">Dane osobowe</a></li>
<li><a href="#s9">Postanowienia końcowe</a></li>
<li><a href="#wzor">Wzór formularza odstąpienia od umowy</a></li>
</ol></nav>

<h2 id="s1">§ 1. Sprzedawca</h2>
<ol>
<li>Vouchery sprzedaje <?= administrator() ?>, adres siedziby: <?= dana('siedziba') ?>, adres gabinetu: <?= dana('adres') ?>,
NIP <?= dana('nip') ?>, REGON <?= dana('regon') ?> (dalej: „gabinet”).</li>
<li>Kontakt: e-mail <?= $mail ?>, telefon <?= $tel ?>, adres gabinetu podany wyżej.</li>
<li>Regulamin jest dostępny bezpłatnie pod adresem <?= dana('domena') ?>/regulamin-voucherow; możesz go zapisać lub wydrukować.
W sprawach nieuregulowanych tu stosuje się <a href="/regulamin">regulamin strony</a> i przepisy prawa polskiego.</li>
</ol>

<h2 id="s2">§ 2. Czym jest voucher</h2>
<ol>
<li>Voucher to podarunkowy bon na okaziciela (bez imienia), uprawniający do skorzystania z usług gabinetu. Może go wykorzystać
kupujący albo osoba, której go podaruje.</li>
<li>Rodzaje voucherów:
<ol type="a">
<li><b>voucher na zabieg</b>: uprawnia do jednego wskazanego zabiegu (w wybranym wariancie, jeśli zabieg ma warianty);</li>
<li><b>voucher kwotowy</b>: uprawnia do zabiegów i usług gabinetu o łącznej wartości do kwoty vouchera; można go
wykorzystać jednorazowo albo w częściach, w czasie jego ważności.</li>
</ol></li>
<li>Cena vouchera na zabieg kupionego online może być niższa niż cena tego zabiegu w gabinecie; cenę online widać przy
zamówieniu. Voucher nie podlega wymianie na gotówkę, a niewykorzystana część vouchera kwotowego nie jest wypłacana,
z zastrzeżeniem § 6 i § 7.</li>
<li>Skorzystanie z zabiegu wymaga konsultacji i kwalifikacji. Jeśli osoba z voucherem nie może mieć wskazanego zabiegu
(np. z powodu przeciwwskazań), gabinet zaproponuje inny zabieg o tej samej lub niższej wartości albo zamianę
na voucher kwotowy o wartości zapłaconej ceny.</li>
</ol>

<h2 id="s3">§ 3. Zamówienie i płatność</h2>
<ol>
<li>Voucher zamawiasz na stronie <a href="/vouchery"><?= dana('domena') ?>/vouchery</a>: wybierasz rodzaj vouchera, podajesz
adres e-mail, akceptujesz regulamin i klikasz „Kupuję i płacę”. To oznacza złożenie zamówienia z obowiązkiem zapłaty.</li>
<li>Płatność obsługuje Stripe Payments Europe Ltd. (Dublin, Irlandia). Dostępne metody to m.in. BLIK, Przelewy24 i karta
płatnicza; aktualną listę widać na stronie płatności. Gabinet nie otrzymuje ani nie przechowuje danych Twojej karty.</li>
<li>Ceny są cenami brutto w złotych polskich. Zakup nie wiąże się z dodatkowymi kosztami dostawy.</li>
<li>Umowa sprzedaży vouchera zostaje zawarta z chwilą zaksięgowania płatności. Potwierdzenie zawarcia umowy, treść
vouchera i link do tego regulaminu otrzymasz e-mailem.</li>
<li>Jeśli płatność się nie powiedzie albo ją przerwiesz, umowa nie zostaje zawarta i możesz złożyć zamówienie ponownie.</li>
</ol>

<h2 id="s4">§ 4. Dostarczenie vouchera</h2>
<ol>
<li>Voucher z unikalnym kodem wysyłamy na podany adres e-mail zaraz po potwierdzeniu płatności, zwykle w ciągu kilku minut.
Voucher widać też od razu na stronie po płatności; można go wydrukować albo zapisać jako PDF.</li>
<li>Jeśli voucher nie dotrze w ciągu 24 godzin (sprawdź też folder spam), napisz na <?= $mail ?>.</li>
<li>Kod vouchera chroni przed jego użyciem przez inne osoby. Nie udostępniaj go publicznie. Gabinet realizuje voucher
po podaniu ważnego kodu i nie odpowiada za wykorzystanie kodu przez osobę, której go przekazano.</li>
</ol>

<h2 id="s5">§ 5. Ważność i realizacja</h2>
<ol>
<li>Voucher jest ważny <b>12 miesięcy od dnia zakupu</b>. Data ważności jest na voucherze. Do końca ważności trzeba
skorzystać z usługi (sama rezerwacja terminu nie wystarcza).</li>
<li>Termin wizyty umawiasz przez Booksy albo telefonicznie (<?= $tel ?>), podając kod vouchera. Termin zależy od dostępności
gabinetu.</li>
<li>Odwołanie i przekładanie wizyty umówionej z vouchera odbywa się na zasadach jak dla zwykłych wizyt, opisanych na stronie
<a href="/informacje#zasady-rezerwacji">Informacje</a>.</li>
<li>Jeśli zabieg jest droższy niż wartość vouchera, różnicę dopłacasz w gabinecie. Voucher na zabieg obejmuje cały zabieg
w wariancie podanym na voucherze.</li>
<li>Gabinet może przedłużyć ważność vouchera, np. gdy wizyta nie mogła się odbyć z przyczyn leżących po stronie gabinetu.</li>
</ol>

<h2 id="s6">§ 6. Odstąpienie od umowy (14 dni)</h2>
<ol>
<li>Jeśli kupujesz jako konsument (albo przedsiębiorca na prawach konsumenta, art. 38a ustawy o prawach konsumenta),
możesz odstąpić od umowy zakupu vouchera bez podawania przyczyny w ciągu <b>14 dni od dnia zakupu</b>.</li>
<li>Wystarczy, że przed upływem tego terminu wyślesz oświadczenie o odstąpieniu, np. e-mailem na <?= $mail ?> albo listem
na adres gabinetu. Możesz użyć <a href="#wzor">wzoru formularza</a>, ale nie musisz.</li>
<li>Zwrócimy całą zapłaconą kwotę niezwłocznie, nie później niż w ciągu 14 dni od otrzymania oświadczenia, tą samą metodą
płatności, której użyto (chyba że zgodzisz się na inną, bez kosztów dla Ciebie). Kod vouchera zostaje wtedy unieważniony.</li>
<li>Jeśli w ciągu 14 dni skorzystasz z vouchera w całości (zabieg został wykonany na Twoje wyraźne żądanie), prawo
odstąpienia wygasa. Przy voucherze kwotowym wykorzystanym w części zwracamy niewykorzystaną część.</li>
</ol>

<h2 id="s7">§ 7. Reklamacje</h2>
<ol>
<li>Reklamację dotyczącą vouchera albo zakupu zgłoś e-mailem na <?= $mail ?> albo listem na adres gabinetu. Opisz problem,
podaj kod vouchera i to, czego oczekujesz.</li>
<li>Odpowiemy w ciągu 14 dni. Reklamacje zabiegów rozpatrujemy na zasadach jak dla zabiegów opłaconych w gabinecie.</li>
<li>Możesz też skorzystać z pozasądowych sposobów rozwiązywania sporów, np. pomocy miejskiego lub powiatowego rzecznika
konsumentów albo Wojewódzkiego Inspektoratu Inspekcji Handlowej.</li>
</ol>

<h2 id="s8">§ 8. Dane osobowe</h2>
<p>Przy zakupie vouchera przetwarzamy adres e-mail, imię i nazwisko (jeśli podasz je w płatności), dane zamówienia i informacje
o płatności, żeby wykonać umowę i rozliczyć sprzedaż. Szczegóły, w tym Twoje prawa, są w
<a href="/polityka-prywatnosci">polityce prywatności</a>.</p>

<h2 id="s9">§ 9. Postanowienia końcowe</h2>
<ol>
<li>Regulamin może się zmienić, np. przy zmianie przepisów albo metod płatności. Do voucherów kupionych przed zmianą stosuje
się regulamin z dnia zakupu.</li>
<li>Regulamin nie ogranicza praw konsumenta wynikających z przepisów prawa.</li>
</ol>

<h2 id="wzor">Wzór formularza odstąpienia od umowy</h2>
<div class="box">
<p>(formularz ten należy wypełnić i odesłać tylko w przypadku chęci odstąpienia od umowy)</p>
<p>Adresat: <?= administrator() ?>, <?= dana('adres') ?>, e-mail: <?= dana('email') ?></p>
<p>Ja/My(*) niniejszym informuję/informujemy(*) o moim/naszym(*) odstąpieniu od umowy sprzedaży vouchera:<br>
kod vouchera: ……………………………<br>
data zawarcia umowy: ……………………………<br>
imię i nazwisko konsumenta(-ów): ……………………………<br>
adres konsumenta(-ów): ……………………………<br>
podpis konsumenta(-ów) (tylko jeżeli formularz jest przesyłany w wersji papierowej): ……………………………<br>
data: ……………………………</p>
<p>(*) Niepotrzebne skreślić.</p>
</div>
<?php
$tresc = ob_get_clean();
require __DIR__ . '/_dokument.php';
