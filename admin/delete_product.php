<?php
require 'auth.php';
require '../bd.php';

$pdo = getBD();

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("DELETE FROM articles WHERE id_art = ?");
$stmt->execute([$id]);

header("Location: manage_products.php");
exit();