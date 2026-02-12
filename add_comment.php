<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/bd.php';
$pdo = getBD();

header('Content-Type: text/plain; charset=utf-8');

function fail(string $code): void {
  echo $code;
  exit;
}

/**
 * "AI check" بسيط محلي (بدون API).
 * بدّليه لاحقًا حسب المطلوب عندكم (أو استدعاء check_message.php إذا عندك).
 */
function is_offensive(string $text): bool {
  $t = mb_strtolower($text, 'UTF-8');

  // لائحة بسيطة كمثال (زيد/نقص حسب الحاجة)
  $bad = [
    'fuck','shit','bitch','asshole',
    'pute','connard','conne','salope','enculé',
  ];

  foreach ($bad as $w) {
    if (mb_strpos($t, $w) !== false) return true;
  }
  return false;
}

/**
 * تنظيف أولي: نحيد null bytes ونقص المسافات
 * (الحماية من XSS تكون عند العرض بـ htmlspecialchars)
 */
function clean_text(string $s): string {
  $s = str_replace("\0", '', $s);
  $s = trim($s);
  return $s;
}

/* 1) لازم يكون user logged-in */
if (empty($_SESSION['id_client'])) {
  fail("error:not_logged");
}

$id_client = (int)$_SESSION['id_client'];

/* 2) قراءة POST */
$id_art = isset($_POST['id_art']) ? (int)$_POST['id_art'] : 0;
$note   = isset($_POST['note']) ? (int)$_POST['note'] : 0;
$msg    = isset($_POST['message']) ? (string)$_POST['message'] : '';

$msg = clean_text($msg);

if ($id_art <= 0) fail("error:bad_article");
if ($note < 1 || $note > 5) fail("error:bad_note");
if ($msg === '') fail("error:empty");

/* 3) حد أقصى للرسالة (بدّلي الرقم إذا بغيت) */
if (mb_strlen($msg, 'UTF-8') > 256) {
  fail("error:too_long");
}

/* 4) AI check (فلترة بسيطة) */
if (is_offensive($msg)) {
  fail("error:offensive");
}

/* 5) شرط الشراء: لازم يكون عندو سطر ف commandes فيها نفس id_client و id_art */
try {
  $stmt = $pdo->prepare("
    SELECT 1
    FROM commandes
    WHERE id_client = :id_client
      AND id_art = :id_art
    LIMIT 1
  ");
  $stmt->execute([
    ':id_client' => $id_client,
    ':id_art'    => $id_art,
  ]);

  $bought = (bool)$stmt->fetchColumn();
  if (!$bought) {
    fail("error:not_bought");
  }

  /* 6) إدخال التعليق */
  $ins = $pdo->prepare("
    INSERT INTO commentaires (id_art, id_client, note, contenu, created_at)
    VALUES (:id_art, :id_client, :note, :contenu, NOW())
  ");
  $ins->execute([
    ':id_art'    => $id_art,
    ':id_client' => $id_client,
    ':note'      => $note,
    ':contenu'   => $msg,
  ]);

  echo "success";
  exit;

} catch (Throwable $e) {
  // ما نعطيش تفاصيل DB للواجهة
  fail("error:server");
}
