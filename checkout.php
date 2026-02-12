<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bd.php';

/* =====================================================
   1) لازم المستخدم يكون مسجل الدخول
===================================================== */
if (!isset($_SESSION['id_client'])) {
    header("Location: connexion.php");
    exit;
}

/* =====================================================
   2) لازم يكون panier فيه منتجات
===================================================== */
$panier = $_SESSION['panier'] ?? [];
if (!$panier) {
    header("Location: panier.php");
    exit;
}

$pdo = getBD();

/* =====================================================
   3) التحقق من الكمية قبل الدفع (IMPORTANT)
===================================================== */
$stockError = false;

foreach ($panier as $index => $item) {

    $id  = (int)($item['id_art'] ?? 0);
    $qte = (int)($item['quantite'] ?? 0);

    if ($id <= 0 || $qte <= 0) {
        unset($_SESSION['panier'][$index]);
        $stockError = true;
        continue;
    }

    $stmt = $pdo->prepare("SELECT quantite FROM Articles WHERE id_art = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $stock = $stmt->fetchColumn();

    // المنتج محذوف
    if ($stock === false) {
        unset($_SESSION['panier'][$index]);
        $stockError = true;
        continue;
    }

    $stock = (int)$stock;

    // الكمية المطلوبة أكبر من المتوفر
    if ($qte > $stock) {
        $stockError = true;

        if ($stock > 0) {
            // نصححو الكمية لأقصى المتوفر
            $_SESSION['panier'][$index]['quantite'] = $stock;
        } else {
            // نفذ الستوك → نحيد المنتج
            unset($_SESSION['panier'][$index]);
        }
    }
}

// إعادة ترتيب panier
$_SESSION['panier'] = array_values($_SESSION['panier']);

// إذا كان مشكل فالكمية → نرجع للسلة مع رسالة
if ($stockError) {
    header("Location: panier.php?error=stock_out");
    exit;
}

/* =====================================================
   4) Stripe
===================================================== */
\Stripe\Stripe::setApiKey("sk_test_51SQoyA70ABCyxL5lO3AGh57aZByWQlHtdXRqiVN50X7hU1HIa8r25qaBYgeL2mHmDjzkobtSiQeiptohKHMPBBfk002awu12Ki");

$line_items = [];

foreach ($_SESSION['panier'] as $item) {

    $id  = (int)$item['id_art'];
    $qte = (int)$item['quantite'];

    $stmt = $pdo->prepare("SELECT stripe_price_id FROM Articles WHERE id_art = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $priceId = $stmt->fetchColumn();

    if (!$priceId) {
        header("Location: panier.php?error=stripe_price");
        exit;
    }

    $line_items[] = [
        "price" => $priceId,
        "quantity" => $qte
    ];
}

if (!$line_items) {
    header("Location: panier.php");
    exit;
}

/* =====================================================
   5) إنشاء Stripe Checkout Session
===================================================== */
$_SESSION['panier_hash'] = hash('sha256', json_encode($_SESSION['panier']));

$session = \Stripe\Checkout\Session::create([
    "mode" => "payment",
    "payment_method_types" => ["card"],
    "line_items" => $line_items,
    "client_reference_id" => (string)$_SESSION['id_client'],
    "metadata" => [
        "panier_hash" => $_SESSION['panier_hash']
    ],
    "success_url" => "http://localhost/ALFAKHRY1/acheter.php?session_id={CHECKOUT_SESSION_ID}",
    "cancel_url"  => "http://localhost/ALFAKHRY1/panier.php?stripe=cancel",
]);

header("Location: " . $session->url);
exit;
