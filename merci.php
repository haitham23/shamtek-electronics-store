<?php
session_start();

// إفراغ السلة بعد الدفع الناجح
unset($_SESSION['panier']);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Paiement réussi</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container panel" style="margin-top:40px;">
    <div class="panel-body">
        <h1>Paiement réussi ✔️</h1>
        <p>Merci pour votre achat.</p>
        <a href="index.php" class="btn">Retour à l’accueil</a>
    </div>
</div>

</body>
</html>
