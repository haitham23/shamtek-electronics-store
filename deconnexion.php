<?php
// deconnexion.php — détruit la session et redirige vers l'accueil
session_start();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params['path'], $params['domain'],
    $params['secure'], $params['httponly']
  );
}
session_destroy();

echo '<!doctype html><html><head><meta charset="utf-8">';
echo '<meta http-equiv="refresh" content="0;url=index.php">';
echo '<title>Déconnexion</title></head><body>Déconnexion...</body></html>';
exit;
