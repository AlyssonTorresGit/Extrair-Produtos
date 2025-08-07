<?php
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    // Config local XAMPP
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "upload_site";
} else {
    // Config hospedagem remota
    $host = "sql204.infinityfree.com";
    $user = "if0_38826779";
    $pass = "KtfE8K8gYWz";
    $db   = "if0_38826779_meu_site";
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
    exit;
}

$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/Extrair-produtos/";
$versao_sistema = "3.0";

require_once '/utils.php';
