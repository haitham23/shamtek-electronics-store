<?php
require 'auth.php';
require '../bd.php';

$pdo = getBD();
$products = $pdo->query("SELECT * FROM articles ORDER BY id_art DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Manage Products</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f4f6f9;}
.sidebar{height:100vh;background:#2f3542;color:white;padding-top:20px;}
.sidebar a{color:white;display:block;padding:12px;text-decoration:none;}
.sidebar a:hover{background:#57606f;}
.content{padding:30px;}
</style>
</head>

<body>
<div class="container-fluid">
<div class="row">

<div class="col-2 sidebar">
<h4 class="text-center">⚙ ADMIN</h4>
<hr>
<a href="dashboard.php">📊 Dashboard</a>
<a href="manage_products.php">🛒 Products</a>
<a href="add_product.php">➕ Add Product</a>
<a href="orders.php">📦 Orders</a>
<a href="../index.php">🏪 Store</a>
<a href="../deconnexion.php">🚪 Logout</a>
</div>

<div class="col-10 content">

<h1>Manage Products</h1>

<a href="add_product.php" class="btn btn-success mb-3">+ Add Product</a>

<table class="table table-bordered bg-white shadow">
<tr>
<th>ID</th>
<th>Name</th>
<th>Price</th>
<th>Stock</th>
<th>Action</th>
</tr>

<?php foreach($products as $p): ?>
<tr>
<td><?= $p['id_art'] ?></td>
<td><?= $p['nom'] ?></td>
<td><?= $p['prix'] ?> €</td>
<td><?= $p['quantite'] ?></td>
<td>
<a href="edit_product.php?id=<?= $p['id_art'] ?>" class="btn btn-warning btn-sm">Edit</a>
<a href="delete_product.php?id=<?= $p['id_art'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>

</table>

</div>
</div>
</div>

</body>
</html>