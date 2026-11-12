<?php
/**
 * Filet de sécurité : si le serveur web pointe sur la racine du projet
 * (au lieu de public/) et que la réécriture d'URL n'est pas active,
 * on redirige vers le vrai point d'entrée.
 */
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
header('Location: ' . $base . '/public/', true, 302);
exit;
