<?php
header('Content-Type: text/html; charset=utf-8');
require_once('variaveis.php');
require_once('config.php');
require_once('gerar_update_icms.php');
require_once('gerar_insert_icms.php');
require_once('funcoes_xml.php'); // novo arquivo com as funções úteis

error_reporting(E_ALL);
ini_set('max_execution_time', 0);

if (!isset($_FILES['xmls'])) {
    die("<p style='color:red;'>Nenhum arquivo XML foi enviado.</p>");
}

$modoCodigo = $_POST['modo_codigo'] ?? 'xml';
$codigoSequencial = 1;
$comandos = [];
$relatorio = [];

$tipos_validos = ['00', '10', '20', '30', '40', '41', '50', '51', '60', '61', '70', '90', '101', '102', '103', '201', '202', '203', '300', '400', '500', '900'];

foreach ($_FILES['xmls']['tmp_name'] as $filePath) {
    $xml = loadXmlWithoutNamespaces($filePath);
    if (!$xml) continue;

    $tipoXml = identificarTipoXML($xml);

    if ($tipoXml === 'NFe' && isset($xml->NFe->infNFe)) {
        $itens = $xml->NFe->infNFe->det;
    } elseif ($tipoXml === 'CFe' && isset($xml->infCFe)) {
        $itens = $xml->infCFe->det;
    } else {
        continue;
    }

    foreach ($itens as $det) {
        $dados = extrairProdutoXML($det);

        $cProd = $dados['cProd'];
        $xProd = str_replace("'", "", $dados['xProd']);
        $vUnCom = floatval($det->prod->vUnCom ?? 0);
        $unidade = substr($dados['uCom'], 0, 3);
        $NCM = (string)($det->prod->NCM ?? '');
        $CFOP = $dados['CFOP'];
        $cEAN = (string)($det->prod->cEAN ?? '');
        $cst_icms = $dados['CST'] ?: '500';

        // Verifica se o produto já foi processado antes
        $stmt = $conn->prepare("SELECT xProd FROM produtos_xml WHERE xProd = ? AND cProd = ?");
        $stmt->execute([$xProd, $cProd]);

        if ($stmt->rowCount() > 0) {
            $sql = gerar_update_icms($cst_icms, $cProd, $xProd, $NCM, $CFOP, $unidade, $vUnCom, $cEAN);
            $comandos[] = $sql;
        } else {
            $codigoFinal = $modoCodigo === 'sequencial' ? $codigoSequencial++ : "$cProd";
            $sql = gerar_insert_icms($cst_icms, $codigoFinal, $xProd, $NCM, $CFOP, $unidade, $vUnCom, $cEAN);
            $comandos[] = $sql;

            $conn->prepare("INSERT INTO produtos_xml (cProd, xProd, vUnCom, unidade, CST_ICMS) VALUES (?, ?, ?, ?, ?)")
                ->execute([$cProd, $xProd, $vUnCom, $unidade, $cst_icms]);
        }

        $relatorio[] = [
            'cProd' => $cProd,
            'xProd' => $xProd,
            'cst'   => $cst_icms,
            'vUnCom' => $vUnCom,
            'tributacao' => match ($cst_icms) {
                '00' => 'Tributada integralmente',
                '10' => 'Tributada e com ST',
                '20' => 'Com redução de base',
                '30' => 'Isenta com ST',
                '40', '41', '50' => 'Isenta ou não tributada',
                '60' => 'ST já retido',
                '61' => 'St já retido',
                '70' => 'Com redução e ST',
                '90' => 'Outros',
                '101' => 'SN com crédito',
                '102', '103', '300', '400' => 'SN isento',
                '500' => 'Isento ou ST',
                '900' => 'SN Outros',
                default => 'CST desconhecido'
            }
        ];
    }
}

$comando_final = implode("\n\n", $comandos);
$dataHora = date('Ymd_His');
$nome_sql = "comandos_$dataHora.sql";
$nome_html = "relatorio_$dataHora.html";

ob_start();
include __DIR__ . '/relatorio_html.php';
$relatorio_html = ob_get_clean();
$base64html = base64_encode($relatorio_html);

include __DIR__ . '/relatorio_cmds.php';
