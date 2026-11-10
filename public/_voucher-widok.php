<?php
// Widoki voucherów (z _vouchery.php): $druk = voucher do wydruku/PDF; inaczej strona „Dziękuję” po płatności ($v, $czeka).
// Voucher to karta podarunkowa: awers (logo, zabieg, kod, ważność) i rewers (jak skorzystać); na wydruku obie na A4 z linią cięcia.
$h = static fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$druk = !empty($druk);
$lm = file_get_contents(__DIR__ . '/assets/logo-mark.svg');
$lw = file_get_contents(__DIR__ . '/assets/logo-word.svg');
$tytul = $druk ? 'Voucher ' . $v['kod'] : ($v ? 'Dziękuję za zakup vouchera' : 'Płatność za voucher');
$LAU = '<svg class="lau" viewBox="0 0 60 100" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1"><path d="M44 98 Q4 56 38 6"/><ellipse cx="27.2" cy="87.7" rx="10.2" ry="4.1" transform="rotate(-169.3 27.2 87.7)"/><ellipse cx="37.5" cy="79.8" rx="10.2" ry="4.1" transform="rotate(-85.3 37.5 79.8)"/><ellipse cx="19.6" cy="72.0" rx="9.8" ry="3.9" transform="rotate(-157.2 19.6 72.0)"/><ellipse cx="30.9" cy="66.7" rx="9.8" ry="3.9" transform="rotate(-73.2 30.9 66.7)"/><ellipse cx="16.3" cy="55.9" rx="9.4" ry="3.8" transform="rotate(-143.1 16.3 55.9)"/><ellipse cx="28.0" cy="53.6" rx="9.4" ry="3.8" transform="rotate(-59.1 28.0 53.6)"/><ellipse cx="17.2" cy="39.9" rx="9.0" ry="3.6" transform="rotate(-128.4 17.2 39.9)"/><ellipse cx="28.6" cy="40.6" rx="9.0" ry="3.6" transform="rotate(-44.4 28.6 40.6)"/><ellipse cx="21.9" cy="24.1" rx="8.6" ry="3.4" transform="rotate(-114.9 21.9 24.1)"/><ellipse cx="32.3" cy="27.3" rx="8.6" ry="3.4" transform="rotate(-30.9 32.3 27.3)"/><ellipse cx="30.1" cy="8.4" rx="8.1" ry="3.3" transform="rotate(-103.6 30.1 8.4)"/><ellipse cx="39.2" cy="13.3" rx="8.1" ry="3.3" transform="rotate(-19.6 39.2 13.3)"/><ellipse cx="40.0" cy="3.0" rx="5" ry="2.4" transform="rotate(-55.8 40.0 3.0)"/></svg>';

/** Awers i rewers karty podarunkowej. */
$karta = static function (array $v) use ($h, $lm, $lw, $LAU): string {
    $kwotowy = ($v['zabieg'] ?? '') === '' || strpos($v['nazwa'], 'kwotowy') !== false;
    $nazwa = preg_replace('/^Voucher: /', '', $v['nazwa']);
    $uzyty = ($v['status'] ?? '') === 'wykorzystany';
    $tel = DANE['telefon'];
    $tytul = $kwotowy
        ? '<p class="tyt kw">' . $h(zl((int)$v['kwota'])) . '</p><p class="pod">do wykorzystania na dowolne zabiegi</p>'
        : '<p class="tyt">' . $h($nazwa) . '</p>' . ($v['szczegoly'] ? '<p class="pod">' . $h($v['szczegoly']) . '</p>' : '');
    return '<div class="karty">'
        . '<section class="karta awers' . ($uzyty ? ' uzyty' : '') . '" aria-label="Voucher">'
        . '<span class="znak" aria-hidden="true">' . $lm . '</span>'
        . '<div class="gora"><span class="logo">' . $lm . $lw . '</span><span class="nr">Nr ' . $h($v['kod']) . '</span></div>'
        . '<div class="srodek"><p class="nad">Voucher podarunkowy</p>'
        . '<div class="wien">' . $LAU . '<div class="tt">' . $tytul . '</div>' . str_replace('class="lau"', 'class="lau p"', $LAU) . '</div></div>'
        . '<div class="dol">'
        . '<div><span class="l">Kod vouchera</span><span class="kod">' . $h($v['kod']) . '</span></div>'
        . '<div><span class="l">Ważny do</span><span class="w">' . $h(voucher_data($v['wazny_do'])) . '</span></div>'
        . '<div class="adr"><span class="l">Gabinet</span><span class="w">' . $h(DANE['adres']) . '</span></div>'
        . '</div>'
        . ($uzyty ? '<span class="stempel">Wykorzystany</span>' : '')
        . '</section>'
        . '<div class="ciecie" aria-hidden="true"><span>✂</span></div>'
        . '<section class="karta rewers" aria-label="Jak skorzystać z vouchera">'
        . '<div class="rl"><span class="logo">' . $lm . '</span><div><p class="nad">Ludwinek Aesthetic</p><p class="rh">Jak skorzystać <em>z vouchera</em></p></div></div>'
        . '<ol class="kroki">'
        . '<li><b>Umów wizytę</b><span>przez Booksy albo telefonicznie: ' . $h($tel) . '</span></li>'
        . '<li><b>Podaj kod vouchera</b><span>' . $h($v['kod']) . '</span></li>'
        . '<li><b>Przyjdź do gabinetu</b><span>zabieg poprzedza krótka konsultacja i kwalifikacja</span></li>'
        . '</ol>'
        . '<p class="drob">Voucher na okaziciela, ważny do ' . $h(voucher_data($v['wazny_do'])) . '. Nie podlega wymianie na gotówkę. '
        . 'Regulamin: ' . $h(DANE['domena']) . '/regulamin-voucherow · ' . $h(DANE['adres']) . ' · ' . $h(DANE['domena']) . '</p>'
        . '</section></div>';
};
?><!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $h($tytul) ?> · Ludwinek Aesthetic</title>
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="/assets/fonts.css">
<?php if (!$druk): ?><script src="/assets/zgody.js" defer></script><?php endif; ?>
<style>
:root{--bg:#FAF8F5;--alt:#EFEBE4;--ink:#141414;--soft:#3A3A3A;--mute:#6F675C;--line:#D8D2C8;--gold:#C9A97F;--gold2:#E6D2B0;--dark:#1F1E1C;--acc:#6e133b;--serif:'Cormorant Garamond',Garamond,serif;--sans:'Inter',system-ui,sans-serif}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--soft);font:400 16px/1.65 var(--sans);-webkit-font-smoothing:antialiased}
a{color:var(--ink);text-underline-offset:3px}
.top{display:flex;align-items:center;justify-content:space-between;padding:14px clamp(20px,5vw,64px);border-bottom:1px solid var(--line)}
.top .brand{display:flex;align-items:center;gap:14px;color:var(--ink);text-decoration:none}.top .lm{height:36px;width:auto}.top .lw{height:26px;width:auto}
main{max-width:860px;margin:0 auto;padding:44px 20px 72px}
.kick{margin:0;font:500 11px/1.5 var(--sans);letter-spacing:.16em;text-transform:uppercase;color:var(--mute)}
h1{margin:10px 0 14px;font:300 clamp(38px,7vw,58px)/1.02 var(--serif);color:var(--ink)}h1 em{font-style:italic}
.btn{display:inline-flex;align-items:center;justify-content:center;min-height:50px;padding:0 22px;background:var(--acc);color:#fff;border:0;font:500 12px/1 var(--sans);letter-spacing:.14em;text-transform:uppercase;text-decoration:none;cursor:pointer}
.btn-o{background:transparent;color:var(--ink);border:1px solid var(--ink)}
.acts{display:flex;flex-wrap:wrap;gap:10px;margin:26px 0 18px}
.sm{font-size:14px;color:var(--mute)}

/* ---- karta podarunkowa ---- */
.karty{display:flex;flex-direction:column;align-items:center;gap:22px;margin-top:26px;container-type:inline-size}
.karta{position:relative;width:100%;aspect-ratio:1.72;overflow:hidden;-webkit-print-color-adjust:exact;print-color-adjust:exact;box-shadow:0 30px 60px -28px rgba(20,20,20,.45)}
.karta::before{content:"";position:absolute;inset:3.2cqw;border:1px solid rgba(201,169,127,.55);pointer-events:none}
.karta::after{content:"";position:absolute;inset:3.9cqw;border:1px solid rgba(201,169,127,.25);pointer-events:none}
.awers{display:flex;flex-direction:column;padding:6.6cqw 7.4cqw 6cqw;color:#F4EEE6;
  background:radial-gradient(120% 90% at 100% 0%,rgba(110,19,59,.55) 0%,rgba(110,19,59,0) 55%),radial-gradient(90% 80% at 0% 100%,rgba(201,169,127,.12) 0%,rgba(201,169,127,0) 60%),linear-gradient(160deg,#2A2523 0%,#1B1918 60%,#151414 100%)}
.awers .znak{position:absolute;right:-6cqw;bottom:-14cqw;height:62cqw;color:var(--gold);opacity:.07;pointer-events:none}
.awers .znak svg{height:100%;width:auto}
.gora{position:relative;display:flex;align-items:center;justify-content:space-between;gap:3cqw}
.gora .logo{display:flex;align-items:center;gap:1.8cqw;color:#F4EEE6}
.gora .logo .lm{height:5.4cqw;width:auto}.gora .logo .lw{height:3.6cqw;width:auto}
.nr{font:500 1.25cqw/1 var(--sans);letter-spacing:.24em;text-transform:uppercase;color:rgba(230,210,176,.7)}
.srodek{position:relative;flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}
.nad{margin:0 0 1.4cqw;font:500 1.45cqw/1.4 var(--sans);letter-spacing:.42em;text-transform:uppercase;color:var(--gold)}
.wien{display:flex;align-items:center;justify-content:center;gap:2.4cqw;max-width:100%}
.wien .lau{flex:none;height:12cqw;width:auto;color:var(--gold);opacity:.9}
.wien .lau.p{transform:scaleX(-1)}
.tt{min-width:0}
.tyt{margin:0;font:300 italic 6.4cqw/1.02 var(--serif);color:#FFFFFF;letter-spacing:.005em;text-wrap:balance}
.tyt.kw{font-size:8.6cqw;font-style:normal}
.pod{margin:1.1cqw 0 0;font:400 1.65cqw/1.4 var(--sans);letter-spacing:.06em;color:rgba(244,238,230,.75)}
.dol{position:relative;display:grid;grid-template-columns:1.25fr 1fr 1.35fr;border-top:1px solid rgba(201,169,127,.45);padding-top:2cqw}
.dol>div{display:flex;flex-direction:column;gap:.6cqw;padding:0 2cqw}
.dol>div:first-child{padding-left:0}.dol>div+div{border-left:1px solid rgba(201,169,127,.25)}
.dol .l{font:500 1.05cqw/1.2 var(--sans);letter-spacing:.26em;text-transform:uppercase;color:rgba(230,210,176,.75)}
.dol .kod{white-space:nowrap;font:500 2.5cqw/1.1 ui-monospace,'SF Mono',Menlo,Consolas,monospace;letter-spacing:.14em;color:#fff}
.dol .w{font:400 1.75cqw/1.3 var(--sans);color:#F4EEE6}
.stempel{position:absolute;right:9cqw;top:36%;transform:rotate(-12deg);padding:1cqw 2.4cqw;border:2px solid rgba(255,255,255,.75);color:#fff;font:700 2.4cqw/1 var(--sans);letter-spacing:.2em;text-transform:uppercase}
.uzyty .srodek,.uzyty .dol{opacity:.45}
.ciecie{position:relative;width:100%;border-top:1px dashed var(--line);display:none}
.ciecie span{position:absolute;left:-2px;top:-11px;font-size:14px;color:var(--mute);background:var(--bg)}
.rewers{display:flex;flex-direction:column;padding:6.6cqw 7.4cqw 5.6cqw;background:linear-gradient(160deg,#FBF8F3 0%,#F2ECE3 100%);color:var(--soft)}
.rewers::before{border-color:rgba(201,169,127,.7)}.rewers::after{border-color:rgba(201,169,127,.35)}
.rl{display:flex;align-items:center;gap:2.4cqw}
.rl .logo{color:var(--ink)}.rl .logo .lm{height:5cqw;width:auto}
.rl .nad{margin:0 0 .6cqw;color:#8C6A3E}
.rh{margin:0;font:300 4.4cqw/1.05 var(--serif);color:var(--ink)}.rh em{font-style:italic}
.kroki{flex:1;display:grid;grid-template-columns:repeat(3,1fr);gap:3cqw;margin:2cqw 0 3cqw;padding:0;list-style:none;counter-reset:k;align-content:center}
.kroki li{counter-increment:k;display:flex;flex-direction:column;gap:.8cqw}
.kroki li::before{content:counter(k,decimal-leading-zero);font:300 italic 5.2cqw/1 var(--serif);color:#B08A57}
.kroki b{font:500 1.9cqw/1.3 var(--sans);color:var(--ink)}
.kroki span{font:400 1.6cqw/1.45 var(--sans);color:var(--soft)}
.drob{margin:0;padding-top:1.8cqw;border-top:1px solid rgba(201,169,127,.5);font:400 1.25cqw/1.5 var(--sans);color:var(--mute)}
/* wąskie ekrany: karta wyższa, większe litery */
@container (max-width:560px){
  .karta{aspect-ratio:auto;min-height:118cqw}
  .awers,.rewers{padding:9cqw 9cqw 8cqw}
  .karta::before{inset:4cqw}.karta::after{inset:5.2cqw}
  .gora .logo .lm{height:9cqw}.gora .logo .lw{height:6cqw}.nr{display:none}
  .srodek{padding:7cqw 0}
  .nad{font-size:2.6cqw;margin-bottom:3cqw}
  .wien .lau{height:20cqw}
  .tyt{font-size:10.5cqw}.tyt.kw{font-size:14cqw}.pod{font-size:3.3cqw;margin-top:2cqw}
  .dol{grid-template-columns:1fr 1fr;row-gap:4cqw;padding-top:4cqw}
  .dol>div{gap:1.2cqw;padding:0 3cqw}
  .dol>div:first-child{grid-column:1/-1}
  .dol>div:nth-child(2){border-left:0!important;padding-left:0}
  .dol .l{font-size:2.2cqw}.dol .kod{font-size:6cqw}.dol .w{font-size:3.4cqw}
  .stempel{font-size:4.4cqw;top:40%}
  .rl .logo .lm{height:11cqw}.rl .nad{font-size:2.6cqw}.rh{font-size:8cqw}
  .kroki{grid-template-columns:1fr;gap:5cqw;margin-top:6cqw}
  .kroki li::before{font-size:9cqw}.kroki b{font-size:3.8cqw}.kroki span{font-size:3.4cqw}
  .drob{font-size:2.8cqw;padding-top:4cqw;margin-top:6cqw}
}
/* wydruk: awers i rewers na A4, z linią cięcia */
@page{size:A4 portrait;margin:14mm}
@media print{
  .top,.acts,.np{display:none!important}
  body{background:#fff}main{padding:0;max-width:none}
  .karty{gap:0;margin:0}
  .karta{width:182mm;box-shadow:none;break-inside:avoid}
  .ciecie{display:block;width:182mm;margin:9mm 0}
}
@media (max-width:520px){.top .lw{display:none}}
</style>
</head>
<body>
<header class="top"><a class="brand" href="/" aria-label="Ludwinek Aesthetic, strona główna"><?= $lm ?><?= $lw ?></a></header>
<main>
<?php if ($druk || $v): ?>
<?php if (!$druk): ?>
<p class="kick np">Płatność przyjęta</p>
<h1 class="np">Dziękuję za zakup <em>vouchera.</em></h1>
<p class="np">Voucher wysłałam też na adres <b><?= $h($v['email']) ?></b>. Jeśli nie widzisz maila, zajrzyj do spamu albo do zakładki „Oferty”.</p>
<?php else: ?>
<p class="kick np">Voucher podarunkowy · Ludwinek Aesthetic</p>
<?php endif; ?>
<?= $karta($v) ?>
<div class="acts">
<button type="button" class="btn" onclick="window.print()">Drukuj / zapisz PDF</button>
<?php if (!$druk): ?><a class="btn btn-o" href="<?= $h(voucher_link($v['kod'])) ?>">Link do vouchera (do przesłania)</a><?php endif; ?>
</div>
<p class="sm np">Na wydruku awers i rewers mieszczą się na jednej kartce A4, z linią do wycięcia. Zasady, także 14 dni na odstąpienie od umowy: <a href="/regulamin-voucherow">regulamin voucherów</a>.</p>
<?php if (!$druk): ?>
<script>
/* pomiar zakupu (GA4 purchase) raz na zakup, gdy jest zgoda na analitykę */
(function () {
  var id = <?= json_encode($v['sesja']) ?>, k = 'la-zakup-' + id, n = 0;
  try { if (localStorage.getItem(k)) return; } catch (e) {}
  (function t() {
    if (window.laZdarzenie && (window.dataLayer || window.fbq)) {
      window.laZdarzenie('purchase', { transaction_id: id, value: <?= json_encode($v['kwota'] / 100) ?>, currency: 'PLN',
        items: [{ item_id: <?= json_encode($v['zabieg'] ?: 'voucher-kwotowy') ?>, item_name: <?= json_encode($v['nazwa']) ?>, item_category: 'Voucher', price: <?= json_encode($v['kwota'] / 100) ?>, quantity: 1 }] });
      try { localStorage.setItem(k, '1'); } catch (e) {}
    } else if (++n < 20) setTimeout(t, 500);
  })();
})();
</script>
<?php endif; ?>
<?php elseif (!empty($czeka)): ?>
<p class="kick">Płatność w toku</p>
<h1>Czekam na <em>potwierdzenie płatności.</em></h1>
<p>Bank jeszcze nie potwierdził płatności. Gdy to zrobi, voucher przyjdzie na Twój e-mail. Zwykle trwa to kilka minut.</p>
<div class="acts"><a class="btn btn-o" href="">Sprawdź ponownie</a></div>
<?php else: ?>
<p class="kick">Voucher</p>
<h1>Nie widzę <em>tej płatności.</em></h1>
<p>Jeśli płatność się udała, voucher przyjdzie e-mailem w ciągu kilku minut. Gdyby nie dotarł, napisz na <a href="mailto:<?= $h(DANE['email']) ?>"><?= $h(DANE['email']) ?></a> albo zadzwoń: <?= $h(DANE['telefon']) ?>.</p>
<div class="acts"><a class="btn btn-o" href="/vouchery">Wróć do voucherów</a></div>
<?php endif; ?>
</main>
</body>
</html>
