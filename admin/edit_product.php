<?php
require 'auth.php';
require '../bd.php';

$pdo = getBD();

if(!isset($_GET['id'])){
    header("Location: manage_products.php");
    exit();
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM articles WHERE id_art=?");
$stmt->execute([$id]);

$product = $stmt->fetch();

if(!$product){
    header("Location: manage_products.php");
    exit();
}

if($_SERVER['REQUEST_METHOD']=='POST'){

$update=$pdo->prepare("
UPDATE articles
SET nom=?, prix=?, quantite=?, url_photo=?, description=?, categorie=?
WHERE id_art=?
");

$update->execute([
$_POST['nom'],
$_POST['prix'],
$_POST['quantite'],
$_POST['url_photo'],
$_POST['description'],
$_POST['categorie'],
$id
]);

header("Location: manage_products.php");
exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Edit Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-5">

<h1>Edit Product</h1>

<form method="POST" class="card p-4 shadow">

<label>Name</label>
<input name="nom" value="<?= htmlspecialchars($product['nom']) ?>" class="form-control mb-3">

<label>Category</label>
<input name="categorie" value="<?= htmlspecialchars($product['categorie']) ?>" class="form-control mb-3">

<label>Price</label>
<input name="prix" type="number" step="0.01"
value="<?= $product['prix'] ?>" class="form-control mb-3">

<label>Stock</label>
<input name="quantite" type="number"
value="<?= $product['quantite'] ?>" class="form-control mb-3">

<label>Image URL</label>
<input name="url_photo"
value="<?= htmlspecialchars($product['url_photo']) ?>"
class="form-control mb-3">

<label>Description</label>
<textarea name="description"
class="form-control mb-3"><?= htmlspecialchars($product['description']) ?></textarea>

<button class="btn btn-warning">Update Product</button>
<a href="manage_products.php" class="btn btn-secondary">Cancel</a>

</form>

</body>
</html>