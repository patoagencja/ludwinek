# Analityka GA4: ludwinekaesthetic.pl

Usługa GA4 „ludwinekest”, strumień `https://ludwinekaesthetic.pl/`, identyfikator **G-5LJXC4VE72**.
Kod: `public/assets/zgody.js` (zgody, wczytanie tagu, zdarzenia kontaktu) i `public/assets/analityka.js` (reszta zdarzeń).
Tag Google wczytuje się dopiero po zgodzie na cookies analityczne (baner na stronie), więc część ruchu nie będzie widoczna. To normalne i zgodne z prawem.

## Lejek sprzedaży

| Krok | Zdarzenia |
|---|---|
| 1. Wejście | `session_start`, `page_view` (z grupą treści: Strona główna, Zabieg, Lista zabiegów, Cennik, Vouchery…) |
| 2. Zainteresowanie zabiegiem | `view_item` (strona zabiegu), `view_item_list` (Zabiegi, Cennik, Vouchery), `select_item` (klik w zabieg) |
| 3. Zaangażowanie | `sekcja_widoczna` (Cena, Opinie, Efekty, FAQ…), `faq_otwarcie`, `wybor_opcji`, `porownanie_przed_po`, `scroll_glebokosc`, `zaangazowanie` (30 s, 90 s, 3 min) |
| 4. Zamiar kontaktu | `formularz_start`, `cta_formularz`, `klik_voucher` |
| 5. **Konwersja** | `klik_booksy`, `klik_telefon`, `generate_lead` (wysłany formularz); później `begin_checkout` i `purchase` (vouchery przez Stripe) |

## Wszystkie zdarzenia

| Zdarzenie | Kiedy | Parametry |
|---|---|---|
| `generate_lead` | formularz kontaktowy wysłany | `zabieg` (wybrany w formularzu), `formularz` |
| `klik_booksy` | klik w „Umów wizytę” (Booksy) | `zabieg`, `sekcja`, `link_text`, `strona` |
| `klik_telefon` / `klik_email` / `klik_whatsapp` | klik w numer, e-mail, WhatsApp | jw. |
| `klik_mapa` / `klik_social` | mapa Google, Instagram/Facebook | jw. |
| `klik_voucher` | przycisk voucherów | jw. |
| `view_item` | wejście na stronę zabiegu | `items[]` (item_id, item_name, item_category) |
| `view_item_list` | Zabiegi, Cennik, Vouchery | `item_list_id`, `item_list_name` |
| `select_item` | klik w kafelek/link zabiegu | `item_list_name` (skąd), `items[]` |
| `formularz_start` | pierwsze kliknięcie w pole formularza | `formularz`, `strona` |
| `formularz_blad` | próba wysłania z błędami | `pola` (np. `tel,ok`) |
| `cta_formularz` | przycisk „Zapytaj o termin / voucher” | `sekcja`, `zabieg`, `link_text` |
| `faq_otwarcie` | otwarte pytanie FAQ | `pytanie`, `zabieg`, `strona` |
| `wybor_opcji` | przełącznik (warianty, kategorie, ceny) | `opcja`, `sekcja`, `zabieg` |
| `znajdz_problem` | „Znajdź zabieg do problemu” | `problem` |
| `efekty_przypadek` | wybór przypadku przed/po | `przypadek` |
| `porownanie_przed_po` | użycie suwaka przed/po | `sekcja`, `zabieg` |
| `sekcja_widoczna` | ważna sekcja na ekranie ≥ 1 s | `sekcja`, `zabieg`, `strona` |
| `scroll_glebokosc` | 25/50/75/100 % strony | `procent`, `zabieg` |
| `zaangazowanie` | 30 s, 90 s, 3 min aktywnego oglądania | `sekundy`, `zabieg`, `strona` |
| `menu_otwarcie` / `nawigacja` | menu, linki w nagłówku i stopce | `menu` / `cel`, `miejsce`, `link_text` |
| `galeria_gabinetu` | zdjęcia gabinetu | `sekcja` |
| `kopiowanie` | ktoś kopiuje tekst | `typ` (telefon/email/adres/tekst), `tekst` |

Wizyty z urządzeń, na których ktoś zalogował się do `/panel`, mają `traffic_type=internal` (filtr niżej).

## Ustawienia w GA4 (jednorazowo, Administracja)

1. **Przechowywanie danych** (Zbieranie i modyfikowanie danych → Przechowywanie danych): 14 miesięcy. Tak mówi polityka prywatności.
2. **Zbieranie danych → Sygnały Google**: wyłączone (polityka mówi, że ich nie używamy).
3. **Strumienie danych → strumień → Pomiar zaawansowany → koło zębate**: wyłącz *Przewinięcia* (mamy dokładniejsze `scroll_glebokosc`), *Interakcje z formularzami* (formularz wysyła się skryptem, mamy `formularz_start` i `generate_lead`) i *Wyszukiwanie w witrynie* (strona nie ma wyszukiwarki). Reszta włączona.
4. **Filtry danych**: filtr „Internal Traffic” (Ruch wewnętrzny) przełącz z *Testowanie* na *Aktywny*.
5. **Kluczowe zdarzenia** (Wyświetlanie danych → Zdarzenia, gdy zdarzenia się pojawią, albo „Nowe kluczowe zdarzenie” z nazwą): `generate_lead`, `klik_booksy`, `klik_telefon`. Po starcie voucherów: `purchase`.
6. **Niestandardowe definicje → Wymiary niestandardowe** (zakres: Zdarzenie; nazwa wymiaru = nazwa parametru):
   `zabieg`, `sekcja`, `opcja`, `pytanie`, `problem`, `przypadek`, `pola`, `formularz`, `link_text`, `miejsce`, `cel`, `typ`, `menu`.
   **Dane niestandardowe** (zakres: Zdarzenie, jednostka: standardowa): `procent`, `sekundy`.
7. **Połączenia usług**: połącz Search Console (po dodaniu domeny w Search Console), a gdy ruszą kampanie: Google Ads.

## Raporty do zbudowania (Eksploruj)

- **Lejek rezerwacji** (Eksploracja ścieżki do celu, otwarta): 1) `session_start` → 2) `view_item` lub `view_item_list` → 3) `sekcja_widoczna` (sekcja = Cena) lub `faq_otwarcie` lub `wybor_opcji` → 4) `klik_booksy` lub `klik_telefon` lub `formularz_start` → 5) `klik_booksy` lub `klik_telefon` lub `generate_lead`. Podział: *Kategoria urządzenia*, potem *Źródło/medium sesji*.
- **Które zabiegi sprzedają**: Dowolna; wiersze *Nazwa elementu* (z `view_item`) i wymiar `zabieg`; wartości: liczba `view_item`, `klik_booksy`, `generate_lead`.
- **Skąd przychodzą klientki**: Pozyskiwanie ruchu, kluczowe zdarzenia wg *Źródło/medium sesji*.
- **Pytania przed zakupem**: `faq_otwarcie` wg `pytanie` i `zabieg`. Najczęstsze pytania warto przenieść wyżej na stronie.
- **Gdzie ludzie odpadają w formularzu**: `formularz_start` vs `generate_lead`, a `formularz_blad` wg `pola`.

## Linki z oznaczeniem źródła (UTM)

W profilu Google (Wizytówka) wpisz stronę jako
`https://ludwinekaesthetic.pl/?utm_source=google&utm_medium=organic&utm_campaign=wizytowka`,
a na Instagramie (link w bio)
`https://ludwinekaesthetic.pl/?utm_source=instagram&utm_medium=social&utm_campaign=bio`.
Bez tego ruch z wizytówki i Instagrama miesza się z „direct”.

Uwaga: Booksy to osobny serwis, więc samej rezerwacji GA nie widzi. Konwersją jest przejście do Booksy (`klik_booksy`).
