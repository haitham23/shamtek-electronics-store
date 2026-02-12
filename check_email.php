<?php
header('Content-Type: application/json');

require 'bd.php';
$bdd = getBD();

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

$response = ["exists" => false];

// تحقق من صحة الإيميل
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode($response);
    exit;
}

$stmt = $bdd->prepare("SELECT COUNT(*) FROM clients WHERE mail = ?");
$stmt->execute([$email]);
$exists = $stmt->fetchColumn() > 0;

$response["exists"] = $exists;

echo json_encode($response);
exit;
