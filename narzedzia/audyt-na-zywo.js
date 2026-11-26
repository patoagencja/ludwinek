// Audyt ludwinekaesthetic.pl na żywo (Playwright): wszystkie strony na komputerze i telefonie, promocja (przekreślone ceny
// i ich wyliczenia), ceny voucherów na stronie i w płatności. Uruchamia go .github/workflows/sprawdz-domene.yml
// (push na gałąź „sprawdzenie”). Wynik: linie OK / BŁĄD w logu, kod 1 przy błędach.
const { chromium, devices } = require('playwright');
const B = process.env.ADRES || 'https://ludwinekaesthetic.pl';
const STRONY = ['/', '/modelowanie-ust', '/usuwanie-kwasu-hialuronowego', '/stymulatory-tkankowe', '/mezoterapia-iglowa', '/zabiegi', '/dermapen',
  '/terapia-blizn-i-rozstepow', '/biopeeling', '/zabiegi-pielegnacyjne', '/event-glow', '/signature-glow', '/brwi-i-rzesy', '/mezoterapia-skory-glowy',
  '/dla-mezczyzn', '/blizny-potradzikowe', '/cennik', '/vouchery', '/o-mnie', '/informacje', '/kontakt', '/polityka-prywatnosci', '/regulamin', '/regulamin-voucherow'];
const ZAMOWIEN = +(process.env.ZAMOWIEN || 0);   // ile prawdziwych sesji Stripe utworzyć do sprawdzenia kwoty (nieopłacone wygasają po 24 h); ZAMOWIEN=3 w workflow, gdy potrzeba
const bledy = [], ok = [];
const BLAD = (s) => { bledy.push(s); console.log('BŁĄD: ' + s); };
const OK = (s) => { ok.push(s); console.log('OK: ' + s); };
const liczba = (t) => +(String(t).match(/(\d+)\s?zł/) || [0, NaN])[1];

(async () => {
  const b = await chromium.launch();
  let promo = null;
  for (const [nazwa, opcje] of [['komputer', { viewport: { width: 1440, height: 900 } }], ['telefon', devices['iPhone 13']], ['komputer-2', { viewport: { width: 1280, height: 800 } }]]) {
    const c = await b.newContext(opcje);
    for (const u of STRONY) {
      const p = await c.newPage(); const err = [];
      p.on('pageerror', (e) => err.push(e.message));
      const zleZap = [];
      p.on('response', (x) => { if (x.url().startsWith(B) && x.status() >= 400) zleZap.push(x.status() + ' ' + x.url().replace(B, '')); });
      p.on('requestfailed', (x) => { if (x.url().startsWith(B)) zleZap.push('nieudane ' + x.url().replace(B, '') + ' (' + (x.failure() || {}).errorText + ')'); });
      const r = await p.goto(B + u, { waitUntil: 'networkidle', timeout: 60000 }).catch((e) => ({ status: () => 'błąd: ' + e.message }));
      await p.waitForSelector('h1', { timeout: 20000 }).catch(() => {});
      await p.waitForTimeout(900);
      const d = await p.evaluate(() => {
        const P = window.LA_PROMO || null, t = document.body.innerText;
        const pary = [...document.querySelectorAll('s.la-stara')].filter((s) => s.offsetParent !== null || s.getClientRects().length)
          .map((s) => [s.textContent, s.nextSibling && s.nextSibling.data]);
        // ceny bez przekreślenia (poza voucherami, ratami i stroną /vouchery)
        const WZ = /^\s*(od\s+)?\d{2,5}\s?zł(\s*\/\s*[a-ząęółśżźćń.]+)?\s*$/i, bez = [];
        const w = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT); let n;
        while ((n = w.nextNode())) {
          if (!WZ.test(n.data)) continue;
          const el = n.parentElement; if (!el || !el.getClientRects().length) continue;
          if (el.closest('script,style,s,small,.klarna,.offer,.vpay,.zv,a[href^="/vouchery"],#zgody,option')) continue;
          const dd = el.closest('dd'); if (dd && dd.previousElementSibling && /voucher/i.test(dd.previousElementSibling.textContent)) continue;
          if (/voucher/i.test(el.textContent)) continue;
          if (n.previousSibling && n.previousSibling.nodeName === 'S') continue;
          bez.push(n.data.trim());
        }
        const dt = [...document.querySelectorAll('dl.thh-price > div')].map((x) => [x.querySelector('dt').textContent, x.querySelector('dd').textContent]);
        return { P, pary, bez, dt, h1: document.querySelectorAll('h1').length, pasek: !!document.querySelector('.la-pasek'),
          zle: /hialuronidaz/i.test(t) || /\[do ustalenia/.test(t), voucherOnline: (t.match(/voucher online (od )?\d+ zł/gi) || []).slice(0, 50) };
      });
      if (!promo && d.P) promo = d.P;
      const st = typeof r.status === 'function' ? r.status() : r;
      if (st !== 200) BLAD(`${nazwa} ${u}: HTTP ${st}`);
      if (err.length) BLAD(`${nazwa} ${u}: błędy JS: ${err.join(' | ')}`);
      if (zleZap.length) BLAD(`${nazwa} ${u}: nieudane zapytania: ${zleZap.join(', ')}`);
      if (d.h1 !== 1 || st !== 200) {
        const diag = await p.evaluate(() => ({ url: location.href, title: document.title, txt: document.body ? document.body.innerText.slice(0, 200) : '', html: document.documentElement.outerHTML.length,
          ma: { dc: !!document.querySelector('script[src*="dc.js"]'), tpl: document.querySelectorAll('template').length, root: !!document.getElementById('dc-root') } })).catch((e) => ({ blad: e.message }));
        const nag = r && typeof r.headers === 'function' ? r.headers() : {};
        BLAD(`${nazwa} ${u}: h1 = ${d.h1}, HTTP ${st}; diagnoza: ${JSON.stringify(diag)}; nagłówki: ${JSON.stringify({ server: nag.server, cc: nag['cache-control'], ct: nag['content-type'], len: nag['content-length'], age: nag.age, xc: nag['x-cache'] })}`);
      }
      if (d.zle) BLAD(`${nazwa} ${u}: zakazane słowo albo placeholder w tekście`);
      const pr = d.P && d.P.rabat;
      if (d.P && d.P.pasek && !d.pasek) BLAD(`${nazwa} ${u}: brak paska promocji`);
      if (pr && u !== '/vouchery') {
        const zleWyl = d.pary.filter(([s, nw]) => Math.round(liczba(s) * (100 - pr) / 100) !== liczba(nw));
        if (zleWyl.length) BLAD(`${nazwa} ${u}: złe wyliczenie rabatu: ${zleWyl.slice(0, 5).map((x) => x.join(' → ')).join('; ')}`);
        const bez = d.bez.filter((x) => !/^\s*(od\s+)?\d+\s?zł\s*$/.test(x) || true);
        if (bez.length) BLAD(`${nazwa} ${u}: ceny bez przekreślenia: ${[...new Set(bez)].slice(0, 8).join(', ')}`);
        // nagłówek zabiegu: voucher online = cena zabiegu po rabacie (gdy vouchery w promocji)
        if (d.dt.length === 2 && d.P.vmapa) {
          const stara = d.pary.length ? liczba(d.pary[0][0]) : NaN, vouch = liczba(d.dt[1][1]);
          const ocz = Math.min(Math.round(stara * 0.9), Math.round(stara * (100 - pr) / 100));
          if (vouch !== ocz) BLAD(`${nazwa} ${u}: voucher w nagłówku ${vouch} zł, a powinno być ${ocz} zł (${d.dt[1][0]})`);
        }
        if (!zleWyl.length && !bez.length) OK(`${nazwa} ${u}: ${d.pary.length} cen przekreślonych poprawnie`);
      } else if (!err.length && st === 200) OK(`${nazwa} ${u}`);
      await p.close();
    }

    // ---- strona voucherów: każdy voucher na zabieg, każdy wariant ----
    if (promo && promo.vmapa) {
      const p = await c.newPage(); const err = []; p.on('pageerror', (e) => err.push(e.message));
      await p.goto(B + '/vouchery', { waitUntil: 'networkidle' }); await p.waitForTimeout(900);
      const pr = promo.rabat, ids = await p.evaluate(() => [...document.querySelectorAll('button[id^="v-"]')].map((x) => x.id));
      let sprawdzone = 0;
      for (const id of ids) {
        await p.locator('#' + id).click(); await p.waitForTimeout(250);
        const grupy = await p.locator('.vvar .vchips button').count();
        for (let g = 0; g < Math.max(1, grupy); g++) {
          if (grupy) { await p.locator('.vvar .vchips button').nth(g).click(); await p.waitForTimeout(200); }
          const wiersze = await p.locator('.vvar .vrows button').count();
          for (let r = 0; r < Math.max(1, wiersze); r++) {
            if (wiersze) { await p.locator('.vvar .vrows button').nth(r).click(); await p.waitForTimeout(200); }
            const o = await p.evaluate(() => {
              const m = document.querySelector('.offer .orow.main'), rows = [...document.querySelectorAll('.offer .orow')];
              const s = m.querySelector('s.la-stara');
              return { s: s && s.textContent, nowa: m.querySelector('b').textContent.replace(s ? s.textContent : '', ''), gab: rows[1] && rows[1].querySelector('b').textContent };
            });
            const gab = liczba(o.gab), nowa = liczba(o.nowa), stara = liczba(o.s), ocz = Math.round(gab * (100 - pr) / 100);
            const opis = `${id}${grupy ? ' / grupa ' + (g + 1) : ''}${wiersze ? ' / wiersz ' + (r + 1) : ''}`;
            if (nowa !== ocz) BLAD(`${nazwa} /vouchery ${opis}: Do zapłaty ${nowa} zł, a po rabacie z ${gab} zł powinno być ${ocz} zł`);
            else if (!o.s) BLAD(`${nazwa} /vouchery ${opis}: brak przekreślonej ceny przy ${nowa} zł`);
            else if (stara !== Math.round(gab * 0.9)) BLAD(`${nazwa} /vouchery ${opis}: przekreślone ${stara} zł, zwykła cena online to ${Math.round(gab * 0.9)} zł`);
            sprawdzone++;
          }
        }
      }
      // voucher kwotowy: bez promocji
      await p.locator('button', { hasText: /^\s*Na kwotę\s*$/ }).first().click(); await p.waitForTimeout(300);
      const kw = await p.evaluate(() => ({ s: document.querySelectorAll('.offer s.la-stara').length, t: document.querySelector('.offer .orow.main').innerText }));
      if (kw.s) BLAD(`${nazwa} /vouchery: voucher kwotowy ma przekreślenie (${kw.t})`);
      if (err.length) BLAD(`${nazwa} /vouchery: błędy JS: ${err.join(' | ')}`);
      OK(`${nazwa} /vouchery: sprawdzonych wariantów ${sprawdzone}`);
      await p.close();
    }
  }

  // ---- płatność: kwota sesji Stripe = cena na stronie ----
  if (ZAMOWIEN > 0) {
    const c = await b.newContext();
    const proby = [{ typ: 'zabieg', zabieg: 'modelowanie-ust' }, { typ: 'zabieg', zabieg: 'stymulatory', g: '0', r: '2' }, { typ: 'kwota', va: '0' }].slice(0, ZAMOWIEN);
    const pr = promo && promo.vmapa ? promo.rabat : 0;
    const oczek = [Math.min(630, Math.round(700 * (100 - pr) / 100)), Math.min(720, Math.round(800 * (100 - pr) / 100)), 300];
    for (const [i, f] of proby.entries()) {
      const r = await c.request.post(B + '/voucher/zamow', { form: { email: 'audyt@ludwinekaesthetic.pl', zgoda: '1', ...f } });
      const j = await r.json().catch(() => ({}));
      if (!j.ok) BLAD(`płatność ${JSON.stringify(f)}: ${j.blad || r.status()}`);
      else if (j.kwota !== oczek[i]) BLAD(`płatność ${JSON.stringify(f)}: Stripe dostał ${j.kwota} zł, strona pokazuje ${oczek[i]} zł`);
      else OK(`płatność ${j.nazwa}: ${j.kwota} zł (zgodnie ze stroną), sesja Stripe ${/checkout\.stripe\.com/.test(j.url) ? 'OK' : j.url}`);
    }
  }
  console.log(`\nPromocja na stronie: ${JSON.stringify(promo && { rabat: promo.rabat, pasek: promo.pasek, vouchery: !!promo.vmapa })}`);
  console.log(`WYNIK: ${ok.length} OK, ${bledy.length} błędów`);
  await b.close();
  process.exit(bledy.length ? 1 : 0);
})();
