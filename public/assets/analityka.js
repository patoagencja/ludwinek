/* Pomiar GA4 (i Meta, jeśli kiedyś dojdzie) dla ludwinekaesthetic.pl. Ładuje go zgody.js dopiero po zgodzie.
   Zdarzenia wysyła window.laZdarzenie(nazwa, parametry) z zgody.js. Opis zdarzeń i ustawień GA4: docs/analityka.md.

   LEJEK: page_view → view_item / view_item_list (zabieg, cennik, vouchery) → zaangażowanie (sekcja_widoczna cena/opinie,
   faq_otwarcie, wybor_opcji, porownanie_przed_po, zaangazowanie) → intencja (formularz_start, klik_booksy, klik_telefon)
   → konwersja (generate_lead, klik_booksy, klik_telefon; później purchase dla voucherów). */
(function () {
  'use strict';
  if (window.__laAnalityka) return;
  window.__laAnalityka = true;
  var D = document, W = window, sciezka = location.pathname.replace(/\/+$/, '') || '/';
  function ev(n, p) { if (W.laZdarzenie) W.laZdarzenie(n, p || {}); }
  function txt(el, max) { return String((el && (el.getAttribute('aria-label') || el.textContent)) || '').replace(/\s+/g, ' ').replace(/[−+]$/, '').trim().slice(0, max || 100); }
  function sekcja(el) {
    for (var x = el; x && x !== D.body; x = x.parentElement) {
      if (x.id && !/^(dc-root|la-|ddp-)/.test(x.id)) return x.id;
      if (x.tagName === 'HEADER' || /(^|\s)(hd|top)(\s|$)/.test(x.className)) return 'naglowek';
      if (x.tagName === 'FOOTER' || /(^|\s)ft(\s|$)/.test(x.className)) return 'stopka';
      if (/drawer|dr(\s|$)/.test(String(x.className))) return 'menu';
    }
    return 'tresc';
  }

  // ---- zabiegi: nazwy do raportów e-commerce (view_item, select_item) ----
  var ZABIEGI = {
    'modelowanie-ust': 'Modelowanie ust', 'dermapen': 'Dermapen 4.0', 'terapia-blizn-i-rozstepow': 'Terapia blizn i rozstępów',
    'zabiegi-pielegnacyjne': 'Zabiegi pielęgnacyjne', 'event-glow': 'Event Glow', 'signature-glow': 'Ludwinek Signature Glow',
    'dla-mezczyzn': 'Zabiegi dla mężczyzn', 'hialuronidaza': 'Hialuronidaza', 'stymulatory': 'Stymulatory tkankowe',
    'mezoterapia': 'Mezoterapia igłowa', 'biopeeling': 'Biopeeling', 'oczyszczanie': 'Oczyszczanie wodorowe',
    'pielegnacja': 'Zabiegi pielęgnacyjne', 'signature': 'Ludwinek Signature Glow', 'brwi-rzesy': 'Brwi i rzęsy',
    'skora-glowy': 'Mezoterapia skóry głowy', 'blizny': 'Terapia blizn i rozstępów'
  };
  function zabiegZ(href) {
    var u; try { u = new URL(href, location.href); } catch (e) { return null; }
    if (u.origin !== location.origin) return null;
    var p = u.pathname.replace(/^\/|\/$/g, ''), h = u.hash.replace(/^#(v-)?/, '');
    if (ZABIEGI[p]) return { id: p, name: ZABIEGI[p] };
    if ((p === 'zabiegi' || p === 'vouchery') && ZABIEGI[h]) return { id: h, name: ZABIEGI[h] };
    return null;
  }
  var tenZabieg = zabiegZ(location.href);
  function item(z, extra) { var o = { item_id: z.id, item_name: z.name, item_category: 'Zabieg', item_brand: 'Ludwinek Aesthetic' }; for (var k in extra) o[k] = extra[k]; return o; }
  function zabiegParam() { return tenZabieg ? tenZabieg.name : ''; }
  // kontekst dla zdarzeń kontaktu liczonych w zgody.js (klik_booksy, klik_telefon …): gdzie kliknięto i przy jakim zabiegu
  W.laKontekst = function (el) { return { sekcja: sekcja(el), zabieg: zabiegParam() }; };

  // ---- widok strony w lejku ----
  if (tenZabieg && sciezka !== '/vouchery') ev('view_item', { currency: 'PLN', items: [item(tenZabieg)] });
  if (sciezka === '/zabiegi') ev('view_item_list', { item_list_id: 'zabiegi', item_list_name: 'Lista zabiegów' });
  if (sciezka === '/cennik') ev('view_item_list', { item_list_id: 'cennik', item_list_name: 'Cennik' });
  if (sciezka === '/vouchery') ev('view_item_list', { item_list_id: 'vouchery', item_list_name: 'Vouchery' });

  // ---- kliknięcia ----
  D.addEventListener('click', function (e) {
    var el = e.target.closest && e.target.closest('a[href],button,[role=button],summary');
    if (!el) return;
    var sek = sekcja(el), h = el.getAttribute('href') || '';
    // linki zewnętrzne i kontakt liczy zgody.js (klik_booksy, klik_telefon …); tu dokładamy kontekst
    if (el.tagName === 'A') {
      var z = zabiegZ(el.href);
      if (/^\/kontakt#formularz/.test(h)) { ev('cta_formularz', { sekcja: sek, zabieg: zabiegParam(), link_text: txt(el) }); return; }
      if (el.classList.contains('vsoon-btn')) return; // klik_voucher w zgody.js
      if (z && !(tenZabieg && z.id === tenZabieg.id)) {
        var lista = sek === 'naglowek' || sek === 'menu' ? 'Menu' : sek === 'stopka' ? 'Stopka' : (sciezka === '/' ? 'Strona główna' : sciezka.slice(1)) + ' / ' + sek;
        ev('select_item', { item_list_name: lista, items: [item(z)] });
        return;
      }
      if (/^\/#znajdz-/.test(h)) { ev('znajdz_problem', { problem: txt(el, 60), sekcja: sek }); return; }
      if (/gmap|google\.[a-z.]+\/maps/.test(el.className + ' ' + h)) return; // klik_mapa w zgody.js
      if (el.origin === location.origin && (sek === 'naglowek' || sek === 'menu' || sek === 'stopka')) { ev('nawigacja', { cel: el.pathname + el.hash, miejsce: sek, link_text: txt(el, 60) }); }
      return;
    }
    // przyciski: FAQ, menu, przełączniki
    var exp = el.getAttribute('aria-expanded'), pres = el.getAttribute('aria-pressed');
    if (exp === 'false') { // za chwilę się otworzy
      if (/(^|\s)fqb(\s|$)/.test(el.className) || sek === 'faq') ev('faq_otwarcie', { pytanie: txt(el, 100), strona: sciezka, zabieg: zabiegParam() });
      else if (/(^|\s)(mn|ddb)(\s|$)/.test(el.className)) ev('menu_otwarcie', { menu: txt(el, 40) });
      else ev('rozwiniecie', { element: txt(el, 60), sekcja: sek });
      return;
    }
    if (pres === 'false') {
      if (sek === 'znajdz') ev('znajdz_problem', { problem: txt(el, 60).replace(/^\d+/, ''), sekcja: sek });
      else if (sek === 'efekty') ev('efekty_przypadek', { przypadek: txt(el, 60).replace(/^\d+/, '') });
      else ev('wybor_opcji', { opcja: txt(el, 60), sekcja: sek, strona: sciezka, zabieg: zabiegParam() });
      return;
    }
    if (/gopen|gth|gal/.test(el.className)) ev('galeria_gabinetu', { sekcja: sek });
  }, true);

  // ---- porównanie przed/po (suwak) ----
  var porownanie = false;
  D.addEventListener('input', function (e) {
    if (porownanie || !e.target || e.target.type !== 'range') return;
    porownanie = true; ev('porownanie_przed_po', { sekcja: sekcja(e.target), zabieg: zabiegParam() });
  }, true);

  // ---- formularz kontaktowy: start i błędy (sukces = generate_lead z formularza) ----
  var start = false;
  D.addEventListener('focusin', function (e) {
    if (start || !e.target.closest || !e.target.closest('form.kf')) return;
    start = true; ev('formularz_start', { formularz: 'kontakt', strona: sciezka });
  }, true);
  W.laFormularzBlad = function (pola) { ev('formularz_blad', { formularz: 'kontakt', pola: pola }); };

  // ---- kopiowanie (np. numeru telefonu, adresu) ----
  D.addEventListener('copy', function () {
    var s = String(W.getSelection ? W.getSelection() : '').replace(/\s+/g, ' ').trim();
    if (s) ev('kopiowanie', { tekst: s.slice(0, 60), typ: /\d{3}.?\d{3}.?\d{3}/.test(s) ? 'telefon' : /@/.test(s) ? 'email' : /plac|konstytucji|warszawa/i.test(s) ? 'adres' : 'tekst' });
  });

  // ---- głębokość przewinięcia 25/50/75/100 % ----
  var progi = [25, 50, 75, 100], wyslane = {};
  function przewin() {
    var h = D.documentElement.scrollHeight - W.innerHeight; if (h < 200) return;
    var p = Math.round((W.pageYOffset || 0) / h * 100);
    progi.forEach(function (x) { if (p >= x - 2 && !wyslane[x]) { wyslane[x] = 1; ev('scroll_glebokosc', { procent: x, zabieg: zabiegParam() }); } });
  }
  var t; W.addEventListener('scroll', function () { clearTimeout(t); t = setTimeout(przewin, 200); }, { passive: true });

  // ---- ważne sekcje zobaczone (min. połowa na ekranie przez 1 s) ----
  var WAZNE = { cena: 'Cena', rezerwacja: 'Rezerwacja', opinie: 'Opinie', efekty: 'Efekty przed/po', faq: 'FAQ', 'o-mnie': 'O mnie',
    gabinet: 'Gabinet', kontakt: 'Kontakt', znajdz: 'Znajdź problem', 'o-zabiegu': 'O zabiegu', formularz: 'Formularz' };
  if ('IntersectionObserver' in W) {
    var widziane = {}, timery = {};
    var io = new IntersectionObserver(function (wpisy) {
      wpisy.forEach(function (w) {
        var id = w.target.id;
        if (w.isIntersecting && !widziane[id]) timery[id] = setTimeout(function () { widziane[id] = 1; io.unobserve(w.target); ev('sekcja_widoczna', { sekcja: WAZNE[id], strona: sciezka, zabieg: zabiegParam() }); }, 1000);
        else clearTimeout(timery[id]);
      });
    }, { threshold: [0.5] });
    var szukaj = function () { Object.keys(WAZNE).forEach(function (id) { var el = D.getElementById(id); if (el && !el.__laObs) { el.__laObs = 1; io.observe(el); } }); };
    szukaj(); setTimeout(szukaj, 1500); setTimeout(szukaj, 4000);
  }

  // ---- czas aktywnego oglądania (karta widoczna): 30 s, 90 s, 3 min ----
  var sek = 0, kroki = [30, 90, 180];
  setInterval(function () {
    if (D.visibilityState !== 'visible') return;
    sek += 5;
    if (kroki.indexOf(sek) >= 0) ev('zaangazowanie', { sekundy: sek, strona: sciezka, zabieg: zabiegParam() });
  }, 5000);
})();
