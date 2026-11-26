<?php
// DAO conversa com o banco de dados


class LivroDAO
{

//buscar todos livros - fichas
public function listarTodos(): array
{    
$pdo = Database ::getConexao();
$linhas = $pdo->query('SELECT id, titulo, autor, isbn, disponivel, usuario_id FROM Livros ORDER BY id')->fetchAll();
    
return array_map([$this, 'mapear'], $linhas);
}

    



// mesma coisa - busca id (nao achou, retorna null)
    public function buscarPorId(int $id): ?Livro
    {
        $pdo = Database::getConexao();
        $stmt = $pdo->prepare('SELECT id, titulo, autor, isbn, disponivel, usuario_id FROM Livros WHERE id = ?');
        $stmt->execute([$id]);
        $linha = $stmt->fetch();

        return $linha ? $this->mapear($linha) : null;
    }

    // transforma o que foi buscado em objeto e manda para o service e o controler
    private function mapear(array $linha): Livro
    {
        return new Livro(
            (int) $linha['id'],
            $linha['titulo'],
            $linha['autor'],
            $linha['isbn'],
            (bool) $linha['disponivel'],
            (int) $linha['usuario_id']
        );
    }



// CREATE - service monta o livro e DAO cadastra no banco
    public function inserir(Livro $livro): Livro
    {
        $pdo = Database::getConexao();
        $stmt = $pdo->prepare('INSERT INTO Livros (titulo, autor, isbn, disponivel, usuario_id) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$livro->titulo, $livro->autor, $livro->isbn, (int) $livro->disponivel, $livro->usuarioId]);
        $livro->id = (int) $pdo->lastInsertId();
        return $livro;
    }

     // UPDATE
    public function atualizar(Livro $livro): void
    {
        $pdo = Database::getConexao();
        $stmt = $pdo->prepare('UPDATE Livros SET titulo = ?, autor = ?, isbn = ?, disponivel = ?, usuario_id = ? WHERE id = ?');
        $stmt->execute([$livro->titulo, $livro->autor, $livro->isbn, (int) $livro->disponivel, $livro->usuarioId, $livro->id]);
    }


      // DELETE
    public function excluir(int $id): void
    {
        $pdo = Database::getConexao();
        $stmt = $pdo->prepare('DELETE FROM Livros WHERE id = ?');
        $stmt->execute([$id]);
    }
}