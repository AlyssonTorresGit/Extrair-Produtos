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

$tipos_validos = ['00', '10', '20', '30', '40', '41', '50', '51', '60', '61', '70', '90', '101', '102', '103', '201', '202', '203', '300', '400', '500', '900'];

foreach ($_FILES['xmls']['tmp_name'] as $tmpPath) {
    $xml = loadXmlWithoutNamespaces($tmpPath);
    if (!$xml) continue;

    //Detecta tipo de XML
    if (isset($xml->NFe->infNFe)) {
        $inf = $xml->NFe->infNFe;
        $modelo = (string)($inf->ide->mod ?? '');
    } elseif (isset($xml->infNFe)) {
        $inf = $xml->infNFe;
        $modelo = (string)($inf->ide->mod ?? '');
    } elseif (isset($xml->CFe->infCFe)) {
        $inf = $xml->CFe->infCFe;
        $modelo = '59'; // CF-e SAT
    } else {
        continue; // XML desconhecido
    }
    // echo "<!-- Modelo detectado: $modelo -->";
    // if (isset($xml->NFe->infNFe)) {
    //     $itens = $xml->NFe->infNFe->det;
    // } elseif (isset($xml->CFe->infCFe)) {
    //     $itens = $xml->CFe->infCFe->det;
    // } else {
    //     $itens = [];
    // }

    if (isset($xml->NFe->infNFe)) {
        $inf = $xml->NFe->infNFe;
    } elseif (isset($xml->CFe->infCFe)) {
        $inf = $xml->CFe->infCFe;
    } else {
        continue;
    }


    // Apenas aceita modelos conhecidos (55 = NFe, 65 = NFC-e, 59 = CF-e SAT)
    if (!in_array($modelo, ['55', '65', '59'])) {
        continue;
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



        $cst_icms = '500'; // valor padrão
        $tributacao = '';  // nova variável para armazenar o CST/CSOSN bruto

        if (isset($imposto->ICMS)) {
            $icms = $imposto->ICMS;
            foreach ($icms->children() as $tipo => $info) {
                if (in_array((string)$info->CST, $tipos_validos)) {
                    $cst_icms = (string)$info->CST;
                    $tributacao = (string)$info->CST;
                } elseif (in_array((string)$info->CSOSN, $tipos_validos)) {
                    $cst_icms = (string)$info->CSOSN;
                    $tributacao = (string)$info->CSOSN;
                }
            }
        }


        // Verifica se o produto já foi processado antes
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
                '00' => 'Tributada integralmente',
                '10' => 'Tributada e com ST',
                '20' => 'Com redução de base',
                '30' => 'Isenta com ST',
                '40', '41', '50' => 'Isenta ou não tributada',
                '60' => 'ST já retido',
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
