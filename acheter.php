<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bd.php';

// لازم يكون المستخدم مسجل الدخول
if (!isset($_SESSION['id_client'])) {
    header("Location: connexion.php");
    exit;
}

// لازم session_id يجي من Stripe
$sessionId = $_GET['session_id'] ?? '';
if (!$sessionId) {
    header("Location: panier.php");
    exit;
}

// Stripe Secret Key
\Stripe\Stripe::setApiKey("sk_test_51SQoyA70ABCyxL5lO3AGh57aZByWQlHtdXRqiVN50X7hU1HIa8r25qaBYgeL2mHmDjzkobtSiQeiptohKHMPBBfk002awu12Ki");

// جلب session ديال Stripe
try {
    $checkout = \Stripe\Checkout\Session::retrieve($sessionId);
} catch (Throwable $e) {
    header("Location: panier.php");
    exit;
}

// 1) تأكيد أن الدفع paid
if (($checkout->payment_status ?? '') !== 'paid') {
    header("Location: panier.php?stripe=not_paid");
    exit;
}

// 2) تأكيد نفس الزبون
$idClient = (int)$_SESSION['id_client'];
if ((string)$idClient !== (string)($checkout->client_reference_id ?? '')) {
    header("Location: panier.php?stripe=bad_client");
    exit;
}

// panier من session
$panier = $_SESSION['panier'] ?? [];
if (!$panier) {
    header("Location: panier.php");
    exit;
}

$pdo = getBD();
$pdo->beginTransaction();

try {
    // INSERT commande
    $ins = $pdo->prepare('INSERT INTO Commandes (id_art, id_client, quantite, envoi)
                          VALUES (:id_art, :id_client, :qte, 0)');

    // UPDATE stock (مصحح: بارامترات مختلفة باش ما يوقعش HY093)
    $upd = $pdo->prepare('UPDATE Articles
                          SET quantite = quantite - :qte
                          WHERE id_art = :id_art AND quantite >= :qte_check');

    foreach ($panier as $item) {
        $id  = (int)($item['id_art'] ?? 0);
        $qte = (int)($item['quantite'] ?? 0);

        if ($id <= 0 || $qte <= 0) continue;

        // 1) نسجل الطلب
        $ins->execute([
            ':id_art' => $id,
            ':id_client' => $idClient,
            ':qte' => $qte
        ]);

        // 2) ننقص stock
        $upd->execute([
            ':qte' => $qte,
            ':qte_check' => $qte,
            ':id_art' => $id
        ]);

        // إذا ما نقصش stock => يا stock ناقص يا أسماء أعمدة/جداول غلط
        if ($upd->rowCount() === 0) {
            throw new Exception("Stock insuffisant ou colonne/table incorrecte (id_art=$id).");
        }
    }

    $pdo->commit();

    // 3) نفرغ السلة
    unset($_SESSION['panier']);
    unset($_SESSION['panier_hash'], $_SESSION['id_client_checkout']);

} catch (Throwable $e) {
    $pdo->rollBack();
    die("ERROR: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Confirmation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container panel" style="margin-top:40px;">
    <div class="panel-body">
      <h1>Paiement OK ✅</h1>
      <p>Commande enregistrée, stock mis à jour, panier vidé.</p>
      <a href="historique.php" class="btn">Voir historique</a>
      <a href="index.php" class="btn">Accueil</a>
    </div>
  </div>
</body>
</html>
