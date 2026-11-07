/* Panel strony (/panel): skrypt wstrzykiwany do podglądu strony w ramce. W trybie edycji klik w tekst lub zdjęcie
   wysyła je do panelu; w trybie przeglądania linki do podstron otwierają się w panelu. */
(function () {
  var me = document.currentScript, strona = me && me.getAttribute('data-strona'), P = window.parent, D = document;
  if (!P || P === window) return;
  var tryb = 'edycja', hov = null, sel = null, t0 = 0;
  var st = D.createElement('style');
  st.textContent = 'html.pe-ed body *{cursor:pointer!important}' +
    '.pe-hov{outline:2px dashed #C9A97F!important;outline-offset:3px!important}' +
    '.pe-sel{outline:3px solid #6e133b!important;outline-offset:3px!important}';
  D.head.appendChild(st);
  function send(m) { P.postMessage(m, location.origin); }
  function ustawTryb(t) { tryb = t; D.documentElement.classList.toggle('pe-ed', t === 'edycja'); if (t !== 'edycja') { mark(null, 'pe-hov'); } }

  // czy punkt kliknięcia leży na tym tekście (a nie np. między liniami nagłówka)
  function trafia(n, e) {
    var r = D.createRange(); r.selectNodeContents(n); var rs = r.getClientRects();
    for (var i = 0; i < rs.length; i++) { var q = rs[i]; if (e.clientX >= q.left - 3 && e.clientX <= q.right + 3 && e.clientY >= q.top - 3 && e.clientY <= q.bottom + 3) return true; }
    return false;
  }
  function textAt(e) {
    var n = null;
    if (D.caretRangeFromPoint) { var r = D.caretRangeFromPoint(e.clientX, e.clientY); n = r && r.startContainer; }
    else if (D.caretPositionFromPoint) { var p = D.caretPositionFromPoint(e.clientX, e.clientY); n = p && p.offsetNode; }
    if (n && n.nodeType === 3 && n.data.trim() && e.target.contains(n) && trafia(n, e)) return n;
    var w = D.createTreeWalker(e.target, NodeFilter.SHOW_TEXT, null), list = [], t;
    while ((t = w.nextNode())) { if (t.data.trim() && t.parentElement && t.parentElement.closest('script,style') === null) list.push(t); }
    return list.length === 1 ? list[0] : null;
  }
  function imgAt(e) {
    var els = D.elementsFromPoint ? D.elementsFromPoint(e.clientX, e.clientY) : [e.target];
    for (var i = 0; i < els.length; i++) { if (els[i].tagName === 'IMG') return els[i]; }
    return null;
  }
  function target(e) {
    var tx = textAt(e);
    if (tx) return { el: tx.parentElement, node: tx, msg: { typ: 'tekst', z: tx.data } };
    var im = imgAt(e);
    if (im) {
      var u; try { u = new URL(im.currentSrc || im.src, location.href); } catch (x) { return null; }
      if (u.origin === location.origin && /^\/img\//.test(u.pathname)) return { el: im, msg: { typ: 'zdjecie', src: u.pathname, w: im.naturalWidth, h: im.naturalHeight } };
    }
    return null;
  }
  // wszystkie kawałki tekstu strony w kolejności (bez skryptów i stylów)
  function teksty() {
    var w = D.createTreeWalker(D.body, NodeFilter.SHOW_TEXT, null), out = [], t;
    while ((t = w.nextNode())) { if (t.data.trim() && !(t.parentElement && t.parentElement.closest('script,style,noscript'))) out.push(t); }
    return out;
  }
  // ile razy ten sam tekst jest na stronie; jeśli więcej niż raz, kotwica = najbliższy wcześniejszy tekst, który jest jedyny
  function kontekst(node) {
    var all = teksty(), ile = 0, licz = {}, i, idx = all.indexOf(node);
    for (i = 0; i < all.length; i++) { licz[all[i].data] = (licz[all[i].data] || 0) + 1; }
    ile = licz[node.data] || 1;
    var przed = '';
    if (ile > 1) for (i = idx - 1; i >= 0 && i > idx - 40; i--) { if (licz[all[i].data] === 1 && all[i].data.trim().length > 2) { przed = all[i].data; break; } }
    return { ile: ile, przed: przed };
  }
  function mark(el, cls) {
    var old = cls === 'pe-sel' ? sel : hov;
    if (old && old !== el) old.classList.remove(cls);
    if (el) el.classList.add(cls);
    if (cls === 'pe-sel') sel = el; else hov = el;
  }

  window.addEventListener('mousemove', function (e) {
    if (tryb !== 'edycja') return;
    var t = target(e); mark(t ? t.el : null, 'pe-hov');
  }, true);

  function blokuj(e) { e.preventDefault(); e.stopPropagation(); if (e.stopImmediatePropagation) e.stopImmediatePropagation(); }
  window.addEventListener('click', function (e) {
    if (tryb === 'edycja') {
      blokuj(e);
      var t = target(e);
      if (!t) { send({ typ: 'nic' }); return; }
      mark(t.el, 'pe-sel');
      if (t.node) { var k = kontekst(t.node); t.msg.ile = k.ile; t.msg.przed = k.przed; }
      send(t.msg);
      return;
    }
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a) return;
    var u; try { u = new URL(a.href, location.href); } catch (x) { return; }
    if (u.origin === location.origin) {
      if (u.pathname === location.pathname) return; // kotwice na tej samej stronie obsługuje strona
      blokuj(e); send({ typ: 'nawigacja', sciezka: u.pathname, hash: u.hash });
    } else if (a.target !== '_blank') { blokuj(e); window.open(u.href, '_blank', 'noopener'); }
  }, true);
  window.addEventListener('submit', function (e) { blokuj(e); }, true);
  ['pointerdown', 'mousedown', 'touchstart'].forEach(function (n) {
    window.addEventListener(n, function (e) { if (tryb === 'edycja' && e.target.closest && e.target.closest('input,select,textarea,label')) blokuj(e); }, true);
  });
  window.addEventListener('scroll', function () {
    clearTimeout(t0); t0 = setTimeout(function () { send({ typ: 'przewiniecie', y: window.pageYOffset || 0 }); }, 120);
  }, { passive: true });
  window.addEventListener('message', function (e) {
    if (e.origin !== location.origin || !e.data) return;
    var m = e.data;
    if (m.typ === 'tryb') ustawTryb(m.tryb);
    if (m.typ === 'odznacz') mark(null, 'pe-sel');
    if (m.typ === 'przewin') { var y = +m.y || 0, n = 0; (function t() { window.scrollTo(0, y); if (Math.abs((window.pageYOffset || 0) - y) > 4 && ++n < 20) setTimeout(t, 100); })(); }
  });
  ustawTryb('edycja');
  function gotowe() { send({ typ: 'gotowe', strona: strona, sciezka: location.pathname, tytul: D.title }); }
  if (D.readyState === 'complete') gotowe(); else window.addEventListener('load', gotowe);
})();
