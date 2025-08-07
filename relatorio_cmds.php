<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h2>Comandos SQL Gerados</h2>
    <textarea class="insert" rows="25"
        style="width: 100%; font-size: 12px; height: 510px;"><?= htmlspecialchars($comando_final) ?></textarea><br><br>


    <script>
        function abrirRelatorio() {
            const html = atob("<?= $base64html ?>");
            const blob = new Blob([html], {
                type: 'text/html'
            });
            const url = URL.createObjectURL(blob);
            window.open(url, '_blank');
        }

        setTimeout(abrirRelatorio, 500);

        const blob = new Blob([atob("<?= $base64sql ?>")], {
            type: 'text/plain'
        });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = "<?= $nome_sql ?>";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    </script>

</body>

</html>