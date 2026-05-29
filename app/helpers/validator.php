<?php

class Validator
{
    private array $erros = [];

    public function required(
        string $campo,
        string $valor,
        string $mensagem
    ): self {

        if (trim($valor) === '') {
            $this->erros[$campo] = $mensagem;
        }

        return $this;
    }

    public function email(
        string $campo,
        string $valor,
        string $mensagem
    ): self {

        if (
            trim($valor) !== '' &&
            !filter_var($valor, FILTER_VALIDATE_EMAIL)
        ) {

            $this->erros[$campo] = $mensagem;
        }

        return $this;
    }

    public function min(
        string $campo,
        string $valor,
        int $minimo,
        string $mensagem
    ): self {

        if (mb_strlen(trim($valor)) < $minimo) {
            $this->erros[$campo] = $mensagem;
        }

        return $this;
    }

    public function max(
        string $campo,
        string $valor,
        int $maximo,
        string $mensagem
    ): self {

        if (mb_strlen(trim($valor)) > $maximo) {
            $this->erros[$campo] = $mensagem;
        }

        return $this;
    }

    public function errors(): array
    {
        return $this->erros;
    }

    public function fails(): bool
    {
        return !empty($this->erros);
    }
}
?>