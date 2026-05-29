<?php

class Response
{
    public static function json(
        array $dados,
        int $status = 200
    ): void {

        http_response_code($status);

        header('Content-Type: application/json');

        echo json_encode(
            $dados,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

        exit;
    }

    public static function success(
        string $mensagem,
        array $extra = []
    ): void {

        self::json(array_merge([
            'success' => true,
            'message' => $mensagem
        ], $extra));
    }

    public static function error(
        string $mensagem,
        int $status = 400
    ): void {

        self::json([
            'success' => false,
            'message' => $mensagem
        ], $status);
    }
}
?>