<?php
// Widoki voucherów (z _vouchery.php): $druk = voucher do wydruku/PDF; inaczej strona „Dziękuję” po płatności ($v, $czeka).
$h = static fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$druk = !empty($druk);
$lm = file_get_contents(__DIR__ . '/assets/logo-mark.svg');
$lw = file_get_contents(__DIR__ . '/assets/logo-word.svg');
$tytul = $druk ? 'Voucher ' . $v['kod'] : ($v ? 'Dziękuję za zakup vouchera' : 'Płatność za voucher');
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
:root{--bg:#FAF8F5;--alt:#EFEBE4;--ink:#141414;--soft:#3A3A3A;--mute:#6F675C;--line:#D8D2C8;--gold:#C9A97F;--dark:#1F1E1C;--acc:#6e133b;--serif:'Cormorant Garamond',Garamond,serif;--sans:'Inter',system-ui,sans-serif}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--soft);font:400 16px/1.65 var(--sans);-webkit-font-smoothing:antialiased}
a{color:var(--ink);text-underline-offset:3px}
.top{display:flex;align-items:center;justify-content:space-between;padding:14px clamp(20px,5vw,64px);border-bottom:1px solid var(--line)}
.brand{display:flex;align-items:center;gap:14px;color:var(--ink);text-decoration:none}.brand .lm{height:36px;width:auto}.brand .lw{height:26px;width:auto}
main{max-width:720px;margin:0 auto;padding:48px 20px 72px}
.kick{margin:0;font:500 11px/1.5 var(--sans);letter-spacing:.16em;text-transform:uppercase;color:var(--mute)}
h1{margin:10px 0 18px;font:300 clamp(38px,7vw,58px)/1.02 var(--serif);color:var(--ink)}h1 em{font-style:italic}
.v{position:relative;background:var(--dark);color:#FAF8F5;padding:34px 32px 30px;margin:26px 0}
.v .lbl{margin:0;font:500 10.5px/1.4 var(--sans);letter-spacing:.2em;text-transform:uppercase;color:var(--gold)}
.v .nm{margin:8px 0 4px;font:300 34px/1.1 var(--serif)}
.v .det{margin:0;color:#D8D2C8;font-size:15px}
.v .kod{margin:22px 0 2px;font:600 28px/1.2 ui-monospace,Menlo,Consolas,monospace;letter-spacing:.08em;color:#fff}
.v .row{display:flex;flex-wrap:wrap;gap:8px 28px;margin-top:18px;font-size:14px;color:#D8D2C8}.v .row b{color:#fff;font-weight:500}
.v .wm{position:absolute;right:28px;top:26px;height:60px;width:auto;color:#FAF8F5;opacity:.85}
.v.uzyty{opacity:.55}
.btn{display:inline-flex;align-items:center;justify-content:center;min-height:50px;padding:0 22px;background:var(--acc);color:#fff;border:0;font:500 12px/1 var(--sans);letter-spacing:.14em;text-transform:uppercase;text-decoration:none;cursor:pointer}
.btn-o{background:transparent;color:var(--ink);border:1px solid var(--ink)}
.acts{display:flex;flex-wrap:wrap;gap:10px;margin:8px 0 26px}
.box{margin:18px 0;padding:16px 20px;border-left:2px solid var(--gold);background:#fff;font-size:15px}
.sm{font-size:14px;color:var(--mute)}
@media print{.top,.acts,.np{display:none!important}body{background:#fff}main{padding:0}.v{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
@media (max-width:520px){.brand .lw{display:none}.v{padding:28px 22px 24px}.v .wm{height:44px;right:18px;top:20px}.v .kod{font-size:23px}}
</style>
</head>
<body>
<header class="top"><a class="brand" href="/" aria-label="Ludwinek Aesthetic, strona główna"><?= $lm ?><?= $lw ?></a></header>
<main>
<?php if ($druk || $v): ?>
<?php if (!$druk): ?>
<p class="kick">Płatność przyjęta</p>
<h1>Dziękuję za zakup <em>vouchera.</em></h1>
<p>Voucher wysłałam też na adres <b><?= $h($v['email']) ?></b>. Jeśli nie widzisz maila, zajrzyj do spamu albo do zakładki „Oferty”.</p>
<?php else: ?>
<p class="kick np">Voucher podarunkowy</p>
<?php endif; ?>
<div class="v<?= ($v['status'] ?? '') === 'wykorzystany' ? ' uzyty' : '' ?>">
<span class="wm" aria-hidden="true"><?= str_replace('<svg', '<svg style="height:100%;width:auto"', $lm) ?></span>
<p class="lbl">Voucher · Ludwinek Aesthetic</p>
<p class="nm"><?= $h(preg_replace('/^Voucher: /', '', $v['nazwa'])) ?></p>
<?php if ($v['szczegoly']): ?><p class="det"><?= $h($v['szczegoly']) ?></p><?php endif; ?>
<p class="lbl" style="margin-top:22px;color:#A9A195">Kod</p>
<p class="kod" style="margin-top:4px"><?= $h($v['kod']) ?></p>
<div class="row"><span>Ważny do <b><?= $h(voucher_data($v['wazny_do'])) ?></b></span><span><?= $h(DANE['adres']) ?></span><span>tel. <?= $h(DANE['telefon']) ?></span></div>
<?php if (($v['status'] ?? '') === 'wykorzystany'): ?><p class="det" style="margin-top:12px;color:#fff"><b>Voucher wykorzystany.</b></p><?php endif; ?>
</div>
<div class="acts">
<button type="button" class="btn" onclick="window.print()">Drukuj / zapisz PDF</button>
<?php if (!$druk): ?><a class="btn btn-o" href="<?= $h(voucher_link($v['kod'])) ?>">Voucher do wysłania</a><?php endif; ?>
</div>
<div class="box"><b>Jak skorzystać?</b> Umów wizytę przez Booksy albo zadzwoń: <a href="tel:+48<?= str_replace(' ', '', DANE['telefon']) ?>"><?= $h(DANE['telefon']) ?></a>, i podaj kod vouchera. Zabieg poprzedza krótka konsultacja i kwalifikacja.</div>
<p class="sm np">Zasady, także 14 dni na odstąpienie od umowy: <a href="/regulamin-voucherow">regulamin voucherów</a>.</p>
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
