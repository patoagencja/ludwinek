# Podgląd projektu (dokumenty "dc" z przełącznika) → prawdziwa strona w public/.
#   zrodlo/strony/<Klucz>-<Desktop|Mobile>.html   dokumenty z podglądu (zdjęcia jako /_blob/<klucz>)
#   zrodlo/imgs.json (opcjonalnie)                 zdjęcia z podglądu; potrzebne tylko, gdy w public/img czegoś brakuje
# Wynik: public/_strony/*.html (router public/index.php wybiera wersję telefon/komputer), public/img/*.
# Uruchom: python3 -I narzedzia/konwertuj.py   (z katalogu repo)
import base64, json, os, re, unicodedata

R = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(R, 'zrodlo', 'strony')
OUT = os.path.join(R, 'public', '_strony')
IMG = os.path.join(R, 'public', 'img')

SLUG = {'Home': '/', 'Zabieg': '/modelowanie-ust', 'Zabiegi': '/zabiegi', 'Dermapen': '/dermapen',
        'Blizny': '/terapia-blizn-i-rozstepow', 'Pielegnacja': '/zabiegi-pielegnacyjne', 'EventGlow': '/event-glow',
        'Signature': '/signature-glow', 'Mezczyzni': '/dla-mezczyzn', 'Problem': '/blizny-potradzikowe',
        'Cennik': '/cennik', 'Voucher': '/vouchery', 'Omnie': '/o-mnie', 'Informacje': '/informacje',
        'Kontakt': '/kontakt', 'Maile': '/'}
TITLE = {
    'Home': ('Ludwinek Aesthetic · kosmetologia i medycyna estetyczna, Warszawa',
             'Modelowanie ust, Dermapen 4.0 i terapie skóry dobierane po konsultacji. Gabinet Julii Ludwinek przy Placu Konstytucji w Warszawie.'),
    'Zabieg': ('Modelowanie ust · Ludwinek Aesthetic, Warszawa',
               'Modelowanie i powiększanie ust kwasem hialuronowym, z proporcjami dobranymi do twarzy. Konsultacja, projekt i wizyta kontrolna.'),
    'Zabiegi': ('Zabiegi · Ludwinek Aesthetic, Warszawa', 'Wszystkie zabiegi gabinetu Ludwinek Aesthetic z cenami: usta, skóra, blizny, skóra głowy, brwi i rzęsy.'),
    'Dermapen': ('Dermapen 4.0 · Ludwinek Aesthetic, Warszawa', 'Mikronakłucia na oryginalnym Dermapen 4.0 w certyfikowanym gabinecie Premium Dermapen Clinic.'),
    'Blizny': ('Terapia blizn i rozstępów · Ludwinek Aesthetic, Warszawa', 'Seria zabiegów spłycających blizny potrądzikowe, pooperacyjne i rozstępy.'),
    'Pielegnacja': ('Zabiegi pielęgnacyjne · Ludwinek Aesthetic, Warszawa', 'Oczyszczanie wodorowe, peelingi i zabiegi nawilżające bez okresu rekonwalescencji.'),
    'EventGlow': ('Event Glow · Ludwinek Aesthetic, Warszawa', 'Szybkie rozświetlenie skóry przed ważnym wyjściem, bez rekonwalescencji.'),
    'Signature': ('Ludwinek Signature Glow · Ludwinek Aesthetic, Warszawa', 'Autorski rytuał pielęgnacyjny Julii Ludwinek, z etapami dobieranymi do skóry.'),
    'Mezczyzni': ('Dla mężczyzn · Ludwinek Aesthetic, Warszawa', 'Blizny, pory, struktura skóry i skóra głowy: zabiegi dla mężczyzn uporządkowane według problemu.'),
    'Problem': ('Blizny potrądzikowe · Ludwinek Aesthetic, Warszawa', 'Jak spłycić blizny potrądzikowe: zabiegi, seria i czego się spodziewać.'),
    'Cennik': ('Cennik · Ludwinek Aesthetic, Warszawa', 'Ceny zabiegów w gabinecie Ludwinek Aesthetic.'),
    'Voucher': ('Vouchery · Ludwinek Aesthetic, Warszawa', 'Vouchery na zabieg albo na kwotę.'),
    'Omnie': ('O mnie · Julia Ludwinek · Ludwinek Aesthetic', 'Julia Ludwinek, kosmetolog. Specjalizacja: modelowanie ust i terapie skóry.'),
    'Informacje': ('Informacje dla klientek · Ludwinek Aesthetic', 'Przygotowanie do zabiegu, zalecenia, przeciwwskazania, zasady rezerwacji i FAQ.'),
    'Kontakt': ('Kontakt · Ludwinek Aesthetic, Plac Konstytucji 1, Warszawa', 'Adres, godziny i formularz „Zapytaj o termin”. Plac Konstytucji 1, 00-647 Warszawa.'),
}

def slugify(t):
    t = unicodedata.normalize('NFKD', t.replace('ł', 'l').replace('Ł', 'L'))
    t = ''.join(c for c in t if not unicodedata.combining(c)).lower()
    return re.sub(r'[^a-z0-9]+', '-', t).strip('-')

PROBLEMS = ['Blizny potrądzikowe', 'Przebarwienia', 'Rozszerzone pory', 'Nierówna struktura skóry', 'Przesuszenie',
            'Utrata jędrności', 'Zmarszczki', 'Oznaki starzenia', 'Rozstępy', 'Zmęczona lub szara skóra', 'Skóra głowy i włosy']
PSLUG = {slugify(p): p for p in PROBLEMS}

NAV = r'''<script id="la-nav">
/* nawigacja na prawdziwej stronie (zamiast skryptu przełącznika podglądu): przewijanie do #kotwic z poprawką na
   przyklejony nagłówek, otwieranie zwiniętych sekcji, #kotwice w adresie po wczytaniu, #znajdz-<problem> na stronie głównej */
(function(){var D=document,H=D.documentElement,jt=0,st=0,ht=0;
function slug(t){return String(t).replace(/ł/g,'l').replace(/Ł/g,'L').normalize('NFKD').replace(/[̀-ͯ]/g,'').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');}
function hdOff(){var hd=D.querySelector('.hd'),o=16;if(hd){var p=getComputedStyle(hd).position;if(p==='sticky'||p==='fixed')o+=hd.getBoundingClientRect().height;}return o;}
function toggleOf(t){var b=t.matches&&t.matches('button')?t:(t.querySelector?t.querySelector(':scope>button[aria-expanded],:scope>button[aria-pressed]'):null);return b&&(b.getAttribute('aria-expanded')==='false'||b.getAttribute('aria-pressed')==='false')?b:null;}
function dest(id){var t=D.getElementById(id);if(!t)return null;var c=t.getAttribute('aria-controls'),p=c?D.getElementById(c):null;return p||t;}
function jump(id,smooth){var d=dest(id);if(!d)return;var y=Math.max(0,Math.round(d.getBoundingClientRect().top+(window.pageYOffset||0)-hdOff()));window.scrollTo({top:y,behavior:smooth?'smooth':'auto'});}
function go(id){var t=D.getElementById(id);if(!t)return false;if(window.__laUnlock){try{window.__laUnlock(true);}catch(x){}}var b=toggleOf(t);if(b)b.click();H.classList.add('la-jump');H.classList.remove('cta-on');clearTimeout(jt);clearTimeout(st);clearTimeout(ht);ht=setTimeout(function(){jump(id,true);var e=D.getElementById(id);if(e){e.classList.remove('la-hit');void e.offsetWidth;e.classList.add('la-hit');}},b?60:0);st=setTimeout(function(){var d=dest(id);if(d&&Math.abs(d.getBoundingClientRect().top-hdOff())>6)jump(id,false);},950);jt=setTimeout(function(){H.classList.remove('la-jump');H.classList.remove('cta-on');var e=D.getElementById(id);if(e)e.classList.remove('la-hit');},1900);return true;}
window.__goHash=go;
function pick(id){if(id.indexOf('znajdz-')!==0)return id;var z=D.getElementById('znajdz');if(!z)return id;var want=id.slice(7),bs=z.querySelectorAll('button');for(var i=0;i<bs.length;i++){if(slug(bs[i].textContent).replace(/^\d+-?/,'')===want){if(bs[i].getAttribute('aria-pressed')!=='true')bs[i].click();break;}}return 'znajdz';}
function hashGo(h){var id=decodeURIComponent(String(h).replace(/^#/,''));if(!id)return;var n=0;(function t(){n++;var ok=false;try{var x=pick(id);ok=window.__goHash&&window.__goHash(x);}catch(e){}if(!ok&&n<30)setTimeout(t,150);})();}
D.addEventListener('click',function(e){var a=e.target.closest&&e.target.closest('a[href]');if(!a)return;var h=a.getAttribute('href')||'';
 if(h.charAt(0)==='#'){e.preventDefault();if(h.length>1){hashGo(h);try{history.replaceState(null,'',h);}catch(x){}}else window.scrollTo({top:0,behavior:'smooth'});return;}
 var u;try{u=new URL(a.href,location.href);}catch(x){return;}
 if(u.origin===location.origin&&u.pathname===location.pathname&&!a.target){e.preventDefault();if(u.hash){hashGo(u.hash);try{history.replaceState(null,'',u.hash);}catch(x){}}else window.scrollTo({top:0,behavior:'smooth'});}
},true);
window.addEventListener('load',function(){if(location.hash)setTimeout(function(){hashGo(location.hash);},200);});
})();
</script>
'''

HEAD_D = '''<meta name="viewport" content="width=device-width, initial-scale=1">
<script>(function(){var w=window.innerWidth,c=document.cookie;if(w<700&&c.indexOf('widok=')<0){document.cookie='widok=m;path=/;max-age=31536000;samesite=lax';location.reload();return;}function z(){var w=window.innerWidth;document.documentElement.style.zoom=w<1100?String(w/1440):'';}z();window.addEventListener('resize',z);})();</script>'''
HEAD_M = '''<meta name="viewport" content="width=device-width, initial-scale=1">
<script>(function(){var w=window.innerWidth,c=document.cookie;if(w>1000&&c.indexOf('widok=')<0){document.cookie='widok=d;path=/;max-age=31536000;samesite=lax';location.reload();}})();</script>'''
HEAD_ALL = '''<script>if(location.protocol==="http:"&&/(^|\\.)ludwinekaesthetic\\.pl$/.test(location.hostname))location.replace("https://ludwinekaesthetic.pl"+location.pathname+location.search+location.hash);</script>
<meta name="theme-color" content="#1F1E1C">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/assets/favicon-32.png" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="/assets/apple-touch-icon.png">
<script src="/assets/zgody.js" defer></script>
<link rel="preload" href="/assets/fonts/cormorant-garamond-latin-300-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/inter-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>'''

def images(s, imgs):
    def rep(m):
        k = m.group(1)
        found = [f for f in os.listdir(IMG) if f.startswith(k + '.')]
        if not found:
            d = imgs[k]; mime = d[5:d.find(';')]; ext = {'image/jpeg': 'jpg', 'image/png': 'png', 'image/webp': 'webp', 'image/svg+xml': 'svg'}[mime]
            open(os.path.join(IMG, f'{k}.{ext}'), 'wb').write(base64.b64decode(d[d.find(',') + 1:])); found = [f'{k}.{ext}']
        return '/img/' + found[0]
    return re.sub(r'/_blob/([0-9a-f]{32})', rep, s)

def links(s):
    # problem links: the anchor text says which problem → home picker (#znajdz-<problem>), Blizny → its own page
    def prob(m):
        txt = re.sub(r'<[^>]+>', ' ', m.group(3)); key = slugify(txt)
        hit = next((sl for sl in PSLUG if sl and sl in key), None)
        href = '/blizny-potradzikowe' if (hit is None or hit == 'blizny-potradzikowe') else '/#znajdz-' + hit
        return f'<a {m.group(1)}href="{href}"{m.group(2)}>{m.group(3)}</a>'
    s = re.sub(r'<a ([^>]*?)href="Problem-(?:Desktop|Mobile)\.dc\.html"([^>]*)>(.*?)</a>', prob, s, flags=re.S)
    # data pairs in scripts: ["Przebarwienia", "Problem-Desktop.dc.html"]
    def pair(m):
        sl = slugify(m.group(2)); href = '/blizny-potradzikowe' if sl == 'blizny-potradzikowe' or sl not in PSLUG else '/#znajdz-' + sl
        return m.group(1) + m.group(2) + m.group(3) + href + m.group(4)
    s = re.sub(r'''(["'])([^"'\n]{3,40})(\1\s*,\s*["'])Problem-(?:Desktop|Mobile)\.dc\.html(["'])''', pair, s)
    # Home-Mobile drawer: href built from a name → key map
    s = s.replace("href: MF[name] + '-Mobile.dc.html'", 'href: (' + json.dumps({k: v for k, v in SLUG.items()}) + ')[MF[name]]')
    def gen(m):
        base = SLUG[m.group(1)]; h = m.group(3) or ''
        return (base if base != '/' or not h else '/') + h
    s = re.sub(r'\b(' + '|'.join(SLUG) + r')-(Desktop|Mobile)\.dc\.html(#[\w-]*)?', gen, s)
    return s

def legal(s):
    # stopka: prawdziwe dokumenty i ustawienia cookies; regulamin voucherów wróci razem ze sprzedażą voucherów;
    # Instagram/Facebook wrócą z prawdziwymi adresami profili
    s = re.sub(r'<span class="flegal">.*?</span>', '<span class="flegal"><a href="/regulamin">Regulamin</a> · <a href="/polityka-prywatnosci">Polityka prywatności</a> · <a href="#ustawienia-cookies">Ustawienia cookies</a></span>', s, flags=re.S)
    s = re.sub(r'<span class="fsoc">.*?</span>', '', s, flags=re.S)
    s = s.replace('href="/informacje">polityce prywatności', 'href="/polityka-prywatnosci">polityce prywatności')
    s = s.replace('href="/informacje">politykę prywatności', 'href="/polityka-prywatnosci">politykę prywatności')
    # zgoda w formularzu kontaktowym: informacja o danych + zgoda na informacje o zdrowiu, jeśli ktoś je wpisze (RODO art. 9)
    s = s.replace('Wyrażam zgodę na kontakt w sprawie wizyty. Szczegóły w <a href="/polityka-prywatnosci">polityce prywatności</a>.',
                  'Zapoznałam się z <a href="/polityka-prywatnosci">informacją o przetwarzaniu danych</a>. Proszę, nie wpisuj tu informacji o zdrowiu: omówimy je na konsultacji.')
    return s

VSOON_CSS = """
/* vouchery wkrótce: sprzedaż online jeszcze nie działa (brak płatności), Klarna wyłączona */
.klarna{display:none!important}
.la a.vsoon-btn,.la a.vsoon-btn:hover{background:transparent!important;border-color:currentColor!important;color:inherit!important;opacity:.5;letter-spacing:.06em;white-space:nowrap;text-align:center;justify-content:center}
@media (max-width:374px){.la a.vsoon-btn{font-size:10.5px;letter-spacing:.04em;padding:0 6px;white-space:normal;line-height:1.25}}
.vsoon{display:flex;flex-direction:column;gap:12px;margin-top:20px;padding:22px 22px 24px;background:#EFEBE4}
.vsoon .h3{margin:0}
.vsoon .btn{align-self:flex-start;margin-top:4px}
"""

# Sprzedaż voucherów przez Stripe (public/_vouchery.php). Konwerter robi dwie wersje stron: z zakupem (_strony/)
# i z „Vouchery wkrótce” (_strony-wkrotce/). index.php podaje tę drugą, dopóki na serwerze nie ma klucza Stripe.
SPRZEDAZ_VOUCHEROW = True
VOUCHER_WAZNOSC = '12 miesięcy'

VP_GO = """        if (this.state.vpBusy) return;
        const fd = new FormData();
        [['email', v.mail], ['zgoda', v.ok ? '1' : ''], ['typ', st.vt === 0 ? 'zabieg' : 'kwota'], ['zabieg', vT.slug || ''], ['g', String(st.vzg || 0)], ['r', String(st.vzr || 0)], ['va', String(st.va)]].forEach(([k, x]) => fd.append(k, x));
        this.setState({ vpBusy: true, vpOk: null, vpErr: {} });
        fetch('/voucher/zamow', { method: 'POST', body: fd, credentials: 'same-origin' }).then((r) => r.json().catch(() => ({}))).then((j) => {
          if (j.ok && j.url) {
            try { window.laZdarzenie && window.laZdarzenie('begin_checkout', { currency: 'PLN', value: j.kwota, items: [{ item_id: j.zabieg || 'voucher-kwotowy', item_name: j.nazwa, item_category: 'Voucher', price: j.kwota, quantity: 1 }] }); } catch (x) {}
            setTimeout(() => { location.href = j.url; }, 200); return;
          }
          if (j.bledy) { const k0 = Object.keys(j.bledy)[0]; this.setState({ vpBusy: false, vpErr: j.bledy, vpTry: true }, () => { const el = document.getElementById(k0 === 'mail' ? 'vmail' : 'vok'); this.vpFocus(el); }); return; }
          this.setState({ vpBusy: false, vpOk: j.blad || 'Nie udało się otworzyć płatności. Spróbuj ponownie albo zadzwoń: 737 460 105.' }, () => this.vpShow(document.getElementById('vpay')));
        }).catch(() => this.setState({ vpBusy: false, vpOk: 'Brak połączenia z internetem. Spróbuj ponownie.' }));"""

def vouchers(s, key):
    # Klarna i raty: bez Klarny (teksty w danych; pudełka z ratami chowa CSS)
    if SPRZEDAZ_VOUCHEROW:
        zam = [(', także w 3 ratach 0% z Klarna.', '.'), ('Płatność BLIK, kartą lub w 3 ratach 0% z Klarna.', 'Płatność BLIK, Przelewy24 lub kartą.'),
               ('Vouchery online: BLIK, karta, przelew, Klarna (3 raty 0%)', 'Vouchery online: BLIK, Przelewy24, karta'),
               (' Także w 3 ratach 0% z Klarna.', '')]
    else:
        # przyciski „Kup voucher” → „Vouchery wkrótce” (wyszarzone; prowadzą do oferty, gdzie widać, że sprzedaż rusza wkrótce)
        s = re.sub(r'(<a class="btn[^"]*)("[^>]*href="/vouchery[^"]*"[^>]*>)Kup voucher(?: online)?</a>', r'\1 vsoon-btn\2Vouchery wkrótce</a>', s)
        zam = [(', także w 3 ratach 0% z Klarna.', '.'), ('Płatność BLIK, kartą lub w 3 ratach 0% z Klarna.', 'Sprzedaż online wkrótce.'),
               ('Vouchery online: BLIK, karta, przelew, Klarna (3 raty 0%)', 'Vouchery online: wkrótce'),
               (' Także w 3 ratach 0% z Klarna.', ''), ('voucher kupisz online w kilka minut', 'voucher kupisz online już wkrótce')]
    for a, b in zam:
        s = s.replace(a, b)
    s = re.sub(r', \["Czy mogę zapłacić w ratach\? Tak, vouchery w 3 ratach 0% z Klarna"\]', '', s)
    s = re.sub(r'"Czy mogę zapłacić w ratach\? Tak, vouchery w 3 ratach 0% z Klarna",\s*', '', s)
    s = re.sub(r',\s*\["Jak działa płatność Klarna\?", "[^"]*", "klarna"\]', '', s)
    s = s.replace('[6] miesięcy', VOUCHER_WAZNOSC)
    s = s.replace(', "Gdzie zaparkować? [informacja o parkingu / dojeździe]"', '')  # wróci z prawdziwą informacją o dojeździe
    if key == 'Voucher' and SPRZEDAZ_VOUCHEROW:
        for a, b in [
            ('href="/informacje#zasady-rezerwacji">regulamin voucherów</a>', 'href="/regulamin-voucherow" target="_blank">regulamin voucherów</a>'),
            ('<button type="button" class="btn vgo" onClick="{{ vpGo }}">Przejdź do płatności</button>', '<button type="button" class="btn vgo" onClick="{{ vpGo }}">{{ vpBtn }}</button>'),
            ("vpOpen: !!st.vpOk, vpMail: st.vpOk || '',", "vpOpen: !!st.vpOk, vpMail: st.vpOk || '', vpBtn: st.vpBusy ? 'Otwieram płatność…' : 'Kupuję i płacę',"),
            ("        this.setState({ vpErr: {}, vpTry: false, vpOk: v.mail }, () => { this.vpShow(document.getElementById('vpay')); this.vpFocus(document.getElementById('vpay-h')); });", VP_GO),
            ('<p class="sm" style="margin: 14px 0 0">BLIK · karta · przelew · Klarna</p>',
             '<p class="sm" style="margin: 14px 0 0">BLIK · Przelewy24 · karta. Płatność obsługuje Stripe. Voucher z kodem przyjdzie e-mailem zaraz po płatności. Masz 14 dni na odstąpienie od umowy.</p>'),
        ]:
            assert s.count(a) == 1, ('voucher', a[:60])
            s = s.replace(a, b)
        s, n = re.subn(r'<sc-if value="\{\{ vpOpen \}\}".*?</div></sc-if>',
                       '<sc-if value="{{ vpOpen }}" hint-placeholder-val="{{ false }}"><div class="vpay" id="vpay" role="alert"><p class="ferr" style="margin: 0">{{ vpMail }}</p></div></sc-if>', s, count=1, flags=re.S)
        assert n == 1, 'blok vpay'
    elif key == 'Voucher':
        s = s.replace('<span>Do zapłaty</span>', '<span>Cena online</span>')
        s, n = re.subn(r'<div class="\{\{ vpC\.mail \}\}".*?BLIK · karta · przelew · Klarna</p>',
            '<div class="vsoon"><p class="lbl">Sprzedaż online</p><p class="h3">Vouchery kupisz tu <em>już wkrótce.</em></p>'
            '<p class="sm" style="margin: 0">Masz pytanie o voucher? Napisz albo zadzwoń.</p>'
            '<a class="btn btn-o" href="/kontakt#formularz">Zapytaj o voucher</a></div>', s, count=1, flags=re.S)
        assert n == 1, 'blok płatności vouchera nie znaleziony'
    h = s.find('</helmet>'); i = s.rfind('</style>', 0, h)
    return s[:i] + VSOON_CSS + s[i:]

MAP_SRC = 'https://www.google.com/maps?q=Ludwinek+Aesthetic,+Plac+Konstytucji+1,+00-647+Warszawa&amp;output=embed'
MAP_IFRAME = ('<iframe class="gmap-f" src="' + MAP_SRC + '" title="Mapa: Ludwinek Aesthetic, Plac Konstytucji 1, Warszawa" '
              'loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>')
MAP_CSS = """
/* mapa Google (osadzona, widoczna od razu) w miejscu atrapy mapy z projektu */
.map.gmap{overflow:hidden}
.map.gmap::before{display:none}
.map.gmap .gmap-f{position:absolute;top:0;left:0;width:100%;height:100%;border:0;filter:grayscale(.35) sepia(.08)}
.map.gmap .gmap-open{position:absolute;z-index:2;left:12px;bottom:12px;right:auto;padding:8px 12px;background:rgba(250,248,245,.94);color:#3E3A35;text-decoration:none}
.map.gmap .gmap-open:hover{color:#141414}
"""

def mapy(s):
    # Home: link-atrapa „Otwórz w Google Maps” → prawdziwa mapa z małym linkiem w rogu
    s = re.sub(r'<a class="map([^"]*)" href="(https://www\.google\.com/maps/search/[^"]*)" target="_blank" rel="noopener" aria-label="[^"]*"( style="[^"]*")?><span class="pin"></span><span class="cap"[^>]*>Otwórz w Google Maps →</span></a>',
               lambda m: f'<div class="map{m.group(1)} gmap"{m.group(3) or ""}>{MAP_IFRAME}<a class="cap gmap-open" href="{m.group(2)}" target="_blank" rel="noopener">Otwórz w Google Maps →</a></div>', s)
    # Kontakt: atrapa z podpisem „Mapa Google”
    s = re.sub(r'<div class="map" role="img" aria-label="Mapa: Plac Konstytucji 1"><span class="pin"></span><span class="cap"[^>]*>Mapa Google: Plac Konstytucji 1</span></div>',
               '<div class="map gmap">' + MAP_IFRAME + '</div>', s)
    if 'class="gmap-f"' in s:
        h = s.find('</helmet>'); i = s.rfind('</style>', 0, h); s = s[:i] + MAP_CSS + s[i:]
    return s

def kontakt(s, key):
    if key != 'Kontakt':
        return s
    # wiersze bez prawdziwych danych: wrócą z danymi
    s = s.replace('<div><dt>Dojazd</dt><dd>[parking / komunikacja: do uzupełnienia]</dd></div>', '')
    s = s.replace('<div><dt>Social</dt><dd><a href="https://instagram.com/">Instagram</a> · <a href="https://facebook.com/">Facebook</a></dd></div>', '')
    return formularz(s)

# formularz „Zapytaj o termin”: prawdziwa wysyłka do public/_formularz.php (POST /formularz), ukryte pole na boty,
# czas wypełniania, blokada przycisku w trakcie wysyłki i komunikat, gdy wysyłka się nie uda; bez kopii do klientki
FORM_SEND = ("""        if (this.state.kfBusy) return;
        const fd = new FormData(); Object.entries({ name: v.name, tel: v.tel, email: v.mail, zabieg: v.zab, termin: v.term, msg: v.msg, zgoda: v.ok ? '1' : '', strona: (f.elements.strona || {}).value || '', t: String(Date.now() - (window.__kfT0 || Date.now())) }).forEach(([k, x]) => fd.append(k, x));
        this.setState({ kfBusy: true, kfFail: '' });
        fetch('/formularz', { method: 'POST', body: fd, credentials: 'same-origin' }).then((r) => r.json().catch(() => ({})).then((j) => ({ r, j }))).then(({ r, j }) => {
          if (r.ok && j.ok) { try { window.laZdarzenie && window.laZdarzenie('generate_lead', { zabieg: v.zab || '', formularz: 'kontakt' }); } catch (x) {} this.setState({ kfBusy: false, kfSent: v, kfErr: {}, kfTry: false }, () => { const h = document.getElementById('kf-done-h'); this.kfFocus(h); this.kfShow(document.getElementById('kf-done')); }); return; }
          if (r.status === 422 && j.bledy) { this.setState({ kfBusy: false, kfErr: j.bledy, kfTry: true }); return; }
          throw new Error(String(r.status));
        }).catch((x) => this.setState({ kfBusy: false, kfFail: x && x.message === '429' ? 'Wysłano już kilka zapytań z tego urządzenia. Zadzwoń proszę: 737 460 105.' : 'Nie udało się wysłać wiadomości. Spróbuj ponownie albo zadzwoń: 737 460 105.' }));""")

def formularz(s):
    a = "        this.setState({ kfSent: v, kfErr: {}, kfTry: false }, () => { const h = document.getElementById('kf-done-h'); this.kfFocus(h); this.kfShow(document.getElementById('kf-done')); });"
    assert s.count(a) == 1
    s = s.replace(a, FORM_SEND)
    # pomiar: błędy w formularzu (które pola) dla analityki
    a = "          this.setState({ kfErr: err, kfTry: true }, () =>"
    assert s.count(a) == 1
    s = s.replace(a, "          try { window.laFormularzBlad && window.laFormularzBlad(ks.join(',')); } catch (x) {}\n" + a)
    a = 'kfOpen: !kfS, kfDone: !!kfS, kfE,'
    assert s.count(a) == 1
    s = s.replace(a, a + " kfFail: st.kfFail || '', kfBtn: st.kfBusy ? 'Wysyłam…' : 'Wyślij zapytanie', kfBusy: !!st.kfBusy,")
    s = s.replace("kfSent: null, kfErr: {}, kfTry: false };", "kfSent: null, kfErr: {}, kfTry: false, kfBusy: false, kfFail: '' };\n    window.__kfT0 = Date.now();", 1)
    a = '<div style="grid-column: 1 / -1" class="kf-send"><button type="submit" class="btn">Wyślij zapytanie</button>'
    assert s.count(a) == 1
    s = s.replace(a, '<div class="kf-hp" aria-hidden="true"><label for="k9">Strona www</label><input id="k9" name="strona" tabindex="-1" autocomplete="off"></div>'
                  '<sc-if value="{{ kfFail }}" hint-placeholder-val="{{ false }}"><p class="ferr" role="alert" style="grid-column: 1 / -1; margin: 0">{{ kfFail }}</p></sc-if>'
                  '<div style="grid-column: 1 / -1" class="kf-send"><button type="submit" class="btn">{{ kfBtn }}</button>')
    s, n = re.subn(r'<sc-if value="\{\{ kfHasMail \}\}".*?</sc-if>', '', s, count=1, flags=re.S)
    assert n == 1
    s = s.replace('</style>', '.kf-hp{position:absolute!important;left:-9999px!important;width:1px;height:1px;overflow:hidden}\n</style>', 1)
    return s

def convert(name, imgs):
    key, var = name.rsplit('-', 1)
    s = open(os.path.join(SRC, name + '.html'), encoding='utf-8').read()
    s = images(s, imgs)
    s = s.replace('<script src="./support.js"></script>', '<script src="/assets/dc.js"></script>')
    s = re.sub(r'<link rel="stylesheet" href="https://fonts\.googleapis\.com/css2[^"]*">', '<link rel="stylesheet" href="/assets/fonts.css">', s)
    s = links(s)
    s = legal(s)
    s = vouchers(s, key)
    s = kontakt(s, key)
    s = mapy(s)
    t, d = TITLE[key]
    s = re.sub(r'<title>.*?</title>', lambda m: f'<title>{t}</title>\n<meta name="description" content="{d}">', s, count=1, flags=re.S)
    s = s.replace('<meta charset="utf-8">', '<meta charset="utf-8">\n' + (HEAD_M if var == 'Mobile' else HEAD_D) + '\n' + HEAD_ALL, 1)
    s = s.replace('</body>', NAV + '</body>', 1)
    left = re.findall(r'[A-Za-z]+-(?:Desktop|Mobile)\.dc\.html', s)
    assert not left, (name, left[:5])
    return s

def main():
    global SPRZEDAZ_VOUCHEROW
    os.makedirs(IMG, exist_ok=True)
    jp = os.path.join(R, 'zrodlo', 'imgs.json')
    imgs = json.load(open(jp)) if os.path.exists(jp) else {}
    for sprzedaz, out in [(True, OUT), (False, OUT + '-wkrotce')]:
        SPRZEDAZ_VOUCHEROW = sprzedaz
        os.makedirs(out, exist_ok=True)
        open(os.path.join(out, '.htaccess'), 'w').write('Require all denied\n')
        n = 0
        for f in sorted(os.listdir(SRC)):
            if not f.endswith('.html') or f.startswith('Maile'): continue
            open(os.path.join(out, f), 'w', encoding='utf-8').write(convert(f[:-5], imgs)); n += 1
        print(os.path.basename(out) + ':', n, 'stron')
    print('zdjęć:', len(os.listdir(IMG)))

if __name__ == '__main__':
    main()
