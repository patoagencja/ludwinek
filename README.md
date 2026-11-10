# ludwinekaesthetic.pl

Strona gabinetu Ludwinek Aesthetic (Julia Ludwinek, Plac Konstytucji 1, Warszawa).

- `public/` to wszystko, co leży na serwerze. Każdy push na `main` wgrywa ten folder na LH.pl (serwer534076)
  i sprawdza stronę: `.github/workflows/wdrozenie.yml`. Login i hasło FTP są w sekretach repo.
- Strona jest publiczna (`PRZED_STARTEM = false` w `public/index.php`). Ustawienie `true` przywraca stronę „Wkrótce” (`public/_wkrotce.php`) z pełną stroną po haśle pod `/zaloguj`.
- `public/_strony/` to podstrony w dwóch wersjach (Desktop, Mobile); `public/index.php` wybiera wersję i adres.
- `zrodlo/strony/` to ten sam projekt w formie z podglądu; `narzedzia/konwertuj.py` robi z niego `public/_strony/`.
- Panel dla Julii: `/panel` (hasło w `public/_panel-auth.php` jako skrót bcrypt). Klik w tekst lub zdjęcie na podglądzie i zamiana.
  Zmiany leżą tylko na serwerze (`_panel-dane/_edycje.json`, zdjęcia w `img/p/`) i są nakładane na strony przy wysyłce
  (`public/_edycje.php`), więc wgrywanie z repo ich nie nadpisuje. Każde wdrożenie robi ich kopię (artefakt „kopia-panelu”).
  Uwaga: jeśli zmienisz w `zrodlo/` tekst, który Julia już edytowała, jej zmiana przestanie działać (panel to pokaże).
- Analityka GA4 (G-5LJXC4VE72): `docs/analityka.md` (zdarzenia, lejek, ustawienia w GA4).
- Vouchery: zakup na /vouchery przez Stripe Checkout (`public/_vouchery.php`, `_vouchery-dane.php`), cena liczona na serwerze
  z cennika na stronie, ważność 12 miesięcy, kod i link do wydruku mailem, lista i realizacja w `/panel/vouchery`,
  regulamin `/regulamin-voucherow`. Klucz: sekret repo `STRIPE_KEY` (albo `STRIPE_KEY_TEST`); wdrożenie zapisuje go na
  serwerze i zakłada webhook (zmiana klucza w sekretach: wystarczy kolejne wgranie). Bez klucza (albo z kluczem testowym dla gości) strona podaje `_strony-wkrotce/` („Vouchery wkrótce”).
