<?php
session_start();

if (
    !isset($_SESSION['id_client']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../index.php");
    exit();
}