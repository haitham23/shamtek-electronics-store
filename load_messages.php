<?php
header('Content-Type: application/json; charset=UTF-8');

require __DIR__ . '/bd.php';
$pdo = getBD();

try {
    // حذف رسائل أقدم من 10 دقائق
    $pdo->exec("DELETE FROM messages WHERE created_at < (NOW() - INTERVAL 10 MINUTE)");

    $stmt = $pdo->query("SELECT `user`, `message` FROM messages ORDER BY id DESC LIMIT 50");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = [];
    foreach (array_reverse($rows) as $r) {
        $u = htmlspecialchars((string)$r['user'], ENT_QUOTES, 'UTF-8');
        $m = htmlspecialchars((string)$r['message'], ENT_QUOTES, 'UTF-8');
        // فورما الـTP: Bob dit 'message'
        $out[] = $u . " dit '" . $m . "'";
    }

    echo json_encode($out, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
}
