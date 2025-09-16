<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1><?= $exception->getMessage() ?></h1>

    <p><strong>In file:</strong> <?= $exception->getFile() ?></p>
    <p><strong>On line:</strong> <?= $exception->getLine() ?></p>

    <pre>
<?= $exception->getTraceAsString() ?>
    </pre>
</body>
</html>