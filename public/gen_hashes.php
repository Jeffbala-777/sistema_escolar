<?php
$passwords = [
    'ADMIN123',
    'ESCOLA123',
    'PROF123',
    'ALUNO123'
];

echo "HASHES REAIS:\n";
foreach ($passwords as $pwd) {
    echo $pwd . ": " . password_hash($pwd, PASSWORD_DEFAULT) . "\n";
}
?>
