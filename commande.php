<?php
// commande.php — Récapitulatif + adresse + actions (Valider / Retour)
session_start();
require __DIR__ . '/bd.php';

// Vérifier si client connecté
if (!isset($_SESSION['id_client'])) {
  echo '<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=connexion.php"><title>Connexion requise</title></head><body>Redirection...</body></html>';
  exit;
}

$panier = $_SESSION['panier'] ?? [];
if (!$panier) {
  echo '<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=panier.php"><title>Panier vide</title></head><body>Redirection...</body></html>';
  exit;
}

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$pdo = getBD();

// Récupérer infos client
$stmtCli = $pdo->prepare('SELECT nom, prenom, adresse FROM Clients WHERE id_client = :id');
$stmtCli->execute([':id' => (int)$_SESSION['id_client']]);
$cli = $stmtCli->fetch(PDO::FETCH_ASSOC) ?: ['nom'=>'','prenom'=>'','adresse'=>''];

// Charger panier
$rows = [];
$total = 0.0;

foreach ($panier as $item) {
  $id  = (int)($item['id_art'] ?? 0);
  $qte = (int)($item['quantite'] ?? 0);
  if ($id <= 0 || $qte <= 0) continue;

  $stmt = $pdo->prepare('SELECT id_art, nom, prix FROM Articles WHERE id_art = :id');
  $stmt->execute([':id'=>$id]);

  if ($a = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $st = (float)$a['prix'] * $qte;
    $total += $st;
    $rows[] = [
      'id_art'=>$a['id_art'],
      'nom'=>$a['nom'],
      'prix'=>(float)$a['prix'],
      'qte'=>$qte,
      'sous_total'=>$st
    ];
  }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>SHAMTEK — Commande</title>
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
      <h1 class="h1">Récapitulatif de votre commande</h1>

      <div class="table-wrap" style="overflow:auto;">
        <table class="table" style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left; padding:8px; border-bottom:1px solid #e5e7eb;">ID</th>
              <th style="text-align:left; padding:8px; border-bottom:1px solid #e5e7eb;">Nom</th>
              <th style="text-align:right; padding:8px; border-bottom:1px solid #e5e7eb;">Prix (€)</th>
              <th style="text-align:right; padding:8px; border-bottom:1px solid #e5e7eb;">Quantité</th>
              <th style="text-align:right; padding:8px; border-bottom:1px solid #e5e7eb;">Sous-total (€)</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td style="padding:8px; border-bottom:1px solid #f3f4f6;"><?= (int)$r['id_art'] ?></td>
              <td style="padding:8px; border-bottom:1px solid #f3f4f6;"><?= e($r['nom']) ?></td>
              <td style="padding:8px; text-align:right; border-bottom:1px solid #f3f4f6;"><?= number_format($r['prix'], 2, ',', ' ') ?></td>
              <td style="padding:8px; text-align:right; border-bottom:1px solid #f3f4f6;"><?= (int)$r['qte'] ?></td>
              <td style="padding:8px; text-align:right; border-bottom:1px solid #f3f4f6;"><?= number_format($r['sous_total'], 2, ',', ' ') ?></td>
            </tr>
          <?php endforeach; ?>
            <tr>
              <td colspan="4" style="padding:12px; text-align:right; font-weight:700;">Montant total</td>
              <td style="padding:12px; text-align:right; font-weight:700;"><?= number_format($total, 2, ',', ' ') ?> €</td>
            </tr>
          </tbody>
        </table>
      </div>

      <h2 class="h2" style="margin-top:18px;">Adresse d'expédition</h2>
      <p><?= e($cli['nom']) ?> <?= e($cli['prenom']) ?><br><?= nl2br(e($cli['adresse'])) ?></p>

      <div style="margin-top:14px; display:flex; gap:10px;">
        <form action="acheter.php" method="post">
          <button type="submit" class="btn">Valider</button>
        </form>
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
