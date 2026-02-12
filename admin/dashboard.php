<?php
require 'auth.php';
require '../bd.php';

$pdo = getBD();

$totalProducts = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
$totalClients  = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f4f6f9;}
.sidebar{height:100vh;background:#2f3542;color:white;padding-top:20px;}
.sidebar a{color:white;display:block;padding:12px;text-decoration:none;}
.sidebar a:hover{background:#57606f;}
.content{padding:30px;}
.card{border:none;border-radius:14px;}
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

<h1 class="mb-4">Dashboard</h1>

<div class="row">
<div class="col-md-4">
<div class="card p-4 shadow">
<h5>Products</h5>
<h2><?= $totalProducts ?></h2>
</div>
</div>

<div class="col-md-4">
<div class="card p-4 shadow">
<h5>Orders</h5>
<h2><?= $totalOrders ?></h2>
</div>
</div>

<div class="col-md-4">
<div class="card p-4 shadow">
<h5>Clients</h5>
<h2><?= $totalClients ?></h2>
</div>
</div>
</div>

</div>
</div>
</div>

</body>
</html>