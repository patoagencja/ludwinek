/* Zgody na pliki cookies (Prawo komunikacji elektronicznej, art. 399; RODO).
   Niezbędne cookies (logowanie do podglądu, wybór wersji strony, zapamiętanie tego wyboru) działają bez zgody.
   Narzędzia analityczne i marketingowe ładują się dopiero po zgodzie, osobno dla każdej kategorii (Google Consent Mode v2
   w trybie podstawowym: bez zgody tag Google w ogóle się nie ładuje).
   Baner pokazuje się tylko wtedy, gdy wpisany jest identyfikator któregoś narzędzia (GA4_ID, META_PIXEL_ID). Bez nich
   strona nie używa cookies wymagających zgody, więc nie pyta o nią. Link „Ustawienia cookies” (href="#ustawienia-cookies")
   zawsze otwiera ustawienia. Podgląd banera: dopisz do adresu ?zgody=pokaz. */
(function () {
  'use strict';
  var GA4_ID = 'G-5LJXC4VE72'; // Google Analytics 4, strumień ludwinekaesthetic.pl (ten sam w _dane.php)
  var META_PIXEL_ID = '';   // np. '1234567890'
  var NAZWA = 'zgody', WERSJA = 1, WAZNOSC_DNI = 365;

  var D = document, H = D.documentElement;
  // podgląd w panelu edycji (/panel) nie liczy się do statystyk
  if (/[?&]panel=1\b/.test(location.search)) { GA4_ID = ''; META_PIXEL_ID = ''; }
  var narzedzia = { a: !!GA4_ID, m: !!META_PIXEL_ID };
  var podglad = /[?&]zgody=pokaz\b/.test(location.search);
  if (!narzedzia.a && !narzedzia.m && !podglad) { narzedzia.brak = true; }

  function czytaj() {
    var m = D.cookie.match(/(?:^|;\s*)zgody=([^;]+)/);
    if (!m) return null;
    try { var z = JSON.parse(decodeURIComponent(m[1])); return z && z.v === WERSJA ? z : null; } catch (e) { return null; }
  }
  function zapisz(a, mk) {
    var z = { v: WERSJA, a: a ? 1 : 0, m: mk ? 1 : 0, t: Math.floor(Date.now() / 1000) };
    D.cookie = NAZWA + '=' + encodeURIComponent(JSON.stringify(z)) + ';path=/;max-age=' + (WAZNOSC_DNI * 86400) + ';samesite=lax' + (location.protocol === 'https:' ? ';secure' : '');
    return z;
  }

  var zaladowane = { a: false, m: false };
  function grupa() {
    var p = location.pathname.replace(/\/+$/, '') || '/';
    if (p === '/') return 'Strona główna';
    if (/^\/(modelowanie-ust|dermapen|terapia-blizn-i-rozstepow|zabiegi-pielegnacyjne|event-glow|signature-glow|dla-mezczyzn)$/.test(p)) return 'Zabieg';
    return { '/zabiegi': 'Lista zabiegów', '/blizny-potradzikowe': 'Problem skóry', '/cennik': 'Cennik', '/vouchery': 'Vouchery', '/o-mnie': 'O mnie',
      '/informacje': 'Informacje', '/kontakt': 'Kontakt', '/polityka-prywatnosci': 'Dokumenty', '/regulamin': 'Dokumenty' }[p] || 'Inne';
  }
  function wczytajPomiar() {
    if (D.getElementById('la-analityka')) return;
    var s = D.createElement('script'); s.id = 'la-analityka'; s.async = true; s.src = '/assets/analityka.js?v=1'; D.head.appendChild(s);
  }
  function uruchom(z) {
    if (!z) return;
    if (z.a && GA4_ID && !zaladowane.a) {
      zaladowane.a = true;
      window.dataLayer = window.dataLayer || [];
      window.gtag = function () { window.dataLayer.push(arguments); };
      window.gtag('consent', 'default', { analytics_storage: 'granted', ad_storage: z.m ? 'granted' : 'denied',
        ad_user_data: z.m ? 'granted' : 'denied', ad_personalization: z.m ? 'granted' : 'denied' });
      window.gtag('js', new Date());
      // grupa treści (raport „Grupy treści”) i ruch wewnętrzny gabinetu (ciasteczko z panelu: filtr „Internal Traffic” w GA4)
      var cfg = { content_group: grupa() };
      if (/(?:^|;\s*)ludwinek_wewn=1/.test(D.cookie)) cfg.traffic_type = 'internal';
      window.gtag('config', GA4_ID, cfg);
      var s = D.createElement('script'); s.async = true; s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(GA4_ID); D.head.appendChild(s);
    }
    if (z.m && META_PIXEL_ID && !zaladowane.m) {
      zaladowane.m = true;
      /* kod Meta Pixel (oficjalny), dopiero po zgodzie marketingowej */
      !function (f, b, e, v, n, t, s) { if (f.fbq) return; n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments); };
        if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = []; t = b.createElement(e); t.async = !0; t.src = v;
        s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s); }(window, D, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
      window.fbq('init', META_PIXEL_ID); window.fbq('track', 'PageView');
    }
    if (zaladowane.a || zaladowane.m) wczytajPomiar();
  }

  var CSS = '' +
    '#zgody{position:fixed;z-index:2147483000;left:16px;bottom:16px;width:min(440px,calc(100vw - 32px));box-sizing:border-box;padding:24px 24px 20px;background:#FAF8F5;color:#3A3A3A;border:1px solid #D8D2C8;box-shadow:0 18px 50px -12px rgba(20,20,20,.35);font:400 14px/1.6 Inter,system-ui,-apple-system,sans-serif;opacity:0;transform:translateY(12px);transition:opacity .25s,transform .25s}' +
    '#zgody.on{opacity:1;transform:none}' +
    '#zgody h2{margin:0 0 8px;font:400 24px/1.1 "Cormorant Garamond",Garamond,serif;color:#141414}' +
    '#zgody p{margin:0}#zgody a{color:#141414}' +
    '#zgody .zb{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}' +
    '#zgody button{flex:1 1 120px;min-height:46px;padding:0 14px;border:1px solid #141414;background:transparent;color:#141414;font:500 11px/1.2 Inter,system-ui,sans-serif;letter-spacing:.12em;text-transform:uppercase;cursor:pointer}' +
    '#zgody button.zg{background:#141414;color:#FAF8F5}' +
    '#zgody button.zl{flex:1 1 100%;border-color:transparent;text-decoration:underline;text-underline-offset:4px;min-height:40px}' +
    '#zgody .zk{margin-top:16px;border-top:1px solid #D8D2C8}' +
    '#zgody label{display:flex;gap:12px;align-items:flex-start;padding:12px 0;border-bottom:1px solid #D8D2C8;cursor:pointer}' +
    '#zgody label b{display:block;color:#141414;font-weight:500}#zgody label span{font-size:13px;color:#6F675C}' +
    '#zgody input{width:20px;height:20px;margin:2px 0 0;flex:none;accent-color:#6e133b}' +
    '#zgody :focus-visible{outline:2px solid #C9A97F;outline-offset:2px}' +
    '@media (max-width:520px){#zgody{left:8px;right:8px;bottom:8px;width:auto;padding:20px 18px 16px}}';

  function pokaz(ustawienia) {
    var stary = D.getElementById('zgody'); if (stary) stary.remove();
    if (!D.getElementById('zgody-css')) { var st = D.createElement('style'); st.id = 'zgody-css'; st.textContent = CSS; D.head.appendChild(st); }
    var z = czytaj() || { a: 0, m: 0 };
    var b = D.createElement('div');
    b.id = 'zgody'; b.setAttribute('role', 'dialog'); b.setAttribute('aria-modal', 'false'); b.setAttribute('aria-labelledby', 'zgody-t');
    b.innerHTML =
      '<h2 id="zgody-t">Pliki cookies</h2>' +
      '<p>Niezbędne pliki cookies pozwalają stronie działać. Za Twoją zgodą użyję też cookies analitycznych (statystyki odwiedzin) ' +
      'i marketingowych (dopasowanie reklam w Google i Meta). Zgodę możesz w każdej chwili zmienić w stopce strony. ' +
      '<a href="/polityka-prywatnosci#cookies">Więcej w polityce prywatności</a>.</p>' +
      '<div class="zk"' + (ustawienia ? '' : ' hidden') + '>' +
      '<label><input type="checkbox" checked disabled><span><b>Niezbędne</b>Działanie strony i zapamiętanie Twoich wyborów. Zawsze włączone.</span></label>' +
      '<label><input type="checkbox" id="zgody-a"' + (z.a ? ' checked' : '') + '><span><b>Analityczne</b>Google Analytics: anonimowe statystyki, które pomagają ulepszać stronę.</span></label>' +
      '<label><input type="checkbox" id="zgody-m"' + (z.m ? ' checked' : '') + '><span><b>Marketingowe</b>Google Ads i Meta (Facebook, Instagram): pomiar i dopasowanie reklam.</span></label>' +
      '</div>' +
      '<div class="zb">' +
      (ustawienia ? '<button type="button" class="zg" data-z="zapisz">Zapisz wybór</button>' : '') +
      '<button type="button" data-z="odrzuc">Odrzuć</button>' +
      '<button type="button"' + (ustawienia ? '' : ' class="zg"') + ' data-z="akceptuj">Akceptuj wszystkie</button>' +
      (ustawienia ? '' : '<button type="button" class="zl" data-z="ustawienia">Ustawienia</button>') +
      '</div>';
    D.body.appendChild(b);
    requestAnimationFrame(function () { b.classList.add('on'); });
    b.addEventListener('click', function (e) {
      var t = e.target.closest && e.target.closest('[data-z]'); if (!t) return;
      var co = t.getAttribute('data-z'), w;
      if (co === 'ustawienia') { pokaz(true); return; }
      if (co === 'akceptuj') w = zapisz(1, 1);
      else if (co === 'odrzuc') w = zapisz(0, 0);
      else w = zapisz(D.getElementById('zgody-a').checked, D.getElementById('zgody-m').checked);
      zamknij(b);
      // wycofanie wcześniejszej zgody: narzędzia już załadowane wyłączy dopiero przeładowanie strony
      if ((zaladowane.a && !w.a) || (zaladowane.m && !w.m)) location.reload(); else uruchom(w);
    });
    var pierwszy = b.querySelector('button'); if (pierwszy && ustawienia) pierwszy.focus();
  }
  function zamknij(b) { b.classList.remove('on'); setTimeout(function () { if (b.parentNode) b.parentNode.removeChild(b); }, 260); }

  /* Zdarzenia (tylko po zgodzie, gdy narzędzie jest załadowane). Kluczowe dla gabinetu:
     generate_lead (wysłany formularz), klik_booksy (przejście do rezerwacji), klik_telefon, klik_email.
     window.laZdarzenie(nazwa, parametry) wołają też strony (np. formularz po udanej wysyłce). */
  var META = { generate_lead: 'Lead', klik_booksy: 'Schedule', klik_telefon: 'Contact', klik_email: 'Contact', klik_whatsapp: 'Contact', view_item: 'ViewContent', begin_checkout: 'InitiateCheckout', purchase: 'Purchase' };
  window.laZdarzenie = function (nazwa, par) {
    par = par || {};
    try { if (zaladowane.a && window.gtag) window.gtag('event', nazwa, par); } catch (e) {}
    try { if (zaladowane.m && window.fbq && META[nazwa]) { var it = par.items && par.items[0]; window.fbq('track', META[nazwa], it ? { content_name: it.item_name, content_category: it.item_category, currency: 'PLN', value: par.value || 0 } : par.zabieg ? { content_name: par.zabieg } : {}); } } catch (e) {}
  };
  function tekst(el) { return String(el.getAttribute('aria-label') || el.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 80); }
  D.addEventListener('click', function (e) {
    if (!zaladowane.a && !zaladowane.m) return;
    var a = e.target.closest && e.target.closest('a[href],button'); if (!a) return;
    var h = a.getAttribute('href') || '', par = { link_text: tekst(a), strona: location.pathname };
    if (window.laKontekst) { var k = window.laKontekst(a); par.sekcja = k.sekcja; par.zabieg = k.zabieg; }
    if (/booksy\.com/i.test(h)) window.laZdarzenie('klik_booksy', par);
    else if (/^tel:/i.test(h)) window.laZdarzenie('klik_telefon', par);
    else if (/^mailto:/i.test(h)) window.laZdarzenie('klik_email', par);
    else if (/wa\.me|whatsapp/i.test(h)) window.laZdarzenie('klik_whatsapp', par);
    else if (/google\.[a-z.]+\/maps|maps\.app\.goo|goo\.gl\/maps/i.test(h)) window.laZdarzenie('klik_mapa', par);
    else if (/instagram\.com|facebook\.com|tiktok\.com/i.test(h)) window.laZdarzenie('klik_social', par);
    else if (a.classList.contains('vsoon-btn')) window.laZdarzenie('klik_voucher', par);
  }, true);

  // link „Ustawienia cookies” w stopce (działa też, gdy strona jest jeszcze renderowana)
  D.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href$="#ustawienia-cookies"]'); if (!a) return;
    e.preventDefault(); e.stopPropagation(); pokaz(true);
  }, true);

  function start() {
    var z = czytaj();
    if (z) uruchom(z);
    if (narzedzia.brak) return;
    if (!z || podglad) pokaz(false);
  }
  if (D.readyState === 'loading') D.addEventListener('DOMContentLoaded', start); else start();
})();
