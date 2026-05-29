-- SQL para criar o usuário Administrador Supremo inicial
-- Email: supremo@adm.com
-- Senha: ADMIN123

INSERT INTO `usuarios` 
(`escola_id`, `perfil_id`, `nome_completo`, `email`, `senha`, `cpf`, `telefone`, `ativo`, `criado_em`) 
VALUES 
(1, 1, 'Administrador Supremo', 'supremo@adm.com', '$2b$12$a.YyUBRP8Pqe9is1efhv2OwihYaIEJtbXSUwPjiz0HynbncuytRfi', '000.000.000-00', '(00) 00000-0000', 1, NOW());
