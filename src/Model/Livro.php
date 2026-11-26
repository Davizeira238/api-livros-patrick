<?php
// Model: só guarda os dados de um livro 
class Livro
{
    public function __construct(
        public ?int $id,
        public string $titulo,
        public string $autor,
        public ?string $isbn,
        public bool $disponivel,
        public int $usuarioId
    ) {
    }

    // Transforma o objeto em array para virar JSON na resposta da API
    // Array tras todas as informacoes do livro
    // JSON LINGUAGEM UNIVERSAL
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'autor' => $this->autor,
            'isbn' => $this->isbn,
            'disponivel' => $this->disponivel,
            'usuario_id' => $this->usuarioId,
        ];
    }
}