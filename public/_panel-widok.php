<?php
// Wygląd panelu (/panel): logowanie albo edytor. Wołane z _panel.php.
$zalogowany = panel_zalogowany();
$NAZWY = [
    '' => 'Strona główna', 'zabiegi' => 'Zabiegi', 'modelowanie-ust' => 'Modelowanie ust', 'dermapen' => 'Dermapen',
    'terapia-blizn-i-rozstepow' => 'Terapia blizn i rozstępów', 'zabiegi-pielegnacyjne' => 'Zabiegi pielęgnacyjne',
    'event-glow' => 'Event Glow', 'signature-glow' => 'Signature Glow', 'dla-mezczyzn' => 'Dla mężczyzn',
    'blizny-potradzikowe' => 'Problemy skóry', 'cennik' => 'Cennik', 'vouchery' => 'Vouchery', 'o-mnie' => 'O mnie',
    'informacje' => 'Informacje', 'kontakt' => 'Kontakt',
];
$strony = [];
foreach ($NAZWY as $slug => $n) $strony[] = ['sciezka' => '/' . $slug, 'klucz' => STRONY[$slug], 'nazwa' => $n];
?><!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<script>if(location.protocol==="http:"&&/(^|\.)ludwinekaesthetic\.pl$/.test(location.hostname))location.replace("https://ludwinekaesthetic.pl"+location.pathname+location.search+location.hash);</script>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#1F1E1C">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="/assets/fonts.css">
<title>Panel strony · Ludwinek Aesthetic</title>
<style>
:root{--ink:#141414;--txt:#3A3A3A;--mut:#6F675C;--bg:#FAF8F5;--pap:#EFEBE4;--line:#D8D2C8;--acc:#6e133b;--acc2:#57102f;--gold:#C9A97F;--dark:#1F1E1C;--err:#b3261e;--ok:#2f6b3a}
*{box-sizing:border-box}
html,body{height:100%}
body{margin:0;background:var(--bg);color:var(--txt);font:400 15px/1.55 'Inter',system-ui,sans-serif;-webkit-font-smoothing:antialiased}
button,input,select,textarea{font:inherit;color:inherit}
:focus-visible{outline:2px solid var(--gold);outline-offset:2px}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:0 18px;border:1px solid var(--acc);background:var(--acc);color:#fff;font:500 11.5px/1 'Inter',sans-serif;letter-spacing:.12em;text-transform:uppercase;text-decoration:none;cursor:pointer}
.btn:hover{background:var(--acc2)}
.btn[disabled]{opacity:.55;cursor:default}
.btn-o{background:transparent;color:var(--ink);border-color:var(--line)}
.btn-o:hover{background:var(--pap)}
.lbl{display:block;margin:0 0 8px;font:500 10.5px/1.4 'Inter',sans-serif;letter-spacing:.14em;text-transform:uppercase;color:var(--mut)}
h1,h2{font-family:'Cormorant Garamond',Garamond,serif;font-weight:300;color:var(--ink);margin:0}
/* logowanie */
.log{min-height:100%;display:grid;place-items:center;padding:20px;background:var(--dark)}
.log form{width:100%;max-width:400px;background:var(--bg);padding:38px 30px 32px;text-align:center}
.log h1{font-size:38px;line-height:1.05;margin-top:6px}
.log h1 em{font-style:italic}
.log p{margin:10px 0 0;color:var(--mut);font-size:14.5px}
.log label{display:block;margin:24px 0 8px;text-align:left;font:500 10.5px/1.4 'Inter',sans-serif;letter-spacing:.14em;text-transform:uppercase;color:var(--ink)}
.log input{width:100%;padding:13px 15px;border:1px solid var(--line);background:#fff;border-radius:0;font-size:16px}
.log .btn{width:100%;margin-top:16px;min-height:50px}
.log .err{color:var(--err)}
/* edytor */
.app{display:grid;grid-template-columns:340px 1fr;grid-template-rows:auto 1fr;height:100%;height:100dvh}
.top{grid-column:1/-1;display:flex;align-items:center;gap:14px;padding:10px 18px;background:var(--dark);color:#EDE7DE}
.top b{font:300 22px/1 'Cormorant Garamond',serif;color:#fff;letter-spacing:.01em}
.top .sp{flex:1}
.top a{color:#EDE7DE;font-size:13px;text-decoration:none;opacity:.85}
.top a:hover{opacity:1;text-decoration:underline}
.side{overflow:auto;border-right:1px solid var(--line);padding:18px 18px 40px;display:flex;flex-direction:column;gap:18px;background:var(--bg)}
.ctl select{width:100%;min-height:44px;padding:0 12px;border:1px solid var(--line);background:#fff;border-radius:0}
.seg{display:flex;border:1px solid var(--line);background:#fff}
.seg button{flex:1;min-height:40px;border:0;background:transparent;cursor:pointer;font-size:13.5px}
.seg button+button{border-left:1px solid var(--line)}
.seg button.on{background:var(--ink);color:#fff}
.hint{margin:0;font-size:13.5px;color:var(--mut)}
.card{background:#fff;border:1px solid var(--line);padding:16px}
.card h2{font-size:24px;line-height:1.1;margin-bottom:10px}
.card textarea{width:100%;min-height:120px;padding:10px 12px;border:1px solid var(--line);border-radius:0;resize:vertical;font-size:15px;line-height:1.5}
.card .row{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap}
.card .row .btn{flex:1}
.chk{display:flex;gap:9px;align-items:flex-start;margin-top:10px;font-size:13.5px;cursor:pointer}
.chk input{flex:none;margin:3px 0 0;width:16px;height:16px;accent-color:var(--acc)}
.old{margin:0 0 10px;font-size:13px;color:var(--mut)}
.old q{color:var(--ink)}
.imgs{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:4px}
.imgs figure{margin:0}
.imgs img{display:block;width:100%;aspect-ratio:1;object-fit:cover;background:var(--pap)}
.imgs figcaption{font-size:12px;color:var(--mut);margin-top:4px}
.file{display:block;margin-top:12px}
.msg{margin:10px 0 0;font-size:13.5px}
.msg.err{color:var(--err)}
.msg.ok{color:var(--ok)}
.list{display:flex;flex-direction:column;gap:8px}
.it{display:grid;grid-template-columns:1fr auto;gap:4px 10px;align-items:start;padding:10px 12px;background:#fff;border:1px solid var(--line);font-size:13px}
.it .wh{grid-column:1/-1;font:500 10px/1.4 'Inter',sans-serif;letter-spacing:.12em;text-transform:uppercase;color:var(--mut)}
.it .ch{word-break:break-word}
.it .ch s{color:var(--mut)}
.it .bad{grid-column:1/-1;color:var(--err);font-size:12px}
.it img{width:44px;height:44px;object-fit:cover;vertical-align:middle;margin-right:4px}
.it button{border:0;background:none;color:var(--acc);font-size:12.5px;text-decoration:underline;cursor:pointer;padding:2px 0}
.empty{font-size:13.5px;color:var(--mut);margin:0}
.view{position:relative;overflow:hidden;background:#E6E1D9}
.frame{position:absolute;top:0;left:0;border:0;background:#fff;transform-origin:0 0}
.view.m .frame{left:50%;top:16px;transform:translateX(-50%);box-shadow:0 10px 40px rgba(0,0,0,.18);border-radius:14px}
.toast{position:fixed;left:50%;bottom:22px;transform:translateX(-50%) translateY(20px);opacity:0;background:var(--ink);color:#fff;padding:12px 18px;font-size:14px;transition:.25s;pointer-events:none;z-index:9;max-width:calc(100% - 32px)}
.toast.on{opacity:1;transform:translateX(-50%)}
.load{position:absolute;inset:0;display:none;place-items:center;background:rgba(230,225,217,.6);font-size:14px;color:var(--mut)}
.view.busy .load{display:grid}
.sheet-x{display:none}
/* telefon: podgląd na całą szerokość, edycja w wysuwanym panelu na dole */
@media (max-width:900px){
  .app{grid-template-columns:1fr;grid-template-rows:auto auto 1fr}
  .side{border-right:0;border-bottom:1px solid var(--line);padding:10px 12px;gap:8px 8px;overflow:visible;display:grid;grid-template-columns:1fr 1fr;align-items:end}
  .side .ctl .lbl,.side .hint{display:none}
  .ctl-row{display:contents}
  .seg button{min-height:44px;font-size:13px}
  .list-wrap{grid-column:1/-1}
  .list-wrap summary{margin:2px 0 0!important}
  .side .opt-d{display:none}
  .list-wrap .list{max-height:40vh;overflow:auto}
  .view.m .frame{top:0;left:0;transform:none;border-radius:0;box-shadow:none}
  #ed{position:fixed;left:0;right:0;bottom:0;z-index:8;max-height:75vh;overflow:auto;border:0;border-top:3px solid var(--acc);box-shadow:0 -10px 30px rgba(0,0,0,.2);padding:16px 16px calc(16px + env(safe-area-inset-bottom))}
  .sheet-x{display:block;position:absolute;top:8px;right:10px;border:0;background:none;font-size:26px;line-height:1;cursor:pointer;color:var(--mut)}
  .top{padding:8px 14px}
  .top .hide-m{display:none}
}
</style>
</head>
<body>
<?php if (!$zalogowany): ?>
<div class="log">
<form method="post" action="/panel">
<p class="lbl">Ludwinek Aesthetic</p>
<h1>Panel <em>strony</em></h1>
<p>Zaloguj się, żeby zmienić teksty i zdjęcia na stronie.</p>
<label for="haslo">Hasło</label>
<input id="haslo" name="haslo" type="password" autocomplete="current-password" required autofocus>
<?php if (!empty($blad)): ?><p class="err" role="alert"><?= htmlspecialchars($blad) ?></p><?php endif; ?>
<button class="btn" type="submit">Zaloguj</button>
</form>
</div>
<?php else: ?>
<div class="app">
<header class="top">
<b>Panel strony</b><span class="hide-m" style="font-size:13px;opacity:.7">Ludwinek Aesthetic</span>
<span class="sp"></span>
<a id="otworz" href="/" target="_blank" rel="noopener">Zobacz stronę ↗</a>
<a href="/panel/wyloguj">Wyloguj</a>
</header>

<aside class="side" id="side">
<div class="ctl">
<label class="lbl" for="strona">Strona</label>
<select id="strona"><?php foreach ($strony as $s): ?><option value="<?= htmlspecialchars($s['sciezka']) ?>"><?= htmlspecialchars($s['nazwa']) ?></option><?php endforeach; ?></select>
</div>
<div class="ctl-row">
<div class="seg opt-d" id="widok" role="group" aria-label="Wersja"><button type="button" data-v="d" class="on">Komputer</button><button type="button" data-v="m">Telefon</button></div>
<div class="seg" id="tryb" role="group" aria-label="Tryb"><button type="button" data-t="edycja" class="on">Edycja</button><button type="button" data-t="przegladanie">Przeglądanie</button></div>
</div>
<p class="hint" id="hint">Kliknij tekst albo zdjęcie na stronie, żeby je zmienić. Żeby otworzyć menu, zakładkę albo przejść na inną stronę, przełącz na „Przeglądanie”.</p>

<div class="card" id="ed" hidden></div>

<details class="list-wrap" id="lw" open>
<summary class="lbl" style="margin-top:6px;cursor:pointer">Ostatnie zmiany</summary>
<div class="list" id="lista"><p class="empty">Wczytuję…</p></div>
</details>
</aside>

<main class="view" id="view">
<iframe class="frame" id="frame" title="Podgląd strony"></iframe>
<div class="load">Wczytuję stronę…</div>
</main>
</div>
<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
(function () {
  var STRONY = <?= json_encode($strony, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  var $ = function (s) { return document.querySelector(s); };
  var fr = $('#frame'), view = $('#view'), ed = $('#ed'), sel = $('#strona');
  var mal = window.matchMedia('(max-width: 900px)');
  var st = { sciezka: '/', widok: 'd', tryb: 'edycja', klucz: null, y: 0, przewin: 0 };

  try { var h = JSON.parse(decodeURIComponent(location.hash.slice(1)) || '{}'); if (h.s) st.sciezka = h.s; if (h.w) st.widok = h.w; } catch (x) {}
  if (mal.matches) { st.widok = 'm'; $('#lw').open = false; }
  function nazwa(k) { if (k === '*') return 'Wszystkie strony'; for (var i = 0; i < STRONY.length; i++) if (STRONY[i].klucz === k) return STRONY[i].nazwa; return k; }
  function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
  function krotko(s, n) { s = String(s).replace(/\s+/g, ' ').trim(); return s.length > n ? s.slice(0, n - 1) + '…' : s; }
  var tt = 0;
  function toast(t) { var e = $('#toast'); e.textContent = t; e.classList.add('on'); clearTimeout(tt); tt = setTimeout(function () { e.classList.remove('on'); }, 3200); }
  function post(akcja, dane) {
    var fd = dane instanceof FormData ? dane : new FormData();
    if (!(dane instanceof FormData)) Object.keys(dane || {}).forEach(function (k) { fd.append(k, dane[k]); });
    return fetch('/panel/' + akcja, { method: 'POST', body: fd, headers: { 'X-Panel': '1' }, credentials: 'same-origin' })
      .then(function (r) { return r.json().catch(function () { return { ok: false, blad: 'Błąd serwera (' + r.status + ').' }; }); })
      .catch(function () { return { ok: false, blad: 'Brak połączenia z internetem. Spróbuj ponownie.' }; });
  }

  function rozmiar() {
    var W = view.clientWidth, H = view.clientHeight;
    view.classList.toggle('m', st.widok === 'm');
    if (st.widok === 'm') {
      if (mal.matches) { fr.style.width = W + 'px'; fr.style.height = H + 'px'; fr.style.transform = 'none'; }
      else { var h = Math.min(860, H - 32); fr.style.width = '390px'; fr.style.height = h + 'px'; fr.style.transform = ''; }
    } else {
      var s = Math.min(1, W / 1440); fr.style.width = '1440px'; fr.style.height = Math.ceil(H / s) + 'px'; fr.style.transform = 'scale(' + s + ')';
    }
  }
  function wczytaj(zachowajPozycje) {
    st.przewin = zachowajPozycje ? st.y : 0;
    if (!zachowajPozycje) st.y = 0;
    view.classList.add('busy');
    fr.src = st.sciezka + '?panel=1&widok=' + st.widok + '&t=' + Date.now();
    sel.value = st.sciezka; $('#otworz').href = st.sciezka;
    history.replaceState(null, '', '#' + encodeURIComponent(JSON.stringify({ s: st.sciezka, w: st.widok })));
    zamknij();
  }
  function doRamki(m) { try { fr.contentWindow.postMessage(m, location.origin); } catch (x) {} }
  function seg(id, attr, val) { document.querySelectorAll('#' + id + ' button').forEach(function (b) { b.classList.toggle('on', b.getAttribute(attr) === val); }); }

  sel.addEventListener('change', function () { st.sciezka = sel.value; wczytaj(false); });
  $('#widok').addEventListener('click', function (e) { var b = e.target.closest('button'); if (!b) return; st.widok = b.getAttribute('data-v'); seg('widok', 'data-v', st.widok); rozmiar(); wczytaj(true); });
  $('#tryb').addEventListener('click', function (e) {
    var b = e.target.closest('button'); if (!b) return; st.tryb = b.getAttribute('data-t'); seg('tryb', 'data-t', st.tryb); doRamki({ typ: 'tryb', tryb: st.tryb });
    $('#hint').textContent = st.tryb === 'edycja' ? 'Kliknij tekst albo zdjęcie na stronie, żeby je zmienić.' : 'Teraz możesz klikać jak zwykły gość: menu, zakładki, linki. Wróć do „Edycja”, żeby coś zmienić.';
    if (st.tryb !== 'edycja') zamknij();
  });
  window.addEventListener('resize', rozmiar);
  seg('widok', 'data-v', st.widok);

  function zamknij() { ed.hidden = true; ed.innerHTML = ''; doRamki({ typ: 'odznacz' }); }
  function przyciski(zapisz) {
    return '<div class="row"><button type="button" class="btn" id="zap">' + zapisz + '</button><button type="button" class="btn btn-o" id="anuluj">Anuluj</button></div><p class="msg" id="msg" role="alert"></p>';
  }
  function blad(t) { var m = $('#msg'); m.className = 'msg err'; m.textContent = t; }
  function poZapisie(t) { toast(t); zamknij(); wczytaj(true); lista(); }

  function edytujTekst(m) {
    var z = m.z, widoczny = z.replace(/\s+/g, ' ').trim(), wiele = m.ile > 1;
    ed.innerHTML = '<button type="button" class="sheet-x" aria-label="Zamknij">×</button><h2>Zmień tekst</h2>' +
      '<label class="lbl" for="na">Nowy tekst</label><textarea id="na"></textarea>' +
      (wiele ? '<p class="old" style="margin:10px 0 0">Ten sam tekst jest na tej stronie w ' + m.ile + ' miejscach.</p>' +
        (m.przed ? '<label class="chk"><input type="radio" name="gdzie" value="jedno" checked><span>Zmień tylko w tym miejscu (zaznaczonym na stronie)</span></label>' : '') +
        '<label class="chk"><input type="radio" name="gdzie" value="wsz"' + (m.przed ? '' : ' checked') + '><span>Zmień we wszystkich ' + m.ile + ' miejscach na tej stronie</span></label>' : '') +
      '<label class="chk"><input type="checkbox" id="wsz"><span>Zmień ten tekst na wszystkich podstronach (np. telefon albo godziny otwarcia)</span></label>' +
      przyciski('Zapisz');
    var ta = $('#na'); ta.value = widoczny; ta.style.minHeight = widoczny.length > 140 ? '200px' : widoczny.length > 40 ? '110px' : '64px';
    ed.hidden = false; ta.focus(); ta.select();
    $('#anuluj').onclick = zamknij; ed.querySelector('.sheet-x').onclick = zamknij;
    ta.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); $('#zap').click(); } if (e.key === 'Escape') zamknij(); });
    $('#zap').onclick = function () {
      var b = this; b.disabled = true; b.textContent = 'Zapisuję…';
      var g = ed.querySelector('input[name=gdzie]:checked'), jedno = g && g.value === 'jedno' && !$('#wsz').checked;
      post('tekst', { strona: st.klucz, z: z, na: ta.value, wszedzie: $('#wsz').checked ? '1' : '', przed: jedno ? m.przed : '' }).then(function (r) {
        b.disabled = false; b.textContent = 'Zapisz';
        if (!r.ok) return blad(r.blad || 'Nie udało się zapisać.');
        if (r.bez_zmian) return zamknij();
        poZapisie(r.miejsc > 1 ? 'Zapisane. Zmieniło się w ' + r.miejsc + ' miejscach.' : 'Zapisane. Zmiana jest już na stronie.');
      });
    };
  }

  function zmniejsz(plik) {
    return new Promise(function (ok) {
      var u = URL.createObjectURL(plik), im = new Image();
      im.onload = function () {
        var max = 2400, s = Math.min(1, max / Math.max(im.naturalWidth, im.naturalHeight));
        var c = document.createElement('canvas'); c.width = Math.round(im.naturalWidth * s); c.height = Math.round(im.naturalHeight * s);
        var x = c.getContext('2d'); x.fillStyle = '#fff'; x.fillRect(0, 0, c.width, c.height); x.drawImage(im, 0, 0, c.width, c.height);
        URL.revokeObjectURL(u); c.toBlob(function (b) { ok(b || plik); }, 'image/jpeg', 0.86);
      };
      im.onerror = function () { URL.revokeObjectURL(u); ok(plik); };
      im.src = u;
    });
  }
  function edytujZdjecie(m) {
    ed.innerHTML = '<button type="button" class="sheet-x" aria-label="Zamknij">×</button><h2>Zmień zdjęcie</h2>' +
      '<div class="imgs"><figure><img src="' + esc(m.src) + '" alt=""><figcaption>Teraz</figcaption></figure><figure><img id="nowe" alt="" hidden><figcaption id="nowe-p">Nowe: wybierz plik</figcaption></figure></div>' +
      '<input class="file" type="file" id="plik" accept="image/*">' +
      '<p class="hint" style="margin-top:8px">Zdjęcie zostanie przycięte do tego samego kształtu co obecne' + (m.w && m.h ? ' (' + (m.w >= m.h ? 'poziome lub kwadratowe' : 'pionowe') + ')' : '') + '. Najlepiej wybierz podobny kadr.</p>' +
      '<label class="chk"><input type="checkbox" id="wsz" checked><span>Zmień to zdjęcie wszędzie, gdzie jest na stronie</span></label>' +
      przyciski('Zapisz zdjęcie');
    ed.hidden = false;
    var plik = null;
    $('#anuluj').onclick = zamknij; ed.querySelector('.sheet-x').onclick = zamknij;
    $('#plik').onchange = function () { plik = this.files[0] || null; var n = $('#nowe'); if (plik) { n.src = URL.createObjectURL(plik); n.hidden = false; $('#nowe-p').textContent = 'Nowe'; } };
    $('#zap').onclick = function () {
      if (!plik) return blad('Najpierw wybierz zdjęcie.');
      var b = this; b.disabled = true; b.textContent = 'Wysyłam…';
      zmniejsz(plik).then(function (blob) {
        var fd = new FormData(); fd.append('strona', st.klucz); fd.append('z', m.src); fd.append('wszedzie', $('#wsz').checked ? '1' : ''); fd.append('plik', blob, 'zdjecie.jpg');
        return post('zdjecie', fd);
      }).then(function (r) {
        b.disabled = false; b.textContent = 'Zapisz zdjęcie';
        if (!r.ok) return blad(r.blad || 'Nie udało się zapisać.');
        poZapisie('Zdjęcie zmienione.');
      });
    };
  }

  function lista() {
    post('lista', {}).then(function (r) {
      var el = $('#lista');
      if (!r.ok) { el.innerHTML = '<p class="empty">' + esc(r.blad || 'Nie udało się wczytać zmian.') + '</p>'; return; }
      if (!r.zmiany.length) { el.innerHTML = '<p class="empty">Na razie bez zmian. Wszystko wygląda jak w projekcie.</p>'; return; }
      el.innerHTML = r.zmiany.slice(0, 60).map(function (e) {
        var d = new Date(e.kiedy), kiedy = isNaN(d) ? '' : d.toLocaleDateString('pl-PL', { day: 'numeric', month: 'short' }) + ', ' + d.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' });
        var ch = e.typ === 'zdjecie' ? '<img src="' + esc(e.z) + '" alt="">→ <img src="' + esc(e.na) + '" alt="">' : '<s>' + esc(krotko(e.z, 60)) + '</s> → ' + esc(krotko(e.na, 90));
        return '<div class="it"><span class="wh">' + esc(nazwa(e.strona)) + ' · ' + esc(kiedy) + '</span><span class="ch">' + ch + '</span>' +
          '<button type="button" data-id="' + esc(e.id) + '">Cofnij</button>' + (e.dziala ? '' : '<span class="bad">Ta zmiana już nie działa (tekst na stronie się zmienił). Możesz ją cofnąć.</span>') + '</div>';
      }).join('');
    });
  }
  $('#lista').addEventListener('click', function (e) {
    var b = e.target.closest('button[data-id]'); if (!b) return;
    if (!confirm('Cofnąć tę zmianę? Wróci poprzednia wersja.')) return;
    post('cofnij', { id: b.getAttribute('data-id') }).then(function (r) { if (!r.ok) return toast(r.blad || 'Nie udało się cofnąć.'); toast('Cofnięte.'); lista(); wczytaj(true); });
  });

  window.addEventListener('message', function (e) {
    if (e.origin !== location.origin || e.source !== fr.contentWindow || !e.data) return;
    var m = e.data;
    if (m.typ === 'gotowe') {
      st.klucz = m.strona; view.classList.remove('busy');
      doRamki({ typ: 'tryb', tryb: st.tryb });
      if (st.przewin) doRamki({ typ: 'przewin', y: st.przewin });
      if (m.sciezka !== st.sciezka) { st.sciezka = m.sciezka; sel.value = m.sciezka; }
    }
    if (m.typ === 'przewiniecie') st.y = m.y;
    if (m.typ === 'nawigacja') { if (STRONY.some(function (s) { return s.sciezka === m.sciezka; })) { st.sciezka = m.sciezka; wczytaj(false); } else toast('Tej strony nie da się edytować w panelu.'); }
    if (m.typ === 'tekst') edytujTekst(m);
    if (m.typ === 'zdjecie') edytujZdjecie(m);
    if (m.typ === 'nic') toast('Tu nie ma tekstu ani zdjęcia do zmiany. Kliknij bezpośrednio w słowa albo w zdjęcie.');
  });

  rozmiar(); wczytaj(false); lista();
})();
</script>
<?php endif; ?>
</body>
</html>
