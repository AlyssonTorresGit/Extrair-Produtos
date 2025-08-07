<?php
require_once(__DIR__ . '/../../configs/config.php');

$stmt = $conn->prepare("DELETE FROM produtos_xml;");
$stmt->execute();

echo "Tabela de controle foi LIMPA com sucesso.";
