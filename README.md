# ludwinekaesthetic.pl

Strona gabinetu Ludwinek Aesthetic (Julia Ludwinek, Plac Konstytucji 1, Warszawa).

- `public/` to wszystko, co leży na serwerze. Każdy push na `main` wgrywa ten folder na LH.pl (serwer534076)
  i sprawdza stronę: `.github/workflows/wdrozenie.yml`. Login i hasło FTP są w sekretach repo.
- Przed startem wszyscy widzą stronę „Wkrótce” (`public/_wkrotce.php`), a pełną stronę po haśle pod `/zaloguj`.
  Start: w `public/index.php` ustaw `PRZED_STARTEM = false`.
- `public/_strony/` to podstrony w dwóch wersjach (Desktop, Mobile); `public/index.php` wybiera wersję i adres.
- `zrodlo/strony/` to ten sam projekt w formie z podglądu; `narzedzia/konwertuj.py` robi z niego `public/_strony/`.
