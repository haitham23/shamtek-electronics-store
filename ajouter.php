<?php
// ajouter.php — ajoute un article au panier en session puis redirige vers index
session_start();

$id  = isset($_POST['id_art']) ? (int)$_POST['id_art'] : 0;
$qte = isset($_POST['qte']) ? (int)$_POST['qte'] : 0;

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_client'])) {
  echo '<!doctype html><html><head><meta charset="utf-8">';
  echo '<meta http-equiv="refresh" content="0;url=connexion.php">';
  echo '<title>Connexion requise</title></head><body>Redirection...</body></html>';
  exit;
}

// Validation minimale
if ($id <= 0 || $qte <= 0) {
  echo '<!doctype html><html><head><meta charset="utf-8">';
  echo '<meta http-equiv="refresh" content="0;url=index.php">';
  echo '<title>Paramètres invalides</title></head><body>Redirection...</body></html>';
  exit;
}

// Structure du panier
if (!isset($_SESSION['panier'])) {
  $_SESSION['panier'] = [
    ['id_art' => $id, 'quantite' => $qte]
  ];
} else {
  // Ajout au panier
  $_SESSION['panier'][] = [
    'id_art' => $id,
    'quantite' => $qte
  ];
}

// Redirection
echo '<!doctype html><html><head><meta charset="utf-8">';
echo '<meta http-equiv="refresh" content="0;url=index.php">';
echo '<title>Ajout au panier</title></head><body>Redirection...</body></html>';
exit;
