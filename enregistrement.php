<?php
// enregistrement.php — Version AJAX pour TP7B
session_start();
header('Content-Type: application/json');

require 'bd.php';
$pdo = getBD();

// Lire POST
$n    = trim($_POST['n']    ?? '');
$p    = trim($_POST['p']    ?? '');
$adr  = trim($_POST['adr']  ?? '');
$num  = trim($_POST['num']  ?? '');
$mail = trim($_POST['mail'] ?? '');
$mdp1 = $_POST['mdp1']      ?? '';
$mdp2 = $_POST['mdp2']      ?? '';

// Vérifier champs vides
if($n==='' || $p==='' || $adr==='' || $num==='' || $mail==='' || $mdp1==='' || $mdp2===''){
    echo json_encode(["success"=>false, "error"=>"Champs manquants"]);
    exit;
}

// Vérification email valide
if(!filter_var($mail, FILTER_VALIDATE_EMAIL)){
    echo json_encode(["success"=>false, "error"=>"Email invalide"]);
    exit;
}

// Mots de passe identiques ?
if($mdp1 !== $mdp2){
    echo json_encode(["success"=>false, "error"=>"Les mots de passe ne correspondent pas"]);
    exit;
}

// Vérifier si email existe déjà
$chk = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE LOWER(mail)=LOWER(?)");
$chk->execute([$mail]);
if($chk->fetchColumn() > 0){
    echo json_encode(["success"=>false, "error"=>"Email déjà utilisé"]);
    exit;
}

// Hasher le mot de passe
$mdp_hash = password_hash($mdp1, PASSWORD_DEFAULT);

// Insertion BD
$sql = $pdo->prepare("
    INSERT INTO clients (nom, prenom, adresse, numero, mail, mdp)
    VALUES (?, ?, ?, ?, ?, ?)
");

if($sql->execute([$n, $p, $adr, $num, $mail, $mdp_hash])){
    
    // Connexion automatique après création
    $_SESSION['nom']       = $n;
    $_SESSION['prenom']    = $p;
    $_SESSION['mail']      = $mail;
    $_SESSION['id_client'] = $pdo->lastInsertId();

    echo json_encode(["success"=>true]);
    exit;

} else {
    echo json_encode(["success"=>false, "error"=>"Erreur lors de l'insertion"]);
    exit;
}
