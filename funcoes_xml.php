<?php
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

    $icmsTag = $icms ? array_keys((array) $icms)[0] ?? '' : '';
    $icmsObj = $icmsTag && isset($icms->$icmsTag) ? $icms->$icmsTag : null;

    $cst = '';
    if ($icmsObj) {
        $cst = getTagSeguro($icmsObj, 'CST') ?: getTagSeguro($icmsObj, 'CSOSN');
    }

    $cfop = getTagSeguro($produto, 'CFOP') ?: getTagSeguro($det, 'CFOP');

    return [
        'cProd' => getTagSeguro($produto, 'cProd'),
        'xProd' => getTagSeguro($produto, 'xProd'),
        'uCom'  => getTagSeguro($produto, 'uCom'),
        'CFOP'  => $cfop,
        'CST'   => $cst,
    ];
}

function loadXmlWithoutNamespaces($filePath)
{
    $xml = simplexml_load_file($filePath, 'SimpleXMLElement', LIBXML_NOCDATA);
    if (!$xml) return false;

    foreach ($xml->getDocNamespaces(true) as $prefix => $ns) {
        $xml->registerXPathNamespace($prefix, $ns);
    }

    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $xmlStr = $dom->saveXML();

    $xmlStr = preg_replace('/xmlns(:\w+)?="[^"]+"/', '', $xmlStr);

    return simplexml_load_string($xmlStr);
}
