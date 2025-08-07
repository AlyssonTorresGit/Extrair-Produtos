<?php

// ================================
// FUNÇÕES ÚTEIS GLOBAIS
// ================================

/**
 * Redireciona para uma URL relativa ao $base_url
 */
function redirecionar($caminho = '')
{
    global $base_url;
    header("Location: " . rtrim($base_url, '/') . '/' . ltrim($caminho, '/'));
    exit;
}

/**
 * Exibe uma mensagem de erro personalizada e encerra
 */
function erro($mensagem = 'Erro desconhecido')
{
    echo "<div style='color: red; font-weight: bold; padding: 10px;'>Erro: $mensagem</div>";
    exit;
}

/**
 * Escreve em um arquivo de log simples
 */
function registrar_log($mensagem, $arquivo = 'logs/erros.log')
{
    $data = date('Y-m-d H:i:s');
    $linha = "[$data] $mensagem\n";
    file_put_contents($arquivo, $linha, FILE_APPEND);
}
