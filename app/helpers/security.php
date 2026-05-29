<?php

if (!function_exists('e')) {

    function e(?string $valor): string
    {
        return htmlspecialchars(
            $valor ?? '',
            ENT_QUOTES,
            'UTF-8'
        );
    }
}

if (!function_exists('perfil_label')) {

    function perfil_label(string $perfil): string
    {
        $labels = [

            'admin' => 'Administrador',

            'admin_supremo' => 'Administrador Supremo',

            'professor' => 'Professor',

            'aluno' => 'Aluno'
        ];

        return $labels[$perfil] ?? ucfirst($perfil);
    }
}

if (!function_exists('dominio_permitido')) {

    function dominio_permitido(string $email, ?string $perfil = null): bool
    {
        $email = trim(strtolower($email));

        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            return false;
        }

        $dominiosPorPerfil = [
            'admin_supremo' => 'adm.com',
            'admin' => 'admin.edu.com',
            'professor' => 'prof.edu.com',
            'aluno' => 'aluno.edu.com'
        ];

        $partes = explode('@', $email);
        $dominio = strtolower(end($partes));

        if ($perfil !== null && $perfil !== '') {
            return isset($dominiosPorPerfil[$perfil]) &&
                $dominio === $dominiosPorPerfil[$perfil];
        }

        return in_array(
            $dominio,
            array_values($dominiosPorPerfil),
            true
        );
    }
}

if (!function_exists('usuario_logado')) {

    function usuario_logado(): bool
    {
        return isset($_SESSION['usuario']);
    }
}

if (!function_exists('logout_seguro')) {

    function logout_seguro(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {

            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_unset();

        session_destroy();
    }
}

?>