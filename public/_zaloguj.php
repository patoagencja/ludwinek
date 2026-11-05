<?php /* formularz hasła do pełnej strony przed startem (index.php, ścieżka /zaloguj); $blad ustawia index.php */ ?><!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#1F1E1C">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/assets/favicon-32.png" sizes="32x32" type="image/png">
<link rel="stylesheet" href="/assets/fonts.css">
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;min-height:100svh;display:grid;place-items:center;padding:20px;background:#1F1E1C;color:#3A3A3A;font:400 16px/1.6 'Inter',system-ui,sans-serif;-webkit-font-smoothing:antialiased}
.box{width:100%;max-width:420px;background:#FAF8F5;padding:40px 32px 34px;text-align:center}
.lm{height:64px;width:auto;color:#141414}
.k{margin:22px 0 0;font:500 10.5px/1.5 'Inter',sans-serif;letter-spacing:.16em;text-transform:uppercase;color:#6F675C}
h1{margin:10px 0 0;font:300 40px/1.05 'Cormorant Garamond',Garamond,serif;color:#141414}
h1 em{font-style:italic}
p{margin:12px 0 0;font-size:15px;color:#6F675C}
label{display:block;margin:26px 0 8px;text-align:left;font:500 10.5px/1.4 'Inter',sans-serif;letter-spacing:.14em;text-transform:uppercase;color:#141414}
input{width:100%;padding:14px 16px;border:1px solid #D8D2C8;border-radius:0;background:#fff;color:#141414;font:16px 'Inter',sans-serif}
button,.btn{display:inline-flex;align-items:center;justify-content:center;width:100%;min-height:52px;margin-top:16px;border:0;background:#6e133b;color:#fff;font:500 12px/1 'Inter',sans-serif;letter-spacing:.14em;text-transform:uppercase;text-decoration:none;cursor:pointer}
button:hover,.btn:hover{background:#57102f}
.err{color:#b3261e}
.back{display:inline-block;margin-top:18px;font-size:13px;color:#6F675C}
:focus-visible{outline:2px solid #C9A97F;outline-offset:3px}
</style>
<title>Podgląd strony · Ludwinek Aesthetic</title>
</head>
<body>
<form class="box" method="post" action="/zaloguj">
<svg class="lm" viewBox="570 134 798 1162" fill="currentColor" aria-hidden="true" focusable="false"><path transform="matrix(1,0,0,-1,863.5,527.9)" d="M0 0V124.6C16.9 124.8 36.4 124.7 58.6 124.1 91.9 124.1 103.5 99.7 103.5 67.8V-125.6C83.4-77.6 40.9-35.3 0 0M-47.3 39.5C-102.1 85.1-130.6 113.7-47.3 122.1ZM195.6 136.8H58.6C36.6 136.8 17 136.4 0 135.6V320C0 350.8 11.6 376.4 44.8 376.4V387.9H-92.1V376.4C-58.8 376.4-47.3 350.8-47.3 320V131.8C-146.4 118.6-109.4 81.4-47.3 27V-435.5C-47.3-467.4-58.8-493-92.1-493V-504.5H82.7V-493H44.8C11.6-493 0-467.4 0-435.5V-14.8C52.1-62.6 103.5-119.4 103.5-181.9V-445.7C103.5-461.1 103.4-477 102.7-493 102.7-496.8 102.5-500.6 102.4-504.5 101.7-518.1 100.9-531.8 99.5-545.1 89.1-652.6 52.6-750.4-70.7-750.4-216.6-750.4-275.5-607-275.5-553.2H-287V-664.7H-275.5C-253.8-651.8-224.3-762-70.7-762 59.1-762 137.7-717.4 149.3-504.5 149.6-500.7 149.7-496.8 150-493 150.5-478 150.8-462.2 150.8-445.7V67.8C150.8 99.7 162.3 124.1 195.6 124.1Z"/><path transform="matrix(1,0,0,-1,1342.6,956.5)" d="M0 0C-33.7-52.9-75.5-64.4-144.9-64.4H-309.1V-75.9H-6.6L19.8 0Z"/></svg>
<p class="k">Ludwinek Aesthetic</p>
<h1>Podgląd <em>nowej strony</em></h1>
<p>Wpisz hasło, żeby zobaczyć całą stronę przed startem.</p>
<label for="haslo">Hasło</label>
<input id="haslo" name="haslo" type="password" autocomplete="current-password" required autofocus>
<?php if (!empty($blad)): ?><p class="err" role="alert">Złe hasło. Spróbuj jeszcze raz.</p><?php endif; ?>
<button type="submit">Wejdź</button>
<a class="back" href="/">Wróć</a>
</form>
</body>
</html>
