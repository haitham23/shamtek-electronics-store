<?php
// connexion.php — Page de connexion (même style que le site)
session_start();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>SHAMTEK — Connexion</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- 🔹 HEADER -->
  <header>
    <div class="container header-row">
      <div class="logo">
        <a href="index.php"><img src="images/logo.svg" alt="SHAMTEK" class="logo-img"></a>
      </div>
      <div class="login-icon">
        <a href="nouveau.php" title="Créer un compte">
          <img src="images/login.svg" alt="Nouveau client" class="icon-login">
          <span class="login-text"> Nouveau client </span>
        </a>
      </div>
    </div>
  </header>

  <main>
    <div class="container">
      <div class="panel">
        <div class="panel-body">
          <h1 class="h1">Se connecter</h1>
          <p class="desc">Pas encore de compte ? <a href="nouveau.php">Créez-en un ici</a>.</p>

          <?php if (isset($_GET['e']) && $_GET['e'] === '1'): ?>
            <div class="alert error" style="margin:10px 0; padding:10px; border:1px solid #fca5a5; background:#fee2e2; border-radius:8px;">
              Adresse e-mail ou mot de passe incorrect.
            </div>
          <?php endif; ?>

          <form action="connecter.php" method="post" class="login-form" novalidate>
            <div class="grid" style="grid-template-columns: repeat(auto-fit,minmax(260px,1fr));">
              <label class="panel-body" style="padding:0">
                <div class="card-body" style="padding:0 0 6px; font-weight:600">Adresse e-mail</div>
                <input type="email" name="mail" required
                       value="<?php echo isset($_GET['mail']) ? htmlspecialchars($_GET['mail'], ENT_QUOTES, 'UTF-8') : '';?>"
                       style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px;">
              </label>

              <label class="panel-body" style="padding:0">
                <div class="card-body" style="padding:0 0 6px; font-weight:600">Mot de passe</div>
                <input type="password" name="mdp" required minlength="4"
                       style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px;">
              </label>
            </div>

            <div style="margin-top:14px; display:flex; gap:10px; align-items:center;">
              <button class="btn" type="submit">Se connecter</button>
              <a href="index.php" class="back">← Retour à l'accueil</a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </main>

  <!-- 🔹 FOOTER -->
  <footer>
    <div class="container foot">
      <span>© <?= date('Y') ?> SHAMTEK</span>
      <span><a href="contact.php" class="footer-link">Contact</a></span>
    </div>
  </footer>


  <!--  ضعِـ AJAX هنا قبل </body> مباشرة -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
  $(function(){

    $(".login-form").on("submit", function(e){
      e.preventDefault();

      const mail = $('input[name="mail"]').val().trim();
      const mdp  = $('input[name="mdp"]').val().trim();

      $(".alert").remove();
      $(".login-form").before('<div class="alert" style="padding:10px;background:#fef9c3;border:1px solid #fcd34d;border-radius:8px;">Vérification...</div>');

      $.ajax({
        url: "connecter.php",
        method: "POST",
        dataType: "json",
        data: { mail: mail, mdp: mdp }
      })
      .done(function(resp){
        $(".alert").remove();
        if (resp.success) {
          $(".login-form").before('<div class="alert" style="padding:10px;background:#d1fae5;border:1px solid #10b981;border-radius:8px;">Connexion réussie !</div>');
          setTimeout(() => window.location.href = "index.php", 1000);
        } else {
          $(".login-form").before('<div class="alert" style="padding:10px;background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;">Adresse e-mail ou mot de passe incorrect.</div>');
        }
      })
      .fail(function(){
        $(".alert").remove();
        $(".login-form").before('<div class="alert" style="padding:10px;background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;">Erreur serveur.</div>');
      });

    });

  });
  </script>

</body>
</html>
