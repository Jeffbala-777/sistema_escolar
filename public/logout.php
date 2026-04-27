<?php
require_once __DIR__ . '/../app/config/config.php';

$_SESSION = [];
session_destroy();

header('Location: /sistema_escolar/public/index.php');
exit;