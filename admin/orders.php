<?php
require 'admin_layout.php';
require '../bd.php';

$pdo = getBD();

//////////////////////////////////////////////////
// 
//////////////////////////////////////////////////

if(isset($_GET['ship'])){
    
    $id = (int)$_GET['ship'];

    $pdo->prepare("
        UPDATE commandes
        SET envoi = 1
        WHERE id_commande = ?
    ")->execute([$id]);

    header("Location: orders.php");
    exit();
}

//////////////////////////////////////////////////

//////////////////////////////////////////////////

$orders = $pdo->query("
SELECT 
    c.id_commande,
    c.date_commande,
    c.envoi,
    cl.nom,
    cl.prenom
FROM commandes c
JOIN clients cl 
ON cl.id_client = c.id_client
ORDER BY c.id_commande DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<h1 class="mb-4">📦 Orders Management</h1>

<div class="card shadow">
<div class="card-body">

<table class="table table-striped">

<tr>
<th>ID</th>
<th>Client</th>
<th>Date</th>
<th>Products</th>
<th>Total (€)</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php foreach($orders as $order): ?>

<?php
//////////////////////////////////////////////////

//////////////////////////////////////////////////

$items = $pdo->prepare("
SELECT a.nom, a.prix, co.quantite
FROM commandes co
JOIN articles a ON a.id_art = co.id_art
WHERE co.id_commande = ?
");

$items->execute([$order['id_commande']]);

$products = $items->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
?>

<tr>

<td>
<strong>#<?= $order['id_commande'] ?></strong>
</td>

<td>
<?= $order['prenom']." ".$order['nom'] ?>
</td>

<td>
<?= $order['date_commande'] ?? '-' ?>
</td>

<td>

<?php foreach($products as $p): 

$subtotal = $p['prix'] * $p['quantite'];
$total += $subtotal;
?>

✅ <?= $p['nom'] ?>
<br>
<small>
<?= $p['quantite'] ?> × <?= $p['prix'] ?> €
</small>

<hr>

<?php endforeach; ?>

</td>

<td>
<strong><?= number_format($total,2) ?> €</strong>
</td>

<td>

<?php if($order['envoi']): ?>

<span class="badge bg-success">
✔ Shipped
</span>

<?php else: ?>

<span class="badge bg-warning text-dark">
Pending
</span>

<?php endif; ?>

</td>

<td>

<?php if(!$order['envoi']): ?>

<a href="?ship=<?= $order['id_commande'] ?>"
class="btn btn-sm btn-primary">
Mark Shipped
</a>

<?php else: ?>

—

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>
</div>

<?php require 'admin_footer.php'; ?>