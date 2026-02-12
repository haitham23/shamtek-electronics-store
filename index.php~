<?php
session_start();
require __DIR__ . '/bd.php';
$pdo = getBD();

// جلب المقالات من قاعدة البيانات (✅ اسم الجدول صحيح)
$stmt = $pdo->query('SELECT id_art, nom, quantite, prix, url_photo FROM articles ORDER BY id_art DESC');
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// دالة مساعدة للعرض الآمن
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>SHAMTEK — Accueil</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="chatstyle.css">
</head>
<body>

<?php require 'header.php'; ?>

  <!-- 🔹 MAIN CONTENT -->
  <main class="container">

    <?php if (!$articles): ?>
      <p>Aucun article pour le moment. Ajoutez des produits dans la table <b>articles</b>.</p>

    <?php else: ?>
      <div class="grid" aria-label="Liste des articles">

        <?php foreach ($articles as $a):
          $url = 'articles/article.php?id_art=' . (int)$a['id_art'];
          $img = trim((string)$a['url_photo']);
        ?>
          <article class="card">

            <a href="<?= e($url) ?>" class="thumb" aria-label="Voir l'article">
              <?php if ($img !== ''): ?>
                <img src="<?= e($img) ?>" alt="">
              <?php else: ?>
                <span>Aucune image</span>
              <?php endif; ?>
            </a>

            <div class="card-body">
              <h2 class="card-title">
                <a href="<?= e($url) ?>"><?= e($a['nom']) ?></a>
              </h2>

              <div class="meta">
                <span class="price"><?= number_format((float)$a['prix'], 2, ',', ' ') ?> €</span>
                <span class="qty">Qté <?= (int)$a['quantite'] ?></span>
              </div>

              <a class="btn" href="<?= e($url) ?>">Voir le produit</a>
            </div>
          </article>

        <?php endforeach; ?>

      </div>
    <?php endif; ?>

  </main>

  <!-- 🔹 FOOTER -->
  <footer>
    <div class="container foot">
      <span>© <?= date('Y') ?> SHAMTEK</span>
      <span><a href="contact.php" class="footer-link">Contact</a></span>
    </div>
  </footer>

  <!-- 🔹 Chat Widget -->
  <button id="chat-toggle" type="button" aria-expanded="false" aria-controls="chat-panel">
    💬 <span class="chat-toggle-text">Chat</span>
    <span id="chat-badge" class="chat-badge" style="display:none;">!</span>
  </button>

  <div id="chat-panel" class="chat-panel" aria-hidden="true">
    <div class="chat-header">
      <div class="chat-title">Messagerie</div>
      <button id="chat-close" type="button" class="chat-close" aria-label="Fermer">✕</button>
    </div>

    <div id="messages" class="chat-messages" aria-live="polite"></div>

    <div class="chat-input">
      <textarea id="msg" maxlength="256" placeholder="Écrire un message..."></textarea>
      <button id="chat-send" type="button">Envoyer</button>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>
