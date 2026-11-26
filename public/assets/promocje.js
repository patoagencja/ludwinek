/* Promocje z panelu (/panel/promocje): pasek u góry strony i rabat procentowy na ceny zabiegów.
   Ustawienia przychodzą w window.LA_PROMO = { pasek: { tekst, link }, rabat: procent }.
   Rabat: każda cena zabiegu („700 zł”, „od 450 zł”, „700 zł / amp.”) dostaje przekreśloną cenę i cenę po rabacie.
   Bez zmian zostają ceny voucherów (online i kwotowe), raty i strona /vouchery. */
(function () {
  'use strict';
  var P = window.LA_PROMO || {}, D = document;
  var procent = +P.rabat || 0;

  var css = '.la-pasek{position:relative;z-index:60;display:flex;align-items:center;justify-content:center;gap:12px;padding:10px 44px;background:#6e133b;color:#fff;font:500 13px/1.45 Inter,system-ui,sans-serif;text-align:center}' +
    '.la-pasek a{color:#fff;text-underline-offset:3px}.la-pasek small{display:block;font-size:11px;font-weight:400;opacity:.8}' +
    '.la-pasek button{position:absolute;right:10px;top:50%;transform:translateY(-50%);width:32px;height:32px;border:0;background:none;color:#fff;font-size:20px;line-height:1;cursor:pointer;opacity:.8}' +
    's.la-stara{opacity:.55;font-size:.78em;margin-right:.4em;text-decoration-thickness:1px;font-weight:inherit}.la-cena{color:#6e133b!important}' +
    '.dk .la-cena,.awers .la-cena{color:#E6D2B0!important}';
  var st = D.createElement('style'); st.textContent = css; D.head.appendChild(st);

  // ---- pasek ----
  var zamk = false;
  try { zamk = sessionStorage.getItem('la-pasek') === (P.pasek ? P.pasek.tekst : ''); } catch (e) {}
  if ((P.pasek || procent) && !zamk) {
    var b = D.createElement('div'); b.className = 'la-pasek'; b.setAttribute('role', 'region'); b.setAttribute('aria-label', 'Promocja');
    var t = D.createElement(P.pasek && P.pasek.link ? 'a' : 'span');
    t.textContent = P.pasek ? P.pasek.tekst : ('Promocja: −' + procent + '% na zabiegi');
    if (P.pasek && P.pasek.link) t.href = P.pasek.link;
    var w = D.createElement('span'); w.appendChild(t);
    if (procent) { var sm = D.createElement('small'); sm.textContent = 'Przekreślona cena to najniższa cena z 30 dni przed obniżką.'; w.appendChild(sm); }
    var x = D.createElement('button'); x.type = 'button'; x.setAttribute('aria-label', 'Zamknij'); x.textContent = '×';
    x.onclick = function () { b.remove(); try { sessionStorage.setItem('la-pasek', P.pasek ? P.pasek.tekst : ''); } catch (e) {} };
    b.appendChild(w); b.appendChild(x);
    D.body.insertBefore(b, D.body.firstChild);
  }

  // ---- rabat ----
  var naVouchery = /^\/vouchery/.test(location.pathname), vmapa = P.vmapa || null;
  if (!procent || (naVouchery && !vmapa)) return;
  var WZ = /^(\s*)(od\s+)?(\d{2,5})\s?zł(\s*\/\s*(?:\d+\s?)?[a-ząęółśżźćń.]+)?(\s*)$/i;
  var POMIN = 'script,style,small,s,.klarna,.vpay,.la-pasek,#zgody,[data-bez-rabatu],a[href^="/vouchery"],.zv,option,title';
  function pomin(el) {
    if (!el || el.closest(POMIN)) return true;
    var dd = el.closest('dd');   // „Voucher online · 10% taniej” w nagłówku zabiegu
    if (dd && dd.previousElementSibling && /voucher|vouch/i.test(dd.previousElementSibling.textContent)) return true;
    var b = el.closest('.orow b');   // wiersz „Voucher online · …” w sekcji ceny zabiegu
    if (b && b.previousElementSibling && /^\s*voucher online/i.test(b.previousElementSibling.textContent)) return true;
    return /voucher/i.test(el.textContent);
  }
  function zl(n) { return n + ' zł'; }
  // strona /vouchery: tylko ceny voucherów na zabieg (lista, warianty, „Do zapłaty”), które są w mapie promocji
  function vouchPomin(n) {
    var el = n.parentElement, m = n.data.match(WZ);
    if (!el || !m || !vmapa[m[3]] || el.closest('s,.la-pasek,#zgody')) return true;
    if (el.closest('.vvar') || (el.closest('[id^="v-"]') && el.closest('button'))) return false;
    if (el.closest('.offer .orow.main')) {
      // przełącznik „Na zabieg / Na kwotę”: wybrany ma klasę „on” (albo aria-pressed)
      var b = [].slice.call(D.querySelectorAll('button')).some(function (x) { return /^\s*na zabieg\s*$/i.test(x.textContent) && (x.classList.contains('on') || x.getAttribute('aria-pressed') === 'true'); });
      return !b;
    }
    return true;
  }
  // Nie podmieniamy węzłów, które przerysowuje skrypt strony: zmieniamy tylko ich tekst na cenę po rabacie
  // i wstawiamy przed nimi przekreśloną cenę. Gdy strona wpisze nową cenę (np. inny wariant), liczymy ją od nowa.
  var stan = typeof WeakMap === 'function' ? new WeakMap() : null;
  function zmien(root) {
    if (!stan) return;
    D.querySelectorAll('s.la-stara').forEach(function (s) { var n = s.__la; if (!n || s.nextSibling !== n || !n.parentNode) s.remove(); });
    D.querySelectorAll('.la-cena').forEach(function (el) { if (!el.querySelector('s.la-stara')) el.classList.remove('la-cena'); });
    var wk = D.createTreeWalker(root, NodeFilter.SHOW_TEXT, null), n, lista = [];
    while ((n = wk.nextNode())) {
      var z = stan.get(n);
      if (z && z.po === n.data && z.s.nextSibling === n) continue;
      if (WZ.test(n.data) && (naVouchery ? vouchPomin(n) === false : !pomin(n.parentElement))) lista.push(n);
    }
    lista.forEach(function (n) {
      var m = n.data.match(WZ), stara = +m[3], nowa = Math.round(stara * (100 - procent) / 100);
      if (naVouchery) { nowa = stara; stara = vmapa[String(nowa)]; }   // na /vouchery cena już jest promocyjna: dopisujemy starą
      var z = stan.get(n), s = z && z.s && z.s.nextSibling === n ? z.s : D.createElement('s');
      s.className = 'la-stara'; s.__la = n; s.textContent = (m[2] || '') + zl(stara) + (m[4] || '');
      if (s.nextSibling !== n) n.parentNode.insertBefore(s, n);
      var po = m[1] + (m[2] || '') + zl(nowa) + (m[4] || '') + m[5];
      stan.set(n, { s: s, po: po });
      n.data = po;
      if (n.parentElement) n.parentElement.classList.add('la-cena');
    });
  }
  function start() {
    zmien(D.body);
    // strony renderują się skryptem i przerysowują fragmenty (zakładki z cenami): pilnujemy nowych cen
    var t = 0;
    new MutationObserver(function () { clearTimeout(t); t = setTimeout(function () { zmien(D.body); }, 30); }).observe(D.body, { childList: true, subtree: true, characterData: true });
  }
  if (D.readyState === 'complete') setTimeout(start, 50); else window.addEventListener('load', function () { setTimeout(start, 50); });
})();
