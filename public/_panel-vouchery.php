<?php
// Panel: sprzedane vouchery (/panel/vouchery). Wyszukiwanie po kodzie lub mailu, oznaczanie jako wykorzystany / zwrócony.
require_once __DIR__ . '/_vouchery-dane.php';
$lista = array_reverse(vouchery());
$h = static fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$suma = 0; foreach ($lista as $v) if (($v['tryb'] ?? '') === 'live' && ($v['status'] ?? '') !== 'zwrocony') $suma += (int)$v['kwota'];
$STATUS = ['aktywny' => 'Do wykorzystania', 'wykorzystany' => 'Wykorzystany', 'zwrocony' => 'Zwrócony (odstąpienie)'];
?><!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="/assets/fonts.css">
<title>Vouchery · Panel strony</title>
<style>
:root{--ink:#141414;--txt:#3A3A3A;--mut:#6F675C;--bg:#FAF8F5;--pap:#EFEBE4;--line:#D8D2C8;--acc:#6e133b;--gold:#C9A97F;--dark:#1F1E1C;--ok:#2f6b3a;--err:#b3261e}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--txt);font:400 15px/1.55 'Inter',system-ui,sans-serif}
button,input,select{font:inherit;color:inherit}
.top{display:flex;align-items:center;gap:14px;padding:10px 18px;background:var(--dark);color:#EDE7DE}
.top b{font:300 22px/1 'Cormorant Garamond',serif;color:#fff}.top .sp{flex:1}.top a{color:#EDE7DE;font-size:13px;text-decoration:none;opacity:.85}.top a:hover{opacity:1;text-decoration:underline}
main{max-width:980px;margin:0 auto;padding:26px 16px 60px}
h1{margin:0 0 4px;font:300 40px/1.05 'Cormorant Garamond',serif;color:var(--ink)}
.sub{margin:0 0 20px;color:var(--mut);font-size:14px}
.find{display:flex;gap:8px;margin-bottom:18px}.find input{flex:1;min-height:46px;padding:0 14px;border:1px solid var(--line);background:#fff;border-radius:0;font-size:16px}
.v{display:grid;grid-template-columns:1fr auto;gap:6px 16px;padding:16px;background:#fff;border:1px solid var(--line);margin-bottom:10px}
.v.wykorzystany,.v.zwrocony{background:var(--pap)}
.kod{font:600 18px/1.3 ui-monospace,Menlo,Consolas,monospace;letter-spacing:.05em;color:var(--ink)}
.nm{color:var(--ink);font-weight:500}.meta{font-size:13px;color:var(--mut)}
.st{justify-self:end;font:500 10.5px/1.4 'Inter',sans-serif;letter-spacing:.12em;text-transform:uppercase;padding:5px 8px;background:#E6EFE7;color:var(--ok);white-space:nowrap}
.v.wykorzystany .st{background:#E8E4DD;color:var(--mut)}.v.zwrocony .st{background:#F6E3E1;color:var(--err)}
.test{margin-left:6px;padding:2px 6px;background:#FFF1C9;color:#7a5a00;font:600 10px/1.4 'Inter',sans-serif;letter-spacing:.1em}
.act{grid-column:1/-1;display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:6px}
.act input{flex:1 1 220px;min-height:40px;padding:0 10px;border:1px solid var(--line);border-radius:0;font-size:14px}
.btn{min-height:40px;padding:0 14px;border:1px solid var(--acc);background:var(--acc);color:#fff;font:500 11px/1 'Inter',sans-serif;letter-spacing:.1em;text-transform:uppercase;cursor:pointer}
.btn-o{background:transparent;color:var(--ink);border-color:var(--line)}
.empty{padding:30px;text-align:center;color:var(--mut);background:#fff;border:1px solid var(--line)}
.wazny{color:var(--err)}
</style>
</head>
<body>
<header class="top"><b>Panel strony</b><span class="sp"></span><a href="/panel">Edycja strony</a><a href="/panel/wyloguj">Wyloguj</a></header>
<main>
<h1>Vouchery</h1>
<p class="sub"><?= count($lista) ?> sprzedanych<?= $suma ? ' · razem ' . $h(zl($suma)) : '' ?>. Gdy klientka przychodzi z voucherem, wpisz kod i po zabiegu kliknij „Wykorzystany”.</p>
<div class="find"><input id="q" type="search" placeholder="Szukaj: kod (np. LA-AB12-CD34), e-mail albo zabieg" autocomplete="off"></div>
<?php if (!$lista): ?><p class="empty">Nie ma jeszcze sprzedanych voucherów.</p><?php endif; ?>
<?php foreach ($lista as $v): $st = $v['status'] ?? 'aktywny'; $minal = $st === 'aktywny' && $v['wazny_do'] < date('Y-m-d'); ?>
<div class="v <?= $h($st) ?>" data-s="<?= $h(strtolower($v['kod'] . ' ' . $v['email'] . ' ' . $v['nazwa'] . ' ' . $v['szczegoly'] . ' ' . $v['kupujacy'])) ?>">
<div><span class="kod"><?= $h($v['kod']) ?></span><?php if (($v['tryb'] ?? '') === 'test'): ?><span class="test">TEST</span><?php endif; ?></div>
<span class="st"><?= $h($minal ? 'Po terminie' : $STATUS[$st] ?? $st) ?></span>
<div style="grid-column:1/-1"><span class="nm"><?= $h(preg_replace('/^Voucher: /', '', $v['nazwa'])) ?></span><?= $v['szczegoly'] ? ' · ' . $h($v['szczegoly']) : '' ?> · <b><?= $h(zl((int)$v['kwota'])) ?></b>
<div class="meta">Kupiono <?= $h(date('d.m.Y H:i', strtotime($v['kupiono']))) ?> · <span class="<?= $minal ? 'wazny' : '' ?>">ważny do <?= $h(date('d.m.Y', strtotime($v['wazny_do']))) ?></span> · <?= $h(trim($v['kupujacy'] . ' ' . $v['email'])) ?><?= !empty($v['notatka']) ? ' · Notatka: ' . $h($v['notatka']) : '' ?></div></div>
<div class="act" data-kod="<?= $h($v['kod']) ?>">
<input type="text" placeholder="Notatka (np. data zabiegu, ile zostało z kwoty)" value="<?= $h($v['notatka'] ?? '') ?>">
<?php if ($st !== 'wykorzystany'): ?><button type="button" class="btn" data-st="wykorzystany">Wykorzystany</button><?php endif; ?>
<?php if ($st !== 'aktywny'): ?><button type="button" class="btn btn-o" data-st="aktywny">Przywróć jako aktywny</button><?php endif; ?>
<?php if ($st === 'aktywny'): ?><button type="button" class="btn btn-o" data-st="aktywny">Zapisz notatkę</button><button type="button" class="btn btn-o" data-st="zwrocony">Zwrócony</button><?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</main>
<script>
document.getElementById('q').addEventListener('input', function () {
  var q = this.value.trim().toLowerCase();
  document.querySelectorAll('.v').forEach(function (v) { v.style.display = !q || v.getAttribute('data-s').indexOf(q) >= 0 ? '' : 'none'; });
});
document.addEventListener('click', function (e) {
  var b = e.target.closest('button[data-st]'); if (!b) return;
  var a = b.closest('.act'), st = b.getAttribute('data-st');
  if (st === 'zwrocony' && !confirm('Oznaczyć jako zwrócony? Zwrot pieniędzy zrób w Stripe (Płatności → ta płatność → Zwróć).')) return;
  var fd = new FormData(); fd.append('kod', a.getAttribute('data-kod')); fd.append('status', st); fd.append('notatka', a.querySelector('input').value);
  b.disabled = true;
  fetch('/panel/voucher-status', { method: 'POST', body: fd, headers: { 'X-Panel': '1' }, credentials: 'same-origin' })
    .then(function (r) { return r.json(); }).then(function (j) { if (j.ok) location.reload(); else { alert(j.blad || 'Nie udało się.'); b.disabled = false; } })
    .catch(function () { alert('Brak połączenia.'); b.disabled = false; });
});
</script>
</body>
</html>
