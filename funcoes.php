<?php

// 1. Identifica qual tag ICMS está sendo usada
function identificarTagICMS($icmsNode)
{
    if (!$icmsNode) return null;

    foreach ($icmsNode->children() as $child) {
        return $child->getName(); // Ex: ICMS00, ICMS20, ICMSSN102, etc.
    }

    return null;
}

// 2. Retorna interpretação da relação entre CFOP e a tag ICMS
function obterInfoTributaria($cfop, $tagICMS)
{
    $cfop = (string)$cfop;
    $tagICMS = strtoupper($tagICMS);

    // Tabela de exemplos
    $regras = [

        // REGIME NORMAL
        'ICMS00' => ['descricao' => 'ICMS integral (Tributada)', 'regime' => 'Normal'],
        'ICMS10' => ['descricao' => 'Tributada com ICMS ST', 'regime' => 'Normal'],
        'ICMS20' => ['descricao' => 'ICMS com redução base de cálculo', 'regime' => 'Normal'],
        'ICMS30' => ['descricao' => 'Isenta ou não tributada com ST', 'regime' => 'Normal'],
        'ICMS40' => ['descricao' => 'Isenção do ICMS', 'regime' => 'Normal'],
        'ICMS41' => ['descricao' => 'Não tributada', 'regime' => 'Normal'],
        'ICMS51' => ['descricao' => 'Diferimento do ICMS', 'regime' => 'Normal'],
        'ICMS60' => ['descricao' => 'ICMS cobrado anteriormente (ST)', 'regime' => 'Normal'],
        'ICMS90' => ['descricao' => 'Outras situações de ICMS', 'regime' => 'Normal'],

        // SIMPLES NACIONAL
        'ICMSSN101' => ['descricao' => 'SN - Crédito permitido', 'regime' => 'Simples Nacional'],
        'ICMSSN102' => ['descricao' => 'SN - Isento ou não tributado', 'regime' => 'Simples Nacional'],
        'ICMSSN201' => ['descricao' => 'SN - Com ST e crédito', 'regime' => 'Simples Nacional'],
        'ICMSSN202' => ['descricao' => 'SN - Com ST sem crédito', 'regime' => 'Simples Nacional'],
        'ICMSSN500' => ['descricao' => 'SN - ICMS cobrado anteriormente', 'regime' => 'Simples Nacional'],
        'ICMSSN900' => ['descricao' => 'SN - Outras situações', 'regime' => 'Simples Nacional'],
    ];

    $info = $regras[$tagICMS] ?? ['descricao' => 'Tag ICMS desconhecida', 'regime' => 'Desconhecido'];

    // Opcional: Regras baseadas em CFOP
    $entrada = in_array(substr($cfop, 0, 1), ['1', '2', '3']) ? 'entrada' : 'saida';
    $uf = in_array(substr($cfop, 0, 1), ['1', '5']) ? 'dentro do estado' : 'fora do estado';

    return [
        'cfop' => $cfop,
        'tipo_operacao' => $entrada,
        'abrangencia' => $uf,
        'tagICMS' => $tagICMS,
        'descricao' => $info['descricao'],
        'regime' => $info['regime']
    ];
}
