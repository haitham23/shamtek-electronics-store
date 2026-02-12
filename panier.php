<?php
session_start();
require __DIR__ . '/bd.php';
$pdo = getBD();

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$panier = $_SESSION['panier'] ?? [];

/* =====================================================
   1) Vider le panier
===================================================== */
if (isset($_GET['clear'])) {
    unset($_SESSION['panier']);
    header("Location: panier.php");
    exit;
}

/* =====================================================
   2) Ajouter quantité (+) avec contrôle du stock
===================================================== */
if (isset($_GET['add'])) {

    $id_add = (int) $_GET['add'];

    $stmt = $pdo->prepare("SELECT quantite FROM Articles WHERE id_art = :id LIMIT 1");
    $stmt->execute([':id' => $id_add]);
    $stockData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($stockData) {
        $stock = (int)$stockData['quantite'];

        foreach ($_SESSION['panier'] as &$item) {
            if ((int)$item['id_art'] === $id_add) {

                if ($item['quantite'] >= $stock) {
                    header("Location: panier.php?error=stock");
                    exit;
                }

                $item['quantite']++;
                break;
            }
        }
    }

    header("Location: panier.php");
    exit;
}

/* =====================================================
   3) Diminuer quantité (–)
===================================================== */
if (isset($_GET['minus'])) {

    $id_minus = (int) $_GET['minus'];

    foreach ($_SESSION['panier'] as $index => &$item) {

        if ((int)$item['id_art'] === $id_minus) {

            if ((int)$item['quantite'] > 1) {
                $item['quantite']--;
            } else {
                unset($_SESSION['panier'][$index]);
                $_SESSION['panier'] = array_values($_SESSION['panier']);
            }
            break;
        }
    }

    header("Location: panier.php");
    exit;
}

/* =====================================================
   4) Supprimer un article (❌)
===================================================== */
if (isset($_GET['remove'])) {

    $id_remove = (int) $_GET['remove'];

    foreach ($_SESSION['panier'] as $index => $item) {
        if ((int)$item['id_art'] === $id_remove) {
            unset($_SESSION['panier'][$index]);
            $_SESSION['panier'] = array_values($_SESSION['panier']);
            break;
        }
    }

    header("Location: panier.php");
    exit;
}

/* =====================================================
   5) Vérification stock avant step=address
===================================================== */
if (isset($_GET['step']) && $_GET['step'] === 'address') {

    if (empty($_SESSION['panier'])) {
        header("Location: panier.php");
        exit;
    }

    $stockError = false;

    foreach ($_SESSION['panier'] as $index => $item) {
        $id  = (int)($item['id_art'] ?? 0);
        $qte = (int)($item['quantite'] ?? 0);

        if ($id <= 0 || $qte <= 0) {
            unset($_SESSION['panier'][$index]);
            $stockError = true;
            continue;
        }

        $stmt = $pdo->prepare("SELECT quantite FROM Articles WHERE id_art = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $stock = $stmt->fetchColumn();

        if ($stock === false) {
            unset($_SESSION['panier'][$index]);
            $stockError = true;
            continue;
        }

        $stock = (int)$stock;

        if ($qte > $stock) {
            $stockError = true;

            if ($stock > 0) {
                $_SESSION['panier'][$index]['quantite'] = $stock;
            } else {
                unset($_SESSION['panier'][$index]);
            }
        }
    }

    $_SESSION['panier'] = array_values($_SESSION['panier']);

    if ($stockError) {
        header("Location: panier.php?error=stock_out");
        exit;
    }

    $panier = $_SESSION['panier'];
}

/* =====================================================
   6) Charger les produits du panier
===================================================== */
$rows = [];
$total = 0;

$panier = $_SESSION['panier'] ?? [];

if ($panier) {
    foreach ($panier as $item) {

        $id  = (int)($item['id_art'] ?? 0);
        $qte = (int)($item['quantite'] ?? 0);

        if ($id <= 0 || $qte <= 0) continue;

        $stmt = $pdo->prepare("SELECT id_art, nom, prix, quantite FROM Articles WHERE id_art = :id");
        $stmt->execute([':id' => $id]);

        if ($a = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = [
                'id_art'     => (int)$a['id_art'],
                'nom'        => $a['nom'],
                'prix'       => (float)$a['prix'],
                'stock'      => (int)$a['quantite'],
                'qte'        => $qte,
                'sous_total' => $qte * (float)$a['prix']
            ];
            $total += $qte * (float)$a['prix'];
        }
    }
}

/* =====================================================
   7) Gestion adresse client (dans la même page)
===================================================== */
$clientAddress = null;

if (isset($_SESSION['id_client'])) {
    $stmt = $pdo->prepare("SELECT adresse FROM clients WHERE id_client = :id LIMIT 1");
    $stmt->execute([':id' => $_SESSION['id_client']]);
    $clientAddress = $stmt->fetchColumn();
}

if (isset($_POST['update_address'])) {
    $newAddress = trim($_POST['adresse'] ?? "");

    if ($newAddress !== "" && isset($_SESSION['id_client'])) {
        $stmt = $pdo->prepare("UPDATE clients SET adresse = :adr WHERE id_client = :id");
        $stmt->execute([
            ':adr' => $newAddress,
            ':id'  => $_SESSION['id_client']
        ]);
        $clientAddress = $newAddress;
    }
}

/* =====================================================
   8) Confirmer et payer => vérif stock puis checkout
===================================================== */
if (isset($_POST['confirm_order'])) {

    if (empty($_SESSION['panier'])) {
        header("Location: panier.php");
        exit;
    }

    $stockError = false;

    foreach ($_SESSION['panier'] as $index => $item) {
        $id  = (int)($item['id_art'] ?? 0);
        $qte = (int)($item['quantite'] ?? 0);

        $stmt = $pdo->prepare("SELECT quantite FROM Articles WHERE id_art = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $stock = $stmt->fetchColumn();

        if ($stock === false) {
            unset($_SESSION['panier'][$index]);
            $stockError = true;
            continue;
        }

        $stock = (int)$stock;

        if ($qte > $stock) {
            $stockError = true;

            if ($stock > 0) {
                $_SESSION['panier'][$index]['quantite'] = $stock;
            } else {
                unset($_SESSION['panier'][$index]);
            }
        }
    }

    $_SESSION['panier'] = array_values($_SESSION['panier']);

    if ($stockError) {
        header("Location: panier.php?error=stock_out");
        exit;
    }

    header("Location: checkout.php");
    exit;
}

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>SHAMTEK — Mon panier</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="container header-row">
        <div class="logo">
            <a href="index.php"><img src="images/logo.svg" class="logo-img"></a>
        </div>

        <div>
            <?php if (!isset($_SESSION['id_client'])): ?>
                <a href="connexion.php">Se connecter</a>
            <?php else: ?>
                Bonjour <?= e($_SESSION['prenom']) ?>
                | <a href="historique.php">Historique</a>
                | <a href="deconnexion.php">Déconnexion</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container">
<div class="panel">
<div class="panel-body">

<h1>Votre panier</h1>

<?php if (isset($_GET['error']) && $_GET['error'] === 'stock'): ?>
    <p style="color:red;font-weight:bold;">⚠ La quantité dépasse le stock disponible.</p>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'stock_out'): ?>
    <p style="color:red;font-weight:bold;">
        ❌ Désolé, la quantité demandée n’est plus disponible. Votre panier a été mis à jour.
    </p>
<?php endif; ?>

<?php if (!$rows): ?>

    <p>Votre panier est vide.</p>
    <a href="index.php" class="back">← Retour</a>

<?php else: ?>

<table class="table" style="width:100%; border-collapse:collapse;">
<thead>
<tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Prix (€)</th>
    <th>Quantité</th>
    <th>Sous-total (€)</th>
    <th>Supprimer</th>
</tr>
</thead>

<tbody>

<?php foreach ($rows as $r): ?>
<tr>

    <td><?= (int)$r['id_art'] ?></td>
    <td><?= e($r['nom']) ?></td>
    <td><?= number_format($r['prix'], 2, ',', ' ') ?></td>

    <td style="text-align:center;">
        <div style="display:flex; align-items:center; justify-content:center; gap:8px;">
            <a href="panier.php?minus=<?= (int)$r['id_art'] ?>"
               style="padding:4px 10px; background:#eee; border-radius:4px; text-decoration:none;">–</a>

            <span style="font-size:16px; font-weight:bold;">
                <?= (int)$r['qte'] ?>
            </span>

            <a href="panier.php?add=<?= (int)$r['id_art'] ?>"
               style="padding:4px 10px; background:#eee; border-radius:4px; text-decoration:none;">+</a>
        </div>

        <div style="font-size:12px;color:#666; margin-top:3px;">
            Stock: <?= (int)$r['stock'] ?>
        </div>
    </td>

    <td><?= number_format($r['sous_total'], 2, ',', ' ') ?> €</td>

    <td>
        <a href="panier.php?remove=<?= (int)$r['id_art'] ?>" style="color:red; font-size:22px;">❌</a>
    </td>

</tr>
<?php endforeach; ?>

<tr>
    <td colspan="4" style="text-align:right; font-weight:bold;">Total</td>
    <td colspan="2" style="font-weight:bold;"><?= number_format($total, 2, ',', ' ') ?> €</td>
</tr>

</tbody>
</table>

<br>

<?php if (!isset($_GET['step'])): ?>

    <a href="panier.php?step=address" class="btn">Passer la commande</a>

<?php else: ?>

    <h2>Adresse de livraison</h2>

    <form method="post">

        <textarea name="adresse" style="width:100%; height:90px;"><?= e($clientAddress) ?></textarea>

        <br><br>

        <button type="submit" name="update_address" class="btn">
            Mettre à jour l’adresse
        </button>

        <button type="submit" name="confirm_order" class="btn"
                style="background:green; color:white;">
            Confirmer et payer
        </button>

    </form>

<?php endif; ?>

<br><br>

<a href="panier.php?clear=1" class="btn" style="background:red; color:white;">🗑 Vider</a>
<a href="index.php" class="back">← Retour</a>

<?php endif; ?>

</div>
</div>
</main>

<footer>
    <div class="container foot">
        © <?= date('Y') ?> SHAMTEK |
        <a href="contact.php">Contact</a>
    </div>
</footer>

</body>
</html>
