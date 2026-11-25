<?php
// Panel: promocje (/panel/promocje). Pasek z informacją u góry strony i rabat procentowy na ceny zabiegów.
require_once __DIR__ . '/_ustawienia.php';
$u = ustawienia();
$h = static fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$STRONY_LINK = ['' => 'bez linku', '/cennik' => 'Cennik', '/vouchery' => 'Vouchery', '/zabiegi' => 'Zabiegi', '/kontakt' => 'Kontakt'];
foreach (STRONY as $slug => $k) if ($slug !== '' && !isset($STRONY_LINK['/' . $slug])) $STRONY_LINK['/' . $slug] = ucfirst(str_replace('-', ' ', $slug));
$wlasny = $u['pasek']['link'] !== '' && !isset($STRONY_LINK[$u['pasek']['link']]);
?><!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="/assets/fonts.css">
<title>Promocje · Panel strony</title>
<style>
:root{--ink:#141414;--txt:#3A3A3A;--mut:#6F675C;--bg:#FAF8F5;--pap:#EFEBE4;--line:#D8D2C8;--acc:#6e133b;--gold:#C9A97F;--dark:#1F1E1C;--ok:#2f6b3a;--err:#b3261e}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--txt);font:400 15px/1.55 'Inter',system-ui,sans-serif}
button,input,select{font:inherit;color:inherit}
.top{display:flex;align-items:center;gap:14px;padding:10px 18px;background:var(--dark);color:#EDE7DE}
.top b{font:300 22px/1 'Cormorant Garamond',serif;color:#fff}.top .sp{flex:1}.top a{color:#EDE7DE;font-size:13px;text-decoration:none;opacity:.85}.top a:hover{opacity:1;text-decoration:underline}
main{max-width:760px;margin:0 auto;padding:26px 16px 60px}
h1{margin:0 0 4px;font:300 40px/1.05 'Cormorant Garamond',serif;color:var(--ink)}
.sub{margin:0 0 22px;color:var(--mut);font-size:14px}
.card{background:#fff;border:1px solid var(--line);padding:20px;margin-bottom:16px}
.card h2{margin:0 0 4px;font:300 28px/1.1 'Cormorant Garamond',serif;color:var(--ink)}
.card .opis{margin:0 0 14px;color:var(--mut);font-size:13.5px}
.przel{display:flex;gap:10px;align-items:center;font-weight:500;color:var(--ink);cursor:pointer;margin-bottom:14px}
.przel input{width:20px;height:20px;accent-color:var(--acc)}
label.l{display:block;margin:12px 0 6px;font:500 10.5px/1.4 'Inter',sans-serif;letter-spacing:.14em;text-transform:uppercase;color:var(--mut)}
input[type=text],input[type=number],input[type=date],select{width:100%;min-height:44px;padding:0 12px;border:1px solid var(--line);background:#fff;border-radius:0;font-size:16px}
.row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.podglad{margin-top:14px;padding:10px 14px;background:var(--acc);color:#fff;text-align:center;font-size:13px}
.podglad small{display:block;opacity:.8;font-size:11px}
.cena{margin-top:12px;font-size:14px}.cena s{opacity:.55;margin-right:6px}.cena b{color:var(--acc)}
.uwaga{margin:10px 0 0;font-size:12.5px;color:var(--mut)}
.btn{min-height:50px;padding:0 24px;border:0;background:var(--acc);color:#fff;font:500 12px/1 'Inter',sans-serif;letter-spacing:.14em;text-transform:uppercase;cursor:pointer}
.msg{margin:12px 0 0;font-size:14px}.msg.ok{color:var(--ok)}.msg.err{color:var(--err)}
@media (max-width:560px){.row{grid-template-columns:1fr}}
</style>
</head>
<body>
<header class="top"><b>Panel strony</b><span class="sp"></span><a href="/panel">Edycja strony</a><a href="/panel/vouchery">Vouchery</a><a href="/panel/wyloguj">Wyloguj</a></header>
<main>
<h1>Promocje</h1>
<p class="sub">Zmiany są na stronie od razu po zapisaniu (u gości najpóźniej po minucie). Pamiętaj, żeby te same ceny ustawić w Booksy.</p>
<form id="f">
<section class="card">
<h2>Pasek promocji</h2>
<p class="opis">Bordowy pasek na samej górze każdej strony, np. „Listopad: −20% na stymulatory tkankowe”.</p>
<label class="przel"><input type="checkbox" name="pasek_wl" value="1"<?= $u['pasek']['wl'] ? ' checked' : '' ?>> Pokazuj pasek</label>
<label class="l" for="pt">Tekst paska</label>
<input type="text" id="pt" name="pasek_tekst" maxlength="160" value="<?= $h($u['pasek']['tekst']) ?>" placeholder="np. Listopad: −20% na wszystkie zabiegi">
<div class="row">
<div><label class="l" for="pl">Po kliknięciu prowadzi do</label>
<select id="pl"><?php foreach ($STRONY_LINK as $k => $n): ?><option value="<?= $h($k) ?>"<?= $u['pasek']['link'] === $k ? ' selected' : '' ?>><?= $h($n) ?></option><?php endforeach; ?><option value="*"<?= $wlasny ? ' selected' : '' ?>>inny adres…</option></select>
<input type="text" id="plw" name="pasek_link" value="<?= $h($u['pasek']['link']) ?>" placeholder="/cennik albo https://…" style="margin-top:8px<?= $wlasny ? '' : ';display:none' ?>"></div>
<div><label class="l" for="pd">Pokazuj do (włącznie, opcjonalnie)</label><input type="date" id="pd" name="pasek_do" value="<?= $h($u['pasek']['do']) ?>"></div>
</div>
<div class="podglad" id="pp"></div>
</section>

<section class="card">
<h2>Rabat na zabiegi</h2>
<p class="opis">Przy wszystkich cenach zabiegów pojawi się przekreślona cena i cena po rabacie. Ceny voucherów zostają bez zmian.</p>
<label class="przel"><input type="checkbox" name="rabat_wl" value="1"<?= $u['rabat']['wl'] ? ' checked' : '' ?>> Włącz rabat</label>
<div class="row">
<div><label class="l" for="rp">Rabat (%)</label><input type="number" id="rp" name="rabat_procent" min="1" max="90" value="<?= $h($u['rabat']['procent'] ?: '') ?>" placeholder="np. 20"></div>
<div><label class="l" for="rd">Do (włącznie, opcjonalnie)</label><input type="date" id="rd" name="rabat_do" value="<?= $h($u['rabat']['do']) ?>"></div>
</div>
<p class="cena" id="rc"></p>
<p class="uwaga">Zgodnie z przepisami przy obniżce podajemy najniższą cenę z 30 dni przed nią. Strona pokazuje to w pasku automatycznie. Jeśli w ostatnich 30 dniach zmieniała się cena zabiegu, daj znać Danielowi.</p>
</section>
<button class="btn" type="submit">Zapisz</button>
<p class="msg" id="msg" role="status"></p>
</form>
</main>
<script>
(function () {
  var f = document.getElementById('f'), $ = function (i) { return document.getElementById(i); };
  function podglad() {
    var pr = +f.rabat_procent.value || 0, rab = f.rabat_wl.checked && pr > 0;
    var t = f.pasek_tekst.value.trim() || (rab ? 'Promocja: −' + pr + '% na zabiegi' : '');
    $('pp').style.display = (f.pasek_wl.checked && f.pasek_tekst.value.trim()) || rab ? '' : 'none';
    $('pp').innerHTML = '';
    var s = document.createElement('span'); s.textContent = t; $('pp').appendChild(s);
    if (rab) { var sm = document.createElement('small'); sm.textContent = 'Przekreślona cena to najniższa cena z 30 dni przed obniżką.'; $('pp').appendChild(sm); }
    $('rc').innerHTML = rab ? 'Przykład: <s>700 zł</s> <b>' + Math.round(700 * (100 - pr) / 100) + ' zł</b>' : '';
  }
  $('pl').addEventListener('change', function () { var w = this.value === '*'; $('plw').style.display = w ? '' : 'none'; if (!w) $('plw').value = this.value; });
  f.addEventListener('input', podglad); f.addEventListener('change', podglad); podglad();
  f.addEventListener('submit', function (e) {
    e.preventDefault();
    var fd = new FormData(f), m = $('msg');
    if (!f.pasek_wl.checked) fd.delete('pasek_wl');
    if (!f.rabat_wl.checked) fd.delete('rabat_wl');
    m.className = 'msg'; m.textContent = 'Zapisuję…';
    fetch('/panel/ustawienia', { method: 'POST', body: fd, headers: { 'X-Panel': '1' }, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) { m.className = 'msg ' + (j.ok ? 'ok' : 'err'); m.textContent = j.ok ? 'Zapisane. Zmiany są już na stronie.' : (j.blad || 'Nie udało się zapisać.'); })
      .catch(function () { m.className = 'msg err'; m.textContent = 'Brak połączenia. Spróbuj ponownie.'; });
  });
})();
</script>
</body>
</html>
