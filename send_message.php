<?php
header('Content-Type: text/plain; charset=utf-8');
session_start();

require __DIR__ . '/bd.php';
$pdo = getBD();

if (!isset($_POST['message'])) { echo "error:no_message"; exit; }

$msg = trim((string)$_POST['message']);
if ($msg === '') { echo "error:empty"; exit; }
if (mb_strlen($msg, 'UTF-8') > 256) { echo "error:too_long"; exit; }

// اسم المستخدم
$user = "Visiteur";
if (isset($_SESSION['prenom'], $_SESSION['nom'])) {
  $user = $_SESSION['prenom'] . " " . $_SESSION['nom'];
} elseif (isset($_SESSION['prenom'])) {
  $user = $_SESSION['prenom'];
}

// --- فلترة حسب scoremap ---
$mapFile = __DIR__ . '/scoremap.json';
$scoremap = file_exists($mapFile) ? json_decode(file_get_contents($mapFile), true) : null;
if (!is_array($scoremap)) { echo "error:no_scoremap"; exit; }

$lower = mb_strtolower($msg, 'UTF-8');
$tokens = preg_split('/[^\p{L}\p{N}\']+/u', $lower, -1, PREG_SPLIT_NO_EMPTY);

$total = 0.0;
$matched = 0;

foreach ($tokens as $w) {
  $w = trim($w, "'");
  if ($w === '') continue;

  if (array_key_exists($w, $scoremap)) {
    $total += (float)$scoremap[$w];
    $matched++;
  }
}

// منطق الـTP: إذا total <= 0 ولقينا كلمات => offensive
if ($matched > 0 && $total <= 0) {
  echo "error:offensive";
  exit;
}

// إدخال الرسالة
$stmt = $pdo->prepare("INSERT INTO messages (`user`, `message`, `created_at`) VALUES (?, ?, NOW())");
$stmt->execute([$user, $msg]);

echo "success";
