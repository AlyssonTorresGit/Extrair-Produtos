<?php
// arquivo: funcoes_xml.php

function identificarTipoXML(SimpleXMLElement $xml): string
{
    if (isset($xml->infCFe)) return 'CFe';
    if (isset($xml->NFe->infNFe->ide->nNF)) return 'NFe';
    return 'Desconhecido';
}

function getTagSeguro($elemento, $tag)
{
    return isset($elemento->$tag) ? (string) $elemento->$tag : '';
}

function extrairProdutoXML(SimpleXMLElement $det): array
{
    $produto = $det->prod;
    $imposto = $det->imposto;
    $icms    = $imposto->ICMS ?? null;

    // Identificar tag ICMS existente
    $icmsTag = $icms ? array_keys((array) $icms)[0] ?? '' : '';
    $icmsObj = $icmsTag && isset($icms->$icmsTag) ? $icms->$icmsTag : null;

    // Obter CST ou CSOSN
    $cst = '';
    if ($icmsObj) {
        $cst = getTagSeguro($icmsObj, 'CST') ?: getTagSeguro($icmsObj, 'CSOSN');
    }

    // CFOP pode estar fora de prod em CF-e
    $cfop = getTagSeguro($produto, 'CFOP') ?: getTagSeguro($det, 'CFOP');

    return [
        'cProd' => getTagSeguro($produto, 'cProd'),
        'xProd' => getTagSeguro($produto, 'xProd'),
        'uCom'  => getTagSeguro($produto, 'uCom'),
        'CFOP'  => $cfop,
        'CST'   => $cst,
    ];
}

// Incluir no arquivo onde será usado:
// require_once 'funcoes_xml.php';
// $tipoXml = identificarTipoXML($xml);
// foreach ($xml->NFe->infNFe->det as $det) {
//     $dadosProduto = extrairProdutoXML($det);
//     // usar $dadosProduto['cProd'], $dadosProduto['CST'], etc.
// }