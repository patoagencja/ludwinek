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
HEAD_ALL = '''<meta name="theme-color" content="#1F1E1C">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/assets/favicon-32.png" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="/assets/apple-touch-icon.png">
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

def convert(name, imgs):
    key, var = name.rsplit('-', 1)
    s = open(os.path.join(SRC, name + '.html'), encoding='utf-8').read()
    s = images(s, imgs)
    s = s.replace('<script src="./support.js"></script>', '<script src="/assets/dc.js"></script>')
    s = re.sub(r'<link rel="stylesheet" href="https://fonts\.googleapis\.com/css2[^"]*">', '<link rel="stylesheet" href="/assets/fonts.css">', s)
    s = links(s)
    t, d = TITLE[key]
    s = re.sub(r'<title>.*?</title>', lambda m: f'<title>{t}</title>\n<meta name="description" content="{d}">', s, count=1, flags=re.S)
    s = s.replace('<meta charset="utf-8">', '<meta charset="utf-8">\n' + (HEAD_M if var == 'Mobile' else HEAD_D) + '\n' + HEAD_ALL, 1)
    s = s.replace('</body>', NAV + '</body>', 1)
    left = re.findall(r'[A-Za-z]+-(?:Desktop|Mobile)\.dc\.html', s)
    assert not left, (name, left[:5])
    return s

def main():
    os.makedirs(OUT, exist_ok=True); os.makedirs(IMG, exist_ok=True)
    jp = os.path.join(R, 'zrodlo', 'imgs.json')
    imgs = json.load(open(jp)) if os.path.exists(jp) else {}
    n = 0
    for f in sorted(os.listdir(SRC)):
        if not f.endswith('.html') or f.startswith('Maile'): continue
        name = f[:-5]
        open(os.path.join(OUT, f), 'w', encoding='utf-8').write(convert(name, imgs)); n += 1
    print('stron:', n, 'zdjęć:', len(os.listdir(IMG)))

if __name__ == '__main__':
    main()
