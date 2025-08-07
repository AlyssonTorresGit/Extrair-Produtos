<?php
require_once(__DIR__ . '/../../configs/variaveis.php');
require_once(PATH_CONFIGS . 'config.php');

include_once('../../includes/head.php');
include_once('../../includes/topbar.php');
include_once('../../includes/breadcrumb.php');
?>

<main class="xml">
    <div class="container">
        <h1>Importar XML</h1>
        <form action="limpar_controle.php" method="POST" target="output"
            onsubmit="return confirm('Tem certeza que quer limpar tudo?');">
            <button class="btn-limpar btn destaque-btn" type="submit" class="btn-danger">
                <i data-lucide="trash"></i> Limpar tabela de controle
            </button>
        </form>
        <form action="processa_xml.php" method="POST" enctype="multipart/form-data" target="output">
            <label>Arquivos XML:</label>
            <input type="file" name="xmls[]" multiple required accept=".xml">

            <div class="modo-codigo">
                <label>
                    <input type="radio" name="modo_codigo" value="xml" checked>
                    Usar código do XML (cProd)
                </label>
                <label>
                    <input type="radio" name="modo_codigo" value="sequencial">
                    Usar código sequencial(1, 2, 3, ...)
                </label>
            </div>

            <button class="btn-processar btn destaque-btn" type="submit">
                <i data-lucide="file-code"></i> Processar XMLs
            </button>
        </form>
        <div class="btn-container">
            <button class="btn-copiar btn destaque-btn" onclick="copiar()">
                <i data-lucide="clipboard-copy"></i> Copiar Inserts
            </button>
            <button class="btn-relatorio btn destaque-btn" onclick="abrirRelatorio()">
                <i data-lucide="external-link"></i> Abrir Relatorio de produtos Processados
            </button>
            <iframe class="inserts" name="output" style="width: 100%; height: 600px; border: none;"></iframe>
        </div>

    </div>

    <script>
        function copiar() {
            const iframe = document.querySelector('iframe');
            const pre = iframe.contentDocument.querySelector('pre');
            if (pre) {
                const texto = pre.innerText;
                navigator.clipboard.writeText(texto).then(() => {
                    alert('Comandos copiados!');
                });
            } else {
                alert('Nada para copiar!');
            }
        }

        function abrirRelatorio() {
            window.open('relatorio_html.php', '_blank');
        }
    </script>
</main>

<?php
include_once('../../includes/footer.php');
?>