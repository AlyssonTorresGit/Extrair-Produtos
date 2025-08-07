<?php
require_once('config.php');

$stmt = $conn->prepare("DELETE FROM produtos_xml;");
$stmt->execute();

echo "Tabela de controle foi LIMPA com sucesso.";
