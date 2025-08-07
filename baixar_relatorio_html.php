<?php
// Força o download como .html
header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="relatorio_produtos.html"');

// Inclui o conteúdo do relatório
include 'relatorio_html.php';
