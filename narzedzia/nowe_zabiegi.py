#!/usr/bin/env python3
"""Podstrony zabiegów, które w projekcie nie miały własnej strony (prośba Julii, 11.2026).

Robi zrodlo/strony/<Klucz>-{Desktop,Mobile}.html na wzór strony Dermapen (hero, o zabiegu, ceny z wariantami,
przebieg, FAQ, podobne; bez sekcji opinii i certyfikatu Dermapen) i podpina linki do nich w pozostałych stronach.
Ceny bierze z danych voucherów (Voucher-*.html: cena w gabinecie i online). Teksty są szkicem do akceptacji Julii;
po wygenerowaniu można je poprawiać tutaj (i uruchomić skrypt ponownie) albo w panelu /panel.

Użycie: python3 -I narzedzia/nowe_zabiegi.py && python3 -I narzedzia/konwertuj.py
"""
import json
import os
import re

R = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(R, 'zrodlo', 'strony')
BOOKSY = 'https://booksy.com/pl-pl/307000_ludwinek-aesthetic_medycyna-estetyczna_3_warszawa'
KONSULTACJA = ['Czy konsultacja jest płatna?', 'Nie, konsultacja przed zabiegiem jest bezpłatna. Na niej dobieramy zabieg, preparat i plan.']

# klucz strony: dane (vslug = slug vouchera i kotwicy na /zabiegi)
ZABIEGI = {
    'Hialuronowy': dict(
        vslug='usuwanie-kwasu-hialuronowego', nazwa='Usuwanie kwasu hialuronowego', h1='Usuwanie kwasu <em>hialuronowego</em>',
        lead='Korekta lub rozpuszczenie wcześniejszego wypełniacza z kwasu hialuronowego, gdy efekt jest nierówny, przesadzony albo przemieścił się. Zawsze po konsultacji i kwalifikacji.',
        cele=['Rozpuszczenie niechcianego wypełniacza', 'Wyrównanie nierówności i grudek', 'Powrót do naturalnego kształtu', 'Przygotowanie do ponownego modelowania'],
        img=('8a332e8293c6ccfa4ed299a663407301', 'Usuwanie kwasu hialuronowego w gabinecie'),
        mini=[('Czas', 'ok. 30–45 min'), ('Efekt', 'zwykle 1–2 dni'), ('Powrót', 'od razu'), ('Kontrola', 'po 2 tyg.')],
        h2='Gdy wcześniejszy efekt <em>nie wyszedł</em>',
        p1='Zabieg polega na podaniu preparatu, który rozkłada kwas hialuronowy z wcześniejszego wypełnienia. Pozwala skorygować nierówny, przesadzony lub przemieszczony efekt, także po zabiegu wykonanym w innym gabinecie.',
        p2='Każdy zabieg poprzedza konsultacja, wywiad i kwalifikacja. Jeśli planujesz ponowne modelowanie, ustalimy bezpieczny odstęp między zabiegami.',
        dla=['nierówny lub przesadzony efekt modelowania ust', 'grudki albo przemieszczony wypełniacz', 'chęć powrotu do naturalnego wyglądu'],
        nie=['ciąża i karmienie piersią', 'uczulenie na jad owadów błonkoskrzydłych (po konsultacji)', 'stan zapalny lub infekcja w miejscu zabiegu'],
        ceny_h2='Cena <em>zabiegu</em>', wariant_opis='Usuwanie kwasu hialuronowego, zakres ustalamy na konsultacji.',
        kroki=[('Konsultacja', 'Ocena wcześniejszego wypełnienia, wywiad i kwalifikacja.'), ('Plan', 'Ustalamy, które miejsca korygujemy i w jakim zakresie.'),
               ('Zabieg', 'Precyzyjne podanie preparatu w wybrane miejsca.'), ('Kontrola', 'Po około 2 tygodniach oceniamy efekt i planujemy dalsze kroki.')],
        faq=[['Czy zabieg boli?', 'Odczuwalne jest krótkie ukłucie i pieczenie w miejscu podania. Sam zabieg trwa krótko.'],
             ['Kiedy widać efekt?', 'Zwykle w ciągu 1–2 dni. Pełną ocenę robimy na wizycie kontrolnej.'],
             ['Czy usunę wypełniacz zrobiony w innym gabinecie?', 'Tak, jeśli to kwas hialuronowy. Na konsultacji ocenię sytuację i zakwalifikuję Cię do zabiegu.'],
             ['Kiedy mogę ponownie zrobić modelowanie ust?', 'Zwykle po około 2 tygodniach. Dokładny termin ustalimy na wizycie kontrolnej.'], KONSULTACJA],
        podobne=['Zabieg', 'Stymulatory', 'Mezoterapia']),
    'Stymulatory': dict(
        vslug='stymulatory', nazwa='Stymulatory tkankowe', h1='Stymulatory <em>tkankowe</em>',
        lead='Preparaty, które pobudzają skórę do odnowy i produkcji kolagenu. Poprawiają jędrność, gęstość i nawilżenie skóry twarzy, także w okolicy oczu.',
        cele=['Większa jędrność i gęstość skóry', 'Głębokie nawilżenie', 'Spłycenie drobnych zmarszczek', 'Świeższy wygląd okolicy oczu'],
        img=('1176910073710fd964b814c625b757d7', 'Rozświetlona skóra po zabiegu'),
        mini=[('Czas', 'ok. 45 min'), ('Seria', '2–4 zabiegi'), ('Odstęp', '2–4 tyg.'), ('Powrót', 'po 1–2 dniach')],
        h2='Skóra, która <em>sama się odbudowuje</em>',
        p1='Stymulatory tkankowe to preparaty podawane w skórę, które pobudzają ją do produkcji kolagenu i elastyny. Efekt narasta stopniowo: skóra staje się gęstsza, jędrniejsza i lepiej nawilżona.',
        p2='Pracuję na sprawdzonych preparatach, m.in. Elisir, Neauvia, Xela Rederm, Sunekos i Karisma. Preparat i liczbę zabiegów dobieramy na konsultacji, do potrzeb Twojej skóry.',
        dla=['utrata jędrności i wiotkość skóry', 'cienka, odwodniona skóra', 'drobne zmarszczki, także wokół oczu', 'zmęczona skóra bez blasku'],
        nie=['ciąża i karmienie piersią', 'aktywne stany zapalne i infekcje skóry', 'choroby autoimmunologiczne (po konsultacji)'],
        ceny_h2='Wybierz <em>okolicę i preparat</em>', wariant_opis='Cena za ampułkę. Preparat dobieramy na konsultacji.',
        kroki=[('Konsultacja', 'Rozmowa, ocena skóry, kwalifikacja i przeciwwskazania.'), ('Dobór preparatu', 'Wybieram preparat i plan serii do Twojej skóry.'),
               ('Zabieg', 'Precyzyjne podanie preparatu, około 45 minut.'), ('Zalecenia', 'Wiesz, jak dbać o skórę po wizycie i kiedy wrócić na kolejny zabieg.')],
        faq=[['Ile zabiegów potrzebuję?', 'Zwykle 2–4 zabiegi w odstępach kilku tygodni, zależnie od preparatu i stanu skóry.'],
             ['Kiedy widać efekt?', 'Efekt narasta stopniowo, przez kilka tygodni od zabiegu.'],
             ['Jak wygląda skóra po zabiegu?', 'Przez 1–2 dni mogą być widoczne drobne grudki lub zaczerwienienie w miejscach podania.'],
             ['Czym różnią się preparaty?', 'Mają różny skład i siłę działania. Na konsultacji dobieram preparat do problemu i okolicy.'], KONSULTACJA],
        podobne=['Mezoterapia', 'Dermapen', 'Signature']),
    'Mezoterapia': dict(
        vslug='mezoterapia', nazwa='Mezoterapia igłowa', h1='Mezoterapia <em>igłowa</em>',
        lead='Nawilżenie i odżywienie skóry substancjami aktywnymi podanymi bezpośrednio w skórę. Twarz, szyja, dekolt, okolica oczu i dłonie.',
        cele=['Głębokie nawilżenie skóry', 'Lepsza elastyczność i kondycja', 'Świeży, wypoczęty wygląd', 'Wsparcie przy drobnych zmarszczkach'],
        img=('8a332e8293c6ccfa4ed299a663407301', 'Mezoterapia igłowa w gabinecie'),
        mini=[('Czas', 'ok. 45 min'), ('Seria', '3–4 zabiegi'), ('Odstęp', '2–3 tyg.'), ('Powrót', 'po 1–2 dniach')],
        h2='Nawilżenie tam, <em>gdzie krem nie dociera</em>',
        p1='Mezoterapia igłowa to seria drobnych nakłuć, którymi podaję w skórę koktajl substancji aktywnych, np. niesieciowany kwas hialuronowy, witaminy i aminokwasy. Skóra jest lepiej nawilżona, elastyczna i wygląda świeżej.',
        p2='Preparat dobieram do potrzeb skóry na konsultacji. Najlepsze efekty daje seria zabiegów.',
        dla=['skóra odwodniona i zmęczona', 'utrata elastyczności, drobne zmarszczki', 'zmęczona okolica oczu', 'dłonie, szyja i dekolt bez blasku'],
        nie=['ciąża i karmienie piersią', 'aktywne stany zapalne lub opryszczka', 'choroby autoimmunologiczne (po konsultacji)'],
        ceny_h2='Wybierz <em>obszar</em>', wariant_opis='Preparat dobieramy na konsultacji.',
        kroki=[('Konsultacja', 'Rozmowa, ocena skóry, kwalifikacja i przeciwwskazania.'), ('Dobór preparatu', 'Dobieram koktajl do potrzeb Twojej skóry.'),
               ('Zabieg', 'Krem znieczulający i precyzyjne podanie preparatu, około 45 minut.'), ('Zalecenia', 'Wiesz, jak dbać o skórę po wizycie i kiedy wrócić.')],
        faq=[['Czy mezoterapia boli?', 'Przed zabiegiem nakładam krem znieczulający, więc większość osób czuje tylko lekkie kłucie.'],
             ['Ile zabiegów potrzebuję?', 'Zwykle serię 3–4 zabiegów co 2–3 tygodnie, a potem zabiegi przypominające.'],
             ['Jak wygląda skóra po zabiegu?', 'Przez 1–2 dni mogą być widoczne drobne grudki, zaczerwienienie lub małe siniaki.'],
             ['Czym różni się od mezoterapii bezigłowej?', 'Igłowa podaje preparat głębiej, więc działa mocniej. Bezigłowa to łagodny zabieg pielęgnacyjny bez nakłuć.'], KONSULTACJA],
        podobne=['Stymulatory', 'Dermapen', 'Pielegnacja']),
    'Biopeeling': dict(
        vslug='biopeeling', nazwa='Biopeeling', h1='<em>Biopeeling</em>',
        lead='Peeling, który złuszcza, wygładza i rozświetla skórę bez długiej przerwy. Pomaga przy przebarwieniach, porach, nierównej strukturze i skórze trądzikowej.',
        cele=['Wygładzenie struktury skóry', 'Wyrównanie kolorytu', 'Oczyszczenie i zwężenie porów', 'Wsparcie skóry trądzikowej'],
        img=('5e44c856b90213854830dfe8eb2db929', 'Biopeeling w gabinecie'),
        mini=[('Czas', 'ok. 45 min'), ('Seria', '3–5 zabiegów'), ('Odstęp', '2–3 tyg.'), ('Powrót', 'od razu')],
        h2='Gładka skóra <em>bez długiej przerwy</em>',
        p1='Biopeeling to zabieg z użyciem kwasów i substancji aktywnych, który złuszcza martwy naskórek i pobudza skórę do odnowy. Skóra staje się gładsza, bardziej promienna i ma bardziej wyrównany koloryt.',
        p2='Rodzaj peelingu i liczbę zabiegów dobieram na konsultacji, do typu skóry i problemu, z którym przychodzisz.',
        dla=['przebarwienia i nierówny koloryt', 'rozszerzone pory, skóra tłusta i trądzikowa', 'nierówna struktura, szorstkość', 'szara skóra bez blasku'],
        nie=['ciąża i karmienie piersią', 'aktywna opryszczka, podrażniona lub uszkodzona skóra', 'świeża opalenizna'],
        ceny_h2='Wybierz <em>obszar</em>', wariant_opis='Rodzaj peelingu dobieramy na konsultacji.',
        kroki=[('Konsultacja', 'Rozmowa, ocena skóry, kwalifikacja i przeciwwskazania.'), ('Przygotowanie', 'Demakijaż i oczyszczenie skóry.'),
               ('Zabieg', 'Peeling i preparaty kojące, około 45 minut.'), ('Zalecenia', 'Ochrona przeciwsłoneczna i pielęgnacja domowa między zabiegami.')],
        faq=[['Czy po zabiegu skóra się łuszczy?', 'Może pojawić się delikatne złuszczanie przez kilka dni. Zwykle nie wymaga przerwy w codziennych aktywnościach.'],
             ['Ile zabiegów potrzebuję?', 'Zwykle serię 3–5 zabiegów co 2–3 tygodnie, zależnie od skóry i problemu.'],
             ['Kiedy najlepiej wykonać biopeeling?', 'Najlepiej w okresie mniejszego nasłonecznienia, zawsze z codzienną ochroną SPF.'],
             ['Czy pomaga przy trądziku?', 'Może wspierać skórę trądzikową: oczyszcza pory i wygładza jej powierzchnię. Przy nasilonym trądziku potrzebna jest też konsultacja dermatologiczna.'], KONSULTACJA],
        podobne=['Oczyszczanie', 'Dermapen', 'Blizny']),
    'BrwiRzesy': dict(
        vslug='brwi-rzesy', nazwa='Brwi i rzęsy', h1='Brwi <em>i rzęsy</em>',
        lead='Laminacja brwi, lifting i laminacja rzęs z koloryzacją. Uporządkowane brwi i podkreślone rzęsy na kilka tygodni, bez codziennego makijażu.',
        cele=['Uporządkowany kształt brwi', 'Uniesione, podkręcone rzęsy', 'Głębszy kolor', 'Mniej porannego makijażu'],
        img=('f984f1621a70f24c4fe2524b0cf9e0f5', 'Gabinet Ludwinek Aesthetic'),
        mini=[('Czas', 'ok. 60 min'), ('Efekt', '4–8 tyg.'), ('Powrót', 'od razu'), ('Bez wody', '24 godz.')],
        h2='Spojrzenie bez <em>codziennego makijażu</em>',
        p1='Laminacja układa i utrwala włoski brwi w wybranym kierunku, a lifting unosi i podkręca rzęsy od nasady. Koloryzacja podkreśla je i nadaje im głębi.',
        p2='Kształt brwi i stopień podkręcenia rzęs dobieram do Twojej twarzy. Przed pierwszym zabiegiem warto wykonać próbę uczuleniową.',
        dla=['niesforne, rzadkie lub nierówne brwi', 'proste lub opadające rzęsy', 'osoby, które chcą skrócić poranny makijaż'],
        nie=['infekcja lub stan zapalny oka i powiek', 'uczulenie na składniki preparatów lub farbki', 'świeży zabieg w okolicy oczu (po konsultacji)'],
        ceny_h2='Wybierz <em>zabieg</em>', wariant_opis='Każdy zabieg obejmuje koloryzację.',
        kroki=[('Konsultacja', 'Ustalamy kształt brwi, stopień podkręcenia rzęs i kolor.'), ('Przygotowanie', 'Oczyszczenie włosków i dobór wielkości wałeczków.'),
               ('Zabieg', 'Laminacja lub lifting i koloryzacja, około godziny.'), ('Zalecenia', 'Przez 24 godziny bez wody i kosmetyków na brwiach i rzęsach.')],
        faq=[['Jak długo utrzymuje się efekt?', 'Zwykle 4–8 tygodni, zależnie od cyklu wzrostu włosków.'],
             ['Czy zabieg niszczy rzęsy?', 'Przy prawidłowo wykonanym zabiegu i zachowaniu odstępów nie. Między zabiegami warto stosować odżywkę.'],
             ['Czy mogę się malować po zabiegu?', 'Przez pierwsze 24 godziny nie moczymy i nie malujemy brwi ani rzęs.'],
             ['Czy mogę zrobić brwi i rzęsy na jednej wizycie?', 'Tak, w pakiecie brwi + rzęsy.'],
             ['Czy mogę kupić zabieg w prezencie?', 'Tak, voucher na ten zabieg kupisz online i wyślesz albo wydrukujesz.']],
        podobne=['EventGlow', 'Pielegnacja', 'Mezoterapia']),
    'SkoraGlowy': dict(
        vslug='skora-glowy', nazwa='Mezoterapia skóry głowy', h1='Mezoterapia <em>skóry głowy</em>',
        lead='Substancje aktywne podane w skórę głowy, które odżywiają cebulki włosów i wspierają kondycję włosów. Dla kobiet i mężczyzn.',
        cele=['Wzmocnienie cebulek włosów', 'Wsparcie przy nadmiernym wypadaniu', 'Lepsza kondycja skóry głowy', 'Mocniejsze, odżywione włosy'],
        img=('134f8de48ec28a767cee7b58a9d03454', 'Mezoterapia skóry głowy'),
        mini=[('Czas', 'ok. 45 min'), ('Seria', '4–6 zabiegów'), ('Odstęp', '2–4 tyg.'), ('Powrót', 'od razu')],
        h2='Mocne włosy <em>zaczynają się od skóry</em>',
        p1='Mezoterapia skóry głowy to seria drobnych nakłuć, którymi podaję preparat z witaminami, aminokwasami i peptydami bezpośrednio w skórę głowy. Odżywia mieszki włosowe i wspiera kondycję włosów.',
        p2='Zabieg wspiera włosy, ale nie zastępuje diagnostyki. Przy nasilonym lub nagłym wypadaniu warto skonsultować się też z lekarzem, np. dermatologiem.',
        dla=['nadmierne wypadanie włosów', 'osłabione, przerzedzone włosy', 'słaba kondycja skóry głowy', 'kobiety i mężczyźni'],
        nie=['ciąża i karmienie piersią', 'stany zapalne i infekcje skóry głowy', 'choroby autoimmunologiczne (po konsultacji)'],
        ceny_h2='Cena <em>zabiegu</em>', wariant_opis='Preparat dobieramy na konsultacji.',
        kroki=[('Konsultacja', 'Rozmowa o wypadaniu włosów, ocena skóry głowy i kwalifikacja.'), ('Dobór preparatu', 'Dobieram preparat do potrzeb skóry głowy.'),
               ('Zabieg', 'Precyzyjne podanie preparatu, około 45 minut.'), ('Zalecenia', 'Przez 24 godziny nie myjemy włosów i unikamy stylizacji.')],
        faq=[['Ile zabiegów potrzebuję?', 'Zwykle serię 4–6 zabiegów co 2–4 tygodnie.'],
             ['Kiedy widać efekt?', 'Pierwsze zmiany zwykle po kilku zabiegach, a pełny efekt po zakończeniu serii.'],
             ['Czy zabieg boli?', 'Odczuwalne jest lekkie kłucie. Zabieg trwa około 45 minut.'],
             ['Czy zabieg jest dla mężczyzn?', 'Tak, mezoterapię skóry głowy wykonuję u kobiet i mężczyzn.'], KONSULTACJA],
        podobne=['Mezczyzni', 'Mezoterapia', 'Dermapen']),
}

# karty „Podobne zabiegi”: klucz strony → (nazwa, zdjęcie, cena, opis)
KARTY = {
    'Zabieg': ('Modelowanie ust', '0f55e518ec3860f7c53e7ce88a9dadff', '700 zł', 'Kształt i proporcje dobrane do Twojej twarzy.'),
    'Stymulatory': ('Stymulatory tkankowe', '1176910073710fd964b814c625b757d7', 'od 600 zł', 'Jędrność, gęstość i nawilżenie skóry.'),
    'Mezoterapia': ('Mezoterapia igłowa', '8a332e8293c6ccfa4ed299a663407301', 'od 350 zł', 'Nawilżenie i odżywienie skóry.'),
    'Dermapen': ('Dermapen 4.0', 'ae17b0c3510f08cd2d67dba5d027b27b', 'od 700 zł', 'Struktura, jędrność i koloryt skóry.'),
    'Signature': ('Ludwinek Signature Glow', '4c54a5f486e51a28be3cb9fd7a8112d9', 'od 450 zł', 'Autorski rytuał dobierany do skóry.'),
    'Pielegnacja': ('Zabiegi pielęgnacyjne', '5e44c856b90213854830dfe8eb2db929', 'od 200 zł', 'Szybkie odświeżenie, bez rekonwalescencji.'),
    'Oczyszczanie': ('Oczyszczanie wodorowe', '3ef87b8fffc9bab775f56d02769c6e13', 'od 350 zł', 'Dokładne oczyszczenie porów i regulacja sebum.'),
    'Blizny': ('Terapia blizn i rozstępów', '852188af96b05574c7940688b3cc70e1', 'od 500 zł', 'Blizny potrądzikowe, pooperacyjne, rozstępy.'),
    'EventGlow': ('Event Glow', '1176910073710fd964b814c625b757d7', '350 zł', 'Odświeżenie i blask skóry przed wyjściem.'),
    'Mezczyzni': ('Zabiegi dla mężczyzn', '134f8de48ec28a767cee7b58a9d03454', 'od 300 zł', 'Zabiegi dobrane do potrzeb męskiej skóry.'),
}
STRONA_KARTY = {'Oczyszczanie': 'Pielegnacja'}   # oczyszczanie wodorowe jest na stronie zabiegów pielęgnacyjnych

def zl(n):
    return f'{n} zł'

def warianty(vz):
    """Dane vouchera (grupy i wiersze: [nazwa, online, gabinet]) → D.variants strony."""
    out = []
    for g in vz['g']:
        rows = [[r[0] or 'Zabieg', zl(r[2]), f'voucher online {zl(r[1])}'] for r in g[1]]
        out.append([g[0] or vz['name'], '', rows])
    return out

def zrob(klucz, z, wersja, tpl, vz):
    s = tpl
    def rep(a, b, n=1):
        nonlocal s
        assert s.count(a) == n, (klucz, wersja, a[:70], s.count(a))
        s = s.replace(a, b)
    def sub(wz, b, flags=re.S):
        nonlocal s
        s, k = re.subn(wz, b, s, count=1, flags=flags)
        assert k == 1, (klucz, wersja, wz[:70])
    nazwa = z['nazwa']
    ceny = [r[2] for g in vz['g'] for r in g[1]]
    online = [r[1] for g in vz['g'] for r in g[1]]
    od = '' if len(set(ceny)) == 1 else 'od '
    # hero
    rep('<em>00 /</em> Dermapen 4.0', f'<em>00 /</em> {nazwa}')
    rep('<li>Dermapen 4.0</li></ol>', f'<li>{nazwa}</li></ol>')
    rep('Dermapen <em>4.0</em>', z['h1'])
    sub(r'(<p class="p thh-lead rv">).*?(</p>)', r'\g<1>' + z['lead'] + r'\g<2>')
    sub(r'(<ul class="thh-gl">).*?(</ul>)', r'\g<1>' + ''.join(f'<li>{c}</li>' for c in z['cele']) + r'\g<2>')
    rep('<dt>Cena zabiegu</dt><dd>od 700 zł</dd>', f'<dt>Cena zabiegu</dt><dd>{od}{zl(min(ceny))}</dd>')
    sub(r'(<dt>Voucher online · 10% taniej</dt><dd>)od 630 zł(</dd>)', r'\g<1>' + od + zl(min(online)) + r'\g<2>')
    s = s.replace(f'Voucher-{wersja}.dc.html#v-dermapen', f'Voucher-{wersja}.dc.html#v-{z["vslug"]}')
    sub(r'<figure class="im thh-ph"><img src="/_blob/ae17b0c3510f08cd2d67dba5d027b27b" alt="[^"]*"',
        f'<figure class="im thh-ph"><img src="/_blob/{z["img"][0]}" alt="{z["img"][1]}"')
    sub(r'(<dl class="mini thh-mini">).*?(</dl>)', r'\g<1>' + ''.join(f'<div><dt>{a}</dt><dd>{b}</dd></div>' for a, b in z['mini']) + r'\g<2>')
    # o zabiegu
    rep('Oryginalne urządzenie, <em>oryginalne protokoły</em>', z['h2'])
    sub(r'(<p class="p" style="font-size: 19px; color: #141414">)Dermapen 4\.0 wykonuje.*?(</p>)', r'\g<1>' + z['p1'] + r'\g<2>')
    sub(r'(<p class="p">)Pracuję wyłącznie na oryginalnym urządzeniu.*?(</p>)', r'\g<1>' + z['p2'] + r'\g<2>')
    sub(r'<div style="display: flex; align-items: center; gap: 16px; padding: 16px 0;[^"]*"><figure class="im cert".*?</span></span></div>', '')
    sub(r'(<p class="lbl">Dla kogo</p><ul class="blt">).*?(</ul>)', r'\g<1>' + ''.join(f'<li>{x}</li>' for x in z['dla']) + r'\g<2>')
    sub(r'(<p class="lbl">Kiedy nie</p><ul class="blt">).*?(</ul>)', r'\g<1>' + ''.join(f'<li>{x}</li>' for x in z['nie']) + r'\g<2>')
    # ceny
    rep('Wybierz <em>wariant terapii</em>', z['ceny_h2'])
    # przebieg
    sub(r'(<ol class="steps">).*?(</ol>)', r'\g<1>' + ''.join(f'<li><b class="h3">{a}</b><p class="sm" style="margin: 0">{b}</p></li>' for a, b in z['kroki']) + r'\g<2>')
    # bez opinii (są o Dermapen), kolejne sekcje o numer wcześniej
    sub(r'<section class="s ">\s*<div class="rl"><b><i></i><span><em>04 /</em> Opinie</span></b></div>.*?</section>\s*', '')
    rep('<em>05 /</em> FAQ', '<em>04 /</em> FAQ')
    rep('<em>06 /</em> Podobne', '<em>05 /</em> Podobne')
    # podobne zabiegi
    karty = ''
    for k in z['podobne']:
        n, img, cena, opis = KARTY[k]
        karty += (f'<a class="card" href="{STRONA_KARTY.get(k, k)}-{wersja}.dc.html"><figure class="im"><img src="/_blob/{img}" alt="" style="object-position: 50% 40%"></figure>'
                  f'<div class="th"><b>{n}</b><span>{cena}</span></div><p class="sm" style="margin: 0">{opis}</p></a>')
    sub(r'(<div class="cards scroll">)(?:<a class="card".*?</a>)+', r'\g<1>' + karty.replace('\\', '\\\\'))
    # dane: FAQ i ceny
    D = {'faq': z['faq'], 'variants': warianty(vz)}
    for v in D['variants']:
        v[1] = z['wariant_opis']
    sub(r'const D = \{.*?\};\n', 'const D = ' + json.dumps(D, ensure_ascii=False).replace('\\', '\\\\') + ';\n')
    # menu: bieżąca strona
    s = s.replace(f'href="Dermapen-{wersja}.dc.html" aria-current="page"', f'href="Dermapen-{wersja}.dc.html"')
    s = re.sub(rf'href="{klucz}-{wersja}\.dc\.html"(?! aria-current)', f'href="{klucz}-{wersja}.dc.html" aria-current="page"', s, count=2)
    return s

def main():
    vz = {}
    for z in json.loads(re.search(r'const D = (\{.*?\});\n', open(os.path.join(SRC, 'Voucher-Desktop.html'), encoding='utf-8').read(), re.S).group(1))['vz']:
        vz[z['slug']] = z
    # 1) linki do kotwic na /zabiegi → nowe strony (we wszystkich stronach źródła)
    mapa = {z['vslug']: k for k, z in ZABIEGI.items()}
    for f in sorted(os.listdir(SRC)):
        if not f.endswith('.html'):
            continue
        p = os.path.join(SRC, f)
        s = open(p, encoding='utf-8').read(); o = s
        s = re.sub(r'Zabiegi-(Desktop|Mobile)\.dc\.html#(' + '|'.join(map(re.escape, mapa)) + r')(?=["\'])', lambda m: f'{mapa[m.group(2)]}-{m.group(1)}.dc.html', s)
        if f.startswith('Zabiegi-'):
            # lista zabiegów: te pozycje mają teraz własną stronę („Więcej” zamiast „Zapytaj o zabieg”)
            m = re.search(r'const D = (\{.*?\});\n', s, re.S); D = json.loads(m.group(1)); wersja = f.split('-')[1][:-5]
            for t in D['treat']:
                if t.get('slug') in mapa:
                    t.update(page=f'{mapa[t["slug"]]}-{wersja}.dc.html', hasPage=True, noPage=False, rcls='')
            s = s[:m.start(1)] + json.dumps(D, ensure_ascii=False) + s[m.end(1):]
        if s != o:
            open(p, 'w', encoding='utf-8').write(s)
    # 2) nowe strony na wzór Dermapen
    for wersja in ('Desktop', 'Mobile'):
        tpl = open(os.path.join(SRC, f'Dermapen-{wersja}.html'), encoding='utf-8').read()
        for k, z in ZABIEGI.items():
            open(os.path.join(SRC, f'{k}-{wersja}.html'), 'w', encoding='utf-8').write(zrob(k, z, wersja, tpl, vz[z['vslug']]))
    print('stron:', len(ZABIEGI) * 2)

if __name__ == '__main__':
    main()
