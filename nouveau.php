<?php
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>SHAMTEK — Nouveau Client</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="style.css">

  <style>
    .is-valid{ border-color:#16a34a !important; background:#ecfdf5 !important; }
    .is-invalid{ border-color:#dc2626 !important; background:#fef2f2 !important; }
    .field-hint{ display:block; margin-top:6px; font-size:0.9em; color:#b91c1c; }
    .disabled-btn{ opacity:0.6; pointer-events:none; }
  </style>

</head>
<body>

<header>
  <div class="container header-row">
    <div class="logo">
      <a href="index.php"><img src="images/logo.svg" alt="SHAMTEK" class="logo-img"></a>
    </div>
    <div class="login-icon">
      <a href="index.php" title="Accueil" class="header-link">
        <img src="images/login.svg" alt="Accueil" class="icon-login">
        <span class="login-text"> Accueil </span>
      </a>
    </div>
  </div>
</header>

<main>
  <div class="container">
    <div class="panel">
      <div class="panel-body">
        <h1 class="h1">Créer un nouveau client</h1>
        <div class="badges">
          <span class="badge">Tous les champs sont obligatoires</span>
          <span class="badge">Email valide requis</span>
        </div>

        <form action="enregistrement.php" method="post" class="new-client" novalidate>
          <div class="grid" style="grid-template-columns: repeat(auto-fit,minmax(260px,1fr));">

            <label class="panel-body" style="padding:0">
              <div class="card-body" style="padding-bottom:6px; font-weight:600">Nom</div>
              <input type="text" name="n" required style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px;">
            </label>

            <label class="panel-body" style="padding:0">
              <div class="card-body" style="padding-bottom:6px; font-weight:600">Prénom</div>
              <input type="text" name="p" required style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px;">
            </label>

            <label class="panel-body" style="padding:0">
              <div class="card-body" style="padding-bottom:6px; font-weight:600">Adresse</div>
              <input type="text" name="adr" required style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px;">
            </label>

            <label class="panel-body" style="padding:0">
              <div class="card-body" style="padding-bottom:6px; font-weight:600">Numéro de téléphone</div>
              <input type="text" name="num" required pattern="[0-9+ ]{6,}"
                     style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px;">
            </label>

            <label class="panel-body" style="padding:0">
              <div class="card-body" style="padding-bottom:6px; font-weight:600">Adresse e-mail</div>
              <input type="email" name="mail" required
                     style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px;">
            </label>

            <label class="panel-body" style="padding:0">
              <div class="card-body" style="padding-bottom:6px; font-weight:600">Mot de passe</div>
              <input type="password" name="mdp1" required minlength="4"
                     style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px;">
            </label>

            <label class="panel-body" style="padding:0">
              <div class="card-body" style="padding-bottom:6px; font-weight:600">Confirmer mot de passe</div>
              <input type="password" name="mdp2" required minlength="4"
                     style="width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px;">
            </label>

          </div>

          <div style="margin-top:14px; display:flex; gap:10px; align-items:center;">
            <button class="btn" type="submit">Valider</button>
            <a href="index.php" class="back">← Retour à l'accueil</a>
          </div>

        </form>

      </div>
    </div>
  </div>
</main>

<footer>
  <div class="container foot">
    <span>© <?= date('Y') ?> SHAMTEK</span>
    <span><a href="contact.php" class="footer-link">Contact</a></span>
  </div>
</footer>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
(function($){

  function ensureHint($input){
    let $hint = $input.next(".field-hint");
    if($hint.length === 0){
      $hint = $('<small class="field-hint" aria-live="polite"></small>');
      $input.after($hint);
    }
    return $hint;
  }

  function setValid($input){
    $input.removeClass('is-invalid').addClass('is-valid');
    ensureHint($input).text('');
  }

  function setInvalid($input, msg){
    $input.removeClass('is-valid').addClass('is-invalid');
    ensureHint($input).text(msg || 'Champ invalide.');
  }

  const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const passRe  = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/;

  function allOk($form){
    let ok = true;
    $form.find('input[required]').each(function(){
      if(!$(this).hasClass('is-valid')) ok = false;
    });
    return ok;
  }

  function updateSubmitState($form){
    const $btn = $form.find('button[type="submit"]');
    if(allOk($form)){
      $btn.prop('disabled', false).removeClass('disabled-btn');
    }else{
      $btn.prop('disabled', true).addClass('disabled-btn');
    }
  }

  $(function(){

    const $form = $('.new-client');

    const $n = $form.find('input[name="n"]');
    const $p = $form.find('input[name="p"]');
    const $adr = $form.find('input[name="adr"]');
    const $num = $form.find('input[name="num"]');
    const $mail = $form.find('input[name="mail"]');
    const $mdp1 = $form.find('input[name="mdp1"]');
    const $mdp2 = $form.find('input[name="mdp2"]');

    function validateRequired($input, msg){
      const v = ($input.val()||'').trim();
      if(v === '') setInvalid($input, msg);
      else setValid($input);
    }

    $n.on('input blur', ()=>{ validateRequired($n, "Nom obligatoire"); updateSubmitState($form); });
    $p.on('input blur', ()=>{ validateRequired($p, "Prénom obligatoire"); updateSubmitState($form); });
    $adr.on('input blur', ()=>{ validateRequired($adr, "Adresse obligatoire"); updateSubmitState($form); });
    $num.on('input blur', ()=>{ validateRequired($num, "Numéro obligatoire"); updateSubmitState($form); });

    // EMAIL + Vérification AJAX
    let emailTimer = null;
    function checkEmail(){
      const v = ($mail.val()||'').trim();
      if(v === ''){ setInvalid($mail, "Email requis"); updateSubmitState($form); return; }
      if(!emailRe.test(v)){ setInvalid($mail, "Email invalide"); updateSubmitState($form); return; }
      setValid($mail);
      $.ajax({
        url:"check_email.php",
        method:"POST",
        dataType:"json",
        data:{email:v}
      }).done(function(resp){
        if(resp.exists){
          setInvalid($mail,"Email déjà utilisé");
        } else {
          setValid($mail);
        }
      }).always(()=> updateSubmitState($form));
    }

    $mail.on('input', function(){
      clearTimeout(emailTimer);
      emailTimer = setTimeout(checkEmail, 400);
    });
    $mail.on('blur', checkEmail);

    // PASSWORD
    function validatePass1(){
      const v = $mdp1.val() || "";
      if(v === "") setInvalid($mdp1, "Mot de passe requis");
      else if(!passRe.test(v)) setInvalid($mdp1, "1 lettre, 1 chiffre, 1 caractère spécial");
      else setValid($mdp1);
    }

    function validatePass2(){
      const v1 = $mdp1.val() || "";
      const v2 = $mdp2.val() || "";
      if(v2 === "") setInvalid($mdp2, "Confirmation requise");
      else if(v1 !== v2) setInvalid($mdp2, "Les mots de passe ne correspondent pas");
      else setValid($mdp2);
    }

    $mdp1.on('input blur', ()=>{ validatePass1(); validatePass2(); updateSubmitState($form); });
    $mdp2.on('input blur', ()=>{ validatePass2(); updateSubmitState($form); });


    //  Soumission AJAX 
    $form.on('submit', function(e){
      e.preventDefault();

      // Relancer toutes les validations
      $n.trigger('blur');
      $p.trigger('blur');
      $adr.trigger('blur');
      $num.trigger('blur');
      validatePass1();
      validatePass2();
      checkEmail();
      updateSubmitState($form);

      if(!allOk($form)){ return; }

      const formData = $form.serialize();

      $.ajax({
        url: "enregistrement.php",
        method:"POST",
        dataType:"json",
        data: formData
      })
      .done(function(resp){
        $(".alert").remove();
        if(resp.success){
          $form.before(`
            <div class="alert" style="padding:10px;background:#d1fae5;border:1px solid #10b981;border-radius:8px;">
              Compte créé avec succès !
            </div>
          `);
          setTimeout(()=> window.location.href="index.php", 1000);
        } else {
          $form.before(`
            <div class="alert" style="padding:10px;background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;">
              ${resp.error}
            </div>
          `);
        }
      })
      .fail(function(){
        $form.before(`
          <div class="alert" style="padding:10px;background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;">
            Erreur serveur.
          </div>
        `);
      });

    });

  });

})(jQuery);
</script>

</body>
</html>
