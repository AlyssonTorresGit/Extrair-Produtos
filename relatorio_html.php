<?php
require_once(__DIR__ . '/../../configs/variaveis.php');
require_once(PATH_CONFIGS . 'config.php'); // Isso define $pdo

$stmt = $conn->query("SELECT * FROM produtos_xml");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once('../../includes/head.php');
?>

<h2>Relatório de Produtos Processados</h2>
<main class="relatorio">
    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Produto</th>
                    <th>Preço</th>
                    <th>Un</th>
                    <th>CST</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?= htmlspecialchars($produto['cProd']) ?></td>
                        <td><?= htmlspecialchars($produto['xProd']) ?></td>
                        <td><?= 'R$ ' . number_format((float)$produto['vUnCom'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($produto['unidade']) ?></td>
                        <td><?= htmlspecialchars($produto['CST_ICMS']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <form action="baixar_relatorio_html.php" method="get">
            <button type="submit" class="btn-baixar btn destaque-btn">
                <i data-lucide="clipboard-copy"></i> Baixar Relatório (.html)
            </button>
        </form>
    </div>
</main>
<?php
include_once('../../includes/footer.php');
?>