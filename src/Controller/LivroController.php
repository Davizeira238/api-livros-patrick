<?php
// Controller: recebe a requisição HTTP e devolve JSON (equivale ao LivroController.java).
class LivroController
{
    private LivroService $service;

    public function __construct(?LivroService $service = null)
    {
        $this->service = $service ?? new LivroService();
    }

    // $metodo = GET/POST/PUT/DELETE, $id = número da URL (ou null em /livros)
    public function tratar(string $metodo, ?int $id): void
    {
        try {
            if ($id === null) {
                if ($metodo === 'GET') {
                    $this->responder(200, array_map(fn($l) => $l->toArray(), $this->service->listar()));
                } elseif ($metodo === 'POST') {
                    $livro = $this->service->criar($this->lerJson());
                    $this->responder(201, $livro->toArray());
                } else {
                    $this->responder(405, ['erro' => 'Método não permitido.']);
                }
                return;
            }

            if ($metodo === 'GET') {
                $livro = $this->service->buscar($id);
                $livro ? $this->responder(200, $livro->toArray()) : $this->naoEncontrado();
            } elseif ($metodo === 'PUT') {
                $livro = $this->service->atualizar($id, $this->lerJson());
                $livro ? $this->responder(200, $livro->toArray()) : $this->naoEncontrado();
            } elseif ($metodo === 'DELETE') {
                $this->service->excluir($id) ? $this->responder(204, null) : $this->naoEncontrado();
            } else {
                $this->responder(405, ['erro' => 'Método não permitido.']);
            }
        } catch (InvalidArgumentException $e) {
            // dado inválido -> 400
            $this->responder(400, ['erro' => $e->getMessage()]);
        } catch (PDOException $e) {
            // 23000 = usuário não existe (chave estrangeira) -> 400; outros erros de banco -> 503
            if ($e->getCode() === '23000') {
                $this->responder(400, ['erro' => 'O usuario_id informado não existe.']);
            } else {
                $this->responder(503, ['erro' => 'Não foi possível acessar o banco de dados.']);
            }
        }
    }

    // Lê o corpo da requisição (JSON) e transforma em array.
    private function lerJson(): array
    {
        $dados = json_decode(file_get_contents('php://input'), true);
        if (!is_array($dados)) {
            throw new InvalidArgumentException('Envie um JSON válido no corpo da requisição.');
        }
        return $dados;
    }

    private function naoEncontrado(): void
    {
        $this->responder(404, ['erro' => 'Livro não encontrado.']);
    }

    // Define o código HTTP e escreve o JSON da resposta.
    private function responder(int $status, $dados): void
    {
        http_response_code($status);
        if ($dados !== null) {
            echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        }
    }
}
