<?php
require 'auth.php';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Panel</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
}

.sidebar{
height:100vh;
background:#2f3542;
color:white;
padding-top:20px;
position:fixed;
width:220px;
}

.sidebar a{
color:white;
display:block;
padding:12px;
text-decoration:none;
}

.sidebar a:hover{
background:#57606f;
}

.content{
margin-left:230px;
padding:30px;
}

</style>
</head>

<body>

<div class="sidebar">

<h4 class="text-center">⚙ ADMIN</h4>
<hr>

<a href="dashboard.php">📊 Dashboard</a>
<a href="manage_products.php">🛒 Products</a>
<a href="add_product.php">➕ Add Product</a>
<a href="orders.php">📦 Orders</a>
<a href="../index.php">🏪 Store</a>
<a href="../deconnexion.php">🚪 Logout</a>

</div>

<div class="content">