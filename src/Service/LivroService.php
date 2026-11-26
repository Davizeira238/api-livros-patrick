<?php
// Service: regras de negócio e validação (equivale ao LivroService.java).
class LivroService
{
    private LivroDAO $dao;

    public function __construct(?LivroDAO $dao = null)
    {
        $this->dao = $dao ?? new LivroDAO();
    }

    public function listar(): array
    {
        return $this->dao->listarTodos();
    }

    // Devolve o livro ou null se não existir
    public function buscar(int $id): ?Livro
    {
        return $this->dao->buscarPorId($id);
    }

    // Valida os dados recebidos e cadastra
    public function criar(array $dados): Livro
    {
        $livro = $this->montarLivro(null, $dados);
        return $this->dao->inserir($livro);
    }

    // Valida e atualiza; devolve null se o livro não existir
    public function atualizar(int $id, array $dados): ?Livro
    {
        if ($this->dao->buscarPorId($id) === null) {
            return null;
        }
        $livro = $this->montarLivro($id, $dados);
        $this->dao->atualizar($livro);
        return $livro;
    }

    // Devolve false se o livro não existir
    public function excluir(int $id): bool
    {
        if ($this->dao->buscarPorId($id) === null) {
            return false;
        }
        $this->dao->excluir($id);
        return true;
    }

    // Aplica as regras (mesmas do projeto Java) e cria o objeto Livro.
    private function montarLivro(?int $id, array $dados): Livro
    {
        $titulo = $this->texto($dados['titulo'] ?? null, 'Título', 255, true);
        $autor = $this->texto($dados['autor'] ?? null, 'Autor', 150, true);
        $isbn = $this->texto($dados['isbn'] ?? null, 'ISBN', 17, false);

        $usuarioId = $dados['usuario_id'] ?? null;
        if (!is_int($usuarioId) || $usuarioId <= 0) {
            throw new InvalidArgumentException('Informe o usuario_id (número inteiro positivo).');
        }

        $disponivel = $dados['disponivel'] ?? true;
        if (!is_bool($disponivel)) {
            throw new InvalidArgumentException('disponivel deve ser true ou false.');
        }

        return new Livro($id, $titulo, $autor, $isbn, $disponivel, $usuarioId);
    }

    // Tira espaços, confere obrigatório e tamanho máximo (igual ao método texto() do Java).
    private function texto($valor, string $campo, int $limite, bool $obrigatorio): ?string
    {
        if ($valor !== null && !is_string($valor)) {
            throw new InvalidArgumentException("$campo deve ser texto.");
        }
        $texto = trim($valor ?? '');
        if ($obrigatorio && $texto === '') {
            throw new InvalidArgumentException("$campo é obrigatório.");
        }
        if (mb_strlen($texto) > $limite) {
            throw new InvalidArgumentException("$campo excede $limite caracteres.");
        }
        return $texto === '' ? null : $texto;
    }
}
