# ludwinekaesthetic.pl

Strona gabinetu Ludwinek Aesthetic (Julia Ludwinek, Plac Konstytucji 1, Warszawa).

- `public/` to wszystko, co leży na serwerze. Każdy push na `main` wgrywa ten folder na LH.pl (serwer534076)
  i sprawdza stronę: `.github/workflows/wdrozenie.yml`. Login i hasło FTP są w sekretach repo.
- Strona jest publiczna (`PRZED_STARTEM = false` w `public/index.php`). Ustawienie `true` przywraca stronę „Wkrótce” (`public/_wkrotce.php`) z pełną stroną po haśle pod `/zaloguj`.
- `public/_strony/` to podstrony w dwóch wersjach (Desktop, Mobile); `public/index.php` wybiera wersję i adres.
- `zrodlo/strony/` to ten sam projekt w formie z podglądu; `narzedzia/konwertuj.py` robi z niego `public/_strony/`.
