<?php
header('Content-Type: text/html; charset=utf-8');
require_once('variaveis.php');
require_once('config.php');
require_once('gerar_update_icms.php');
require_once('gerar_insert_icms.php');
function loadXmlWithoutNamespaces($filePath)
{
    $xml = simplexml_load_file($filePath, 'SimpleXMLElement', LIBXML_NOCDATA);
    if (!$xml) return false;

    // Remove todos os namespaces
    foreach ($xml->getDocNamespaces(true) as $prefix => $ns) {
        $xml->registerXPathNamespace($prefix, $ns);
    }

    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $xmlStr = $dom->saveXML();

    // Remove namespaces manualmente da string XML
    $xmlStr = preg_replace('/xmlns(:\w+)?="[^"]+"/', '', $xmlStr);

    return simplexml_load_string($xmlStr);
}


error_reporting(E_ALL);
ini_set('max_execution_time', 0);

if (!isset($_FILES['xmls'])) {
    die("<p style='color:red;'>Nenhum arquivo XML foi enviado.</p>");
}

$modoCodigo = $_POST['modo_codigo'] ?? 'xml';
$codigoSequencial = 1;
$comandos = [];
$relatorio = [];

foreach ($_FILES['xmls']['tmp_name'] as $tmpPath) {
    $xml = loadXmlWithoutNamespaces($tmpPath);
    if (!$xml) continue;

    // Detecta se é NFe ou CF-e
    if (isset($xml->NFe->infNFe)) {
        $inf = $xml->NFe->infNFe; // NFe
    } elseif (isset($xml->CFe->infCFe)) {
        $inf = $xml->CFe->infCFe; // CF-e
    } else {
        continue; // XML desconhecido
    }

    foreach ($inf->det as $det) {
        $prod = $det->prod;
        $imposto = $det->imposto;

        $cProd = (string)$prod->cProd;
        $xProd = str_replace("'", "", (string)$prod->xProd);
        $vUnCom = floatval($prod->vUnCom ?? 0);
        $unidade = substr((string)($prod->uCom ?? ''), 0, 3);
        $NCM = (string)($prod->NCM ?? '');
        $CFOP = (string)($prod->CFOP ?? '');
        $cEAN = (string)($prod->cEAN ?? '');

        // Pega CST ou CSOSN do ICMS
        $icmsTipo = array_keys(get_object_vars($imposto->ICMS))[0] ?? '';
        $cst_icms = (string)(
            $imposto->ICMS->$icmsTipo->CST ??
            $imposto->ICMS->$icmsTipo->CSOSN ??
            '500'
        );

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

        // Adiciona ao relatório (independente de já existir ou não)
        $relatorio[] = [
            'cProd' => $cProd,
            'xProd' => $xProd,
            'cst'   => $cst_icms,
            'vUnCom' => $vUnCom,
            'tributacao' => match ($cst_icms) {
                '500' => 'ICMS Isento / Substituição Tributária',
                '102' => 'Simples Nacional - Tributada',
                default => 'Outro CST'
            }
        ];
    }
}

// Gerar strings
$comando_final = implode("\n\n", $comandos);
$dataHora = date('Ymd_His');
$nome_sql = "comandos_$dataHora.sql";
$nome_html = "relatorio_$dataHora.html";

// Captura o HTML do relatório
ob_start();
include __DIR__ . '/relatorio_html.php';
$relatorio_html = ob_get_clean();

// $base64sql = base64_encode($comando_final);
$base64html = base64_encode($relatorio_html);

// Inclui o layout final
include __DIR__ . '/relatorio_cmds.php';
