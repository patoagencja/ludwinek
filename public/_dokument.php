<?php
// Wspólny wygląd dokumentów prawnych (/polityka-prywatnosci, /regulamin). Dokument ustawia $tytul, $opis, $tresc (HTML)
// i dołącza ten plik. Dane gabinetu są w _dane.php.
require_once __DIR__ . '/_dane.php';
$lm = file_get_contents(__DIR__ . '/assets/logo-mark.svg');
$lw = file_get_contents(__DIR__ . '/assets/logo-word.svg');
?><!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<script>if(location.protocol==="http:"&&/(^|\.)ludwinekaesthetic\.pl$/.test(location.hostname))location.replace("https://ludwinekaesthetic.pl"+location.pathname+location.search+location.hash);</script>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($tytul) ?> · Ludwinek Aesthetic</title>
<meta name="description" content="<?= htmlspecialchars($opis) ?>">
<meta name="theme-color" content="#1F1E1C">
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/assets/favicon-32.png" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="/assets/apple-touch-icon.png">
<link rel="stylesheet" href="/assets/fonts.css">
<script src="/assets/zgody.js" defer></script>
<style>
:root{--bg:#FAF8F5;--alt:#EFEBE4;--ink:#141414;--soft:#3A3A3A;--mute:#6F675C;--line:#D8D2C8;--gold:#C9A97F;--serif:'Cormorant Garamond',Garamond,'Times New Roman',serif;--sans:'Inter',system-ui,-apple-system,sans-serif}
*{box-sizing:border-box}
html{scroll-padding-top:90px}
body{margin:0;background:var(--bg);color:var(--soft);font:400 16px/1.7 var(--sans);-webkit-font-smoothing:antialiased}
a{color:var(--ink);text-underline-offset:3px}
.top{position:sticky;top:0;z-index:5;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px clamp(20px,5vw,64px);background:rgba(250,248,245,.94);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);border-bottom:1px solid var(--line)}
.brand{display:flex;align-items:center;gap:14px;color:var(--ink);text-decoration:none}
.brand .lm{height:36px;width:auto}.brand .lw{height:26px;width:auto}
.back{font:500 10.5px/1 var(--sans);letter-spacing:.14em;text-transform:uppercase;color:var(--mute);text-decoration:none;padding:12px 0}
main{max-width:760px;margin:0 auto;padding:56px 22px 72px}
.kick{margin:0;font:500 11px/1.5 var(--sans);letter-spacing:.16em;text-transform:uppercase;color:var(--mute)}
h1{margin:12px 0 8px;font:300 clamp(40px,7vw,64px)/1 var(--serif);color:var(--ink)}
.data{margin:0 0 32px;font-size:14px;color:var(--mute)}
.spis{margin:0 0 40px;padding:20px 24px;background:var(--alt);font-size:14.5px}
.spis ol{margin:8px 0 0;padding-left:20px}.spis li{margin:2px 0}
h2{margin:44px 0 12px;font:400 28px/1.15 var(--serif);color:var(--ink)}
h3{margin:24px 0 6px;font:500 15px/1.4 var(--sans);color:var(--ink)}
p,li{font-size:15.5px}
ul,ol{padding-left:22px}li{margin:4px 0}
table{width:100%;border-collapse:collapse;margin:12px 0 4px;font-size:14px}
th,td{text-align:left;vertical-align:top;padding:10px 8px;border-bottom:1px solid var(--line)}
th{font-weight:500;color:var(--ink)}
.tw{overflow-x:auto}
.box{margin:16px 0;padding:16px 20px;border-left:2px solid var(--gold);background:#fff}
.ft{border-top:1px solid var(--line);padding:24px 22px 40px;text-align:center;font-size:13px;color:var(--mute)}
.ft a{color:var(--mute)}
:focus-visible{outline:2px solid var(--gold);outline-offset:3px}
@media (max-width:520px){.brand .lw{display:none}main{padding-top:36px}h2{font-size:25px}}
</style>
</head>
<body>
<header class="top">
<a class="brand" href="/" aria-label="Ludwinek Aesthetic, strona główna"><?= $lm ?><?= $lw ?></a>
<a class="back" href="/">Strona główna</a>
</header>
<main>
<?= $tresc ?>
</main>
<footer class="ft">© <?= date('Y') ?> Ludwinek Aesthetic · <a href="/polityka-prywatnosci">Polityka prywatności</a> · <a href="/regulamin">Regulamin</a> · <a href="/regulamin-voucherow">Regulamin voucherów</a> · <a href="#ustawienia-cookies">Ustawienia cookies</a></footer>
</body>
</html>
