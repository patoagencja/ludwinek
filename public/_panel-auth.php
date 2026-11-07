<?php
// Logowanie do panelu (/panel): hasło Julii (skrót bcrypt) i ciasteczko sesji podpisane HMAC.
declare(strict_types=1);

const PANEL_HASLO_HASH = '$2y$12$D6IfxGOqe5XZ3dL73vc49uJfmDk6iJiFCN31p6B.5xr9/U.L.Ayuy';
const PANEL_CIASTKO = 'ludwinek_panel';
const PANEL_DNI = 30;

function panel_token(int $do): string
{
    return $do . '.' . hash_hmac('sha256', 'panel|' . $do, PANEL_HASLO_HASH);
}

function panel_zalogowany(): bool
{
    $c = (string)($_COOKIE[PANEL_CIASTKO] ?? '');
    $do = (int)strtok($c, '.');
    return $do > time() && hash_equals(panel_token($do), $c);
}
