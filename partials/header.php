<?php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/database/database.php';

$title = $title ?? 'Sistema Escolar';

$usuario = $_SESSION['usuario'] ?? null;

?>
<!doctype html>
<html lang="pt-br">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">

    <title>
        <?= e($title) ?>
    </title>

    <link
        rel="preconnect"
        href="https://cdn.jsdelivr.net"
        crossorigin>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

    <link
        href="<?= base_url('partials/style.css') ?>"
        rel="stylesheet">

    <style>.topbar{position:fixed!important;top:0!important;}</style>

    <script src="https://cdn.jsdelivr.net/npm/vanilla-masker@1.2.0/lib/vanilla-masker.min.js"></script>
</head>

<body>
