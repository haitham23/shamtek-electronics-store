<?php
// historique.php — Liste des commandes du client connecté
session_start();
require __DIR__ . '/bd.php';

// Vérifier si client connecté
if (!isset($_SESSION['id_client'])) {
  echo '<!doctype html><html><head><meta charset="utf-8">
        <meta http-equiv="refresh" content="0;url=connexion.php">
        <title>Connexion requise</title></head><body>Redirection...</body></html>';
  exit;
}

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$pdo = getBD();
$idClient = (int)$_SESSION['id_client'];

// Récupération des commandes
$sql = 'SELECT c.id_commande, a.id_art, a.nom, a.prix, c.quantite, c.envoi
        FROM Commandes c
        JOIN Articles a ON c.id_art = a.id_art
        WHERE c.id_client = :id
        ORDER BY c.id_commande DESC, c.id_art ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $idClient]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>SHAMTEK — Historique des commandes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <div class="container header-row">
    <div class="logo">
      <a href="index.php"><img src="images/logo.svg" alt="SHAMTEK" class="logo-img"></a>
    </div>

    <div class="login-icon">
      <span class="login-text" style="margin-right:12px;">
        Bonjour <?= e($_SESSION['prenom'].' '.$_SESSION['nom']); ?>
      </span>
      <a href="deconnexion.php" class="header-link">⎋ Se déconnecter</a>
    </div>
  </div>
</header>

<main class="container">
  <div class="panel">
    <div class="panel-body">
      <h1 class="h1">Historique des commandes</h1>

      <?php if (!$rows): ?>
        <p>Aucune commande pour le moment.</p>

      <?php else: ?>
      <div class="table-wrap" style="overflow:auto;">
        <table class="table" style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:8px; border-bottom:1px solid #e5e7eb;">ID Commande</th>
              <th style="text-align:left; padding:8px; border-bottom:1px solid #e5e7eb;">ID Article</th>
              <th style="text-align:left; padding:8px; border-bottom:1px solid #e5e7eb;">Nom</th>
              <th style="text-align:right; padding:8px; border-bottom:1px solid #e5e7eb;">Prix (€)</th>
              <th style="text-align:right; padding:8px; border-bottom:1px solid #e5e7eb;">Quantité</th>
              <th style="text-align:center; padding:8px; border-bottom:1px solid #e5e7eb;">Envoyée ?</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td style="padding:8px; border-bottom:1px solid #f3f4f6;"><?= (int)$r['id_commande'] ?></td>
                <td style="padding:8px; border-bottom:1px solid #f3f4f6;"><?= (int)$r['id_art'] ?></td>
                <td style="padding:8px; border-bottom:1px solid #f3f4f6;"><?= e($r['nom']) ?></td>
                <td style="padding:8px; text-align:right; border-bottom:1px solid #f3f4f6;">
                  <?= number_format((float)$r['prix'], 2, ',', ' ') ?>
                </td>
                <td style="padding:8px; text-align:right; border-bottom:1px solid #f3f4f6;"><?= (int)$r['quantite'] ?></td>
                <td style="padding:8px; text-align:center; border-bottom:1px solid #f3f4f6;">
                  <?= $r['envoi'] ? 'Oui' : 'Non' ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <div style="margin-top:12px;">
        <a href="index.php" class="back">← Retour</a>
      </div>

    </div>
  </div>
</main>

<footer>
  <div class="container foot">
    <span>© <?= date('Y') ?> SHAMTEK</span>
    <span><a href="contact.php" class="footer-link">Contact</a></span>
  </div>
</footer>

</body>
</html>
