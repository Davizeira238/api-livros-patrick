<?php
// SERVICE = o "GERENTE".
// Fica entre o Controller (garçom) e o DAO (cozinha).
// Confere as REGRAS antes de deixar qualquer coisa chegar no banco.
// Não sabe nada de HTTP/JSON e não tem SQL. (Igual ao LivroService.java)
class LivroService
{
    // Guarda o DAO, para poder pedir coisas ao banco.
    private LivroDAO $dao;

    // Quando o Service é criado, ele cria um DAO para usar
    // ("se ninguém me deu um DAO, eu crio um").
    public function __construct(?LivroDAO $dao = null)
    {
        $this->dao = $dao ?? new LivroDAO();
    }

    // LISTAR: só repassa o pedido para o DAO (não tem regra nenhuma).
    public function listar(): array
    {
        return $this->dao->listarTodos();
    }

    // BUSCAR UM: repassa para o DAO.
    // Devolve o livro, ou null se não existir (o Controller transforma null em 404).
    public function buscar(int $id): ?Livro
    {
        return $this->dao->buscarPorId($id);
    }

    // CADASTRAR:
    // 1) monta e valida o livro (id null, porque livro novo ainda não tem id)
    // 2) manda o DAO gravar; o DAO devolve o livro já com o id novo
    public function criar(array $dados): Livro
    {
        $livro = $this->montarLivro(null, $dados);
        return $this->dao->inserir($livro);
    }

    // ATUALIZAR:
    // 1) confere se o livro existe (se não existe, devolve null -> 404)
    // 2) monta e valida os dados novos
    // 3) manda o DAO atualizar no banco
    public function atualizar(int $id, array $dados): ?Livro
    {
        if ($this->dao->buscarPorId($id) === null) {
            return null;
        }
        $livro = $this->montarLivro($id, $dados);
        $this->dao->atualizar($livro);
        return $livro;
    }

    // EXCLUIR:
    // 1) confere se o livro existe (se não existe, devolve false -> 404)
    // 2) manda o DAO apagar e devolve true (-> 204)
    public function excluir(int $id): bool
    {
        if ($this->dao->buscarPorId($id) === null) {
            return false;
        }
        $this->dao->excluir($id);
        return true;
    }

    // AQUI FICAM AS REGRAS (as mesmas do projeto Java).
    // Pega os dados que vieram no JSON, confere campo por campo
    // e, se estiver tudo certo, monta a "ficha" Livro (o Model).
    // Se algo estiver errado, "lança um alarme" (throw) -> o Controller responde 400.
    private function montarLivro(?int $id, array $dados): Livro
    {
        // texto(valor, nome do campo, tamanho máximo, é obrigatório?)
        $titulo = $this->texto($dados['titulo'] ?? null, 'Título', 255, true);   // obrigatório, até 255
        $autor = $this->texto($dados['autor'] ?? null, 'Autor', 150, true);      // obrigatório, até 150
        $isbn = $this->texto($dados['isbn'] ?? null, 'ISBN', 17, false);         // opcional, até 17

        // usuario_id: tem que ser número inteiro e maior que zero.
        // (Se o usuário existe ou não, quem descobre é o BANCO, pela chave estrangeira.)
        $usuarioId = $dados['usuario_id'] ?? null;
        if (!is_int($usuarioId) || $usuarioId <= 0) {
            throw new InvalidArgumentException('Informe o usuario_id (número inteiro positivo).');
        }

        // disponivel: se não vier, vale true. Se vier, tem que ser true ou false.
        $disponivel = $dados['disponivel'] ?? true;
        if (!is_bool($disponivel)) {
            throw new InvalidArgumentException('disponivel deve ser true ou false.');
        }

        // Tudo certo: monta a ficha do livro.
        return new Livro($id, $titulo, $autor, $isbn, $disponivel, $usuarioId);
    }

    // AJUDANTE para os campos de texto:
    // 1) se veio algo que não é texto -> erro
    // 2) tira os espaços das pontas (trim)
    // 3) se é obrigatório e ficou vazio -> erro "é obrigatório"
    // 4) se passou do tamanho máximo -> erro "excede" (mb_strlen conta acentos certo)
    // 5) texto vazio vira null (ex.: ISBN não informado)
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
